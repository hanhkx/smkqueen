<?php
/**
 * Portable school settings and Settings API integration.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage school identity and contact settings.
 */
final class QAF_Core_Settings {
	/** Option key used for all school settings. */
	const OPTION_NAME = 'qaf_core_settings';

	/** Settings API group. */
	const OPTION_GROUP = 'qaf_core_settings_group';

	/**
	 * Attach admin and Settings API hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	/**
	 * Verified defaults from the school website and government school record.
	 *
	 * @return array<string,string>
	 */
	public static function get_defaults() {
		return array(
			'school_name'      => 'SMK QUEEN AL-FALAH MOJO',
			'short_name'       => 'SMK QUEEN AL-FALAH',
			'motto'            => 'Pencetak Pelopor Teknologi yang Islami',
			'vision'           => 'Mencetak siswa SMK Queen Al Falah yang cerdas, profesional, memiliki daya saing di tingkat nasional maupun internasional, dan berakhlaqul karimah.',
			'mission'          => "Mengembangkan pendidikan kejuruan yang memiliki kecerdasan intelektual tinggi dan mampu bersaing di perguruan tinggi.\nMengembangkan pendidikan yang cakap, handal, adaptif, dan fleksibel dalam menghadapi abad komunikasi.\nMeningkatkan mutu pendidikan bidang teknologi informatika dan bisnis manajemen yang mampu mengisi pasar nasional dan internasional.\nMewujudkan dan membentuk peserta didik yang mampu mandiri, terampil, aktif, kreatif, dan inovatif.\nMencetak kader bangsa yang memiliki nilai-nilai kebangsaan tinggi dan mencerminkan manusia yang beragama.",
			'npsn'             => '20574699',
			'founded_date'     => '2011-02-21',
			'accreditation'    => 'B',
			'foundation'       => 'Yayasan YPI AL-MUTTAQIEN',
			'principal_name'   => 'Kepala SMK Queen Al-Falah',
			'principal_title'  => 'Kepala Sekolah',
			'principal_message' => 'Pendidikan vokasi yang kuat menyatukan kompetensi, karakter, dan keberanian untuk terus belajar.',
			'address'          => 'Jl. Raya Kebanan-Ploso Ds. Ploso Kec. Mojo Kab. Kediri',
			'phone'            => '03544520550',
			'email'            => 'smkqueenalfalah@yahoo.com',
			'website'          => 'https://smkqueenalfalah.sch.id/',
			'whatsapp'         => '6281222245445',
			'latitude'         => '-7.9199',
			'longitude'        => '111.9604',
			'map_url'          => '',
			'registration_url' => 'https://psb.queenalfalah.id/',
			'facebook_url'     => '',
			'instagram_url'    => '',
			'youtube_url'      => '',
			'tiktok_url'       => '',
			'media_drive_waka_url'    => '',
			'media_drive_teacher_url' => '',
			'media_drive_staff_url'   => '',
		);
	}

