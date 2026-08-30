<?php
/**
 * Custom post types and taxonomies.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the school content model.
 */
final class QAF_Core_Post_Types {
	/**
	 * Attach registration callbacks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 6 );
		add_action( 'pre_get_posts', array( __CLASS__, 'prepare_public_archives' ), 20 );
	}

	/**
	 * Apply the public archive rules expected by the companion theme.
	 *
	 * Admin, REST, secondary, and singular queries are deliberately untouched.
	 *
	 * @param WP_Query $query Main query.
	 * @return void
	 */
	public static function prepare_public_archives( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( $query->is_post_type_archive( 'qaf_agenda' ) ) {
			$query->set( 'meta_key', '_qaf_start_date' ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'order', 'ASC' );
			return;
		}

		if ( $query->is_post_type_archive( 'qaf_gallery' ) ) {
			self::filter_gallery_archive_source( $query );
			return;
		}

		if ( $query->is_post_type_archive( 'qaf_partner' ) ) {
			$query->set(
				'orderby',
				array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
					'ID'         => 'ASC',
				)
			);
			return;
		}

		if ( ! $query->is_post_type_archive( 'qaf_notice' ) ) {
			return;
		}

		$active_notice_query = array(
			'relation' => 'OR',
			array(
				'key'     => '_qaf_expiry',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_qaf_expiry',
				'value'   => '',
				'compare' => '=',
			),
			array(
				'key'     => '_qaf_expiry',
				'value'   => current_time( 'Y-m-d' ),
				'compare' => '>=',
				'type'    => 'DATE',
			),
		);

