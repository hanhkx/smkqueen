<?php
/**
 * Registered REST meta and secure generic meta boxes.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register structured fields shared by REST, the editor, and the theme.
 */
final class QAF_Core_Meta {
	/**
	 * Attach meta registration and editor hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_meta' ), 20 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ), 20 );
		add_action( 'save_post', array( __CLASS__, 'save_meta_box' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
	}

	/**
	 * Load the Media Library picker only on the Gallery editor.
	 *
	 * @param string $hook_suffix Current administration page.
	 * @return void
	 */
	public static function enqueue_admin_assets( $hook_suffix ) {
		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'qaf_gallery' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style(
			'qaf-gallery-admin',
			QAF_CORE_URL . 'assets/css/gallery-admin.css',
			array(),
			QAF_CORE_VERSION
		);
		wp_enqueue_script(
			'qaf-gallery-admin',
			QAF_CORE_URL . 'assets/js/gallery-admin.js',
			array( 'jquery' ),
			QAF_CORE_VERSION,
			true
		);
		wp_localize_script(
			'qaf-gallery-admin',
			'qafGalleryAdmin',
			array(
				'mediaTitle'  => __( 'Pilih video Galeri', 'queen-alfalah-core' ),
				'mediaButton' => __( 'Gunakan video ini', 'queen-alfalah-core' ),
				'emptyLabel'  => __( 'Belum ada video lokal dipilih.', 'queen-alfalah-core' ),
				'invalidHost' => __( 'URL tidak sesuai dengan sumber sosial yang dipilih.', 'queen-alfalah-core' ),
				'hosts'       => self::social_hosts(),
			)
		);
	}