	/**
	 * Define fields, groups, input types, and descriptions.
	 *
	 * @return array<string,array<string,string>>
	 */
	public static function get_fields() {
		return array(
			'school_name'      => array(
				'label'       => 'Nama Resmi Sekolah',
				'type'        => 'text',
				'group'       => 'Identitas Sekolah',
				'description' => 'Nama sesuai data satuan pendidikan.',
			),
			'short_name'       => array(
				'label'       => 'Nama Singkat',
				'type'        => 'text',
				'group'       => 'Identitas Sekolah',
				'description' => 'Nama yang ditampilkan pada header dan elemen ringkas.',
			),
			'motto'            => array(
				'label'       => 'Moto',
				'type'        => 'text',
				'group'       => 'Identitas Sekolah',
				'description' => 'Moto atau slogan utama sekolah.',
			),
			'vision'           => array(
				'label'       => 'Visi',
				'type'        => 'textarea',
				'group'       => 'Visi dan Misi',
				'description' => 'Pernyataan visi sekolah.',
			),
			'mission'          => array(
				'label'       => 'Misi',
				'type'        => 'textarea',
				'group'       => 'Visi dan Misi',
				'description' => 'Gunakan satu butir misi per baris.',
			),
			'npsn'             => array(
				'label'       => 'NPSN',
				'type'        => 'npsn',
				'group'       => 'Identitas Sekolah',
				'description' => 'Nomor Pokok Sekolah Nasional.',
			),
			'founded_date'     => array(
				'label'       => 'Tanggal Berdiri',
				'type'        => 'date',
				'group'       => 'Identitas Sekolah',
				'description' => 'Tanggal SK pendirian.',
			),
			'accreditation'    => array(
				'label'       => 'Akreditasi',
				'type'        => 'text',
				'group'       => 'Identitas Sekolah',
				'description' => 'Nilai/status akreditasi yang berlaku.',
			),
			'foundation'       => array(
				'label'       => 'Yayasan',
				'type'        => 'text',
				'group'       => 'Identitas Sekolah',
				'description' => 'Nama badan penyelenggara.',
			),
			'principal_name'   => array(
				'label'       => 'Nama Kepala Sekolah',
				'type'        => 'text',
				'group'       => 'Kepala Sekolah',
				'description' => 'Nama lengkap beserta gelar yang ditampilkan pada beranda.',
			),
			'principal_title'  => array(
				'label'       => 'Jabatan',
				'type'        => 'text',
				'group'       => 'Kepala Sekolah',
				'description' => 'Contoh: Kepala SMK Queen Al-Falah.',
			),
			'principal_message' => array(
				'label'       => 'Pesan Singkat',
				'type'        => 'textarea',
				'group'       => 'Kepala Sekolah',
				'description' => 'Kutipan sambutan singkat yang ditampilkan pada landing page.',
			),
			'address'          => array(
				'label'       => 'Alamat',
				'type'        => 'textarea',
				'group'       => 'Kontak dan Lokasi',
				'description' => 'Alamat pos lengkap sekolah.',
			),
			'phone'            => array(
				'label'       => 'Telepon',
				'type'        => 'phone',
				'group'       => 'Kontak dan Lokasi',
				'description' => 'Nomor telepon resmi.',
			),
			'email'            => array(
				'label'       => 'Email',
				'type'        => 'email',
				'group'       => 'Kontak dan Lokasi',
				'description' => 'Alamat email resmi sekolah.',
			),
			'website'          => array(
				'label'       => 'Website',
				'type'        => 'url',
				'group'       => 'Kontak dan Lokasi',
				'description' => 'Alamat website utama sekolah.',
			),
			'whatsapp'         => array(
				'label'       => 'WhatsApp',
				'type'        => 'phone',
				'group'       => 'Kontak dan Lokasi',
				'description' => 'Kosongkan jika sekolah belum menetapkan nomor publik.',
			),
			'latitude'         => array(
				'label'       => 'Latitude',
				'type'        => 'latitude',
				'group'       => 'Kontak dan Lokasi',
				'description' => 'Koordinat lintang dalam format desimal.',
			),
			'longitude'        => array(
				'label'       => 'Longitude',
				'type'        => 'longitude',
				'group'       => 'Kontak dan Lokasi',
				'description' => 'Koordinat bujur dalam format desimal.',
			),
			'map_url'          => array(
				'label'       => 'Tautan Peta',
				'type'        => 'url',
				'group'       => 'Kontak dan Lokasi',
				'description' => 'Tautan menuju peta; bukan kode iframe.',
			),
			'registration_url' => array(
				'label'       => 'Tautan Pendaftaran',
				'type'        => 'url',
				'group'       => 'Tautan Resmi',
				'description' => 'Alamat sistem PSB/SPMB resmi.',
			),
			'facebook_url'     => array(
				'label'       => 'Facebook',
				'type'        => 'url',
				'group'       => 'Media Sosial',
				'description' => 'Kosongkan sampai akun resmi terverifikasi.',
			),
			'instagram_url'    => array(
				'label'       => 'Instagram',
				'type'        => 'url',
				'group'       => 'Media Sosial',
				'description' => 'Kosongkan sampai akun resmi terverifikasi.',
			),
			'youtube_url'      => array(
				'label'       => 'YouTube',
				'type'        => 'url',
				'group'       => 'Media Sosial',
				'description' => 'Kosongkan sampai kanal resmi terverifikasi.',
			),
			'tiktok_url'       => array(
				'label'       => 'TikTok',
				'type'        => 'url',
				'group'       => 'Media Sosial',
				'description' => 'Kosongkan sampai akun resmi terverifikasi.',
			),
			'media_drive_waka_url' => array(
				'label'       => 'Folder Google Drive Waka',
				'type'        => 'drive_url',
				'group'       => 'Pusat Media',
				'description' => 'Tautan folder Drive khusus Waka. Hanya akun dengan peran Waka yang melihat tautan ini.',
			),
			'media_drive_teacher_url' => array(
				'label'       => 'Folder Google Drive Guru',
				'type'        => 'drive_url',
				'group'       => 'Pusat Media',
				'description' => 'Tautan folder Drive khusus Guru. Ganti kapan saja bila folder penuh atau dipindahkan.',
			),
			'media_drive_staff_url' => array(
				'label'       => 'Folder Google Drive Tendik',
				'type'        => 'drive_url',
				'group'       => 'Pusat Media',
				'description' => 'Tautan folder Drive khusus Tenaga Kependidikan. Pastikan izin Drive dibatasi ke akun yang berwenang.',
			),
		);
	}