		$existing_meta_query = $query->get( 'meta_query' );
		if ( is_array( $existing_meta_query ) && ! empty( $existing_meta_query ) ) {
			$query->set(
				'meta_query', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'relation' => 'AND',
					$existing_meta_query,
					$active_notice_query,
				)
			);
			return;
		}

		$query->set( 'meta_query', $active_notice_query ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	}

	/**
	 * Limit the Gallery archive to one explicitly allowed media source.
	 *
	 * Existing entries without source metadata are treated as local so older
	 * content remains discoverable after the structured source field is added.
	 * Any meta query supplied by WordPress or another extension remains active.
	 *
	 * @param WP_Query $query Main Gallery archive query.
	 * @return void
	 */
	private static function filter_gallery_archive_source( $query ) {
		$raw_source = isset( $_GET['sumber'] ) ? wp_unslash( $_GET['sumber'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$source     = is_scalar( $raw_source ) ? sanitize_key( (string) $raw_source ) : '';
		$allowed    = array( 'local', 'instagram', 'tiktok', 'facebook', 'youtube' );
		if ( ! in_array( $source, $allowed, true ) ) {
			return;
		}

		if ( 'local' === $source ) {
			$source_query = array(
				'relation' => 'OR',
				array(
					'key'     => '_qaf_gallery_source',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => '_qaf_gallery_source',
					'value'   => '',
					'compare' => '=',
				),
				array(
					'key'     => '_qaf_gallery_source',
					'value'   => 'local',
					'compare' => '=',
				),
			);
		} else {
			$source_query = array(
				'key'     => '_qaf_gallery_source',
				'value'   => $source,
				'compare' => '=',
			);
		}

		$existing_meta_query = $query->get( 'meta_query' );
		if ( is_array( $existing_meta_query ) && ! empty( $existing_meta_query ) ) {
			$query->set(
				'meta_query', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'relation' => 'AND',
					$existing_meta_query,
					$source_query,
				)
			);
			return;
		}

		$query->set( 'meta_query', $source_query ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	}

	/**
	 * Return the public post type definitions.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function get_post_types() {
		return array(
			'qaf_program'     => array(
				'plural'   => 'Program Keahlian',
				'singular' => 'Program Keahlian',
				'slug'     => 'program-keahlian',
				'icon'     => 'dashicons-welcome-learn-more',
			),
			'qaf_teacher'     => array(
				'plural'   => 'Guru & Tendik',
				'singular' => 'Guru/Tendik',
				'slug'     => 'guru-tendik',
				'icon'     => 'dashicons-businessperson',
			),
			'qaf_notice'      => array(
				'plural'   => 'Pengumuman',
				'singular' => 'Pengumuman',
				'slug'     => 'pengumuman',
				'icon'     => 'dashicons-megaphone',
			),
			'qaf_agenda'      => array(
				'plural'   => 'Agenda',
				'singular' => 'Agenda',
				'slug'     => 'agenda',
				'icon'     => 'dashicons-calendar-alt',
			),
			'qaf_achievement' => array(
				'plural'   => 'Prestasi',
				'singular' => 'Prestasi',
				'slug'     => 'prestasi',
				'icon'     => 'dashicons-awards',
			),
			'qaf_extra'       => array(
				'plural'   => 'Ekstrakurikuler',
				'singular' => 'Ekstrakurikuler',
				'slug'     => 'ekstrakurikuler',
				'icon'     => 'dashicons-groups',
			),
			'qaf_service'     => array(
				'plural'   => 'Pusat Aplikasi',
				'singular' => 'Aplikasi',
				'slug'     => 'aplikasi',
				'icon'     => 'dashicons-admin-links',
			),
			'qaf_gallery'     => array(
				'plural'   => 'Galeri',
				'singular' => 'Galeri',
				'slug'     => 'galeri',
				'icon'     => 'dashicons-format-gallery',
			),
			'qaf_partner'     => array(
				'plural'   => 'Mitra Industri',
				'singular' => 'Mitra Industri',
				'slug'     => 'mitra-industri',
				'icon'     => 'dashicons-handshake',
			),
			'qaf_vacancy'     => array(
				'plural'   => 'Lowongan Kerja',
				'singular' => 'Lowongan Kerja',
				'slug'     => 'lowongan-kerja',
				'icon'     => 'dashicons-portfolio',
			),
			'qaf_alumni'      => array(
				'plural'   => 'Alumni',
				'singular' => 'Alumni',
				'slug'     => 'alumni',
				'icon'     => 'dashicons-id-alt',
			),
			'qaf_facility'    => array(
				'plural'   => 'Sarana Prasarana',
				'singular' => 'Sarana Prasarana',
				'slug'     => 'sarana-prasarana',
				'icon'     => 'dashicons-building',
			),
		);
	}

	/**
	 * Register every school post type.
	 *
	 * @return void
	 */
	public static function register_post_types() {
		foreach ( self::get_post_types() as $post_type => $definition ) {
			$labels = self::post_type_labels( $definition['plural'], $definition['singular'] );

			register_post_type(
				$post_type,
				array(
					'labels'              => $labels,
					'public'              => true,
					'publicly_queryable'  => true,
					'exclude_from_search' => false,
					'show_ui'             => true,
					'show_in_menu'        => 'qaf-school',
					'show_in_nav_menus'   => true,
					'show_in_admin_bar'   => true,
					'show_in_rest'        => true,
					'rest_base'           => $definition['slug'],
					'menu_icon'           => $definition['icon'],
					'has_archive'         => true,
					'hierarchical'        => false,
					'rewrite'             => array(
						'slug'       => $definition['slug'],
						'with_front' => false,
					),
					'query_var'           => true,
					'capability_type'     => 'post',
					'map_meta_cap'        => true,
					'delete_with_user'    => false,
					'supports'            => array(
						'title',
						'editor',
						'excerpt',
						'thumbnail',
						'page-attributes',
						'revisions',
						'custom-fields',
					),
				)
			);
		}
	}

	/**
	 * Return the taxonomy definitions.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function get_taxonomies() {
		return array(
			'qaf_program_field'     => array( 'Bidang Keahlian', 'Bidang Keahlian', 'bidang-keahlian', array( 'qaf_program' ) ),
			'qaf_staff_type'        => array( 'Jenis Guru/Tendik', 'Jenis Guru/Tendik', 'jenis-guru-tendik', array( 'qaf_teacher' ) ),
			'qaf_subject'           => array( 'Mata Pelajaran/Unit', 'Mata Pelajaran/Unit', 'mata-pelajaran-unit', array( 'qaf_teacher' ) ),
			'qaf_notice_type'       => array( 'Jenis Pengumuman', 'Jenis Pengumuman', 'jenis-pengumuman', array( 'qaf_notice' ) ),
			'qaf_audience'          => array( 'Audiens', 'Audiens', 'audiens', array( 'qaf_notice', 'qaf_agenda', 'qaf_service', 'qaf_vacancy' ) ),
			'qaf_agenda_type'       => array( 'Jenis Agenda', 'Jenis Agenda', 'jenis-agenda', array( 'qaf_agenda' ) ),
			'qaf_achievement_type'  => array( 'Jenis Prestasi', 'Jenis Prestasi', 'jenis-prestasi', array( 'qaf_achievement' ) ),
			'qaf_achievement_level' => array( 'Tingkat Prestasi', 'Tingkat Prestasi', 'tingkat-prestasi', array( 'qaf_achievement' ) ),
			'qaf_extra_type'        => array( 'Jenis Ekstrakurikuler', 'Jenis Ekstrakurikuler', 'jenis-ekstrakurikuler', array( 'qaf_extra' ) ),
			'qaf_service_type'      => array( 'Jenis Layanan', 'Jenis Layanan', 'jenis-layanan', array( 'qaf_service' ) ),
			'qaf_gallery_category'  => array( 'Kategori Galeri', 'Kategori Galeri', 'kategori-galeri', array( 'qaf_gallery' ) ),
			'qaf_partner_sector'    => array( 'Sektor Mitra', 'Sektor Mitra', 'sektor-mitra', array( 'qaf_partner' ) ),
			'qaf_vacancy_field'     => array( 'Bidang Lowongan', 'Bidang Lowongan', 'bidang-lowongan', array( 'qaf_vacancy' ) ),
			'qaf_facility_type'     => array( 'Jenis Sarana', 'Jenis Sarana', 'jenis-sarana', array( 'qaf_facility' ) ),
		);
	}

	/**
	 * Register taxonomies after their post types exist.
	 *
	 * @return void
	 */
	public static function register_taxonomies() {
		foreach ( self::get_taxonomies() as $taxonomy => $definition ) {
			register_taxonomy(
				$taxonomy,
				$definition[3],
				array(
					'labels'            => self::taxonomy_labels( $definition[0], $definition[1] ),
					'public'            => true,
					'publicly_queryable' => true,
					'hierarchical'      => true,
					'show_ui'           => true,
					'show_admin_column' => true,
					'show_in_nav_menus' => true,
					'show_tagcloud'     => false,
					'show_in_rest'      => true,
					'rewrite'           => array(
						'slug'         => $definition[2],
						'with_front'   => false,
						'hierarchical' => true,
					),
				)
			);
		}
	}

	/**
	 * Generate complete Indonesian post type labels.
	 *
	 * @param string $plural   Plural label.
	 * @param string $singular Singular label.
	 * @return array<string,string>
	 */
	private static function post_type_labels( $plural, $singular ) {
		return array(
			'name'                  => $plural,
			'singular_name'         => $singular,
			'menu_name'             => $plural,
			'name_admin_bar'        => $singular,
			'add_new'               => 'Tambah Baru',
			'add_new_item'          => 'Tambah ' . $singular,
			'new_item'              => $singular . ' Baru',
			'edit_item'             => 'Edit ' . $singular,
			'view_item'             => 'Lihat ' . $singular,
			'all_items'             => 'Semua ' . $plural,
			'search_items'          => 'Cari ' . $plural,
			'parent_item_colon'     => 'Induk ' . $singular . ':',
			'not_found'             => 'Belum ada data.',
			'not_found_in_trash'    => 'Tidak ada data di Sampah.',
			'featured_image'        => 'Gambar Utama',
			'set_featured_image'    => 'Tetapkan gambar utama',
			'remove_featured_image' => 'Hapus gambar utama',
			'use_featured_image'    => 'Gunakan sebagai gambar utama',
			'archives'              => 'Arsip ' . $plural,
			'attributes'            => 'Atribut ' . $singular,
			'insert_into_item'      => 'Sisipkan ke ' . strtolower( $singular ),
			'uploaded_to_this_item' => 'Diunggah ke ' . strtolower( $singular ) . ' ini',
			'filter_items_list'     => 'Saring daftar ' . strtolower( $plural ),
			'items_list_navigation' => 'Navigasi daftar ' . strtolower( $plural ),
			'items_list'            => 'Daftar ' . strtolower( $plural ),
		);
	}

	/**
	 * Generate complete Indonesian taxonomy labels.
	 *
	 * @param string $plural   Plural label.
	 * @param string $singular Singular label.
	 * @return array<string,string>
	 */
	private static function taxonomy_labels( $plural, $singular ) {
		return array(
			'name'              => $plural,
			'singular_name'     => $singular,
			'search_items'      => 'Cari ' . $plural,
			'all_items'         => 'Semua ' . $plural,
			'parent_item'       => 'Induk ' . $singular,
			'parent_item_colon' => 'Induk ' . $singular . ':',
			'edit_item'         => 'Edit ' . $singular,
			'update_item'       => 'Perbarui ' . $singular,
			'add_new_item'      => 'Tambah ' . $singular,
			'new_item_name'     => 'Nama ' . $singular . ' Baru',
			'menu_name'         => $plural,
			'not_found'         => 'Tidak ditemukan.',
		);
	}
}
