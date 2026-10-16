<?php
/**
 * Non-destructive WebP optimization for Media Library images.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create WebP sidecars and serve them without deleting the source images.
 */
final class QAF_Core_WebP_Optimizer {
	const ADMIN_PAGE    = 'qaf-webp-optimizer';
	const AJAX_ACTION   = 'qaf_webp_optimize_batch';
	const NONCE_ACTION  = 'qaf_webp_optimizer';
	const VARIANTS_META = '_qaf_webp_variants';
	const PROCESSED_META = '_qaf_webp_processed_version';
	const BACKUP_META    = '_qaf_webp_original_metadata';
	const REPORT_OPTION = 'qaf_webp_optimizer_report';
	const SCHEMA_VERSION = '1.0.0';
	const BATCH_SIZE    = 5;
	const QUALITY       = 80;

	/** Register admin, conversion, and delivery hooks. */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( __CLASS__, 'ajax_optimize_batch' ) );
		add_filter( 'wp_generate_attachment_metadata', array( __CLASS__, 'optimize_new_upload' ), 20, 2 );

		add_filter( 'wp_get_attachment_url', array( __CLASS__, 'filter_attachment_url' ), 20, 2 );
		add_filter( 'wp_get_attachment_image_src', array( __CLASS__, 'filter_image_src' ), 20, 4 );
		add_filter( 'wp_calculate_image_srcset', array( __CLASS__, 'filter_srcset' ), 20, 5 );
		add_filter( 'the_content', array( __CLASS__, 'filter_html' ), 20 );
		add_filter( 'post_thumbnail_html', array( __CLASS__, 'filter_html' ), 20 );
		add_filter( 'widget_text_content', array( __CLASS__, 'filter_html' ), 20 );
	}

	/** Add the optimizer below the school administration menu. */
	public static function register_menu() {
		add_submenu_page(
			'qaf-school',
			'Optimasi Gambar WebP',
			'Optimasi Gambar',
			'manage_options',
			self::ADMIN_PAGE,
			array( __CLASS__, 'render_page' )
		);
	}

	/** Load the small batch runner only on this tool page. */
	public static function enqueue_admin_assets( $hook_suffix ) {
		if ( false === strpos( (string) $hook_suffix, self::ADMIN_PAGE ) ) {
			return;
		}

		wp_enqueue_script(
			'qaf-webp-optimizer',
			QAF_CORE_URL . 'assets/js/webp-optimizer.js',
			array(),
			QAF_CORE_VERSION,
			true
		);
		wp_localize_script(
			'qaf-webp-optimizer',
			'qafWebpOptimizer',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( self::NONCE_ACTION ),
				'action'  => self::AJAX_ACTION,
				'i18n'    => array(
					'running' => 'Sedang mengoptimalkan pustaka media…',
					'done'    => 'Optimasi selesai.',
					'failed'  => 'Optimasi berhenti karena server tidak dapat memproses batch berikutnya.',
				),
			)
		);
	}

	/** Render inventory, safeguards, and progress controls. */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$inventory = self::inventory();
		$report    = get_option( self::REPORT_OPTION, array() );
		?>
		<div class="wrap qaf-webp-admin">
			<h1>Optimasi Gambar WebP</h1>
			<p>Alat ini membuat WebP terkompresi untuk berkas JPEG/PNG utama dan semua ukuran thumbnail. Pilih mode aman untuk mempertahankan berkas asli, atau mode hemat penyimpanan untuk memindahkan metadata lalu menghapus JPG/PNG lama yang sudah berhasil diganti.</p>

			<table class="widefat striped" style="max-width:760px">
				<tbody>
					<tr><th scope="row">Jumlah gambar Media Library</th><td><?php echo esc_html( number_format_i18n( $inventory['total'] ) ); ?></td></tr>
					<tr><th scope="row">Sudah memiliki varian WebP</th><td><?php echo esc_html( number_format_i18n( $inventory['optimized'] ) ); ?></td></tr>
					<tr><th scope="row">Belum diproses</th><td><?php echo esc_html( number_format_i18n( $inventory['pending'] ) ); ?></td></tr>
					<tr><th scope="row">Kualitas kompresi</th><td><?php echo esc_html( (string) self::quality() ); ?> / 100</td></tr>
					<tr><th scope="row">Dukungan WebP hosting</th><td><?php echo self::server_supports_webp() ? '<strong style="color:#16754a">Tersedia</strong>' : '<strong style="color:#b32d2e">Tidak tersedia</strong>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td></tr>
				</tbody>
			</table>

			<h2>Perlindungan media</h2>
			<ul class="ul-disc">
				<li>Mode aman membuat WebP tanpa menghapus berkas sumber.</li>
				<li>Mode hemat penyimpanan baru menghapus JPG/PNG setelah seluruh ukuran berhasil dibuat, metadata attachment diperbarui, dan URL di isi artikel diganti.</li>
				<li>GIF, SVG, video, PDF, dan format lain dilewati agar animasi atau dokumen tidak rusak.</li>
				<li>Gambar WebP yang sudah ada tidak dikompresi ulang untuk mencegah penurunan kualitas berulang.</li>
				<li>Jika hosting tidak mendukung WebP, berkas asli tetap digunakan otomatis.</li>
			</ul>

			<p>
				<button type="button" class="button button-primary" id="qaf-webp-start">Konversi WebP — simpan gambar asli</button>
				<button type="button" class="button" id="qaf-webp-replace" style="border-color:#b32d2e;color:#b32d2e">Konversi WebP dan hapus JPG/PNG lama</button>
				<button type="button" class="button" id="qaf-webp-rebuild">Bangun ulang varian WebP</button>
			</p>
			<div id="qaf-webp-progress" hidden style="max-width:760px">
				<progress value="0" max="100" style="width:100%;height:22px"></progress>
				<p class="description" aria-live="polite">Menunggu proses.</p>
			</div>
			<div id="qaf-webp-result" class="notice notice-info inline" hidden><p aria-live="polite"></p></div>

			<?php if ( is_array( $report ) && ! empty( $report['time'] ) ) : ?>
				<h2>Hasil terakhir</h2>
				<p><?php echo esc_html( self::report_message( $report ) ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/** Return current attachment inventory. */
	public static function inventory() {
		$total_query = new WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => 'image',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		$optimized_query = new WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => 'image',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => self::PROCESSED_META,
			)
		);
		$total     = (int) $total_query->found_posts;
		$optimized = min( $total, (int) $optimized_query->found_posts );
		return array(
			'total'     => $total,
			'optimized' => $optimized,
			'pending'   => max( 0, $total - $optimized ),
		);
	}

	/** Process one bounded Media Library batch. */
	public static function ajax_optimize_batch() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Akses ditolak.' ), 403 );
		}
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );
		if ( ! self::server_supports_webp() ) {
			wp_send_json_error( array( 'message' => 'Hosting belum menyediakan encoder WebP melalui GD atau Imagick.' ), 500 );
		}

		$page  = max( 1, absint( $_POST['page'] ?? 1 ) );
		$force = ! empty( $_POST['force'] );
		$delete_originals = ! empty( $_POST['deleteOriginals'] );
		$query = new WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => 'image',
				'posts_per_page' => self::BATCH_SIZE,
				'paged'          => $page,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'fields'         => 'ids',
				'no_found_rows'  => false,
			)
		);

		$batch = array(
			'attachments' => 0,
			'converted'   => 0,
			'existing'    => 0,
			'skipped'     => 0,
			'deleted'     => 0,
			'reclaimed'   => 0,
			'errors'      => array(),
		);
		foreach ( $query->posts as $attachment_id ) {
			$result = self::optimize_attachment( (int) $attachment_id, $force, null, $delete_originals );
			++$batch['attachments'];
			$batch['converted'] += $result['converted'];
			$batch['existing']  += $result['existing'];
			$batch['skipped']   += $result['skipped'];
			$batch['deleted']   += $result['deleted'];
			$batch['reclaimed'] += $result['reclaimed'];
			$batch['errors']     = array_merge( $batch['errors'], $result['errors'] );
		}

		$report = 1 === $page
			? array( 'attachments' => 0, 'converted' => 0, 'existing' => 0, 'skipped' => 0, 'deleted' => 0, 'reclaimed' => 0, 'errors' => array(), 'time' => time() )
			: get_option( self::REPORT_OPTION, array() );
		foreach ( array( 'attachments', 'converted', 'existing', 'skipped', 'deleted', 'reclaimed' ) as $key ) {
			$report[ $key ] = (int) ( $report[ $key ] ?? 0 ) + (int) $batch[ $key ];
		}
		$report['errors'] = array_slice( array_merge( (array) ( $report['errors'] ?? array() ), $batch['errors'] ), 0, 30 );
		$report['time']   = time();
		update_option( self::REPORT_OPTION, $report, false );

		$total_pages = max( 1, (int) $query->max_num_pages );
		wp_send_json_success(
			array(
				'page'       => $page,
				'totalPages' => $total_pages,
				'total'      => (int) $query->found_posts,
				'done'       => $page >= $total_pages,
				'batch'      => $batch,
				'report'     => $report,
				'message'    => self::report_message( $report ),
			)
		);
	}

	/** Automatically optimize newly generated image sizes. */
	public static function optimize_new_upload( $metadata, $attachment_id ) {
		if ( wp_attachment_is_image( $attachment_id ) ) {
			self::optimize_attachment( (int) $attachment_id, false, is_array( $metadata ) ? $metadata : null );
		}
		return $metadata;
	}

	/**
	 * Create WebP sidecars for one attachment and its generated sizes.
	 *
	 * @param int        $attachment_id Attachment ID.
	 * @param bool       $force         Rebuild existing sidecars.
	 * @param array|null $metadata      Optional fresh metadata during upload.
	 * @param bool       $delete_originals Replace attachment metadata and delete old JPG/PNG.
	 * @return array<string,mixed>
	 */
	public static function optimize_attachment( $attachment_id, $force = false, $metadata = null, $delete_originals = false ) {
		$result = array( 'converted' => 0, 'existing' => 0, 'skipped' => 0, 'deleted' => 0, 'reclaimed' => 0, 'errors' => array() );
		if ( ! self::server_supports_webp() ) {
			$result['errors'][] = sprintf( '#%d: encoder WebP tidak tersedia pada hosting.', $attachment_id );
			return $result;
		}
		$file   = get_attached_file( $attachment_id );
		if ( ! $file || ! is_file( $file ) ) {
			$result['errors'][] = sprintf( '#%d: berkas sumber tidak ditemukan.', $attachment_id );
			return $result;
		}

		$metadata   = is_array( $metadata ) ? $metadata : wp_get_attachment_metadata( $attachment_id );
		$candidates = array( $file );
		$directory  = dirname( $file );
		if ( is_array( $metadata ) ) {
			if ( ! empty( $metadata['original_image'] ) ) {
				$candidates[] = trailingslashit( $directory ) . wp_basename( $metadata['original_image'] );
			}
			foreach ( (array) ( $metadata['sizes'] ?? array() ) as $size ) {
				if ( ! empty( $size['file'] ) ) {
					$candidates[] = trailingslashit( $directory ) . wp_basename( $size['file'] );
				}
			}
		}
		$candidates = array_values( array_unique( $candidates ) );
		$variants   = (array) get_post_meta( $attachment_id, self::VARIANTS_META, true );
		$file_map   = array();

		foreach ( $candidates as $source ) {
			$extension = strtolower( pathinfo( $source, PATHINFO_EXTENSION ) );
			if ( ! in_array( $extension, array( 'jpg', 'jpeg', 'png' ), true ) || ! is_file( $source ) ) {
				++$result['skipped'];
				continue;
			}

			$destination = $source . '.webp';
			$source_key  = self::upload_relative_path( $source );
			$dest_key    = self::upload_relative_path( $destination );
			if ( ! $force && is_file( $destination ) && filesize( $destination ) > 0 && filemtime( $destination ) >= filemtime( $source ) ) {
				$variants[ $source_key ] = $dest_key;
				$file_map[ wp_normalize_path( $source ) ] = wp_normalize_path( $destination );
				++$result['existing'];
				continue;
			}

			$editor = wp_get_image_editor( $source );
			if ( is_wp_error( $editor ) ) {
				$result['errors'][] = sprintf( '#%1$d %2$s: %3$s', $attachment_id, wp_basename( $source ), $editor->get_error_message() );
				continue;
			}
			$editor->set_quality( self::quality() );
			$saved = $editor->save( $destination, 'image/webp' );
			if ( is_wp_error( $saved ) || ! is_file( $destination ) || filesize( $destination ) < 1 ) {
				$result['errors'][] = sprintf(
					'#%1$d %2$s: %3$s',
					$attachment_id,
					wp_basename( $source ),
					is_wp_error( $saved ) ? $saved->get_error_message() : 'hasil WebP kosong'
				);
				continue;
			}
			$variants[ $source_key ] = $dest_key;
			$file_map[ wp_normalize_path( $source ) ] = wp_normalize_path( $destination );
			++$result['converted'];
		}

		if ( $delete_originals && empty( $result['errors'] ) && isset( $file_map[ wp_normalize_path( $file ) ] ) ) {
			$migrated = self::migrate_attachment( $attachment_id, $file, $metadata, $file_map );
			if ( is_wp_error( $migrated ) ) {
				$result['errors'][] = sprintf( '#%1$d: %2$s', $attachment_id, $migrated->get_error_message() );
			} else {
				$result['deleted']   += $migrated['deleted'];
				$result['reclaimed'] += $migrated['reclaimed'];
			}
		}

		if ( $variants ) {
			update_post_meta( $attachment_id, self::VARIANTS_META, $variants );
		}
		if ( empty( $result['errors'] ) ) {
			update_post_meta( $attachment_id, self::PROCESSED_META, self::SCHEMA_VERSION );
		}
		return $result;
	}

	/**
	 * Atomically point an attachment at WebP before removing replaceable sources.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param string $file          Current attached file.
	 * @param array $metadata       Current attachment metadata.
	 * @param array $file_map       Absolute source => WebP path.
	 * @return array|WP_Error
	 */
	private static function migrate_attachment( $attachment_id, $file, $metadata, $file_map ) {
		if ( ! is_array( $metadata ) || empty( $metadata['file'] ) ) {
			return new WP_Error( 'qaf_webp_metadata_missing', 'metadata gambar tidak lengkap; file lama dipertahankan.' );
		}
		$normalized_file = wp_normalize_path( $file );
		$new_file        = $file_map[ $normalized_file ] ?? '';
		if ( ! $new_file || ! is_file( $new_file ) ) {
			return new WP_Error( 'qaf_webp_full_missing', 'gambar WebP utama belum tersedia; file lama dipertahankan.' );
		}

		$backup = array(
			'attached_file' => get_post_meta( $attachment_id, '_wp_attached_file', true ),
			'metadata'      => $metadata,
			'mime_type'     => get_post_mime_type( $attachment_id ),
			'time'          => time(),
		);
		if ( ! metadata_exists( 'post', $attachment_id, self::BACKUP_META ) && ! add_post_meta( $attachment_id, self::BACKUP_META, $backup, true ) ) {
			return new WP_Error( 'qaf_webp_backup_failed', 'cadangan metadata gagal dibuat; file lama dipertahankan.' );
		}

		$new_metadata             = $metadata;
		$new_metadata['file']     = self::upload_relative_path( $new_file );
		$new_metadata['filesize'] = filesize( $new_file );
		$directory                = dirname( $normalized_file );
		foreach ( (array) ( $metadata['sizes'] ?? array() ) as $name => $size ) {
			if ( empty( $size['file'] ) ) {
				continue;
			}
			$old_size = wp_normalize_path( trailingslashit( $directory ) . wp_basename( $size['file'] ) );
			if ( ! isset( $file_map[ $old_size ] ) || ! is_file( $file_map[ $old_size ] ) ) {
				return new WP_Error( 'qaf_webp_size_missing', sprintf( 'ukuran %s belum berhasil dikonversi; file lama dipertahankan.', $name ) );
			}
			$new_metadata['sizes'][ $name ]['file']      = wp_basename( $file_map[ $old_size ] );
			$new_metadata['sizes'][ $name ]['mime-type'] = 'image/webp';
			$new_metadata['sizes'][ $name ]['filesize']  = filesize( $file_map[ $old_size ] );
		}
		if ( ! empty( $metadata['original_image'] ) ) {
			$old_original = wp_normalize_path( trailingslashit( $directory ) . wp_basename( $metadata['original_image'] ) );
			if ( ! isset( $file_map[ $old_original ] ) || ! is_file( $file_map[ $old_original ] ) ) {
				return new WP_Error( 'qaf_webp_original_missing', 'gambar original_image belum berhasil dikonversi; file lama dipertahankan.' );
			}
			$new_metadata['original_image'] = wp_basename( $file_map[ $old_original ] );
		}

		if ( false === update_attached_file( $attachment_id, $new_file ) ) {
			return new WP_Error( 'qaf_webp_attached_file_failed', 'referensi attachment gagal diperbarui; file lama dipertahankan.' );
		}
		$metadata_updated = wp_update_attachment_metadata( $attachment_id, $new_metadata );
		$post_updated     = wp_update_post( array( 'ID' => $attachment_id, 'post_mime_type' => 'image/webp' ), true );
		if ( false === $metadata_updated || is_wp_error( $post_updated ) ) {
			update_attached_file( $attachment_id, $file );
			wp_update_attachment_metadata( $attachment_id, $metadata );
			wp_update_post( array( 'ID' => $attachment_id, 'post_mime_type' => $backup['mime_type'] ) );
			return new WP_Error( 'qaf_webp_database_failed', 'database gagal diperbarui; metadata dikembalikan dan file lama dipertahankan.' );
		}

		$content_updated = self::replace_content_urls( $file_map );
		if ( is_wp_error( $content_updated ) ) {
			update_attached_file( $attachment_id, $file );
			wp_update_attachment_metadata( $attachment_id, $metadata );
			wp_update_post( array( 'ID' => $attachment_id, 'post_mime_type' => $backup['mime_type'] ) );
			return $content_updated;
		}
		$deleted   = 0;
		$reclaimed = 0;
		foreach ( array_keys( $file_map ) as $source ) {
			if ( ! is_file( $source ) ) {
				continue;
			}
			$bytes = (int) filesize( $source );
			wp_delete_file( $source );
			if ( ! is_file( $source ) ) {
				++$deleted;
				$reclaimed += $bytes;
			}
		}
		return array( 'deleted' => $deleted, 'reclaimed' => $reclaimed );
	}

	/** Replace old Media Library URLs in post bodies after a successful migration. */
	private static function replace_content_urls( $file_map ) {
		global $wpdb;
		$uploads     = wp_get_upload_dir();
		$baseurl     = rtrim( (string) ( $uploads['baseurl'] ?? '' ), '/' );
		$replacements = array();
		foreach ( $file_map as $source => $destination ) {
			$replacements[ $baseurl . '/' . self::upload_relative_path( $source ) ] = $baseurl . '/' . self::upload_relative_path( $destination );
		}
		foreach ( $replacements as $old_url => $new_url ) {
			$like = '%' . $wpdb->esc_like( $old_url ) . '%';
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_content, post_excerpt FROM {$wpdb->posts} WHERE post_content LIKE %s OR post_excerpt LIKE %s", $like, $like ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			foreach ( (array) $rows as $row ) {
				$content = str_replace( $old_url, $new_url, (string) $row->post_content );
				$excerpt = str_replace( $old_url, $new_url, (string) $row->post_excerpt );
				if ( $content !== $row->post_content || $excerpt !== $row->post_excerpt ) {
					$updated = wp_update_post( wp_slash( array( 'ID' => (int) $row->ID, 'post_content' => $content, 'post_excerpt' => $excerpt ) ), true );
					if ( is_wp_error( $updated ) ) {
						return new WP_Error( 'qaf_webp_content_update_failed', sprintf( 'referensi gambar pada postingan #%d gagal diperbarui; file lama dipertahankan.', (int) $row->ID ) );
					}
				}
			}
		}
		return true;
	}

	/** Serve the WebP sidecar for an attachment URL on the public site. */
	public static function filter_attachment_url( $url, $attachment_id ) {
		unset( $attachment_id );
		return self::should_filter_output() ? self::webp_url( $url ) : $url;
	}

	/** Serve WebP for one resolved attachment image. */
	public static function filter_image_src( $image, $attachment_id, $size, $icon ) {
		unset( $attachment_id, $size, $icon );
		if ( self::should_filter_output() && is_array( $image ) && ! empty( $image[0] ) ) {
			$image[0] = self::webp_url( $image[0] );
		}
		return $image;
	}

	/** Serve WebP URLs in responsive image candidates. */
	public static function filter_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
		unset( $size_array, $image_src, $image_meta, $attachment_id );
		if ( ! self::should_filter_output() || ! is_array( $sources ) ) {
			return $sources;
		}
		foreach ( $sources as &$source ) {
			if ( ! empty( $source['url'] ) ) {
				$source['url'] = self::webp_url( $source['url'] );
			}
		}
		unset( $source );
		return $sources;
	}

	/** Rewrite direct Media Library image URLs embedded in public HTML. */
	public static function filter_html( $html ) {
		if ( ! self::should_filter_output() || ! is_string( $html ) || '' === $html ) {
			return $html;
		}
		$uploads = wp_get_upload_dir();
		$baseurl = rtrim( (string) ( $uploads['baseurl'] ?? '' ), '/' );
		if ( '' === $baseurl ) {
			return $html;
		}
		$pattern = '~' . preg_quote( $baseurl, '~' ) . '/[^\s\"\'\(\)<>]+\.(?:jpe?g|png)(?:\?[^\s\"\'\(\)<>]*)?(?=[\s\"\'\(\)<>]|$)~i';
		return preg_replace_callback(
			$pattern,
			static function ( $match ) {
				return self::webp_url( $match[0] );
			},
			$html
		);
	}

	/** Resolve a sidecar URL only when the generated file is valid. */
	public static function webp_url( $url ) {
		if ( ! is_string( $url ) || '' === $url || preg_match( '~\.webp(?:\?|$)~i', $url ) ) {
			return $url;
		}
		$uploads = wp_get_upload_dir();
		$baseurl = rtrim( (string) ( $uploads['baseurl'] ?? '' ), '/' );
		$basedir = wp_normalize_path( rtrim( (string) ( $uploads['basedir'] ?? '' ), '/\\' ) );
		if ( '' === $baseurl || '' === $basedir || 0 !== strpos( $url, $baseurl . '/' ) ) {
			return $url;
		}
		$parts      = explode( '?', $url, 2 );
		$clean_url  = $parts[0];
		$query      = isset( $parts[1] ) ? '?' . $parts[1] : '';
		$relative   = ltrim( rawurldecode( substr( $clean_url, strlen( $baseurl ) + 1 ) ), '/\\' );
		if ( preg_match( '~(?:^|/)\.\.(?:/|$)~', str_replace( '\\', '/', $relative ) ) ) {
			return $url;
		}
		$source     = wp_normalize_path( trailingslashit( $basedir ) . ltrim( $relative, '/\\' ) );
		$destination = $source . '.webp';
		if ( 0 !== strpos( $source, trailingslashit( $basedir ) ) || ! is_file( $destination ) || filesize( $destination ) < 1 ) {
			return $url;
		}
		return $clean_url . '.webp' . $query;
	}

	/** Keep admin, feeds, REST, previews, and unsupported clients on originals. */
	private static function should_filter_output() {
		if ( is_admin() || is_feed() || is_preview() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return false;
		}
		$accept = isset( $_SERVER['HTTP_ACCEPT'] ) ? (string) $_SERVER['HTTP_ACCEPT'] : '';
		return '' === $accept || false !== stripos( $accept, 'image/webp' );
	}

	/** Convert an absolute upload path into a portable metadata key. */
	private static function upload_relative_path( $path ) {
		$uploads = wp_get_upload_dir();
		$basedir = wp_normalize_path( rtrim( (string) ( $uploads['basedir'] ?? '' ), '/\\' ) );
		$path    = wp_normalize_path( $path );
		return 0 === strpos( $path, trailingslashit( $basedir ) ) ? substr( $path, strlen( trailingslashit( $basedir ) ) ) : wp_basename( $path );
	}

	/** Allow a controlled quality adjustment without accepting unsafe values. */
	private static function quality() {
		return max( 60, min( 90, (int) apply_filters( 'qaf_webp_quality', self::QUALITY ) ) );
	}

	/** Check the active WordPress image editor before offering conversion. */
	private static function server_supports_webp() {
		return wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) );
	}

	/** Human-readable cumulative batch result. */
	private static function report_message( $report ) {
		return sprintf(
			'%1$d gambar diperiksa; %2$d berkas WebP dibuat; %3$d sudah tersedia; %4$d format/berkas dilewati; %5$d file lama dihapus; %6$s ruang dibebaskan; %7$d kesalahan.',
			(int) ( $report['attachments'] ?? 0 ),
			(int) ( $report['converted'] ?? 0 ),
			(int) ( $report['existing'] ?? 0 ),
			(int) ( $report['skipped'] ?? 0 ),
			(int) ( $report['deleted'] ?? 0 ),
			size_format( (int) ( $report['reclaimed'] ?? 0 ), 1 ),
			count( (array) ( $report['errors'] ?? array() ) )
		);
	}
}
