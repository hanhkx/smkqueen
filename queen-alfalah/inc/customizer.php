<?php
/**
 * Theme Customizer settings.
 *
 * Portable school content lives in Queen Al-Falah Core. These options also
 * provide a graceful fallback when the companion plugin is not active.
 *
 * @package Queen_AlFalah
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize checkbox values.
 *
 * @param mixed $checked Value submitted by Customizer.
 * @return bool
 */
function queen_alfalah_sanitize_checkbox( $checked ) {
	return (bool) $checked;
}

/**
 * Sanitize landing-page background display mode.
 *
 * @param string $value Submitted mode.
 * @return string
 */
function queen_alfalah_sanitize_background_mode( $value ) {
	$allowed = array( 'cover', 'repeat', 'repeat-x', 'repeat-y' );
	return in_array( $value, $allowed, true ) ? $value : 'cover';
}

/**
 * Sanitize landing-page background position.
 *
 * @param string $value Submitted position.
 * @return string
 */
function queen_alfalah_sanitize_background_position( $value ) {
	$allowed = array( 'center center', 'center top', 'center bottom', 'left center', 'right center' );
	return in_array( $value, $allowed, true ) ? $value : 'center center';
}

/**
 * Clamp overlay percentage to a readable range.
 *
 * @param mixed $value Submitted percentage.
 * @return int
 */
function queen_alfalah_sanitize_overlay( $value ) {
	return max( 20, min( 90, absint( $value ) ) );
}

/** Clamp the homepage vacancy-card count. */
function queen_alfalah_sanitize_vacancy_count( $value ) {
	return max( 3, min( 8, absint( $value ) ) );
}

