<?php
/**
 * Safe, reversible editorial standardization for public school stories.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enrich legacy narratives without removing their verified facts or media.
 */
final class QAF_Core_Editorial_Standardizer {
	const VERSION          = '1.1.0';
	const VERSION_META     = '_qaf_editorial_standard_version';
	const BACKUP_META      = '_qaf_editorial_standard_backup';
	const REPORT_OPTION    = 'qaf_editorial_standard_report';
	const LOCK_OPTION      = 'qaf_editorial_standard_lock';
	const LOCK_TTL         = 600;
	const ADMIN_PAGE       = 'qaf-editorial-standard';
	const APPLY_ACTION     = 'qaf_apply_editorial_standard';
	const ROLLBACK_ACTION  = 'qaf_rollback_editorial_standard';

	/**
	 * The four public collections requested by the school.
	 *
	 * @return array<string,string>
	 */
	public static function post_types() {
		return array(
			'post'            => 'Berita',
			'qaf_gallery'     => 'Galeri',
			'qaf_achievement' => 'Prestasi',
			'qaf_work'        => 'Karya Siswa',
		);
	}

	/** Register the administration page and guarded actions. */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_post_' . self::APPLY_ACTION, array( __CLASS__, 'apply' ) );
		add_action( 'admin_post_' . self::ROLLBACK_ACTION, array( __CLASS__, 'rollback' ) );
	}

	/** Add a single-purpose page below the school administration menu. */
	public static function register_menu() {
		add_submenu_page(
			'qaf-school',
			'Standar Narasi Website',
			'Standar Narasi',
			'manage_options',
			self::ADMIN_PAGE,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Render the dry-run inventory. Nothing is written until the administrator
	 * deliberately submits one of the nonce-protected forms.
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$counts = self::inventory();
		$report = get_option( self::REPORT_OPTION, array() );
		?>
		<div class="wrap">
			<h1>Standar Narasi Website</h1>
			<p>Alat ini menata narasi publik menjadi artikel yang lebih nyaman dibaca. Fakta, gambar, video, shortcode, tanggal, kredit, dan tautan sumber lama tetap dipertahankan. Hanya postingan berstatus <strong>Terbit</strong> yang diproses.</p>
			<table class="widefat striped" style="max-width:760px">
				<thead><tr><th>Jenis konten</th><th>Terbit</th><th>Belum distandarkan</th><th>Sudah distandarkan</th></tr></thead>
				<tbody>
				<?php foreach ( self::post_types() as $post_type => $label ) : ?>
					<tr>
						<th scope="row"><?php echo esc_html( $label ); ?></th>
						<td><?php echo esc_html( number_format_i18n( $counts[ $post_type ]['total'] ) ); ?></td>
						<td><?php echo esc_html( number_format_i18n( $counts[ $post_type ]['pending'] ) ); ?></td>
						<td><?php echo esc_html( number_format_i18n( $counts[ $post_type ]['done'] ) ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<h2>Apa yang ditambahkan?</h2>
			<ul class="ul-disc">
				<li>Ringkasan pembuka berdasarkan judul dan ringkasan faktual setiap postingan.</li>
				<li>Struktur subjudul yang berbeda untuk Berita, Galeri, Prestasi, dan Karya Siswa.</li>
				<li>Paragraf nilai pembelajaran yang dipilih dari topik nyata, seperti bahasa, kreativitas, lingkungan, kesehatan, karakter, kompetisi, dan kesiapan kerja.</li>
				<li>Penutup kontekstual dan excerpt ringkas untuk kartu arsip serta mesin pencari.</li>
			</ul>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="<?php echo esc_attr( self::APPLY_ACTION ); ?>">
				<?php wp_nonce_field( self::APPLY_ACTION ); ?>
				<?php submit_button( 'Terapkan ke seluruh postingan terbit', 'primary', 'submit', false ); ?>
			</form>

			<?php if ( self::backup_count() > 0 ) : ?>
				<hr>
				<h2>Pemulihan</h2>
				<p>Cadangan narasi dan excerpt asli tersedia untuk <?php echo esc_html( number_format_i18n( self::backup_count() ) ); ?> postingan.</p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="<?php echo esc_attr( self::ROLLBACK_ACTION ); ?>">
					<?php wp_nonce_field( self::ROLLBACK_ACTION ); ?>
					<?php submit_button( 'Pulihkan seluruh narasi dari cadangan', 'secondary', 'submit', false ); ?>
				</form>
			<?php endif; ?>

			<?php if ( is_array( $report ) && ! empty( $report['message'] ) ) : ?>
				<hr>
				<h2>Hasil terakhir</h2>
				<p><?php echo esc_html( $report['message'] ); ?></p>
				<?php if ( ! empty( $report['errors'] ) && is_array( $report['errors'] ) ) : ?>
					<ul class="ul-disc"><?php foreach ( $report['errors'] as $error ) : ?><li><?php echo esc_html( $error ); ?></li><?php endforeach; ?></ul>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Count public records and current standardization state.
	 *
	 * @return array<string,array<string,int>>
	 */
	public static function inventory() {
		$counts = array();
		foreach ( self::post_types() as $post_type => $label ) {
			unset( $label );
			$total = self::query_ids( $post_type, false );
			$done  = self::query_ids( $post_type, true );
			$counts[ $post_type ] = array(
				'total'   => count( $total ),
				'done'    => count( $done ),
				'pending' => max( 0, count( $total ) - count( $done ) ),
			);
		}
		return $counts;
	}

	/**
	 * Apply the standard to every published target while retaining one immutable
	 * original backup per post.
	 */
	public static function apply() {
		self::guard_request( self::APPLY_ACTION );
		$lock = self::acquire_lock();
		if ( is_wp_error( $lock ) ) {
			wp_die( esc_html( $lock->get_error_message() ), '', array( 'response' => 409 ) );
		}

		$updated = 0;
		$skipped = 0;
		$errors  = array();
		try {
			foreach ( array_keys( self::post_types() ) as $post_type ) {
				foreach ( self::query_ids( $post_type, false ) as $post_id ) {
					if ( self::VERSION === get_post_meta( $post_id, self::VERSION_META, true ) ) {
						++$skipped;
						continue;
					}
					$result = self::standardize_post( $post_id );
					if ( is_wp_error( $result ) ) {
						$errors[] = sprintf( '#%1$d: %2$s', $post_id, $result->get_error_message() );
					} else {
						++$updated;
					}
				}
			}
		} finally {
			self::release_lock( $lock );
		}

		update_option(
			self::REPORT_OPTION,
			array(
				'message' => sprintf( '%1$d postingan diperbarui; %2$d dilewati karena sudah standar.', $updated, $skipped ),
				'errors'  => array_slice( $errors, 0, 20 ),
				'time'    => time(),
			),
			false
		);
		wp_safe_redirect( admin_url( 'admin.php?page=' . self::ADMIN_PAGE ) );
		exit;
	}

	/** Restore the immutable original content and excerpt for every backup. */
	public static function rollback() {
		self::guard_request( self::ROLLBACK_ACTION );
		$lock = self::acquire_lock();
		if ( is_wp_error( $lock ) ) {
			wp_die( esc_html( $lock->get_error_message() ), '', array( 'response' => 409 ) );
		}

		$restored = 0;
		$errors   = array();
		try {
			foreach ( array_keys( self::post_types() ) as $post_type ) {
				foreach ( self::query_ids( $post_type, false, true ) as $post_id ) {
					$backup = get_post_meta( $post_id, self::BACKUP_META, true );
					if ( ! is_array( $backup ) || ! isset( $backup['content'], $backup['excerpt'] ) ) {
						continue;
					}
					$result = wp_update_post(
						wp_slash(
							array(
								'ID'           => $post_id,
								'post_content' => (string) $backup['content'],
								'post_excerpt' => (string) $backup['excerpt'],
							)
						),
						true
					);
					if ( is_wp_error( $result ) ) {
						$errors[] = sprintf( '#%1$d: %2$s', $post_id, $result->get_error_message() );
						continue;
					}
					delete_post_meta( $post_id, self::VERSION_META );
					++$restored;
				}
			}
		} finally {
			self::release_lock( $lock );
		}

		update_option(
			self::REPORT_OPTION,
			array(
				'message' => sprintf( '%d postingan dipulihkan dari cadangan asli.', $restored ),
				'errors'  => array_slice( $errors, 0, 20 ),
				'time'    => time(),
			),
			false
		);
		wp_safe_redirect( admin_url( 'admin.php?page=' . self::ADMIN_PAGE ) );
		exit;
	}

	/**
	 * Standardize one public post.
	 *
	 * @param int $post_id Post ID.
	 * @return int|WP_Error
	 */
	public static function standardize_post( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post || 'publish' !== $post->post_status || ! isset( self::post_types()[ $post->post_type ] ) ) {
			return new WP_Error( 'qaf_editorial_invalid_post', 'Postingan bukan target terbit yang valid.' );
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return new WP_Error( 'qaf_editorial_forbidden', 'Akun tidak berhak menyunting postingan ini.' );
		}

		$backup = array(
			'content'  => $post->post_content,
			'excerpt'  => $post->post_excerpt,
			'modified' => $post->post_modified_gmt,
			'version'  => self::VERSION,
		);
		$stored_backup = get_post_meta( $post_id, self::BACKUP_META, true );
		if ( ! metadata_exists( 'post', $post_id, self::BACKUP_META ) ) {
			if ( ! add_post_meta( $post_id, self::BACKUP_META, $backup, true ) ) {
				return new WP_Error( 'qaf_editorial_backup_failed', 'Cadangan narasi asli tidak dapat dibuat.' );
			}
			$stored_backup = $backup;
		}

		// Every later editorial version is rebuilt from the immutable original.
		// This prevents nested leads/headings when the rules are improved.
		$source_post = clone $post;
		if ( is_array( $stored_backup ) && isset( $stored_backup['content'], $stored_backup['excerpt'] ) ) {
			$source_post->post_content = (string) $stored_backup['content'];
			$source_post->post_excerpt = (string) $stored_backup['excerpt'];
		}

		$new_content = self::build_content( $source_post );
		$new_excerpt = self::build_excerpt( $source_post );
		$result      = wp_update_post(
			wp_slash(
				array(
					'ID'           => $post_id,
					'post_content' => $new_content,
					'post_excerpt' => $new_excerpt,
				)
			),
			true
		);
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		if ( false === update_post_meta( $post_id, self::VERSION_META, self::VERSION ) ) {
			return new WP_Error( 'qaf_editorial_version_failed', 'Narasi tersimpan tetapi penanda versi gagal dicatat.' );
		}
		return (int) $result;
	}

	/**
	 * Build a factual lead, type-specific structure, topic lens, and closing.
	 * Original HTML, embeds, media, and links remain intact in the middle.
	 *
	 * @param WP_Post $post Post object.
	 * @return string
	 */
	public static function build_content( $post ) {
		$title   = self::clean_title( $post->post_title );
		$summary = self::summary_text( $post );
		$lens    = self::topic_lens( $title . ' ' . wp_strip_all_tags( $post->post_content ) );
		$config  = self::content_config( $post->post_type );
		$body    = trim( (string) $post->post_content );

		$lead = $summary;
		if ( '' === $lead ) {
			$lead = sprintf( $config['fallback'], $title );
		}
		$lead = self::ensure_sentence( $lead );

		$parts   = array();
		$parts[] = '<!-- qaf-editorial-standard:' . esc_html( self::VERSION ) . ' -->';
		$parts[] = self::paragraph_block( '<strong>' . esc_html( $title ) . '.</strong> ' . esc_html( $lead ), 'qaf-editorial-lead' );
		$parts[] = self::heading_block( $config['body_heading'] );
		if ( '' !== $body ) {
			$parts[] = $body;
		} else {
			$parts[] = self::paragraph_block( esc_html( sprintf( $config['fallback'], $title ) ) );
		}
		$parts[] = self::heading_block( $lens['heading'] );
		$parts[] = self::paragraph_block( esc_html( $lens['text'] ) );
		$parts[] = self::paragraph_block( esc_html( $config['closing'] ), 'qaf-editorial-closing' );
		return implode( "\n\n", $parts );
	}

	/**
	 * Normalize archive/search copy to a concise factual excerpt.
	 *
	 * @param WP_Post $post Post object.
	 * @return string
	 */
	public static function build_excerpt( $post ) {
		$text = self::summary_text( $post );
		if ( '' === $text ) {
			$text = sprintf( self::content_config( $post->post_type )['fallback'], self::clean_title( $post->post_title ) );
		}
		return wp_trim_words( self::ensure_sentence( $text ), 25, '…' );
	}

	/** Type-specific headings and non-clickbait closings. */
	private static function content_config( $post_type ) {
		$config = array(
			'post' => array(
				'body_heading' => 'Peristiwa dan konteks',
				'fallback'     => '%s menjadi bagian dari informasi dan perkembangan terbaru SMK Queen Al-Falah.',
				'closing'      => 'Ikuti kabar terbaru SMK Queen Al-Falah untuk mengetahui kegiatan, prestasi, karya, dan perkembangan sekolah berikutnya.',
			),
			'qaf_gallery' => array(
				'body_heading' => 'Cerita dalam dokumentasi',
				'fallback'     => 'Dokumentasi %s merekam salah satu momen pembelajaran dan kebersamaan di SMK Queen Al-Falah.',
				'closing'      => 'Jelajahi Galeri Kegiatan untuk melihat lebih banyak proses belajar, kreativitas, kebersamaan, dan pencapaian warga sekolah.',
			),
			'qaf_achievement' => array(
				'body_heading' => 'Capaian dan proses',
				'fallback'     => '%s mencatat salah satu capaian atau pengalaman berkompetisi warga SMK Queen Al-Falah.',
				'closing'      => 'Capaian ini menjadi penyemangat untuk terus belajar, berlatih, dan menghadirkan karya terbaik pada kesempatan berikutnya.',
			),
			'qaf_work' => array(
				'body_heading' => 'Tentang karya',
				'fallback'     => '%s menampilkan gagasan dan proses kreatif siswa SMK Queen Al-Falah.',
				'closing'      => 'Temukan karya siswa lainnya dan ikuti perkembangan ide, keterampilan, serta portofolio generasi kreatif SMK Queen Al-Falah.',
			),
		);
		return isset( $config[ $post_type ] ) ? $config[ $post_type ] : $config['post'];
	}

	/**
	 * Select one contextual learning lens from words already present in the post.
	 *
	 * @param string $haystack Title and body text.
	 * @return array<string,string>
	 */
	public static function topic_lens( $haystack ) {
		$text = strtolower( remove_accents( wp_strip_all_tags( $haystack ) ) );
		$lenses = array(
			array( array( 'english', 'bahasa inggris', 'speech', 'storytelling', 'news anchor', 'bilingual' ), 'Komunikasi yang terus berkembang', 'Kegiatan ini mengasah keberanian menyampaikan gagasan, ketepatan berbahasa, kemampuan menyusun pesan, dan kepercayaan diri saat berkomunikasi di hadapan publik.' ),
			array( array( 'maulid', 'ramadan', 'idulfitri', 'sholeh', 'agama', 'doa', 'tasyakur', 'silaturahmi' ), 'Karakter dan kebersamaan', 'Momen ini memperkuat nilai syukur, saling menghargai, kepedulian, dan kebersamaan sebagai bagian penting dari pembentukan karakter warga sekolah.' ),
			array( array( 'mpls', 'murid baru', 'bakat minat', 'integritas' ), 'Langkah awal mengenal potensi diri', 'Kegiatan pengenalan membantu murid baru memahami budaya sekolah, membangun pertemanan yang sehat, mengenali potensi diri, dan menyiapkan kebiasaan belajar yang bertanggung jawab.' ),
			array( array( 'kesehatan', 'anemia', 'puskesmas', 'uks', 'senam' ), 'Belajar menjaga kesehatan bersama', 'Kegiatan ini membantu siswa memahami kebiasaan hidup sehat, pencegahan masalah kesehatan, disiplin merawat diri, dan pentingnya saling mendukung di lingkungan sekolah.' ),
			array( array( 'peduli lingkungan', 'lingkungan hidup', 'sikap', 'tanam', 'kebun', 'hidroponik', 'bersih', 'keasrian', 'pangan' ), 'Kepedulian yang tumbuh dari kebiasaan', 'Proses yang terdokumentasi memperkuat kepedulian terhadap lingkungan, tanggung jawab bersama, ketelitian, dan kebiasaan merawat ruang belajar secara berkelanjutan.' ),
			array( array( 'juara', 'lomba', 'kompetisi', 'olimpiade', 'festival', 'lbb', 'finalis', 'peringkat', 'prestasi', 'apresiasi' ), 'Pengalaman berharga di balik capaian', 'Setiap proses kompetisi melatih disiplin, sportivitas, keberanian mencoba, ketekunan berlatih, dan kemampuan mengevaluasi diri—nilai yang tetap berarti apa pun hasil akhirnya.' ),
			array( array( 'guru', 'workshop', 'instrumen', 'komunitas belajar', 'mgmp', 'rapat dinas', 'wali kelas' ), 'Kolaborasi untuk pembelajaran bermutu', 'Kolaborasi pendidik membantu menyelaraskan perencanaan, pelaksanaan, dan evaluasi pembelajaran agar pengalaman belajar siswa semakin relevan, terarah, dan bermakna.' ),
			array( array( 'video', 'film', 'fotografi', 'desain', 'dkv', 'konten', 'karya', 'kreatif', 'dekorasi', 'multimedia' ), 'Kreativitas yang menjadi kompetensi', 'Di balik hasil yang terlihat, siswa belajar mengembangkan ide, menyusun konsep, bekerja dalam tim, menggunakan perangkat produksi, dan mengevaluasi karya agar pesan tersampaikan dengan baik.' ),
			array( array( 'industri', 'prakerin', 'pkl', 'karier', 'dunia kerja', 'administrasi' ), 'Bekal menuju dunia kerja', 'Pengalaman ini memperkuat kesiapan kerja melalui kedisiplinan, komunikasi profesional, ketelitian administrasi, pemecahan masalah, dan tanggung jawab terhadap tugas.' ),
			array( array( 'ujian', 'kompetensi', 'laboratorium', 'praktik', 'tka', 'anbk' ), 'Kompetensi yang diuji dan dikembangkan', 'Proses ini memberi ruang bagi siswa untuk menerapkan pengetahuan, bekerja secara teliti, mengelola waktu, dan menunjukkan kemampuan yang telah dibangun selama pembelajaran.' ),
		);

		foreach ( $lenses as $lens ) {
			foreach ( $lens[0] as $keyword ) {
				if ( self::keyword_matches( $text, $keyword ) ) {
					return array( 'heading' => $lens[1], 'text' => $lens[2] );
				}
			}
		}
		return array(
			'heading' => 'Nilai yang dibangun bersama',
			'text'    => 'Setiap kegiatan sekolah menjadi ruang untuk memperkuat tanggung jawab, kolaborasi, komunikasi, kemandirian, dan kemauan belajar sebagai bekal menghadapi tantangan berikutnya.',
		);
	}

	/**
	 * Match short abbreviations as complete tokens so, for example, "UKS" is
	 * never found inside "produksi" and "LBB" is not found inside another word.
	 *
	 * @param string $text    Normalized source text.
	 * @param string $keyword Keyword or phrase.
	 * @return bool
	 */
	private static function keyword_matches( $text, $keyword ) {
		if ( strlen( $keyword ) <= 4 && false === strpos( $keyword, ' ' ) ) {
			return (bool) preg_match( '/(?:^|[^a-z0-9])' . preg_quote( $keyword, '/' ) . '(?:$|[^a-z0-9])/u', $text );
		}
		return false !== strpos( $text, $keyword );
	}

	/** Return a factual summary from the manual excerpt or first body text. */
	private static function summary_text( $post ) {
		$text = trim( wp_strip_all_tags( strip_shortcodes( (string) $post->post_excerpt ) ) );
		if ( '' === $text ) {
			$text = trim( wp_strip_all_tags( strip_shortcodes( (string) $post->post_content ) ) );
		}
		$text = preg_replace( '/\s+/u', ' ', html_entity_decode( $text, ENT_QUOTES, get_bloginfo( 'charset' ) ) );
		return wp_trim_words( (string) $text, 38, '…' );
	}

	/** Remove the repeated site suffix used by several legacy gallery titles. */
	private static function clean_title( $title ) {
		$title = wp_strip_all_tags( (string) $title );
		$title = preg_replace( '/\s*\|\s*SMK Queen Al-Falah(?:\s+Kediri)?\s*$/iu', '', $title );
		return trim( (string) $title, " \t\n\r\0\x0B.-" );
	}

	/** Ensure prose ends like a complete sentence. */
	private static function ensure_sentence( $text ) {
		$text = trim( (string) $text );
		if ( '' !== $text && ! preg_match( '/[.!?…]$/u', $text ) ) {
			$text .= '.';
		}
		return $text;
	}

	/** Build a portable paragraph block. */
	private static function paragraph_block( $html, $class = '' ) {
		$attributes = $class ? ' {"className":"' . esc_attr( $class ) . '"}' : '';
		$class_attr = $class ? ' class="' . esc_attr( $class ) . '"' : '';
		return '<!-- wp:paragraph' . $attributes . ' --><p' . $class_attr . '>' . $html . '</p><!-- /wp:paragraph -->';
	}

	/** Build a portable H2 block. */
	private static function heading_block( $heading ) {
		return '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( $heading ) . '</h2><!-- /wp:heading -->';
	}

	/** Query published IDs, optionally filtered by state or backup presence. */
	private static function query_ids( $post_type, $done = false, $with_backup = false ) {
		$args = array(
			'post_type'              => $post_type,
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);
		if ( $done ) {
			$args['meta_key']   = self::VERSION_META;
			$args['meta_value'] = self::VERSION;
		} elseif ( $with_backup ) {
			$args['meta_key']     = self::BACKUP_META;
			$args['meta_compare'] = 'EXISTS';
		}
		return array_map( 'intval', get_posts( $args ) );
	}

	/** Count all immutable backups. */
	private static function backup_count() {
		$count = 0;
		foreach ( array_keys( self::post_types() ) as $post_type ) {
			$count += count( self::query_ids( $post_type, false, true ) );
		}
		return $count;
	}

	/** Capability, method, and nonce boundary shared by both mutations. */
	private static function guard_request( $action ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Akses ditolak.', '', array( 'response' => 403 ) );
		}
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			wp_die( 'POST diperlukan.', '', array( 'response' => 405 ) );
		}
		check_admin_referer( $action );
	}

	/** Claim a short-lived atomic lock. */
	private static function acquire_lock() {
		$lock = array( 'token' => wp_generate_uuid4(), 'created' => time() );
		if ( add_option( self::LOCK_OPTION, $lock, '', false ) ) {
			return $lock;
		}
		$existing = get_option( self::LOCK_OPTION, false );
		$created  = is_array( $existing ) && isset( $existing['created'] ) ? (int) $existing['created'] : 0;
		if ( $created > time() - self::LOCK_TTL ) {
			return new WP_Error( 'qaf_editorial_locked', 'Proses sebelumnya masih berjalan. Periksa kembali beberapa menit lagi.' );
		}
		delete_option( self::LOCK_OPTION );
		return add_option( self::LOCK_OPTION, $lock, '', false )
			? $lock
			: new WP_Error( 'qaf_editorial_lock_failed', 'Kunci proses tidak dapat dibuat.' );
	}

	/** Release the request lock. */
	private static function release_lock( $lock ) {
		$current = get_option( self::LOCK_OPTION, false );
		if ( $current === $lock ) {
			delete_option( self::LOCK_OPTION );
		}
	}
}
