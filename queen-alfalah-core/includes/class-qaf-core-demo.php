<?php
/**
 * Safe, repeatable demo-site setup.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create the initial site structure without replacing existing content.
 */
final class QAF_Core_Demo {
	/** Admin-post action. */
	const ACTION = 'qaf_core_import_demo';

	/** Nonce action. */
	const NONCE_ACTION = 'qaf_core_import_demo_site';

	/** Private marker shared by demo-created posts and menu items. */
	const MARKER_META = '_qaf_demo_key';

	/** Import version option. */
	const VERSION_OPTION = 'qaf_core_demo_version';

	/**
	 * Attach the setup screen and form handler.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 30 );
		add_action( 'admin_post_' . self::ACTION, array( __CLASS__, 'handle_import' ) );
	}

	/**
	 * Register the setup submenu below the shared School menu.
	 *
	 * @return void
	 */
	public static function register_menu() {
		add_submenu_page(
			'qaf-school',
			'Penyiapan Demo',
			'Penyiapan Demo',
			QAF_Core_Settings::get_capability(),
			'qaf-demo-setup',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Render a transparent, nonce-protected setup form.
	 *
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( QAF_Core_Settings::get_capability() ) ) {
			wp_die( esc_html__( 'Anda tidak memiliki izin untuk mengakses halaman ini.', 'queen-alfalah-core' ) );
		}

		$result = self::consume_result();
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Penyiapan Demo Queen Al-Falah', 'queen-alfalah-core' ); ?></h1>
			<?php if ( $result ) : ?>
				<?php self::render_result_notice( $result ); ?>
			<?php endif; ?>

			<?php if ( ! self::is_queen_theme_active() ) : ?>
				<div class="notice notice-warning inline">
					<p><?php echo esc_html__( 'Tema Queen Al-Falah belum aktif. Konten tetap dapat dibuat, tetapi lokasi menu baru dapat ditetapkan setelah tema atau child theme Queen Al-Falah aktif.', 'queen-alfalah-core' ); ?></p>
				</div>
			<?php endif; ?>

			<p><?php echo esc_html__( 'Alat ini membuat kerangka awal yang langsung dikenali tema. Jalankan pada instalasi baru atau staging, lalu tinjau setiap data sebelum situs dibuka untuk publik.', 'queen-alfalah-core' ); ?></p>

			<h2><?php echo esc_html__( 'Yang akan disiapkan', 'queen-alfalah-core' ); ?></h2>
			<ul class="ul-disc">
				<li><?php echo esc_html__( 'Halaman inti, Beranda statis, dan halaman Berita.', 'queen-alfalah-core' ); ?></li>
				<li><?php echo esc_html__( 'Empat program keahlian, tujuh draf ekstrakurikuler untuk diverifikasi, dan enam akses layanan.', 'queen-alfalah-core' ); ?></li>
				<li><?php echo esc_html__( 'Menu utama, menu atas, menu layanan, dan menu footer.', 'queen-alfalah-core' ); ?></li>
				<li><?php echo esc_html__( 'Draf berita, pengumuman, dan agenda yang wajib diverifikasi sebelum diterbitkan.', 'queen-alfalah-core' ); ?></li>
			</ul>

			<div class="notice notice-info inline">
				<p><strong><?php echo esc_html__( 'Aman dijalankan kembali:', 'queen-alfalah-core' ); ?></strong> <?php echo esc_html__( 'importer memakai penanda dan slug stabil, tidak membuat duplikat, tidak menghapus data, dan tidak menimpa konten atau menu yang sudah ada. Pengaturan halaman depan akan diarahkan ke halaman Beranda dan Berita yang disiapkan.', 'queen-alfalah-core' ); ?></p>
			</div>

			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
				<?php wp_nonce_field( self::NONCE_ACTION ); ?>
				<?php submit_button( 'Siapkan Situs Demo', 'primary', 'submit', true ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Verify the request and run the importer.
	 *
	 * @return void
	 */
	public static function handle_import() {
		if ( ! current_user_can( QAF_Core_Settings::get_capability() ) ) {
			wp_die(
				esc_html__( 'Anda tidak memiliki izin untuk menjalankan penyiapan demo.', 'queen-alfalah-core' ),
				esc_html__( 'Akses ditolak', 'queen-alfalah-core' ),
				array( 'response' => 403 )
			);
		}

		$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : '';
		if ( 'POST' !== $request_method ) {
			wp_die(
				esc_html__( 'Metode permintaan tidak valid.', 'queen-alfalah-core' ),
				esc_html__( 'Permintaan tidak valid', 'queen-alfalah-core' ),
				array( 'response' => 405 )
			);
		}

		check_admin_referer( self::NONCE_ACTION );

		try {
			$result = self::run_import();
		} catch ( Throwable $error ) {
			$result = self::empty_result();
			$result['errors'][] = sprintf(
				/* translators: %s: internal error message. */
				__( 'Penyiapan berhenti karena galat: %s', 'queen-alfalah-core' ),
				$error->getMessage()
			);
		}

		set_transient( self::result_transient_key(), $result, 5 * MINUTE_IN_SECONDS );
		wp_safe_redirect(
			add_query_arg(
				'qaf_demo',
				'complete',
				admin_url( 'admin.php?page=qaf-demo-setup' )
			)
		);
		exit;
	}

	/**
	 * Execute every setup phase.
	 *
	 * @return array<string,mixed>
	 */
	public static function run_import() {
		$result = self::empty_result();

		QAF_Core_Settings::install_defaults();
		QAF_Core_Post_Types::register_post_types();
		QAF_Core_Post_Types::register_taxonomies();
		QAF_Core_Meta::register_meta();

		$page_ids = self::import_pages( $result );
		self::configure_reading( $page_ids, $result );
		self::import_programs( $result );
		self::import_extracurriculars( $result );
		$service_ids = self::import_services( $page_ids, $result );
		self::import_unverified_drafts( $result );
		self::import_menus( $page_ids, $service_ids, $result );

		update_option( self::VERSION_OPTION, QAF_CORE_VERSION, false );
		update_option( 'qaf_core_demo_imported_at', current_time( 'mysql' ), false );
		return $result;
	}

	/**
	 * Create essential pages in parent-before-child order.
	 *
	 * @param array<string,mixed> $result Import result.
	 * @return array<string,int>
	 */
	private static function import_pages( &$result ) {
		$settings = wp_parse_args( get_option( QAF_Core_Settings::OPTION_NAME, array() ), QAF_Core_Settings::get_defaults() );
		$missions = array_filter( array_map( 'trim', explode( "\n", $settings['mission'] ) ) );
		$mission_html = '';
		foreach ( $missions as $mission ) {
			$mission_html .= '<li>' . esc_html( $mission ) . '</li>';
		}

		$registration_url = esc_url( $settings['registration_url'] );
		$pages = array(
			'beranda' => array(
				'title'   => 'Beranda',
				'content' => '<!-- wp:paragraph --><p>Halaman ini digunakan sebagai halaman depan statis. Susunan beranda ditampilkan otomatis oleh tema Queen Al-Falah.</p><!-- /wp:paragraph -->',
			),
			'berita' => array(
				'title'   => 'Berita',
				'content' => '<!-- wp:paragraph --><p>Berita resmi sekolah akan ditampilkan pada halaman ini.</p><!-- /wp:paragraph -->',
			),
			'profil-sekolah' => array(
				'title'   => 'Profil Sekolah',
				'content' => '<!-- wp:heading --><h2>SMK Queen Al-Falah</h2><!-- /wp:heading --><!-- wp:paragraph --><p>SMK Queen Al-Falah merupakan satuan pendidikan kejuruan di Kecamatan Mojo, Kabupaten Kediri. Halaman ini dapat dilengkapi dengan profil kelembagaan yang telah disahkan.</p><!-- /wp:paragraph --><!-- wp:list --><ul><li>NPSN: ' . esc_html( $settings['npsn'] ) . '</li><li>Akreditasi: ' . esc_html( $settings['accreditation'] ) . '</li><li>Penyelenggara: ' . esc_html( $settings['foundation'] ) . '</li><li>Alamat: ' . esc_html( $settings['address'] ) . '</li></ul><!-- /wp:list -->',
			),
			'sambutan-kepala-sekolah' => array(
				'title'       => 'Sambutan Kepala Sekolah',
				'parent_slug' => 'profil-sekolah',
				'content'     => '<!-- wp:paragraph --><p><strong>Lengkapi draf ini dengan nama, jabatan, foto, dan naskah sambutan yang telah disetujui sebelum dipublikasikan ulang.</strong></p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Pendidikan vokasi menyatukan kompetensi, karakter, dan keberanian untuk terus belajar. Kami mengajak seluruh warga sekolah membangun lingkungan belajar yang profesional, adaptif, dan berakhlaqul karimah.</p><!-- /wp:paragraph -->',
			),
			'visi-misi' => array(
				'title'       => 'Visi dan Misi',
				'parent_slug' => 'profil-sekolah',
				'content'     => '<!-- wp:heading --><h2>Visi</h2><!-- /wp:heading --><!-- wp:paragraph --><p>' . esc_html( $settings['vision'] ) . '</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Misi</h2><!-- /wp:heading --><!-- wp:list --><ul>' . $mission_html . '</ul><!-- /wp:list -->',
			),
			'sejarah' => array(
				'title'       => 'Sejarah',
				'parent_slug' => 'profil-sekolah',
				'content'     => '<!-- wp:paragraph --><p>SMK Queen Al-Falah berdiri pada 21 Februari 2011. Lengkapi halaman ini dengan kronologi, tonggak perkembangan, dan sumber arsip resmi sekolah.</p><!-- /wp:paragraph -->',
			),
			'struktur-organisasi' => array(
				'title'       => 'Struktur Organisasi',
				'parent_slug' => 'profil-sekolah',
				'template'    => 'page-templates/template-full-width.php',
				'content'     => '<!-- wp:shortcode -->[qaf_organization]<!-- /wp:shortcode -->',
			),
			'kesiswaan' => array(
				'title'   => 'Kesiswaan',
				'content' => '<!-- wp:paragraph --><p>Informasi layanan peserta didik, pembinaan karakter, organisasi siswa, prestasi, dan ekstrakurikuler dapat dirangkum pada halaman ini.</p><!-- /wp:paragraph -->',
			),
			'informasi' => array(
				'title'   => 'Informasi',
				'content' => '<!-- wp:paragraph --><p>Gunakan pusat informasi ini untuk mengarahkan pengunjung ke berita, pengumuman, dan agenda resmi sekolah.</p><!-- /wp:paragraph -->',
			),
			'ppdb' => array(
				'title'   => 'PPDB',
				'template'=> 'page-templates/template-ppdb.php',
				'content' => '<!-- wp:heading --><h2>Penerimaan Peserta Didik Baru</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Periksa tahun ajaran, jalur, jadwal, kuota, persyaratan, biaya, dan narahubung resmi sebelum menerbitkan informasi pendaftaran.</p><!-- /wp:paragraph --><!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="' . $registration_url . '" target="_blank" rel="noopener noreferrer">Buka Sistem Pendaftaran</a></div><!-- /wp:button --></div><!-- /wp:buttons -->',
			),
			'bursa-kerja-khusus' => array(
				'title'   => 'Bursa Kerja Khusus',
				'content' => '<!-- wp:paragraph --><p>Bursa Kerja Khusus membantu menghubungkan lulusan dengan informasi karier. Setiap lowongan harus diverifikasi sumber, perusahaan, persyaratan, cara melamar, dan batas waktunya.</p><!-- /wp:paragraph -->',
			),
			'kontak' => array(
				'title'   => 'Kontak',
				'template'=> 'page-templates/template-contact.php',
				'content' => '<!-- wp:heading --><h2>Hubungi Sekolah</h2><!-- /wp:heading --><!-- wp:list --><ul><li>Alamat: ' . esc_html( $settings['address'] ) . '</li><li>Telepon: ' . esc_html( $settings['phone'] ) . '</li><li>Email: <a href="mailto:' . esc_attr( $settings['email'] ) . '">' . esc_html( $settings['email'] ) . '</a></li></ul><!-- /wp:list --><!-- wp:paragraph --><p>Periksa kembali jam layanan, nomor WhatsApp, dan tautan peta sebelum dipublikasikan.</p><!-- /wp:paragraph -->',
			),
		);

		$ids = array();
		foreach ( $pages as $slug => $definition ) {
			$parent_id = 0;
			if ( ! empty( $definition['parent_slug'] ) && isset( $ids[ $definition['parent_slug'] ] ) ) {
				$parent_id = $ids[ $definition['parent_slug'] ];
			}

			$ids[ $slug ] = self::ensure_content(
				array(
					'post_type'    => 'page',
					'post_title'   => $definition['title'],
					'post_name'    => $slug,
					'post_content' => $definition['content'],
					'post_status'  => 'publish',
					'post_parent'  => $parent_id,
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'meta_input'     => ! empty( $definition['template'] ) ? array( '_wp_page_template' => $definition['template'] ) : array(),
				),
				'page:' . $slug,
				'pages',
				array(),
				$result
			);
		}

		return $ids;
	}

	/**
	 * Configure a static front page and posts page.
	 *
	 * @param array<string,int>   $page_ids Page IDs.
	 * @param array<string,mixed> $result   Import result.
	 * @return void
	 */
	private static function configure_reading( $page_ids, &$result ) {
		if ( empty( $page_ids['beranda'] ) || empty( $page_ids['berita'] ) ) {
			$result['errors'][] = __( 'Halaman depan tidak diubah karena Beranda atau Berita gagal disiapkan.', 'queen-alfalah-core' );
			return;
		}

		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $page_ids['beranda'] );
		update_option( 'page_for_posts', (int) $page_ids['berita'] );
		$result['configured'][] = __( 'Beranda statis dan halaman Berita', 'queen-alfalah-core' );
	}

	/**
	 * Import the four verified program names used by the theme.
	 *
	 * @param array<string,mixed> $result Import result.
	 * @return void
	 */
	private static function import_programs( &$result ) {
		$programs = array(
			array(
				'code'         => 'TJKT',
				'title'        => 'Teknik Jaringan Komputer dan Telekomunikasi',
				'slug'         => 'teknik-jaringan-komputer-dan-telekomunikasi',
				'gender'       => 'Putra',
				'excerpt'      => 'Pembelajaran jaringan, perangkat komputer, layanan telekomunikasi, dan administrasi sistem.',
				'competencies' => "Instalasi dan konfigurasi jaringan\nPerawatan perangkat komputer\nAdministrasi sistem dan layanan jaringan\nDasar layanan telekomunikasi",
				'careers'      => "Teknisi jaringan\nAdministrator sistem junior\nDukungan teknis\nWirausaha layanan teknologi",
				'field'        => 'Teknologi Informasi',
			),
			array(
				'code'         => 'MPLB',
				'title'        => 'Manajemen Perkantoran dan Layanan Bisnis',
				'slug'         => 'manajemen-perkantoran-dan-layanan-bisnis',
				'gender'       => 'Putri',
				'excerpt'      => 'Pembelajaran administrasi modern, layanan bisnis, komunikasi, dan pengelolaan dokumen digital.',
				'competencies' => "Administrasi perkantoran\nPengelolaan dokumen digital\nKomunikasi dan layanan pelanggan\nDasar pengelolaan kegiatan bisnis",
				'careers'      => "Staf administrasi\nResepsionis dan layanan pelanggan\nPengelola dokumen\nWirausaha jasa administrasi",
				'field'        => 'Bisnis dan Manajemen',
			),
			array(
				'code'         => 'DKV',
				'title'        => 'Desain Komunikasi Visual',
				'slug'         => 'desain-komunikasi-visual',
				'gender'       => 'Putra & Putri',
				'excerpt'      => 'Pembelajaran desain grafis, fotografi, video, animasi, dan produksi media kreatif.',
				'competencies' => "Dasar desain grafis\nFotografi dan pengolahan gambar\nProduksi video dan audio visual\nPengembangan portofolio kreatif",
				'careers'      => "Desainer grafis junior\nFotografer atau videografer junior\nKreator konten\nWirausaha bidang kreatif",
				'field'        => 'Seni dan Ekonomi Kreatif',
			),
			array(
				'code'         => 'LK',
				'title'        => 'Layanan Kesehatan',
				'slug'         => 'layanan-kesehatan',
				'gender'       => 'Putri',
				'excerpt'      => 'Pembelajaran keterampilan dasar layanan kesehatan dengan sikap empatik dan profesional.',
				'competencies' => "Dasar layanan kesehatan\nKomunikasi empatik\nPenerapan keselamatan dan kebersihan\nAdministrasi layanan dasar",
				'careers'      => "Asisten layanan kesehatan sesuai kewenangan\nAdministrasi fasilitas layanan\nPendamping layanan dasar\nStudi lanjut bidang kesehatan",
				'field'        => 'Kesehatan',
			),
		);

		foreach ( $programs as $index => $program ) {
			$content = '<!-- wp:heading --><h2>Gambaran Program</h2><!-- /wp:heading --><!-- wp:paragraph --><p>' . esc_html( $program['excerpt'] ) . '</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Kompetensi Utama</h2><!-- /wp:heading --><!-- wp:paragraph --><p>' . nl2br( esc_html( $program['competencies'] ) ) . '</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Prospek Setelah Lulus</h2><!-- /wp:heading --><!-- wp:paragraph --><p>' . nl2br( esc_html( $program['careers'] ) ) . '</p><!-- /wp:paragraph --><!-- wp:paragraph --><p><em>Periksa kembali kurikulum, konsentrasi, sertifikasi, fasilitas, dan ketentuan penerimaan terbaru sebelum publikasi final.</em></p><!-- /wp:paragraph -->';

			self::ensure_content(
				array(
					'post_type'      => 'qaf_program',
					'post_title'     => $program['title'],
					'post_name'      => $program['slug'],
					'post_excerpt'   => $program['excerpt'],
					'post_content'   => $content,
					'post_status'    => 'publish',
					'menu_order'     => $index + 1,
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'meta_input'     => array(
						'_qaf_program_code'   => $program['code'],
						'_qaf_program_gender' => $program['gender'],
						'_qaf_competencies'   => $program['competencies'],
						'_qaf_careers'        => $program['careers'],
					),
				),
				'program:' . strtolower( $program['code'] ),
				'programs',
				array( 'qaf_program_field' => array( $program['field'] ) ),
				$result
			);
		}
	}

	/**
	 * Import the seven extracurricular cards expected by the home page.
	 *
	 * @param array<string,mixed> $result Import result.
	 * @return void
	 */
	private static function import_extracurriculars( &$result ) {
		$activities = array(
			array( 'Pramuka', 'pramuka', 'Kepemimpinan', 'Pembinaan kepemimpinan, kemandirian, kerja sama, dan kepedulian sosial.' ),
			array( 'Broadcasting', 'broadcasting', 'Kreativitas', 'Ruang belajar produksi konten, penyiaran, dokumentasi, dan komunikasi media.' ),
			array( 'Futsal', 'futsal', 'Olahraga', 'Kegiatan olahraga untuk melatih kebugaran, sportivitas, disiplin, dan kerja tim.' ),
			array( 'Al Banjari', 'al-banjari', 'Keagamaan', 'Pengembangan seni musik islami, kekompakan, dan kepercayaan diri dalam berkarya.' ),
			array( 'Tenis Meja', 'tenis-meja', 'Olahraga', 'Latihan ketangkasan, konsentrasi, kebugaran, dan sportivitas.' ),
			array( 'Bola Voli', 'bola-voli', 'Olahraga', 'Kegiatan olahraga beregu untuk melatih teknik, komunikasi, dan kerja sama.' ),
			array( 'Desain Web', 'desain-web', 'Teknologi', 'Ruang eksplorasi antarmuka, struktur halaman web, kreativitas digital, dan portofolio.' ),
		);

		foreach ( $activities as $index => $activity ) {
			self::ensure_content(
				array(
					'post_type'      => 'qaf_extra',
					'post_title'     => $activity[0],
					'post_name'      => $activity[1],
					'post_excerpt'   => $activity[3],
					'post_content'   => '<!-- wp:paragraph --><p>' . esc_html( $activity[3] ) . '</p><!-- /wp:paragraph --><!-- wp:paragraph --><p><strong>Administrator:</strong> lengkapi jadwal, pembina, syarat peserta, capaian, serta dokumentasi yang telah mendapat izin.</p><!-- /wp:paragraph -->',
					'post_status'    => 'draft',
					'menu_order'     => $index + 1,
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
				),
				'extra:' . $activity[1],
				'extras',
				array( 'qaf_extra_type' => array( $activity[2] ) ),
				$result
			);
		}
	}

	/**
	 * Import six safe quick-access services.
	 *
	 * @param array<string,int>   $page_ids Page IDs.
	 * @param array<string,mixed> $result   Import result.
	 * @return array<string,int>
	 */
	private static function import_services( $page_ids, &$result ) {
		$settings = wp_parse_args( get_option( QAF_Core_Settings::OPTION_NAME, array() ), QAF_Core_Settings::get_defaults() );
		$services = array(
			'pendaftaran' => array( 'Pendaftaran', 'book', $settings['registration_url'], true, 'Pendaftaran resmi calon peserta didik melalui sistem sekolah.' ),
			'program-keahlian' => array( 'Program Keahlian', 'monitor', get_post_type_archive_link( 'qaf_program' ), false, 'Jelajahi program dan kompetensi keahlian yang tersedia.' ),
			'pengumuman' => array( 'Pengumuman', 'calendar', get_post_type_archive_link( 'qaf_notice' ), false, 'Informasi administratif dan pengumuman resmi sekolah.' ),
			'agenda' => array( 'Agenda', 'clock', get_post_type_archive_link( 'qaf_agenda' ), false, 'Jadwal kegiatan sekolah yang telah diverifikasi.' ),
			'bursa-kerja' => array( 'Bursa Kerja', 'briefcase', get_post_type_archive_link( 'qaf_vacancy' ), false, 'Informasi peluang kerja terverifikasi bagi alumni.' ),
			'kontak-sekolah' => array( 'Kontak Sekolah', 'phone', ! empty( $page_ids['kontak'] ) ? get_permalink( $page_ids['kontak'] ) : home_url( '/kontak/' ), false, 'Alamat dan kanal komunikasi resmi sekolah.' ),
		);

		$ids   = array();
		$order = 0;
		foreach ( $services as $slug => $service ) {
			++$order;
			$url = is_string( $service[2] ) && $service[2] ? $service[2] : home_url( '/' );
			$ids[ $slug ] = self::ensure_content(
				array(
					'post_type'      => 'qaf_service',
					'post_title'     => $service[0],
					'post_name'      => $slug,
					'post_excerpt'   => $service[4],
					'post_content'   => '<!-- wp:paragraph --><p>' . esc_html( $service[4] ) . '</p><!-- /wp:paragraph -->',
					'post_status'    => 'publish',
					'menu_order'     => $order,
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'meta_input'     => array(
						'_qaf_external_url' => $url,
						'_qaf_icon_name'    => $service[1],
						'_qaf_open_new'     => $service[3],
					),
				),
				'service:' . $slug,
				'services',
				array( 'qaf_service_type' => array( 'Akses Cepat' ) ),
				$result
			);
		}

		return $ids;
	}

	/**
	 * Create clearly labelled drafts for facts that require school verification.
	 *
	 * @param array<string,mixed> $result Import result.
	 * @return void
	 */
	private static function import_unverified_drafts( &$result ) {
		$drafts = array(
			array( 'post', 'berita-proyek-pembelajaran', '[Draf Demo] Pembelajaran Berbasis Proyek', 'Tuliskan tujuan kegiatan, peserta, proses, hasil, tanggal, narasumber, dan kredit foto yang telah diverifikasi.', array(), array( 'category' => array( 'Berita Sekolah' ) ) ),
			array( 'post', 'berita-kolaborasi-industri', '[Draf Demo] Kolaborasi dengan Dunia Industri', 'Pastikan nama mitra, ruang lingkup kerja sama, masa berlaku, kutipan, dan izin penggunaan logo telah diverifikasi.', array(), array( 'category' => array( 'Berita Sekolah' ) ) ),
			array( 'post', 'berita-prestasi-warga-sekolah', '[Draf Demo] Prestasi Warga Sekolah', 'Pastikan nama penerima, jenis lomba, penyelenggara, tingkat, tanggal, hasil, dan izin publikasi sudah benar.', array(), array( 'category' => array( 'Berita Sekolah' ) ) ),
			array( 'qaf_notice', 'pengumuman-jadwal-pendaftaran', '[Draf Demo] Jadwal Pendaftaran', 'Isi tahun ajaran, periode, jalur, persyaratan, biaya, kuota, tautan, dan narahubung resmi.', array( '_qaf_priority' => 'normal' ), array( 'qaf_notice_type' => array( 'Pendaftaran' ), 'qaf_audience' => array( 'Calon Peserta Didik' ) ) ),
			array( 'qaf_notice', 'pengumuman-administrasi-peserta-didik', '[Draf Demo] Informasi Administrasi Peserta Didik', 'Periksa dasar surat, penerima, tenggat, lampiran, dan kanal konfirmasi sebelum menerbitkan.', array( '_qaf_priority' => 'normal' ), array( 'qaf_notice_type' => array( 'Administrasi' ), 'qaf_audience' => array( 'Peserta Didik' ) ) ),
		);

		$now          = current_datetime();
		$agenda_one   = $now->modify( '+14 days' )->setTime( 8, 0 );
		$agenda_two   = $now->modify( '+21 days' )->setTime( 8, 0 );
		$drafts[]     = array(
			'qaf_agenda',
			'agenda-pertemuan-orang-tua',
			'[Draf Demo] Pertemuan Orang Tua/Wali',
			'Konfirmasikan tanggal, waktu, lokasi, audiens, susunan acara, dan narahubung sebelum menerbitkan.',
			array( '_qaf_start_date' => $agenda_one->format( 'Y-m-d\TH:i' ), '_qaf_location' => 'Isi lokasi resmi' ),
			array( 'qaf_agenda_type' => array( 'Pertemuan' ), 'qaf_audience' => array( 'Orang Tua/Wali' ) ),
		);
		$drafts[]     = array(
			'qaf_agenda',
			'agenda-gelar-karya-siswa',
			'[Draf Demo] Gelar Karya Siswa',
			'Konfirmasikan tanggal, waktu, lokasi, peserta, bentuk karya, dokumentasi, dan izin publikasi sebelum menerbitkan.',
			array( '_qaf_start_date' => $agenda_two->format( 'Y-m-d\TH:i' ), '_qaf_location' => 'Isi lokasi resmi' ),
			array( 'qaf_agenda_type' => array( 'Kegiatan Siswa' ), 'qaf_audience' => array( 'Warga Sekolah' ) ),
		);

		foreach ( $drafts as $draft ) {
			self::ensure_content(
				array(
					'post_type'      => $draft[0],
					'post_title'     => $draft[2],
					'post_name'      => $draft[1],
					'post_excerpt'   => $draft[3],
					'post_content'   => '<!-- wp:paragraph --><p>' . esc_html( $draft[3] ) . '</p><!-- /wp:paragraph --><!-- wp:paragraph --><p><strong>Status:</strong> draf contoh; jangan diterbitkan sebelum verifikasi editorial.</p><!-- /wp:paragraph -->',
					'post_status'    => 'draft',
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'meta_input'     => $draft[4],
				),
				'draft:' . $draft[0] . ':' . $draft[1],
				'drafts',
				$draft[5],
				$result
			);
		}
	}

	/**
	 * Create and assign the four theme menus without replacing assignments.
	 *
	 * @param array<string,int>   $pages       Page IDs.
	 * @param array<string,int>   $service_ids Service IDs.
	 * @param array<string,mixed> $result      Import result.
	 * @return void
	 */
	private static function import_menus( $pages, $service_ids, &$result ) {
		$menus = array();
		$menus['primary']  = self::ensure_menu( 'Menu Utama Queen Al-Falah', 'primary', $result );
		$menus['utility']  = self::ensure_menu( 'Menu Atas Queen Al-Falah', 'utility', $result );
		$menus['services'] = self::ensure_menu( 'Menu Layanan Queen Al-Falah', 'services', $result );
		$menus['footer']   = self::ensure_menu( 'Menu Footer Queen Al-Falah', 'footer', $result );

		if ( $menus['primary'] ) {
			self::ensure_page_menu_item( $menus['primary'], 'primary:beranda', $pages, 'beranda', 'Beranda', 0, $result );
			$profile_parent = self::ensure_page_menu_item( $menus['primary'], 'primary:profil', $pages, 'profil-sekolah', 'Profil', 0, $result );
			self::ensure_page_menu_item( $menus['primary'], 'primary:sambutan', $pages, 'sambutan-kepala-sekolah', 'Sambutan Kepala Sekolah', $profile_parent, $result );
			self::ensure_page_menu_item( $menus['primary'], 'primary:visi-misi', $pages, 'visi-misi', 'Visi dan Misi', $profile_parent, $result );
			self::ensure_page_menu_item( $menus['primary'], 'primary:sejarah', $pages, 'sejarah', 'Sejarah', $profile_parent, $result );
			self::ensure_page_menu_item( $menus['primary'], 'primary:struktur', $pages, 'struktur-organisasi', 'Struktur Organisasi', $profile_parent, $result );
			self::ensure_archive_menu_item( $menus['primary'], 'primary:guru', 'qaf_teacher', 'Guru & Tendik', $profile_parent, $result );
			self::ensure_archive_menu_item( $menus['primary'], 'primary:program', 'qaf_program', 'Program Keahlian', 0, $result );
			$student_parent = self::ensure_page_menu_item( $menus['primary'], 'primary:kesiswaan', $pages, 'kesiswaan', 'Kesiswaan', 0, $result );
			self::ensure_archive_menu_item( $menus['primary'], 'primary:extra', 'qaf_extra', 'Ekstrakurikuler', $student_parent, $result );
			self::ensure_archive_menu_item( $menus['primary'], 'primary:achievement', 'qaf_achievement', 'Prestasi', $student_parent, $result );
			$info_parent = self::ensure_page_menu_item( $menus['primary'], 'primary:informasi', $pages, 'informasi', 'Informasi', 0, $result );
			self::ensure_page_menu_item( $menus['primary'], 'primary:berita', $pages, 'berita', 'Berita', $info_parent, $result );
			self::ensure_archive_menu_item( $menus['primary'], 'primary:notice', 'qaf_notice', 'Pengumuman', $info_parent, $result );
			self::ensure_archive_menu_item( $menus['primary'], 'primary:agenda', 'qaf_agenda', 'Agenda', $info_parent, $result );
			self::ensure_page_menu_item( $menus['primary'], 'primary:ppdb', $pages, 'ppdb', 'PPDB', 0, $result );
			self::ensure_archive_menu_item( $menus['primary'], 'primary:applications', 'qaf_service', 'Aplikasi', 0, $result );
			self::ensure_page_menu_item( $menus['primary'], 'primary:kontak', $pages, 'kontak', 'Kontak', 0, $result );
		}

		if ( $menus['utility'] ) {
			self::ensure_page_menu_item( $menus['utility'], 'utility:berita', $pages, 'berita', 'Berita', 0, $result );
			self::ensure_archive_menu_item( $menus['utility'], 'utility:notice', 'qaf_notice', 'Pengumuman', 0, $result );
			self::ensure_archive_menu_item( $menus['utility'], 'utility:agenda', 'qaf_agenda', 'Agenda', 0, $result );
			self::ensure_page_menu_item( $menus['utility'], 'utility:kontak', $pages, 'kontak', 'Kontak', 0, $result );
		}

		if ( $menus['services'] ) {
			foreach ( $service_ids as $slug => $service_id ) {
				if ( $service_id ) {
					self::ensure_post_menu_item( $menus['services'], 'services:' . $slug, 'qaf_service', $service_id, get_the_title( $service_id ), 0, $result );
				}
			}
		}

		if ( $menus['footer'] ) {
			self::ensure_page_menu_item( $menus['footer'], 'footer:profil', $pages, 'profil-sekolah', 'Profil Sekolah', 0, $result );
			self::ensure_archive_menu_item( $menus['footer'], 'footer:program', 'qaf_program', 'Program Keahlian', 0, $result );
			self::ensure_page_menu_item( $menus['footer'], 'footer:berita', $pages, 'berita', 'Berita', 0, $result );
			self::ensure_page_menu_item( $menus['footer'], 'footer:ppdb', $pages, 'ppdb', 'PPDB', 0, $result );
			self::ensure_page_menu_item( $menus['footer'], 'footer:kontak', $pages, 'kontak', 'Kontak', 0, $result );
		}

		self::assign_menu_locations( $menus, $result );
	}

	/**
	 * Ensure one post/page/CPT exists, retaining existing user content.
	 *
	 * @param array<string,mixed> $postarr  Post data.
	 * @param string              $demo_key Stable marker value.
	 * @param string              $bucket   Result counter bucket.
	 * @param array<string,array<int,string>> $terms Terms by taxonomy.
	 * @param array<string,mixed> $result   Import result.
	 * @return int
	 */
	private static function ensure_content( $postarr, $demo_key, $bucket, $terms, &$result ) {
		$post_type = isset( $postarr['post_type'] ) ? $postarr['post_type'] : 'post';
		$slug      = isset( $postarr['post_name'] ) ? $postarr['post_name'] : '';
		$existing  = self::find_demo_post( $demo_key, $post_type, $slug );

		if ( $existing ) {
			$result['reused'][ $bucket ] = isset( $result['reused'][ $bucket ] ) ? $result['reused'][ $bucket ] + 1 : 1;
			if ( 'trash' === get_post_status( $existing ) ) {
				$result['warnings'][] = sprintf(
					/* translators: %s: post title. */
					__( '“%s” berada di Sampah dan tidak dipulihkan otomatis.', 'queen-alfalah-core' ),
					get_the_title( $existing )
				);
			}
			return (int) $existing;
		}

		$meta_input = isset( $postarr['meta_input'] ) && is_array( $postarr['meta_input'] ) ? $postarr['meta_input'] : array();
		$meta_input[ self::MARKER_META ] = $demo_key;
		$postarr['meta_input']            = $meta_input;
		$postarr['post_author']           = get_current_user_id();

		$post_id = wp_insert_post( wp_slash( $postarr ), true );
		if ( is_wp_error( $post_id ) ) {
			$result['errors'][] = sprintf(
				/* translators: 1: content title, 2: error message. */
				__( 'Gagal membuat “%1$s”: %2$s', 'queen-alfalah-core' ),
				isset( $postarr['post_title'] ) ? $postarr['post_title'] : $slug,
				$post_id->get_error_message()
			);
			return 0;
		}

		$result['created'][ $bucket ] = isset( $result['created'][ $bucket ] ) ? $result['created'][ $bucket ] + 1 : 1;
		self::assign_terms( $post_id, $terms, $result );
		return (int) $post_id;
	}

	/**
	 * Find a demo post by marker, then by stable slug to avoid user duplicates.
	 *
	 * @param string $demo_key Demo marker.
	 * @param string $post_type Post type.
	 * @param string $slug Stable slug.
	 * @return int
	 */
	private static function find_demo_post( $demo_key, $post_type, $slug ) {
		$ids = get_posts(
			array(
				'post_type'              => $post_type,
				'post_status'            => array( 'publish', 'future', 'draft', 'pending', 'private', 'trash' ),
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'meta_key'               => self::MARKER_META,
				'meta_value'             => $demo_key,
				'orderby'                => 'ID',
				'order'                  => 'ASC',
				'no_found_rows'          => true,
				'suppress_filters'       => true,
				'ignore_sticky_posts'    => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		if ( ! empty( $ids ) ) {
			return (int) $ids[0];
		}

		$post = $slug ? get_page_by_path( $slug, OBJECT, $post_type ) : null;
		return $post instanceof WP_Post ? (int) $post->ID : 0;
	}

	/**
	 * Assign controlled demo terms and report failures.
	 *
	 * @param int                              $post_id Post ID.
	 * @param array<string,array<int,string>> $terms   Terms by taxonomy.
	 * @param array<string,mixed>              $result  Import result.
	 * @return void
	 */
	private static function assign_terms( $post_id, $terms, &$result ) {
		foreach ( $terms as $taxonomy => $names ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}
			$assigned = wp_set_object_terms( $post_id, $names, $taxonomy, false );
			if ( is_wp_error( $assigned ) ) {
				$result['warnings'][] = sprintf(
					/* translators: 1: post title, 2: error message. */
					__( 'Taksonomi untuk “%1$s” belum lengkap: %2$s', 'queen-alfalah-core' ),
					get_the_title( $post_id ),
					$assigned->get_error_message()
				);
			}
		}
	}

	/**
	 * Ensure a named navigation menu exists.
	 *
	 * @param string              $name     Menu name.
	 * @param string              $key      Counter key.
	 * @param array<string,mixed> $result   Import result.
	 * @return int
	 */
	private static function ensure_menu( $name, $key, &$result ) {
		$menu = wp_get_nav_menu_object( $name );
		if ( $menu ) {
			$result['reused']['menus'] = isset( $result['reused']['menus'] ) ? $result['reused']['menus'] + 1 : 1;
			return (int) $menu->term_id;
		}

		$menu_id = wp_create_nav_menu( $name );
		if ( is_wp_error( $menu_id ) ) {
			$result['errors'][] = sprintf(
				/* translators: 1: menu name, 2: error message. */
				__( 'Gagal membuat menu “%1$s”: %2$s', 'queen-alfalah-core' ),
				$name,
				$menu_id->get_error_message()
			);
			return 0;
		}

		unset( $key );
		$result['created']['menus'] = isset( $result['created']['menus'] ) ? $result['created']['menus'] + 1 : 1;
		return (int) $menu_id;
	}

	/**
	 * Add a page menu item when the page exists.
	 *
	 * @param int                 $menu_id Menu term ID.
	 * @param string              $key Stable key.
	 * @param array<string,int>   $pages Page IDs.
	 * @param string              $slug Page slug.
	 * @param string              $title Menu label.
	 * @param int                 $parent Parent item ID.
	 * @param array<string,mixed> $result Result.
	 * @return int
	 */
	private static function ensure_page_menu_item( $menu_id, $key, $pages, $slug, $title, $parent, &$result ) {
		if ( empty( $pages[ $slug ] ) ) {
			return 0;
		}
		return self::ensure_post_menu_item( $menu_id, $key, 'page', $pages[ $slug ], $title, $parent, $result );
	}

	/**
	 * Add a post-type-backed menu item.
	 *
	 * @param int                 $menu_id Menu ID.
	 * @param string              $key Stable key.
	 * @param string              $post_type Object type.
	 * @param int                 $object_id Post ID.
	 * @param string              $title Label.
	 * @param int                 $parent Parent item ID.
	 * @param array<string,mixed> $result Result.
	 * @return int
	 */
	private static function ensure_post_menu_item( $menu_id, $key, $post_type, $object_id, $title, $parent, &$result ) {
		return self::ensure_menu_item(
			$menu_id,
			$key,
			array(
				'menu-item-object-id' => (int) $object_id,
				'menu-item-object'    => $post_type,
				'menu-item-parent-id' => (int) $parent,
				'menu-item-type'      => 'post_type',
				'menu-item-title'     => $title,
				'menu-item-status'    => 'publish',
			),
			$result
		);
	}

	/**
	 * Add a post type archive menu item.
	 *
	 * @param int                 $menu_id Menu ID.
	 * @param string              $key Stable key.
	 * @param string              $post_type Post type.
	 * @param string              $title Label.
	 * @param int                 $parent Parent item ID.
	 * @param array<string,mixed> $result Result.
	 * @return int
	 */
	private static function ensure_archive_menu_item( $menu_id, $key, $post_type, $title, $parent, &$result ) {
		if ( ! post_type_exists( $post_type ) ) {
			return 0;
		}
		return self::ensure_menu_item(
			$menu_id,
			$key,
			array(
				'menu-item-object-id' => 0,
				'menu-item-object'    => $post_type,
				'menu-item-parent-id' => (int) $parent,
				'menu-item-type'      => 'post_type_archive',
				'menu-item-title'     => $title,
				'menu-item-status'    => 'publish',
			),
			$result
		);
	}

	/**
	 * Ensure one menu item by marker and object identity.
	 *
	 * @param int                 $menu_id Menu ID.
	 * @param string              $key Stable key.
	 * @param array<string,mixed> $args Menu item args.
	 * @param array<string,mixed> $result Result.
	 * @return int
	 */
	private static function ensure_menu_item( $menu_id, $key, $args, &$result ) {
		$items = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) );
		$items = is_array( $items ) ? $items : array();
		$marker = 'menu-item:' . $key;

		foreach ( $items as $item ) {
			if ( $marker === get_post_meta( $item->ID, self::MARKER_META, true ) ) {
				$result['reused']['menu_items'] = isset( $result['reused']['menu_items'] ) ? $result['reused']['menu_items'] + 1 : 1;
				return (int) $item->ID;
			}

			$type_matches   = isset( $args['menu-item-type'] ) && $item->type === $args['menu-item-type'];
			$object_matches = isset( $args['menu-item-object'] ) && $item->object === $args['menu-item-object'];
			$id_matches     = 'post_type_archive' === $item->type || (int) $item->object_id === (int) $args['menu-item-object-id'];
			if ( $type_matches && $object_matches && $id_matches ) {
				$result['reused']['menu_items'] = isset( $result['reused']['menu_items'] ) ? $result['reused']['menu_items'] + 1 : 1;
				return (int) $item->ID;
			}
		}

		$item_id = wp_update_nav_menu_item( $menu_id, 0, $args );
		if ( is_wp_error( $item_id ) ) {
			$result['errors'][] = sprintf(
				/* translators: 1: menu item title, 2: error message. */
				__( 'Gagal menambahkan item menu “%1$s”: %2$s', 'queen-alfalah-core' ),
				isset( $args['menu-item-title'] ) ? $args['menu-item-title'] : $key,
				$item_id->get_error_message()
			);
			return 0;
		}

		update_post_meta( $item_id, self::MARKER_META, $marker );
		$result['created']['menu_items'] = isset( $result['created']['menu_items'] ) ? $result['created']['menu_items'] + 1 : 1;
		return (int) $item_id;
	}