/**
 * Register theme options.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function queen_alfalah_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

	$wp_customize->add_section(
		'queen_identity',
		array(
			'title'       => __( 'Identitas & Kontak Sekolah', 'queen-alfalah' ),
			'description' => __( 'Fallback tema. Jika Queen Al-Falah Core aktif, pengaturan Sekolah di plugin menjadi sumber utama.', 'queen-alfalah' ),
			'priority'    => 31,
		)
	);

	$defaults = queen_alfalah_default_school_settings();
	$fields   = array(
		'school_name'       => array( __( 'Nama sekolah', 'queen-alfalah' ), 'text', 'sanitize_text_field' ),
		'legal_name'        => array( __( 'Nama legal', 'queen-alfalah' ), 'text', 'sanitize_text_field' ),
		'motto'             => array( __( 'Motto', 'queen-alfalah' ), 'text', 'sanitize_text_field' ),
		'npsn'              => array( __( 'NPSN', 'queen-alfalah' ), 'text', 'sanitize_text_field' ),
		'accreditation'     => array( __( 'Akreditasi', 'queen-alfalah' ), 'text', 'sanitize_text_field' ),
		'founded'           => array( __( 'Tanggal berdiri', 'queen-alfalah' ), 'text', 'sanitize_text_field' ),
		'foundation'        => array( __( 'Yayasan', 'queen-alfalah' ), 'text', 'sanitize_text_field' ),
		'address'           => array( __( 'Alamat', 'queen-alfalah' ), 'textarea', 'sanitize_textarea_field' ),
		'phone'             => array( __( 'Telepon', 'queen-alfalah' ), 'text', 'sanitize_text_field' ),
		'whatsapp'          => array( __( 'WhatsApp (format 62...)', 'queen-alfalah' ), 'text', 'sanitize_text_field' ),
		'email'             => array( __( 'Email', 'queen-alfalah' ), 'email', 'sanitize_email' ),
		'opening_hours'     => array( __( 'Jam layanan', 'queen-alfalah' ), 'text', 'sanitize_text_field' ),
		'maps_url'          => array( __( 'URL peta', 'queen-alfalah' ), 'url', 'esc_url_raw' ),
		'ppdb_label'        => array( __( 'Label PPDB', 'queen-alfalah' ), 'text', 'sanitize_text_field' ),
		'ppdb_url'          => array( __( 'URL pendaftaran', 'queen-alfalah' ), 'url', 'esc_url_raw' ),
		'principal_name'    => array( __( 'Nama kepala sekolah', 'queen-alfalah' ), 'text', 'sanitize_text_field' ),
		'principal_title'   => array( __( 'Jabatan kepala sekolah', 'queen-alfalah' ), 'text', 'sanitize_text_field' ),
		'principal_message' => array( __( 'Pesan kepala sekolah', 'queen-alfalah' ), 'textarea', 'sanitize_textarea_field' ),
		'vision'            => array( __( 'Visi singkat', 'queen-alfalah' ), 'textarea', 'sanitize_textarea_field' ),
	);

	foreach ( $fields as $key => $field ) {
		$wp_customize->add_setting(
			'queen_' . $key,
			array(
				'default'           => isset( $defaults[ $key ] ) ? $defaults[ $key ] : '',
				'sanitize_callback' => $field[2],
			)
		);
		$wp_customize->add_control(
			'queen_' . $key,
			array(
				'label'   => $field[0],
				'section' => 'queen_identity',
				'type'    => $field[1],
			)
		);
	}

	$wp_customize->add_section(
		'queen_hero',
		array(
			'title'    => __( 'Hero Beranda', 'queen-alfalah' ),
			'priority' => 32,
		)
	);

	$hero_fields = array(
		'hero_kicker'          => array( __( 'Label kecil', 'queen-alfalah' ), __( 'Sekolah Vokasi Berbasis Pesantren', 'queen-alfalah' ), 'text', 'sanitize_text_field' ),
		'hero_title'           => array( __( 'Judul hero', 'queen-alfalah' ), __( 'Berilmu. Terampil. Berakhlaqul Karimah.', 'queen-alfalah' ), 'textarea', 'sanitize_textarea_field' ),
		'hero_text'            => array( __( 'Deskripsi hero', 'queen-alfalah' ), __( 'Menyiapkan generasi profesional, adaptif, dan berdaya saing melalui pendidikan vokasi yang terhubung dengan dunia kerja.', 'queen-alfalah' ), 'textarea', 'sanitize_textarea_field' ),
		'hero_primary_label'   => array( __( 'Teks tombol utama', 'queen-alfalah' ), __( 'Daftar Sekarang', 'queen-alfalah' ), 'text', 'sanitize_text_field' ),
		'hero_primary_url'     => array( __( 'URL tombol utama', 'queen-alfalah' ), $defaults['ppdb_url'], 'url', 'esc_url_raw' ),
		'hero_secondary_label' => array( __( 'Teks tombol kedua', 'queen-alfalah' ), __( 'Jelajahi Program', 'queen-alfalah' ), 'text', 'sanitize_text_field' ),
		'hero_secondary_url'   => array( __( 'URL tombol kedua', 'queen-alfalah' ), home_url( '/program-keahlian/' ), 'url', 'esc_url_raw' ),
	);

	foreach ( $hero_fields as $key => $field ) {
		$wp_customize->add_setting(
			'queen_' . $key,
			array(
				'default'           => $field[1],
				'sanitize_callback' => $field[3],
				'transport'         => in_array( $key, array( 'hero_kicker', 'hero_title', 'hero_text' ), true ) ? 'postMessage' : 'refresh',
			)
		);
		$wp_customize->add_control(
			'queen_' . $key,
			array(
				'label'   => $field[0],
				'section' => 'queen_hero',
				'type'    => $field[2],
			)
		);
	}

	$wp_customize->add_setting(
		'queen_hero_image',
		array(
			'default'           => '',
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'queen_hero_image',
			array(
				'label'     => __( 'Foto hero sekolah', 'queen-alfalah' ),
				'section'   => 'queen_hero',
				'mime_type' => 'image',
			)
		)
	);

	$wp_customize->add_setting(
		'queen_landing_background_video',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'queen_landing_background_video',
			array(
				'label'       => __( 'Video background landing page (MP4/WebM)', 'queen-alfalah' ),
				'description' => __( 'Direkomendasikan sebagai pengganti GIF agar Beranda lebih ringan. Video hanya tampil pada hero, diputar tanpa suara, berulang otomatis, dan dapat diganti kapan saja.', 'queen-alfalah' ),
				'section'     => 'queen_hero',
				'mime_type'   => 'video',
			)
		)
	);

	$wp_customize->add_setting(
		'queen_landing_background',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'queen_landing_background',
			array(
				'label'       => __( 'Background landing page (gambar/GIF)', 'queen-alfalah' ),
				'description' => __( 'Tampil hanya pada hero Beranda. GIF bergerak dan berulang otomatis; ganti atau hapus kapan saja dari Media Library.', 'queen-alfalah' ),
				'section'     => 'queen_hero',
				'mime_type'   => 'image',
			)
		)
	);

	$wp_customize->add_setting(
		'queen_landing_bg_mode',
		array(
			'default'           => 'cover',
			'sanitize_callback' => 'queen_alfalah_sanitize_background_mode',
		)
	);
	$wp_customize->add_control(
		'queen_landing_bg_mode',
		array(
			'label'   => __( 'Mode background', 'queen-alfalah' ),
			'section' => 'queen_hero',
			'type'    => 'select',
			'choices' => array(
				'cover'    => __( 'Penuhi area (cover)', 'queen-alfalah' ),
				'repeat'   => __( 'Ulangi horizontal & vertikal', 'queen-alfalah' ),
				'repeat-x' => __( 'Ulangi horizontal', 'queen-alfalah' ),
				'repeat-y' => __( 'Ulangi vertikal', 'queen-alfalah' ),
			),
		)
	);

	$wp_customize->add_setting(
		'queen_landing_bg_position',
		array(
			'default'           => 'center center',
			'sanitize_callback' => 'queen_alfalah_sanitize_background_position',
		)
	);
	$wp_customize->add_control(
		'queen_landing_bg_position',
		array(
			'label'   => __( 'Posisi background', 'queen-alfalah' ),
			'section' => 'queen_hero',
			'type'    => 'select',
			'choices' => array(
				'center center' => __( 'Tengah', 'queen-alfalah' ),
				'center top'    => __( 'Tengah atas', 'queen-alfalah' ),
				'center bottom' => __( 'Tengah bawah', 'queen-alfalah' ),
				'left center'   => __( 'Kiri tengah', 'queen-alfalah' ),
				'right center'  => __( 'Kanan tengah', 'queen-alfalah' ),
			),
		)
	);

	$wp_customize->add_setting(
		'queen_landing_bg_overlay',
		array(
			'default'           => 68,
			'sanitize_callback' => 'queen_alfalah_sanitize_overlay',
		)
	);
	$wp_customize->add_control(
		'queen_landing_bg_overlay',
		array(
			'label'       => __( 'Kegelapan overlay', 'queen-alfalah' ),
			'description' => __( 'Naikkan nilainya jika teks sulit dibaca.', 'queen-alfalah' ),
			'section'     => 'queen_hero',
			'type'        => 'range',
			'input_attrs' => array(
				'min'  => 20,
				'max'  => 90,
				'step' => 1,
			),
		)
	);

	$wp_customize->add_setting(
		'queen_principal_photo',
		array(
			'default'           => '',
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'queen_principal_photo',
			array(
				'label'     => __( 'Foto kepala sekolah', 'queen-alfalah' ),
				'section'   => 'queen_hero',
				'mime_type' => 'image',
			)
		)
	);

	$wp_customize->add_section(
		'queen_home',
		array(
			'title'    => __( 'Beranda & Statistik', 'queen-alfalah' ),
			'priority' => 33,
		)
	);

	$stats = array(
		1 => array( '4', __( 'Program Keahlian', 'queen-alfalah' ) ),
		2 => array( '2011', __( 'Tahun Berdiri', 'queen-alfalah' ) ),
		3 => array( 'B', __( 'Akreditasi', 'queen-alfalah' ) ),
		4 => array( '20574699', __( 'NPSN', 'queen-alfalah' ) ),
	);
	foreach ( $stats as $number => $stat ) {
		foreach ( array( 'value' => $stat[0], 'label' => $stat[1] ) as $part => $default ) {
			$id = 'queen_stat_' . $number . '_' . $part;
			$wp_customize->add_setting( $id, array( 'default' => $default, 'sanitize_callback' => 'sanitize_text_field' ) );
			$wp_customize->add_control(
				$id,
				array(
					'label'       => sprintf( __( 'Statistik %1$d — %2$s', 'queen-alfalah' ), $number, 'value' === $part ? __( 'nilai', 'queen-alfalah' ) : __( 'label', 'queen-alfalah' ) ),
					'section'     => 'queen_home',
					'type'        => 'text',
					'input_attrs' => array( 'autocomplete' => 'off' ),
				)
			);
		}
	}

	$wp_customize->add_setting( 'queen_stats_updated', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control(
		'queen_stats_updated',
		array(
			'label'       => __( 'Catatan/tanggal pembaruan statistik', 'queen-alfalah' ),
			'description' => __( 'Contoh: Data diperbarui Juli 2026.', 'queen-alfalah' ),
			'section'     => 'queen_home',
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting( 'queen_show_whatsapp', array( 'default' => true, 'sanitize_callback' => 'queen_alfalah_sanitize_checkbox' ) );
	$wp_customize->add_control(
		'queen_show_whatsapp',
		array(
			'label'   => __( 'Tampilkan tombol WhatsApp mengambang', 'queen-alfalah' ),
			'section' => 'queen_home',
			'type'    => 'checkbox',
		)
	);

	$wp_customize->add_setting( 'queen_home_vacancies_show', array( 'default' => true, 'sanitize_callback' => 'queen_alfalah_sanitize_checkbox' ) );
	$wp_customize->add_control(
		'queen_home_vacancies_show',
		array(
			'label'       => __( 'Tampilkan Lowongan Kerja di beranda', 'queen-alfalah' ),
			'description' => __( 'Bagian ini tampil paling bawah sebelum footer dan tidak menghapus lowongan lama.', 'queen-alfalah' ),
			'section'     => 'queen_home',
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting( 'queen_home_vacancies_title', array( 'default' => __( 'Lowongan Kerja Terbaru', 'queen-alfalah' ), 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'queen_home_vacancies_title', array( 'label' => __( 'Judul bagian lowongan', 'queen-alfalah' ), 'section' => 'queen_home', 'type' => 'text' ) );

	$wp_customize->add_setting( 'queen_home_vacancies_description', array( 'default' => __( 'Informasi peluang kerja terkurasi untuk lulusan DKV, TJKT, MPLB, dan Layanan Kesehatan. Selalu periksa sumber asli sebelum melamar.', 'queen-alfalah' ), 'sanitize_callback' => 'sanitize_textarea_field' ) );
	$wp_customize->add_control( 'queen_home_vacancies_description', array( 'label' => __( 'Deskripsi bagian lowongan', 'queen-alfalah' ), 'section' => 'queen_home', 'type' => 'textarea' ) );

	$wp_customize->add_setting( 'queen_home_vacancies_count', array( 'default' => 4, 'sanitize_callback' => 'queen_alfalah_sanitize_vacancy_count' ) );
	$wp_customize->add_control(
		'queen_home_vacancies_count',
		array(
			'label'       => __( 'Jumlah lowongan di beranda', 'queen-alfalah' ),
			'description' => __( 'Pilih 3–8 lowongan terbaru. Semua lowongan lain tetap tersedia pada Bursa Kerja.', 'queen-alfalah' ),
			'section'     => 'queen_home',
			'type'        => 'number',
			'input_attrs' => array( 'min' => 3, 'max' => 8, 'step' => 1 ),
		)
	);

	$wp_customize->add_section(
		'queen_appearance',
		array(
			'title'    => __( 'Warna & Header', 'queen-alfalah' ),
			'priority' => 34,
		)
	);
	$wp_customize->add_setting( 'queen_primary_color', array( 'default' => '#0b5d4b', 'sanitize_callback' => 'sanitize_hex_color' ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'queen_primary_color', array( 'label' => __( 'Warna utama', 'queen-alfalah' ), 'section' => 'queen_appearance' ) ) );
	$wp_customize->add_setting( 'queen_accent_color', array( 'default' => '#d4a72c', 'sanitize_callback' => 'sanitize_hex_color' ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'queen_accent_color', array( 'label' => __( 'Warna aksen', 'queen-alfalah' ), 'section' => 'queen_appearance' ) ) );
	$wp_customize->add_setting( 'queen_sticky_header', array( 'default' => true, 'sanitize_callback' => 'queen_alfalah_sanitize_checkbox' ) );
	$wp_customize->add_control( 'queen_sticky_header', array( 'label' => __( 'Header tetap saat digulir', 'queen-alfalah' ), 'section' => 'queen_appearance', 'type' => 'checkbox' ) );

	$wp_customize->add_section(
		'queen_social',
		array(
			'title'    => __( 'Media Sosial', 'queen-alfalah' ),
			'priority' => 35,
		)
	);
	foreach ( array( 'instagram' => 'Instagram', 'facebook' => 'Facebook', 'youtube' => 'YouTube', 'tiktok' => 'TikTok' ) as $key => $label ) {
		$wp_customize->add_setting( 'queen_' . $key, array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
		$wp_customize->add_control( 'queen_' . $key, array( 'label' => $label, 'section' => 'queen_social', 'type' => 'url' ) );
	}

	$wp_customize->add_section(
		'queen_footer',
		array(
			'title'    => __( 'Footer', 'queen-alfalah' ),
			'priority' => 36,
		)
	);
	$wp_customize->add_setting(
		'queen_footer_note',
		array(
			'default'           => __( 'Pendidikan vokasi berbasis pesantren untuk generasi yang kompeten dan berkarakter.', 'queen-alfalah' ),
			'sanitize_callback' => 'sanitize_textarea_field',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control( 'queen_footer_note', array( 'label' => __( 'Deskripsi footer', 'queen-alfalah' ), 'section' => 'queen_footer', 'type' => 'textarea' ) );
}
add_action( 'customize_register', 'queen_alfalah_customize_register' );