	/**
	 * Meta field contract used by both plugin and theme.
	 *
	 * @return array<string,array<string,array<string,mixed>>>
	 */
	public static function get_fields() {
		return array(
			'post'            => array(
				'_qaf_instagram_source_url' => array(
					'label'       => 'Sumber Instagram',
					'type'        => 'social_url',
					'description' => 'URL HTTPS kanonik unggahan Instagram yang menjadi sumber berita dan foto.',
				),
				'_qaf_image_credit' => array(
					'label'       => 'Kredit Gambar',
					'type'        => 'text',
					'description' => 'Contoh: Foto: Instagram @smkqueenalfalah_official. Caption gambar dapat disunting melalui Media Library.',
				),
				'_qaf_news_source_id' => array(
					'label'       => 'ID Sinkronisasi Berita',
					'type'        => 'text',
					'description' => 'Penanda stabil agar paket media tidak membuat duplikasi. Jangan diubah.',
					'readonly'    => true,
				),
			),
			'qaf_program'     => array(
				'_qaf_program_code'   => array(
					'label'       => 'Kode/Singkatan Program',
					'type'        => 'text',
					'description' => 'Contoh: TJKT, MPLB, atau DKV.',
				),
				'_qaf_program_head'   => array(
					'label'       => 'Kepala Program',
					'type'        => 'text',
					'description' => 'Nama kepala program; kosongkan jika belum ditetapkan untuk publik.',
				),
				'_qaf_program_gender' => array(
					'label'       => 'Ketentuan Peserta',
					'type'        => 'text',
					'description' => 'Contoh: Putra, Putri, atau Putra/Putri.',
				),
				'_qaf_competencies'   => array(
					'label'       => 'Kompetensi Utama',
					'type'        => 'textarea',
					'description' => 'Ringkasan kompetensi; satu butir per baris bila diperlukan.',
				),
				'_qaf_careers'        => array(
					'label'       => 'Prospek Karier',
					'type'        => 'textarea',
					'description' => 'Prospek karier atau studi lanjut; hindari janji penempatan kerja.',
				),
			),
			'qaf_teacher'     => array(
				'_qaf_role'    => array(
					'label'       => 'Jabatan/Peran',
					'type'        => 'text',
					'description' => 'Contoh: Kepala Sekolah, Guru Produktif, atau Tenaga Administrasi.',
				),
				'_qaf_subject' => array(
					'label'       => 'Mata Pelajaran/Unit',
					'type'        => 'text',
					'description' => 'Bidang ajar atau unit kerja.',
				),
				'_qaf_order'   => array(
					'label'       => 'Urutan Tampil',
					'type'        => 'integer',
					'description' => 'Angka lebih kecil ditampilkan lebih awal.',
					'default'     => 0,
				),
			),
			'qaf_notice'      => array(
				'_qaf_priority' => array(
					'label'       => 'Prioritas',
					'type'        => 'select',
					'description' => 'Gunakan Mendesak hanya untuk informasi yang benar-benar harus segera terlihat.',
					'default'     => 'normal',
					'options'     => array(
						'normal'   => 'Normal',
						'penting'  => 'Penting',
						'mendesak' => 'Mendesak',
					),
				),
				'_qaf_expiry'   => array(
					'label'       => 'Berlaku Sampai',
					'type'        => 'date',
					'description' => 'Setelah tanggal ini tema dapat menyembunyikan pengumuman dari daftar aktif.',
				),
				'_qaf_file_url' => array(
					'label'       => 'Tautan Lampiran',
					'type'        => 'url',
					'description' => 'Tautan HTTPS ke dokumen resmi; unggah melalui Media lalu salin URL-nya.',
				),
			),
			'qaf_agenda'      => array(
				'_qaf_start_date' => array(
					'label'       => 'Mulai',
					'type'        => 'datetime',
					'description' => 'Waktu lokal situs.',
				),
				'_qaf_end_date'   => array(
					'label'       => 'Selesai',
					'type'        => 'datetime',
					'description' => 'Waktu lokal situs; boleh dikosongkan.',
				),
				'_qaf_location'   => array(
					'label'       => 'Lokasi',
					'type'        => 'text',
					'description' => 'Lokasi fisik atau nama ruang pertemuan daring.',
				),
			),
			'qaf_achievement' => array(
				'_qaf_level'            => array(
					'label'       => 'Ruang Lingkup/Tingkat',
					'type'        => 'text',
					'description' => 'Contoh: Sekolah, Kabupaten, Provinsi, Nasional, atau Internasional.',
				),
				'_qaf_achievement_date' => array(
					'label'       => 'Tanggal Prestasi',
					'type'        => 'date',
					'description' => 'Tanggal perolehan atau pengumuman prestasi.',
				),
				'_qaf_recipient'        => array(
					'label'       => 'Penerima',
					'type'        => 'textarea',
					'description' => 'Nama peserta atau tim yang telah disetujui untuk dipublikasikan.',
				),
				'_qaf_organizer'        => array(
					'label'       => 'Penyelenggara',
					'type'        => 'text',
					'description' => 'Nama resmi instansi, organisasi, atau panitia penyelenggara.',
				),
				'_qaf_award'            => array(
					'label'       => 'Juara/Penghargaan',
					'type'        => 'text',
					'description' => 'Contoh: Juara 1, Juara Harapan 1, Finalis, atau Delegasi.',
				),
				'_qaf_field'            => array(
					'label'       => 'Bidang',
					'type'        => 'text',
					'description' => 'Bidang lomba atau prestasi, misalnya Fotografi, Olahraga, atau Akademik.',
				),
				'_qaf_source_url'       => array(
					'label'       => 'Tautan Sumber',
					'type'        => 'url',
					'description' => 'Tautan HTTP/HTTPS ke unggahan atau pengumuman resmi yang menjadi sumber data.',
				),
				'_qaf_achievement_source_id' => array(
					'label'       => 'ID Sumber Impor',
					'type'        => 'text',
					'description' => 'Penanda stabil untuk mencegah duplikasi saat sinkronisasi. Jangan diubah setelah entri dibuat.',
					'readonly'    => true,
				),
			),
			'qaf_extra'       => array(
				'_qaf_schedule' => array(
					'label'       => 'Jadwal Latihan',
					'type'        => 'text',
					'description' => 'Kosongkan jika jadwal belum final.',
				),
				'_qaf_coach'    => array(
					'label'       => 'Pembina/Pelatih',
					'type'        => 'text',
					'description' => 'Nama pembina yang telah disetujui untuk tampil publik.',
				),
				'_qaf_extra_location' => array(
					'label'       => 'Lokasi Kegiatan',
					'type'        => 'text',
					'description' => 'Gedung atau ruang kegiatan; kosongkan bila dapat berubah.',
				),
				'_qaf_benefits' => array(
					'label'       => 'Yang Dipelajari dan Didapat',
					'type'        => 'textarea',
					'description' => 'Gunakan satu manfaat atau keterampilan per baris.',
				),
				'_qaf_career_relevance' => array(
					'label'       => 'Kegunaan untuk Studi dan Dunia Kerja',
					'type'        => 'textarea',
					'description' => 'Jelaskan keterampilan yang dapat ditransfer tanpa menjanjikan pekerjaan atau sertifikasi.',
				),
				'_qaf_join_info' => array(
					'label'       => 'Informasi Bergabung',
					'type'        => 'textarea',
					'description' => 'Syarat, kontak, atau alur pendaftaran; kosongkan sampai informasinya ditetapkan.',
				),
				'_qaf_extra_seed_key' => array(
					'label'       => 'ID Data Awal',
					'type'        => 'text',
					'description' => 'Penanda stabil agar pengisian awal tidak membuat duplikasi. Jangan diubah.',
					'readonly'    => true,
				),
			),
			'qaf_service'     => array(
				'_qaf_external_url' => array(
					'label'       => 'Alamat Layanan',
					'type'        => 'url',
					'description' => 'Hanya URL HTTP/HTTPS yang diizinkan.',
				),
				'_qaf_icon_name'    => array(
					'label'       => 'Nama Ikon',
					'type'        => 'text',
					'description' => 'Nama ikon yang didukung tema, misalnya globe, book, atau user-plus.',
				),
				'_qaf_open_new'     => array(
					'label'       => 'Buka Tab Baru',
					'type'        => 'boolean',
					'description' => 'Tautan eksternal yang membuka tab baru harus memakai rel=noopener.',
					'default'     => true,
				),
				'_qaf_service_status' => array(
					'label'       => 'Status Aplikasi',
					'type'        => 'select',
					'description' => 'Status terlihat oleh pengunjung pada kartu aplikasi.',
					'default'     => 'active',
					'options'     => array(
						'active'      => 'Aktif',
						'maintenance' => 'Pemeliharaan',
						'inactive'    => 'Nonaktif',
					),
				),
			),
			'qaf_gallery'     => array(
				'_qaf_gallery_source' => array(
					'label'       => 'Sumber Media',
					'type'        => 'select',
					'description' => 'Pilih Lokal untuk berkas Media Library/blok editor, atau platform asal untuk satu konten sosial.',
					'default'     => '',
					'options'     => array(
						''          => 'Otomatis / kompatibilitas lama',
						'local'     => 'Lokal / Media Library',
						'instagram' => 'Instagram',
						'tiktok'    => 'TikTok',
						'facebook'  => 'Facebook',
						'youtube'   => 'YouTube',
					),
				),
				'_qaf_gallery_media_type' => array(
					'label'       => 'Jenis Media',
					'type'        => 'select',
					'description' => 'Tentukan apakah entri berisi foto, video, atau gabungan keduanya.',
					'default'     => 'photo',
					'options'     => array(
						'photo' => 'Foto',
						'video' => 'Video',
						'mixed' => 'Foto dan Video',
					),
				),
				'_qaf_video_url' => array(
					'label'       => 'URL Konten Sosial / Video',
					'type'        => 'social_url',
					'description' => 'Gunakan URL HTTPS kanonik satu postingan Instagram, TikTok, Facebook, atau YouTube. Jangan tempel iframe atau kode embed.',
				),
				'_qaf_gallery_local_video_id' => array(
					'label'       => 'Video Lokal',
					'type'        => 'attachment',
					'description' => 'Pilih satu video dari Media Library. Untuk beberapa video, gunakan blok Video di editor konten.',
					'mime_prefix' => 'video/',
				),
				'_qaf_gallery_embed_behavior' => array(
					'label'       => 'Perilaku Embed',
					'type'        => 'select',
					'description' => 'Klik untuk memuat paling ramah privasi; Otomatis memuat platform saat halaman dibuka; Tautan saja tidak menyematkan konten.',
					'default'     => 'click',
					'options'     => array(
						'click' => 'Klik untuk memuat',
						'auto'  => 'Muat otomatis',
						'link'  => 'Tautan saja',
					),
				),
				'_qaf_album_date' => array(
					'label'       => 'Tanggal Album',
					'type'        => 'date',
					'description' => 'Tanggal kegiatan atau dokumentasi.',
				),
			),
			'qaf_partner'     => array(
				'_qaf_partner_url'    => array(
					'label'       => 'Website Mitra',
					'type'        => 'url',
					'description' => 'Website resmi mitra.',
				),
				'_qaf_partner_sector' => array(
					'label'       => 'Sektor Kerja Sama',
					'type'        => 'text',
					'description' => 'Contoh: Teknologi, Kesehatan, atau Bisnis.',
				),
				'_qaf_partner_legal_name' => array(
					'label'       => 'Nama Legal/Bentuk Lembaga',
					'type'        => 'text',
					'description' => 'Gunakan nama badan usaha atau lembaga yang sudah terverifikasi; kosongkan bila belum pasti.',
				),
				'_qaf_partner_location' => array(
					'label'       => 'Lokasi',
					'type'        => 'text',
					'description' => 'Alamat atau wilayah layanan yang didukung sumber resmi.',
				),
				'_qaf_partner_programs' => array(
					'label'       => 'Program Keahlian Terkait',
					'type'        => 'textarea',
					'description' => 'Satu program per baris, misalnya DKV, TJKT, MPLB, atau Layanan Kesehatan.',
				),
				'_qaf_partner_expertise' => array(
					'label'       => 'Keahlian/Layanan Mitra',
					'type'        => 'textarea',
					'description' => 'Satu layanan atau keahlian relevan per baris berdasarkan profil publik mitra.',
				),
				'_qaf_partner_cooperation' => array(
					'label'       => 'Penyaluran Kerja Sama ke Sekolah',
					'type'        => 'textarea',
					'description' => 'Tuliskan bentuk PKL, pembelajaran, pelatihan, proyek, atau dukungan lain berdasarkan arsip sekolah.',
				),
				'_qaf_partner_source_url' => array(
					'label'       => 'Sumber Profil',
					'type'        => 'url',
					'description' => 'Tautan resmi atau sumber publik utama yang mendukung profil mitra.',
				),
				'_qaf_partner_verification' => array(
					'label'       => 'Status Informasi',
					'type'        => 'select',
					'description' => 'Bedakan profil publik yang terverifikasi dari keterangan kerja sama berdasarkan data sekolah.',
					'default'     => 'school-data',
					'options'     => array(
						'verified-profile'   => 'Profil publik terverifikasi',
						'school-data'        => 'Kerja sama berdasarkan data sekolah',
						'needs-confirmation' => 'Identitas perlu konfirmasi',
					),
				),
				'_qaf_partner_seed_key' => array(
					'label'       => 'ID Data Awal',
					'type'        => 'text',
					'description' => 'Penanda stabil agar impor mitra tidak membuat duplikasi. Jangan diubah.',
					'readonly'    => true,
				),
			),
			'qaf_vacancy'     => array(
				'_qaf_deadline'  => array(
					'label'       => 'Batas Lamaran',
					'type'        => 'date',
					'description' => 'Lowongan kedaluwarsa dapat disembunyikan oleh tema.',
				),
				'_qaf_company'   => array(
					'label'       => 'Perusahaan/Instansi',
					'type'        => 'text',
					'description' => 'Pastikan sumber lowongan telah diverifikasi.',
				),
				'_qaf_apply_url' => array(
					'label'       => 'Tautan Lamaran',
					'type'        => 'url',
					'description' => 'Tautan resmi perusahaan atau penyedia lowongan.',
				),
			),
			'qaf_alumni'      => array(
				'_qaf_graduation_year' => array(
					'label'       => 'Tahun Lulus',
					'type'        => 'integer',
					'description' => 'Tahun empat digit.',
				),
				'_qaf_current_role'    => array(
					'label'       => 'Aktivitas/Peran Saat Ini',
					'type'        => 'text',
					'description' => 'Tampilkan hanya dengan persetujuan alumni.',
				),
			),
			'qaf_facility'    => array(
				'_qaf_capacity'        => array(
					'label'       => 'Kapasitas/Jumlah',
					'type'        => 'integer',
					'description' => 'Gunakan data yang sudah diverifikasi; nol berarti tidak ditampilkan.',
				),
				'_qaf_facility_status' => array(
					'label'       => 'Status Sarana',
					'type'        => 'select',
					'description' => 'Status operasional internal untuk membantu penyajian informasi.',
					'default'     => '',
					'options'     => array(
						''                  => 'Belum Diverifikasi',
						'baik'              => 'Baik/Operasional',
						'perlu-perawatan'   => 'Perlu Perawatan',
						'tidak-operasional' => 'Tidak Operasional',
					),
				),
				'_qaf_facility_location' => array(
					'label'       => 'Lokasi Sarana',
					'type'        => 'text',
					'description' => 'Gedung, lantai, atau area tempat sarana berada.',
				),
				'_qaf_facility_function' => array(
					'label'       => 'Fungsi Utama',
					'type'        => 'textarea',
					'description' => 'Jelaskan penggunaan utama sarana dalam pembelajaran atau layanan sekolah.',
				),
				'_qaf_facility_features' => array(
					'label'       => 'Fasilitas/Perlengkapan',
					'type'        => 'textarea',
					'description' => 'Gunakan satu butir per baris dan hanya cantumkan inventaris yang sudah diperiksa.',
				),
				'_qaf_facility_access' => array(
					'label'       => 'Akses dan Aturan Penggunaan',
					'type'        => 'textarea',
					'description' => 'Jadwal, prioritas pengguna, prosedur peminjaman, atau aturan keselamatan.',
				),
				'_qaf_facility_manager' => array(
					'label'       => 'Penanggung Jawab',
					'type'        => 'text',
					'description' => 'Nama atau unit pengelola yang disetujui untuk dipublikasikan.',
				),
				'_qaf_facility_last_check' => array(
					'label'       => 'Tanggal Pemeriksaan Terakhir',
					'type'        => 'date',
					'description' => 'Opsional; gunakan tanggal pemeriksaan inventaris atau kondisi terbaru.',
				),
				'_qaf_facility_seed_key' => array(
					'label'       => 'ID Data Awal',
					'type'        => 'text',
					'description' => 'Penanda stabil agar pengisian awal tidak membuat duplikasi. Jangan diubah.',
					'readonly'    => true,
				),
			),
		);
	}