	/**
	 * Assign only empty menu locations registered by the active theme.
	 *
	 * @param array<string,int>   $menus Menu IDs by location.
	 * @param array<string,mixed> $result Result.
	 * @return void
	 */
	private static function assign_menu_locations( $menus, &$result ) {
		$registered = get_registered_nav_menus();
		$locations  = get_theme_mod( 'nav_menu_locations', array() );
		$locations  = is_array( $locations ) ? $locations : array();
		$changed    = false;

		foreach ( $menus as $location => $menu_id ) {
			if ( ! $menu_id || ! isset( $registered[ $location ] ) ) {
				continue;
			}
			if ( ! empty( $locations[ $location ] ) ) {
				continue;
			}
			$locations[ $location ] = $menu_id;
			$changed                = true;
			$result['configured'][] = sprintf(
				/* translators: %s: menu location label. */
				__( 'Lokasi menu: %s', 'queen-alfalah-core' ),
				$registered[ $location ]
			);
		}

		if ( $changed ) {
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}

	/**
	 * Determine whether the theme or its child theme is active.
	 *
	 * @return bool
	 */
	private static function is_queen_theme_active() {
		$theme = wp_get_theme();
		return 'queen-alfalah' === $theme->get_template()
			|| 'queen-alfalah' === $theme->get_stylesheet()
			|| 'queen-alfalah' === $theme->get( 'TextDomain' );
	}

	/**
	 * Create an empty result structure.
	 *
	 * @return array<string,mixed>
	 */
	private static function empty_result() {
		return array(
			'created'    => array(),
			'reused'     => array(),
			'configured' => array(),
			'warnings'   => array(),
			'errors'     => array(),
		);
	}

	/**
	 * Render the result saved after the redirect.
	 *
	 * @param array<string,mixed> $result Import result.
	 * @return void
	 */
	private static function render_result_notice( $result ) {
		$created = isset( $result['created'] ) && is_array( $result['created'] ) ? array_sum( $result['created'] ) : 0;
		$reused  = isset( $result['reused'] ) && is_array( $result['reused'] ) ? array_sum( $result['reused'] ) : 0;
		$errors  = isset( $result['errors'] ) && is_array( $result['errors'] ) ? $result['errors'] : array();
		$class   = $errors ? 'notice notice-warning is-dismissible' : 'notice notice-success is-dismissible';
		?>
		<div class="<?php echo esc_attr( $class ); ?>">
			<p><strong><?php echo esc_html__( 'Penyiapan demo selesai.', 'queen-alfalah-core' ); ?></strong>
			<?php
			echo esc_html(
				sprintf(
					/* translators: 1: number created, 2: number reused. */
					__( '%1$d item dibuat dan %2$d item yang sudah ada dipakai kembali.', 'queen-alfalah-core' ),
					$created,
					$reused
				)
			);
			?>
			</p>
			<?php if ( ! empty( $result['configured'] ) ) : ?>
				<p><?php echo esc_html__( 'Dikonfigurasi:', 'queen-alfalah-core' ); ?> <?php echo esc_html( implode( ', ', $result['configured'] ) ); ?>.</p>
			<?php endif; ?>
			<?php if ( ! empty( $result['warnings'] ) ) : ?>
				<ul class="ul-disc">
					<?php foreach ( $result['warnings'] as $warning ) : ?><li><?php echo esc_html( $warning ); ?></li><?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<?php if ( $errors ) : ?>
				<p><strong><?php echo esc_html__( 'Hal yang perlu diperiksa:', 'queen-alfalah-core' ); ?></strong></p>
				<ul class="ul-disc">
					<?php foreach ( $errors as $error ) : ?><li><?php echo esc_html( $error ); ?></li><?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Retrieve and remove the per-user result transient.
	 *
	 * @return array<string,mixed>|false
	 */
	private static function consume_result() {
		$status = isset( $_GET['qaf_demo'] ) ? sanitize_key( wp_unslash( $_GET['qaf_demo'] ) ) : '';
		if ( 'complete' !== $status ) {
			return false;
		}

		$key    = self::result_transient_key();
		$result = get_transient( $key );
		delete_transient( $key );
		return is_array( $result ) ? $result : false;
	}

	/**
	 * Build an isolated result transient key for the current administrator.
	 *
	 * @return string
	 */
	private static function result_transient_key() {
		return 'qaf_core_demo_result_' . get_current_user_id();
	}
}
