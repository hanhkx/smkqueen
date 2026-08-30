<?php
/**
 * Structured school organization page and teacher-photo matching.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provision and render the official 2026/2027 organization structure.
 */
final class QAF_Core_Organization {
	const PAGE_OPTION    = 'qaf_organization_page_id';
	const VERSION_OPTION = 'qaf_organization_schema_version';
	const SCHEMA_VERSION = '1.0.0';
	const SHORTCODE      = 'qaf_organization';

	/** Register page, shortcode, and front-end assets. */
	public static function init() {
		add_shortcode( self::SHORTCODE, array( __CLASS__, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_upgrade' ) );
	}

	/** Create or migrate the organization page without replacing editor content. */
	public static function activate() {
		self::ensure_page();
		update_option( self::VERSION_OPTION, self::SCHEMA_VERSION, false );
	}

	/** Run non-destructive migrations after a plugin update. */
	public static function maybe_upgrade() {
		if ( self::SCHEMA_VERSION === get_option( self::VERSION_OPTION ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		self::activate();
	}

	/** Ensure the canonical page exists and replace only the old demo placeholder. */
	private static function ensure_page() {
		$page_id = absint( get_option( self::PAGE_OPTION ) );
		$page    = $page_id ? get_post( $page_id ) : null;
		if ( ! $page instanceof WP_Post || 'page' !== $page->post_type || 'trash' === $page->post_status ) {
			$page = get_page_by_path( 'profil-sekolah/struktur-organisasi', OBJECT, 'page' );
		}
		if ( ! $page instanceof WP_Post ) {
			$matches = get_posts(
				array(
					'post_type'      => 'page',
					'post_status'    => array( 'publish', 'draft', 'private', 'pending' ),
					'name'           => 'struktur-organisasi',
					'posts_per_page' => 1,
					'no_found_rows'  => true,
				)
			);
			$page = $matches ? $matches[0] : null;
		}

		if ( ! $page instanceof WP_Post || 'trash' === $page->post_status ) {
			$profile_page = get_page_by_path( 'profil-sekolah', OBJECT, 'page' );
			$page_id = wp_insert_post(
				array(
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'post_title'     => 'Struktur Organisasi',
					'post_name'      => 'struktur-organisasi',
					'post_content'   => '<!-- wp:shortcode -->[' . self::SHORTCODE . ']<!-- /wp:shortcode -->',
					'post_parent'    => $profile_page instanceof WP_Post ? $profile_page->ID : 0,
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'meta_input'     => array( '_wp_page_template' => 'page-templates/template-full-width.php' ),
				),
				true
			);

			if ( ! is_wp_error( $page_id ) ) {
				update_option( self::PAGE_OPTION, (int) $page_id, false );
			}
			return;
		}

		$page_id = (int) $page->ID;
		update_option( self::PAGE_OPTION, $page_id, false );

		$has_shortcode   = has_shortcode( $page->post_content, self::SHORTCODE );
		$is_placeholder  = false !== strpos( $page->post_content, 'Tambahkan struktur organisasi terbaru yang telah disahkan' );
		if ( ! $has_shortcode && $is_placeholder ) {
			wp_update_post(
				array(
					'ID'           => $page_id,
					'post_content' => '<!-- wp:shortcode -->[' . self::SHORTCODE . ']<!-- /wp:shortcode -->',
				)
			);
		}

		$template = get_post_meta( $page_id, '_wp_page_template', true );
		if ( ! $template || 'default' === $template ) {
			update_post_meta( $page_id, '_wp_page_template', 'page-templates/template-full-width.php' );
		}
	}

	/** Load the isolated organization layout only on its canonical page. */
	public static function enqueue_assets() {
		$page_id = absint( get_option( self::PAGE_OPTION ) );
		if ( ! is_page( $page_id ? $page_id : 'struktur-organisasi' ) ) {
			return;
		}

		wp_enqueue_style(
			'qaf-organization',
			QAF_CORE_URL . 'assets/css/organization.css',
			array(),
			QAF_CORE_VERSION
		);
	}

	/** Return versioned organization data from the dedicated data file. */
	private static function sections() {
		static $sections = null;
		if ( null === $sections ) {
			$sections = require QAF_CORE_PATH . 'includes/organization-data.php';
		}
		return is_array( $sections ) ? $sections : array();
	}

	/**
	 * Normalize a public name for matching against Guru/Tendik post titles.
	 *
	 * Academic degrees are removed, punctuation is flattened, and common
	 * honorifics are ignored without altering the public display name.
	 *
	 * @param string $name Display name.
	 * @return string
	 */
	private static function normalize_name( $name ) {
		$name = remove_accents( wp_strip_all_tags( (string) $name ) );
		$name = preg_replace( '/,.*/u', '', $name );
		$name = strtolower( $name );
		$name = preg_replace( '/[^a-z0-9]+/', ' ', $name );
		$name = trim( preg_replace( '/\s+/', ' ', $name ) );
		$name = preg_replace( '/^(ns|h|drs|dr|kh)\s+/', '', $name );
		$name = preg_replace( '/^(ns|h|drs|dr|kh)\s+/', '', $name );
		return trim( $name );
	}

	/** Build a normalized map of published Guru/Tendik profiles. */
	private static function teacher_profiles() {
		static $profiles = null;
		if ( null !== $profiles ) {
			return $profiles;
		}

		$profiles = array();
		$query    = new WP_Query(
			array(
				'post_type'              => 'qaf_teacher',
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'no_found_rows'          => true,
				'orderby'                => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
			)
		);

		foreach ( $query->posts as $post ) {
			$key = self::normalize_name( $post->post_title );
			if ( $key && ! isset( $profiles[ $key ] ) ) {
				$profiles[ $key ] = $post;
			}
		}
		return $profiles;
	}

	/** Find a Guru/Tendik profile using the canonical name and known aliases. */
	private static function profile_for( $member ) {
		if ( ! empty( $member['collective'] ) ) {
			return null;
		}

		$profiles = self::teacher_profiles();
		$names    = array_merge(
			array( isset( $member['name'] ) ? $member['name'] : '' ),
			isset( $member['aliases'] ) && is_array( $member['aliases'] ) ? $member['aliases'] : array()
		);
		foreach ( $names as $name ) {
			$key = self::normalize_name( $name );
			if ( $key && isset( $profiles[ $key ] ) ) {
				return $profiles[ $key ];
			}
		}

		// Support profile titles that append a degree without a comma.
		foreach ( $names as $name ) {
			$key = self::normalize_name( $name );
			if ( strlen( $key ) < 8 ) {
				continue;
			}
			foreach ( $profiles as $profile_key => $profile ) {
				if ( 0 === strpos( $profile_key, $key . ' ' ) || 0 === strpos( $key, $profile_key . ' ' ) ) {
					return $profile;
				}
			}
		}

		return null;
	}

	/** Produce a short accessible initial when no approved portrait exists. */
	private static function initials( $name ) {
		$plain = preg_replace( '/,.*/u', '', wp_strip_all_tags( (string) $name ) );
		$words = preg_split( '/\s+/u', trim( $plain ) );
		$chars = '';
		foreach ( (array) $words as $word ) {
			if ( $word && ! in_array( strtolower( trim( $word, ". " ) ), array( 'h', 'ns', 'dr', 'drs', 'kh' ), true ) ) {
				$chars .= function_exists( 'mb_substr' ) ? mb_substr( $word, 0, 1 ) : substr( $word, 0, 1 );
			}
			if ( 2 <= strlen( $chars ) ) {
				break;
			}
		}
		return strtoupper( $chars ? $chars : 'QA' );
	}

	/**
	 * Describe the practical responsibility of one role in its section.
	 *
	 * @param string $role    Position label.
	 * @param string $section Section title.
	 * @return string
	 */
	private static function duty_for( $role, $section ) {
		$role_lower    = strtolower( remove_accents( $role ) );
		$section       = wp_strip_all_tags( $section );
		$section_lower = strtolower( remove_accents( $section ) );

		if ( false !== strpos( $role_lower, 'kepala sekolah' ) ) {
			return 'Memimpin tata kelola sekolah, menetapkan kebijakan, mengoordinasikan seluruh program, serta memastikan mutu dan akuntabilitas layanan pendidikan.';
		}
		if ( false !== strpos( $role_lower, 'wakil kepala sekolah bidang' ) ) {
			$field = trim( preg_replace( '/^.*bidang/i', '', $role ) );
			return 'Menyusun, menjalankan, memantau, dan mengevaluasi program bidang ' . $field . ' serta melaporkannya kepada Kepala Sekolah.';
		}
		if ( false !== strpos( $role_lower, 'kepala program keahlian' ) ) {
			return 'Mengelola kurikulum, pembelajaran, sarana praktik, kemitraan industri, dan peningkatan mutu pada program keahlian terkait.';
		}
		if ( false !== strpos( $role_lower, 'wali kelas' ) ) {
			$class = trim( preg_replace( '/^Wali Kelas/i', '', $role ) );
			return 'Mengelola administrasi, komunikasi orang tua, pemantauan akademik, kedisiplinan, dan pembinaan peserta didik kelas ' . $class . '.';
		}
		if ( false !== strpos( $role_lower, 'penanggung jawab' ) || false !== strpos( $role_lower, 'dewan pengarah' ) ) {
			return 'Memberikan arah kebijakan, memastikan pelaksanaan sesuai ketentuan, serta mengendalikan mutu dan pertanggungjawaban ' . $section . '.';
		}
		if ( false !== strpos( $role_lower, 'ketua' ) || false !== strpos( $role_lower, 'kepala bagian' ) || false !== strpos( $role_lower, 'kepala tata usaha' ) || false !== strpos( $role_lower, 'kepala laboratorium' ) || false !== strpos( $role_lower, 'kepala perpustakaan' ) ) {
			return 'Memimpin perencanaan, pembagian tugas, pelaksanaan, dokumentasi, evaluasi, dan pelaporan kegiatan ' . $section . '.';
		}
		if ( false !== strpos( $role_lower, 'sekretaris' ) ) {
			return 'Mengelola agenda, surat-menyurat, notula, arsip, data, dan laporan kegiatan ' . $section . '.';
		}
		if ( false !== strpos( $role_lower, 'bendahara' ) ) {
			return 'Merencanakan, mencatat, mengendalikan, dan mempertanggungjawabkan penerimaan serta pengeluaran sesuai ketentuan keuangan sekolah.';
		}
		if ( false !== strpos( $role_lower, 'koordinator' ) ) {
			return 'Mengoordinasikan personel, jadwal, sumber daya, pelaksanaan program, evaluasi, dan tindak lanjut pada ' . $section . '.';
		}
		if ( false !== strpos( $role_lower, 'pembina' ) || false !== strpos( $role_lower, 'pendamping' ) ) {
			return 'Menyusun program pembinaan, mendampingi kegiatan, menjaga keselamatan, memantau perkembangan peserta, dan mengevaluasi hasil kegiatan.';
		}
		if ( false !== strpos( $role_lower, 'operator sekolah' ) || false !== strpos( $role_lower, 'pengelola data' ) ) {
			return 'Mengelola validitas, pembaruan, sinkronisasi, keamanan, dan pelaporan data sekolah pada sistem resmi.';
		}
		if ( false !== strpos( $role_lower, 'pengelola website' ) || false !== strpos( $role_lower, 'editing' ) || ( false !== strpos( $section_lower, 'website' ) && false !== strpos( $role_lower, 'pengembangan' ) ) ) {
			return 'Merencanakan, memproduksi, memeriksa, menerbitkan, dan memelihara informasi digital sekolah sesuai standar editorial dan perlindungan data.';
		}
		if ( 0 === strpos( $role_lower, 'bidang ' ) ) {
			return 'Menangani penilaian, verifikasi bukti, analisis, dan tindak lanjut sesuai fokus ' . $role . ' dalam pelaksanaan ' . $section . '.';
		}
		if ( false !== strpos( $role_lower, 'komite skema' ) ) {
			return 'Menyusun, meninjau, dan menjaga kesesuaian skema sertifikasi dengan standar kompetensi serta kebutuhan program keahlian.';
		}
		if ( false !== strpos( $role_lower, 'laboran' ) || false !== strpos( $role_lower, 'teknisi' ) ) {
			return 'Menyiapkan perangkat dan ruang praktik, melakukan inventarisasi serta perawatan, menangani gangguan teknis, dan menjaga keselamatan laboratorium.';
		}
		if ( false !== strpos( $role_lower, 'staf tata usaha' ) || false !== strpos( $role_lower, 'tata usaha' ) ) {
			return 'Melaksanakan layanan administrasi, persuratan, arsip, data, dan dukungan operasional sekolah sesuai pembagian tugas.';
		}
		if ( false !== strpos( $role_lower, 'satpam' ) || false !== strpos( $role_lower, 'penjaga sekolah' ) || false !== strpos( $role_lower, 'pengatur lalu lintas' ) ) {
			return 'Menjaga keamanan, ketertiban, akses lingkungan, keselamatan warga sekolah, dan pelaporan kejadian selama jadwal tugas.';
		}
		if ( false !== strpos( $role_lower, 'kebersihan' ) || false !== strpos( $role_lower, 'pesuruh' ) ) {
			return 'Menjaga kebersihan, kerapian, sanitasi, dan kesiapan fasilitas umum serta membantu kebutuhan operasional sekolah.';
		}
		if ( false !== strpos( $role_lower, 'kantin' ) ) {
			return 'Mengelola pelayanan kantin yang tertib, bersih, aman, sehat, serta mematuhi ketentuan sekolah.';
		}
		if ( false !== strpos( $role_lower, 'komite sekolah' ) ) {
			return 'Memberikan pertimbangan, dukungan, pengawasan, dan penghubung aspirasi orang tua serta masyarakat dalam penyelenggaraan sekolah.';
		}
		if ( false !== strpos( $role_lower, 'anggota' ) || false !== strpos( $role_lower, 'seluruh ' ) ) {
			return 'Melaksanakan program kerja, menyiapkan data dan bukti kegiatan, berkoordinasi dengan tim, serta menindaklanjuti hasil evaluasi ' . $section . '.';
		}

		return 'Menjalankan tugas teknis dan administratif sesuai jabatan, berkoordinasi dengan penanggung jawab, serta mendokumentasikan hasil kerja pada ' . $section . '.';
	}

	/** Render one person or collective assignment card. */
	private static function render_member( $member, $section_title ) {
		$name    = isset( $member['name'] ) ? $member['name'] : '';
		$role    = isset( $member['role'] ) ? $member['role'] : '';
		$duty    = ! empty( $member['duty'] ) ? $member['duty'] : self::duty_for( $role, $section_title );
		$profile = self::profile_for( $member );
		?>
		<article class="qaf-org-person">
			<div class="qaf-org-person__portrait">
				<?php if ( $profile && has_post_thumbnail( $profile ) ) : ?>
					<?php echo get_the_post_thumbnail( $profile, 'queen-person', array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => $name ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php else : ?>
					<span aria-hidden="true"><?php echo esc_html( self::initials( $name ) ); ?></span>
				<?php endif; ?>
			</div>
			<div class="qaf-org-person__body">
				<h3>
					<?php if ( $profile ) : ?>
						<a href="<?php echo esc_url( get_permalink( $profile ) ); ?>"><?php echo esc_html( $name ); ?></a>
					<?php else : ?>
						<?php echo esc_html( $name ); ?>
					<?php endif; ?>
				</h3>
				<p class="qaf-org-person__role"><?php echo esc_html( $role ); ?></p>
				<p class="qaf-org-person__duty"><strong><?php esc_html_e( 'Tupoksi:', 'queen-alfalah-core' ); ?></strong> <?php echo esc_html( $duty ); ?></p>
			</div>
		</article>
		<?php
	}

	/** Render the complete organization page. */
	public static function render_shortcode() {
		$sections = self::sections();
		if ( ! $sections ) {
			return '';
		}

		ob_start();
		?>
		<section class="qaf-org" aria-labelledby="qaf-org-title">
			<header class="qaf-org-hero">
				<div>
					<p class="qaf-org-eyebrow"><?php esc_html_e( 'Struktur Kelembagaan', 'queen-alfalah-core' ); ?></p>
					<h2 id="qaf-org-title"><?php esc_html_e( 'SMK Queen Al-Falah', 'queen-alfalah-core' ); ?></h2>
					<p><?php esc_html_e( 'Tahun Pelajaran 2026/2027 — berdasarkan SK Kepala Sekolah Nomor 400.3/347/SMK.QA/2026.', 'queen-alfalah-core' ); ?></p>
				</div>
				<div class="qaf-org-hero__meta">
					<strong><?php echo esc_html( count( $sections ) ); ?></strong>
					<span><?php esc_html_e( 'bidang dan tim kerja', 'queen-alfalah-core' ); ?></span>
				</div>
			</header>

			<div class="qaf-org-note">
				<p><strong><?php esc_html_e( 'Foto tersinkron otomatis.', 'queen-alfalah-core' ); ?></strong> <?php esc_html_e( 'Foto diambil dari entri Guru & Tendik dengan nama yang sama. Inisial tampil jika foto atau profil belum dipublikasikan.', 'queen-alfalah-core' ); ?></p>
				<p><?php esc_html_e( 'Ejaan nama, gelar, dan penetapan jabatan tetap perlu dikonfirmasi oleh Tata Usaha sebelum laman produksi diumumkan.', 'queen-alfalah-core' ); ?></p>
			</div>

			<nav class="qaf-org-index" aria-label="<?php esc_attr_e( 'Daftar bagian struktur organisasi', 'queen-alfalah-core' ); ?>">
				<ol>
					<?php foreach ( $sections as $index => $section ) : ?>
						<li><a href="#qaf-org-<?php echo esc_attr( sanitize_title( $section['title'] ) ); ?>"><span><?php echo esc_html( $index + 1 ); ?></span><?php echo esc_html( $section['title'] ); ?></a></li>
					<?php endforeach; ?>
				</ol>
			</nav>

			<div class="qaf-org-sections">
				<?php foreach ( $sections as $index => $section ) : ?>
					<section id="qaf-org-<?php echo esc_attr( sanitize_title( $section['title'] ) ); ?>" class="qaf-org-section">
						<header class="qaf-org-section__header">
							<span><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<div>
								<h2><?php echo esc_html( $section['title'] ); ?></h2>
								<?php if ( ! empty( $section['overview'] ) ) : ?><p><?php echo esc_html( $section['overview'] ); ?></p><?php endif; ?>
							</div>
						</header>
						<div class="qaf-org-people">
							<?php foreach ( $section['members'] as $member ) : ?>
								<?php self::render_member( $member, $section['title'] ); ?>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}
}