	/**
	 * Register every field with the WordPress metadata and REST APIs.
	 *
	 * @return void
	 */
	public static function register_meta() {
		foreach ( self::get_fields() as $post_type => $fields ) {
			foreach ( $fields as $meta_key => $field ) {
				$rest_type = self::get_rest_type( $field['type'] );

				register_post_meta(
					$post_type,
					$meta_key,
					array(
						'type'              => $rest_type,
						'description'       => $field['description'],
						'single'            => true,
						'show_in_rest'      => true,
						'sanitize_callback' => static function ( $value ) use ( $field ) {
							return QAF_Core_Meta::sanitize_value( $value, $field );
						},
						'auth_callback'     => static function ( $allowed, $key, $post_id ) use ( $field ) {
							unset( $allowed, $key );
							if ( ! empty( $field['readonly'] ) ) {
								return false;
							}
							return $post_id > 0 && current_user_can( 'edit_post', (int) $post_id );
						},
					)
				);
			}
		}
	}

	/**
	 * Add one generated meta box per post type and hide the raw custom-fields box.
	 *
	 * @return void
	 */
	public static function add_meta_boxes() {
		foreach ( self::get_fields() as $post_type => $fields ) {
			unset( $fields );
			remove_meta_box( 'postcustom', $post_type, 'normal' );
			add_meta_box(
				'qaf-core-details',
				'Detail Terstruktur',
				array( __CLASS__, 'render_meta_box' ),
				$post_type,
				'normal',
				'default'
			);
		}
	}

