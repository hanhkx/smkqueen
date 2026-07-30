<?php
/**
 * Seed and upgrade the centralized application directory.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class QAF_Core_Applications {
	const VERSION_OPTION = 'qaf_applications_schema_version';
	const SCHEMA_VERSION = '1.2.0';

	/** Attach the safe, idempotent upgrade routine. */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'maybe_upgrade' ) );
	}

	/** Create starter cards once without overwriting editor changes. */
	public static function maybe_upgrade() {
		if ( self::SCHEMA_VERSION === get_option( self::VERSION_OPTION ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$registration_url = qaf_core_get_setting( 'registration_url', '' );
		$applications     = array(
			'aplikasi-ujian'           => array( 'Ujian Online', 'monitor', '', 'Akses sistem ujian berbasis komputer untuk asesmen dan evaluasi pembelajaran.' ),
			'aplikasi-e-rapor'         => array( 'E-Rapor', 'award', '', 'Pengelolaan dan akses laporan hasil belajar peserta didik secara digital.' ),
			'aplikasi-e-perpus'        => array( 'Buku & E-Perpustakaan', 'book', '', 'Katalog buku, koleksi digital, serta layanan peminjaman perpustakaan sekolah.' ),
			'aplikasi-spmb'            => array( 'SPMB', 'users', $registration_url, 'Pusat informasi dan sistem penerimaan murid baru SMK Queen Al-Falah.' ),
			'aplikasi-gamifikasi-edu'  => array( 'Gamifikasi Edu', 'play', '', 'Pembelajaran interaktif berbasis tantangan, poin, dan aktivitas edukatif.' ),
			'aplikasi-pusat-media'     => array( 'Pusat Media', 'folder', QAF_Core_Media_Center::portal_url(), 'Portal privat setelah login untuk membuka folder Google Drive khusus divisi Waka, Guru, atau Tendik.', false ),
		);

		$order  = 0;
		$errors = array();
		foreach ( $applications as $slug => $application ) {
			++$order;
			$existing = get_page_by_path( $slug, OBJECT, 'qaf_service' );
			if ( $existing instanceof WP_Post ) {
				if ( 'aplikasi-pusat-media' === $slug ) {
					$legacy_excerpt = 'Portal privat untuk unggah dan unduh dokumen Waka, Guru, serta Tendik pada folder Drive masing-masing.';
					$legacy_content = '<!-- wp:paragraph --><p>' . esc_html( $legacy_excerpt ) . '</p><!-- /wp:paragraph -->';
					$update         = array( 'ID' => $existing->ID );

					if ( trim( $existing->post_excerpt ) === trim( $legacy_excerpt ) ) {
						$update['post_excerpt'] = $application[3];
					}
					if ( trim( $existing->post_content ) === trim( $legacy_content ) ) {
						$update['post_content'] = '<!-- wp:paragraph --><p>' . esc_html( $application[3] ) . '</p><!-- /wp:paragraph -->';
					}

					if ( count( $update ) > 1 ) {
						$result = wp_update_post(
							wp_slash( $update ),
							true
						);
						if ( is_wp_error( $result ) ) {
							$errors[] = sprintf( '%s: %s', $slug, $result->get_error_message() );
						}
					}
				}
				continue;
			}

			$result = wp_insert_post(
				array(
					'post_type'      => 'qaf_service',
					'post_status'    => 'publish',
					'post_title'     => $application[0],
					'post_name'      => $slug,
					'post_excerpt'   => $application[3],
					'post_content'   => '<!-- wp:paragraph --><p>' . esc_html( $application[3] ) . '</p><!-- /wp:paragraph -->',
					'menu_order'     => $order,
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'meta_input'     => array(
						'_qaf_external_url'   => esc_url_raw( $application[2] ),
						'_qaf_icon_name'      => $application[1],
						'_qaf_open_new'       => isset( $application[4] ) ? (bool) $application[4] : true,
						'_qaf_service_status' => $application[2] ? 'active' : 'inactive',
					),
				),
				true
			);
			if ( is_wp_error( $result ) ) {
				$errors[] = sprintf( '%s: %s', $slug, $result->get_error_message() );
			}
		}

		if ( empty( $errors ) ) {
			update_option( self::VERSION_OPTION, self::SCHEMA_VERSION, false );
			flush_rewrite_rules( false );
		}
	}
}