	/**
	 * Add the school admin menu before CPT submenus are attached.
	 *
	 * @return void
	 */
	public static function register_menu() {
		$capability = self::get_capability();

		add_menu_page(
			'Pengaturan Sekolah',
			'Sekolah',
			$capability,
			'qaf-school',
			array( __CLASS__, 'render_page' ),
			'dashicons-welcome-learn-more',
			25
		);

		add_submenu_page(
			'qaf-school',
			'Pengaturan Sekolah',
			'Pengaturan',
			$capability,
			'qaf-school',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Register a typed option with the Settings and REST APIs.
	 *
	 * @return void
	 */
	public static function register_settings() {
		$properties = array();

		foreach ( self::get_fields() as $key => $field ) {
			$properties[ $key ] = array(
				'type'        => 'string',
				'description' => $field['description'],
			);
		}

		register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			array(
				'type'              => 'object',
				'description'       => 'Identitas dan kontak resmi SMK Queen Al-Falah.',
				'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
				'default'           => self::get_defaults(),
				'show_in_rest'      => array(
					'schema' => array(
						'type'                 => 'object',
						'properties'           => $properties,
						'additionalProperties' => false,
					),
				),
			)
		);
	}

	/**
	 * Seed settings without overwriting an existing installation.
	 *
	 * @return void
	 */
	public static function install_defaults() {
		add_option( self::OPTION_NAME, self::get_defaults(), '', false );
	}

	/**
	 * Get one setting, falling back to its verified default.
	 *
	 * @param string $key      Setting key.
	 * @param mixed  $fallback Fallback for unknown keys.
	 * @return mixed
	 */
	public static function get_setting( $key, $fallback = null ) {
		$key      = sanitize_key( $key );
		$settings = get_option( self::OPTION_NAME, array() );
		$defaults = self::get_defaults();

		if ( is_array( $settings ) && array_key_exists( $key, $settings ) ) {
			return $settings[ $key ];
		}

		if ( array_key_exists( $key, $defaults ) ) {
			return $defaults[ $key ];
		}

		return $fallback;
	}

	/**
	 * Sanitize the complete settings payload.
	 *
	 * @param mixed $input Submitted value.
	 * @return array<string,string>
	 */
	public static function sanitize_settings( $input ) {
		if ( ! is_array( $input ) ) {
			add_settings_error( self::OPTION_NAME, 'invalid_payload', 'Data pengaturan tidak valid.' );
			return self::get_defaults();
		}

		$sanitized = array();
		foreach ( self::get_fields() as $key => $field ) {
			$value             = isset( $input[ $key ] ) ? wp_unslash( $input[ $key ] ) : '';
			$sanitized[ $key ] = self::sanitize_field( $value, $field['type'], $key );
		}

		return $sanitized;
	}

	/**
	 * Render the Settings API form.
	 *
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( self::get_capability() ) ) {
			wp_die( esc_html__( 'Anda tidak memiliki izin untuk mengakses halaman ini.', 'queen-alfalah-core' ) );
		}

		$settings     = wp_parse_args( get_option( self::OPTION_NAME, array() ), self::get_defaults() );
		$current_group = '';
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Pengaturan Sekolah', 'queen-alfalah-core' ); ?></h1>
			<p><?php echo esc_html__( 'Kelola identitas resmi yang digunakan bersama oleh tema dan seluruh model konten sekolah.', 'queen-alfalah-core' ); ?></p>
			<?php settings_errors( self::OPTION_NAME ); ?>
			<form action="options.php" method="post">
				<?php settings_fields( self::OPTION_GROUP ); ?>
				<table class="form-table" role="presentation">
					<tbody>
					<?php foreach ( self::get_fields() as $key => $field ) : ?>
						<?php if ( $current_group !== $field['group'] ) : ?>
							<?php $current_group = $field['group']; ?>
							<tr>
								<th colspan="2" scope="rowgroup">
									<h2 style="margin:1.25rem 0 0;"><?php echo esc_html( $current_group ); ?></h2>
								</th>
							</tr>
						<?php endif; ?>
						<tr>
							<th scope="row">
								<label for="qaf-core-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							</th>
							<td>
								<?php self::render_field( $key, $field, isset( $settings[ $key ] ) ? $settings[ $key ] : '' ); ?>
								<p class="description"><?php echo esc_html( $field['description'] ); ?></p>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php submit_button( 'Simpan Pengaturan' ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Capability used for settings and setup tools.
	 *
	 * @return string
	 */
	public static function get_capability() {
		return (string) apply_filters( 'qaf_core_manage_settings_capability', 'manage_options' );
	}

	/**
	 * Sanitize one value according to its field type.
	 *
	 * @param mixed  $value Value.
	 * @param string $type  Field type.
	 * @param string $key   Field key for error messages.
	 * @return string
	 */
	private static function sanitize_field( $value, $type, $key ) {
		$value = is_scalar( $value ) ? (string) $value : '';

		switch ( $type ) {
			case 'textarea':
				return sanitize_textarea_field( $value );
			case 'email':
				return sanitize_email( $value );
			case 'url':
				return esc_url_raw( $value, array( 'http', 'https' ) );
			case 'drive_url':
				$url  = esc_url_raw( $value, array( 'https' ) );
				$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
				if ( '' === $url ) {
					return '';
				}
				if ( ! in_array( $host, array( 'drive.google.com', 'docs.google.com' ), true ) ) {
					add_settings_error( self::OPTION_NAME, 'invalid_' . $key, 'Tautan Pusat Media harus berasal dari Google Drive.' );
					return '';
				}
				return $url;
			case 'npsn':
				return preg_replace( '/[^0-9]/', '', $value );
			case 'phone':
				return trim( preg_replace( '/[^0-9+().\s-]/', '', $value ) );
			case 'date':
				if ( '' === $value ) {
					return '';
				}
				if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $value, $matches ) || ! checkdate( (int) $matches[2], (int) $matches[3], (int) $matches[1] ) ) {
					add_settings_error( self::OPTION_NAME, 'invalid_' . $key, 'Tanggal berdiri tidak valid.' );
					return '';
				}
				return $value;
			case 'latitude':
				return self::sanitize_coordinate( $value, -90, 90 );
			case 'longitude':
				return self::sanitize_coordinate( $value, -180, 180 );
			default:
				return sanitize_text_field( $value );
		}
	}

	/**
	 * Sanitize a decimal coordinate and enforce its range.
	 *
	 * @param string $value Value.
	 * @param float  $min   Minimum.
	 * @param float  $max   Maximum.
	 * @return string
	 */
	private static function sanitize_coordinate( $value, $min, $max ) {
		$value = trim( str_replace( ',', '.', $value ) );
		if ( '' === $value || ! is_numeric( $value ) ) {
			return '';
		}

		$number = (float) $value;
		if ( $number < $min || $number > $max ) {
			return '';
		}

		return rtrim( rtrim( number_format( $number, 7, '.', '' ), '0' ), '.' );
	}

	/**
	 * Render one settings field.
	 *
	 * @param string              $key   Setting key.
	 * @param array<string,string> $field Field definition.
	 * @param string              $value Current value.
	 * @return void
	 */
	private static function render_field( $key, $field, $value ) {
		$name = self::OPTION_NAME . '[' . $key . ']';
		$id   = 'qaf-core-' . $key;

		if ( 'textarea' === $field['type'] ) {
			printf(
				'<textarea class="large-text" rows="5" id="%1$s" name="%2$s">%3$s</textarea>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_textarea( $value )
			);
			return;
		}

		$html_type = in_array( $field['type'], array( 'email', 'url', 'date' ), true ) ? $field['type'] : ( 'drive_url' === $field['type'] ? 'url' : 'text' );
		printf(
			'<input class="regular-text" type="%1$s" id="%2$s" name="%3$s" value="%4$s" autocomplete="off">',
			esc_attr( $html_type ),
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( $value )
		);
	}
}