	/**
	 * Render a generated table of fields for the current post type.
	 *
	 * @param WP_Post $post Current post.
	 * @return void
	 */
	public static function render_meta_box( $post ) {
		$fields = self::get_fields();
		if ( ! isset( $fields[ $post->post_type ] ) ) {
			return;
		}

		wp_nonce_field( 'qaf_core_save_meta_' . $post->post_type, 'qaf_core_meta_nonce' );
		?>
		<?php if ( 'qaf_gallery' === $post->post_type ) : ?>
			<div class="qaf-gallery-editor-guide" role="note">
				<strong><?php esc_html_e( 'Panduan singkat Galeri', 'queen-alfalah-core' ); ?></strong>
				<ul>
					<li><?php esc_html_e( 'Gunakan Gambar Utama sebagai sampul kartu dan halaman Galeri.', 'queen-alfalah-core' ); ?></li>
					<li><?php esc_html_e( 'Untuk media lokal, susun foto atau video memakai blok Gallery, Image, dan Video pada editor konten.', 'queen-alfalah-core' ); ?></li>
					<li><?php esc_html_e( 'Untuk media sosial, masukkan URL kanonik satu postingan. Jangan tempel iframe, token API, atau alamat feed otomatis.', 'queen-alfalah-core' ); ?></li>
				</ul>
			</div>
		<?php endif; ?>
		<table class="form-table" role="presentation">
			<tbody>
			<?php foreach ( $fields[ $post->post_type ] as $meta_key => $field ) : ?>
				<?php
				$value = metadata_exists( 'post', $post->ID, $meta_key )
					? get_post_meta( $post->ID, $meta_key, true )
					: ( isset( $field['default'] ) ? $field['default'] : '' );
				?>
				<tr data-qaf-meta-key="<?php echo esc_attr( $meta_key ); ?>">
					<th scope="row"><label for="<?php echo esc_attr( 'qaf-meta-' . $meta_key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
					<td>
						<?php self::render_input( $meta_key, $field, $value ); ?>
						<p class="description"><?php echo esc_html( $field['description'] ); ?></p>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Verify and persist a generated meta box.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @return void
	 */
	public static function save_meta_box( $post_id, $post ) {
		$all_fields = self::get_fields();
		if ( ! isset( $all_fields[ $post->post_type ] ) ) {
			return;
		}

		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['qaf_core_meta_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['qaf_core_meta_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'qaf_core_save_meta_' . $post->post_type ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$posted = isset( $_POST['qaf_core_meta'] ) && is_array( $_POST['qaf_core_meta'] )
			? wp_unslash( $_POST['qaf_core_meta'] )
			: array();

		foreach ( $all_fields[ $post->post_type ] as $meta_key => $field ) {
			if ( ! empty( $field['readonly'] ) ) {
				continue;
			}

			if ( ! array_key_exists( $meta_key, $posted ) && 'boolean' !== $field['type'] ) {
				continue;
			}

			$raw_value = array_key_exists( $meta_key, $posted ) ? $posted[ $meta_key ] : 0;
			$value     = self::sanitize_value( $raw_value, $field );

			if ( 'social_url' === $field['type'] && '' === $value && is_scalar( $raw_value ) && '' !== trim( (string) $raw_value ) ) {
				// Reject an invalid replacement without erasing a previously stored legacy URL.
				continue;
			} elseif ( 'attachment' === $field['type'] && 0 === $value ) {
				delete_post_meta( $post_id, $meta_key );
			} elseif ( '' === $value && ! in_array( $field['type'], array( 'integer', 'boolean' ), true ) ) {
				delete_post_meta( $post_id, $meta_key );
			} else {
				update_post_meta( $post_id, $meta_key, $value );
			}
		}
	}

	/**
	 * Sanitize one registered meta value.
	 *
	 * @param mixed               $value Raw value.
	 * @param array<string,mixed> $field Field definition.
	 * @return mixed
	 */
	public static function sanitize_value( $value, $field ) {
		$type = isset( $field['type'] ) ? $field['type'] : 'text';

		switch ( $type ) {
			case 'boolean':
				return ! empty( $value ) && 'false' !== $value;
			case 'integer':
				return absint( $value );
			case 'textarea':
				return sanitize_textarea_field( is_scalar( $value ) ? (string) $value : '' );
			case 'url':
				return esc_url_raw( is_scalar( $value ) ? (string) $value : '', array( 'http', 'https' ) );
			case 'social_url':
				return self::sanitize_social_url( $value );
			case 'attachment':
				return self::sanitize_attachment_id( $value, $field );
			case 'date':
				return self::sanitize_date( $value );
			case 'datetime':
				return self::sanitize_datetime( $value );
			case 'select':
				$value = sanitize_key( is_scalar( $value ) ? (string) $value : '' );
				return isset( $field['options'][ $value ] ) ? $value : '';
			default:
				return sanitize_text_field( is_scalar( $value ) ? (string) $value : '' );
		}
	}

	/**
	 * Map editor input types to REST schema scalar types.
	 *
	 * @param string $field_type Editor type.
	 * @return string
	 */
	private static function get_rest_type( $field_type ) {
		if ( in_array( $field_type, array( 'integer', 'attachment' ), true ) ) {
			return 'integer';
		}
		if ( 'boolean' === $field_type ) {
			return 'boolean';
		}
		return 'string';
	}

	/**
	 * Render one input control.
	 *
	 * @param string              $meta_key Meta key.
	 * @param array<string,mixed> $field    Field definition.
	 * @param mixed               $value    Current value.
	 * @return void
	 */
	private static function render_input( $meta_key, $field, $value ) {
		$id   = 'qaf-meta-' . $meta_key;
		$name = 'qaf_core_meta[' . $meta_key . ']';

		if ( 'textarea' === $field['type'] ) {
			printf(
				'<textarea class="large-text" rows="4" id="%1$s" name="%2$s">%3$s</textarea>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_textarea( $value )
			);
			return;
		}

		if ( 'select' === $field['type'] ) {
			printf( '<select id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $name ) );
			foreach ( $field['options'] as $option_value => $option_label ) {
				printf(
					'<option value="%1$s"%2$s>%3$s</option>',
					esc_attr( $option_value ),
					selected( $value, $option_value, false ),
					esc_html( $option_label )
				);
			}
			echo '</select>';
			return;
		}

		if ( 'boolean' === $field['type'] ) {
			printf( '<input type="hidden" name="%1$s" value="0">', esc_attr( $name ) );
			printf(
				'<label><input type="checkbox" id="%1$s" name="%2$s" value="1"%3$s> Ya</label>',
				esc_attr( $id ),
				esc_attr( $name ),
				checked( (bool) $value, true, false )
			);
			return;
		}

		if ( 'attachment' === $field['type'] ) {
			$attachment_id = self::sanitize_attachment_id( $value, $field );
			$attachment     = $attachment_id ? get_post( $attachment_id ) : null;
			$attachment_name = $attachment instanceof WP_Post
				? ( get_the_title( $attachment ) ? get_the_title( $attachment ) : basename( (string) get_attached_file( $attachment_id ) ) )
				: __( 'Belum ada video lokal dipilih.', 'queen-alfalah-core' );
			?>
			<div class="qaf-attachment-picker" data-mime-prefix="<?php echo esc_attr( isset( $field['mime_prefix'] ) ? $field['mime_prefix'] : '' ); ?>">
				<input type="hidden" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $attachment_id ); ?>">
				<button type="button" class="button qaf-attachment-picker__select"><?php esc_html_e( 'Pilih dari Media Library', 'queen-alfalah-core' ); ?></button>
				<button type="button" class="button-link-delete qaf-attachment-picker__remove"<?php echo $attachment_id ? '' : ' hidden'; ?>><?php esc_html_e( 'Hapus pilihan', 'queen-alfalah-core' ); ?></button>
				<span class="qaf-attachment-picker__label"><?php echo esc_html( $attachment_name ); ?></span>
			</div>
			<?php
			return;
		}

		$type = 'text';
		if ( in_array( $field['type'], array( 'url', 'social_url' ), true ) ) {
			$type = 'url';
		} elseif ( 'date' === $field['type'] ) {
			$type = 'date';
		} elseif ( 'datetime' === $field['type'] ) {
			$type = 'datetime-local';
		} elseif ( 'integer' === $field['type'] ) {
			$type = 'number';
		}

		printf(
			'<input class="regular-text" type="%1$s" id="%2$s" name="%3$s" value="%4$s"%5$s>',
			esc_attr( $type ),
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( $value ),
			( 'number' === $type ? ' min="0" step="1"' : '' ) . ( ! empty( $field['readonly'] ) ? ' readonly aria-readonly="true"' : '' )
		);
	}

	/**
	 * Return the exact social hosts accepted for one canonical post URL.
	 *
	 * @return array<string,array<int,string>>
	 */
	private static function social_hosts() {
		return array(
			'instagram' => array( 'instagram.com', 'www.instagram.com' ),
			'tiktok'    => array( 'tiktok.com', 'www.tiktok.com', 'm.tiktok.com' ),
			'facebook'  => array( 'facebook.com', 'www.facebook.com', 'm.facebook.com' ),
			'youtube'   => array( 'youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be' ),
		);
	}

	/**
	 * Keep social URLs on HTTPS and on a known provider host.
	 *
	 * @param mixed $value Raw URL.
	 * @return string
	 */
	private static function sanitize_social_url( $value ) {
		$url = esc_url_raw( is_scalar( $value ) ? trim( (string) $value ) : '', array( 'https' ) );
		if ( '' === $url ) {
			return '';
		}

		$scheme = strtolower( (string) wp_parse_url( $url, PHP_URL_SCHEME ) );
		$host   = strtolower( rtrim( (string) wp_parse_url( $url, PHP_URL_HOST ), '.' ) );
		$port   = wp_parse_url( $url, PHP_URL_PORT );
		$user   = wp_parse_url( $url, PHP_URL_USER );
		if ( 'https' !== $scheme || $user || ( null !== $port && 443 !== (int) $port ) ) {
			return '';
		}

		$allowed_hosts = array();
		foreach ( self::social_hosts() as $hosts ) {
			$allowed_hosts = array_merge( $allowed_hosts, $hosts );
		}

		return in_array( $host, array_unique( $allowed_hosts ), true ) ? $url : '';
	}

	/**
	 * Accept an attachment only when its MIME type matches the field contract.
	 *
	 * @param mixed               $value Raw attachment ID.
	 * @param array<string,mixed> $field Field definition.
	 * @return int
	 */
	private static function sanitize_attachment_id( $value, $field ) {
		$attachment_id = absint( is_scalar( $value ) ? $value : 0 );
		if ( ! $attachment_id || 'attachment' !== get_post_type( $attachment_id ) ) {
			return 0;
		}

		$mime_prefix = isset( $field['mime_prefix'] ) ? sanitize_mime_type( $field['mime_prefix'] ) : '';
		$mime_type   = (string) get_post_mime_type( $attachment_id );
		if ( $mime_prefix && 0 !== strpos( $mime_type, $mime_prefix ) ) {
			return 0;
		}

		return $attachment_id;
	}

	/**
	 * Validate an ISO date.
	 *
	 * @param mixed $value Raw date.
	 * @return string
	 */
	private static function sanitize_date( $value ) {
		$value = is_scalar( $value ) ? trim( (string) $value ) : '';
		if ( '' === $value ) {
			return '';
		}

		if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $value, $matches ) ) {
			return '';
		}

		return checkdate( (int) $matches[2], (int) $matches[3], (int) $matches[1] ) ? $value : '';
	}

	/**
	 * Validate a local ISO date and time.
	 *
	 * @param mixed $value Raw date/time.
	 * @return string
	 */
	private static function sanitize_datetime( $value ) {
		$value = is_scalar( $value ) ? trim( (string) $value ) : '';
		if ( '' === $value ) {
			return '';
		}

		if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})T([01]\d|2[0-3]):([0-5]\d)$/', $value, $matches ) ) {
			return '';
		}

		return checkdate( (int) $matches[2], (int) $matches[3], (int) $matches[1] ) ? $value : '';
	}
}
