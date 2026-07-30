<?php
/**
 * Practical admin-list enhancements for school content.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add structured columns to CPT list screens.
 */
final class QAF_Core_Admin {
	/**
	 * Attach dynamic column and sort hooks.
	 *
	 * @return void
	 */
	public static function init() {
		foreach ( self::get_column_map() as $post_type => $columns ) {
			add_filter(
				'manage_' . $post_type . '_posts_columns',
				static function ( $existing ) use ( $columns ) {
					return QAF_Core_Admin::add_columns( $existing, $columns );
				}
			);
			add_action(
				'manage_' . $post_type . '_posts_custom_column',
				static function ( $column, $post_id ) use ( $columns ) {
					QAF_Core_Admin::render_column( $column, $post_id, $columns );
				},
				10,
				2
			);
			add_filter(
				'manage_edit-' . $post_type . '_sortable_columns',
				static function ( $sortable ) use ( $columns ) {
					return QAF_Core_Admin::add_sortable_columns( $sortable, $columns );
				}
			);
		}

		add_action( 'pre_get_posts', array( __CLASS__, 'apply_meta_sorting' ) );
	}

	/**
	 * Structured column definitions.
	 *
	 * @return array<string,array<string,array<string,mixed>>>
	 */
	public static function get_column_map() {
		return array(
			'qaf_program'     => array(
				'qaf_thumbnail'    => array( 'label' => 'Gambar', 'type' => 'thumbnail' ),
				'qaf_program_code' => array( 'label' => 'Kode', 'meta' => '_qaf_program_code', 'type' => 'text', 'sortable' => true ),
				'qaf_program_head' => array( 'label' => 'Kepala Program', 'meta' => '_qaf_program_head', 'type' => 'text' ),
			),
			'qaf_teacher'     => array(
				'qaf_thumbnail' => array( 'label' => 'Foto', 'type' => 'thumbnail' ),
				'qaf_role'      => array( 'label' => 'Jabatan', 'meta' => '_qaf_role', 'type' => 'text', 'sortable' => true ),
				'qaf_subject'   => array( 'label' => 'Mapel/Unit', 'meta' => '_qaf_subject', 'type' => 'text' ),
				'qaf_order'     => array( 'label' => 'Urutan', 'meta' => '_qaf_order', 'type' => 'integer', 'sortable' => true ),
			),
			'qaf_notice'      => array(
				'qaf_priority' => array( 'label' => 'Prioritas', 'meta' => '_qaf_priority', 'type' => 'select', 'sortable' => true ),
				'qaf_expiry'   => array( 'label' => 'Berlaku Sampai', 'meta' => '_qaf_expiry', 'type' => 'date', 'sortable' => true ),
				'qaf_file_url' => array( 'label' => 'Lampiran', 'meta' => '_qaf_file_url', 'type' => 'url' ),
			),
			'qaf_agenda'      => array(
				'qaf_start_date' => array( 'label' => 'Mulai', 'meta' => '_qaf_start_date', 'type' => 'datetime', 'sortable' => true ),
				'qaf_end_date'   => array( 'label' => 'Selesai', 'meta' => '_qaf_end_date', 'type' => 'datetime' ),
				'qaf_location'   => array( 'label' => 'Lokasi', 'meta' => '_qaf_location', 'type' => 'text' ),
			),
			'qaf_achievement' => array(
				'qaf_thumbnail'        => array( 'label' => 'Foto', 'type' => 'thumbnail' ),
				'qaf_award'            => array( 'label' => 'Juara', 'meta' => '_qaf_award', 'type' => 'text', 'sortable' => true ),
				'qaf_field'            => array( 'label' => 'Bidang', 'meta' => '_qaf_field', 'type' => 'text', 'sortable' => true ),
				'qaf_level'            => array( 'label' => 'Tingkat', 'meta' => '_qaf_level', 'type' => 'text', 'sortable' => true ),
				'qaf_achievement_date' => array( 'label' => 'Tanggal', 'meta' => '_qaf_achievement_date', 'type' => 'date', 'sortable' => true ),
				'qaf_recipient'        => array( 'label' => 'Penerima', 'meta' => '_qaf_recipient', 'type' => 'excerpt' ),
				'qaf_organizer'        => array( 'label' => 'Penyelenggara', 'meta' => '_qaf_organizer', 'type' => 'text' ),
				'qaf_source_url'       => array( 'label' => 'Sumber', 'meta' => '_qaf_source_url', 'type' => 'url' ),
			),
			'qaf_extra'       => array(
				'qaf_thumbnail' => array( 'label' => 'Gambar', 'type' => 'thumbnail' ),
				'qaf_schedule'  => array( 'label' => 'Jadwal', 'meta' => '_qaf_schedule', 'type' => 'text' ),
				'qaf_coach'     => array( 'label' => 'Pembina', 'meta' => '_qaf_coach', 'type' => 'text' ),
				'qaf_extra_location' => array( 'label' => 'Lokasi', 'meta' => '_qaf_extra_location', 'type' => 'text' ),
				'qaf_benefits'  => array( 'label' => 'Manfaat', 'meta' => '_qaf_benefits', 'type' => 'excerpt' ),
			),
			'qaf_service'     => array(
				'qaf_external_url' => array( 'label' => 'Alamat Layanan', 'meta' => '_qaf_external_url', 'type' => 'url' ),
				'qaf_icon_name'    => array( 'label' => 'Ikon', 'meta' => '_qaf_icon_name', 'type' => 'text' ),
				'qaf_open_new'     => array( 'label' => 'Tab Baru', 'meta' => '_qaf_open_new', 'type' => 'boolean' ),
			),
			'qaf_gallery'     => array(
				'qaf_thumbnail'          => array( 'label' => 'Sampul', 'type' => 'thumbnail' ),
				'qaf_gallery_source'     => array( 'label' => 'Sumber', 'meta' => '_qaf_gallery_source', 'type' => 'select', 'empty_label' => 'Otomatis / kompatibilitas lama' ),
				'qaf_gallery_media_type' => array( 'label' => 'Jenis Media', 'meta' => '_qaf_gallery_media_type', 'type' => 'select', 'default' => 'photo' ),
				'qaf_album_date'         => array( 'label' => 'Tanggal Album', 'meta' => '_qaf_album_date', 'type' => 'date', 'sortable' => true ),
				'qaf_video_url'          => array( 'label' => 'URL Media', 'meta' => '_qaf_video_url', 'type' => 'url' ),
			),
			'qaf_partner'     => array(
				'qaf_thumbnail'      => array( 'label' => 'Logo', 'type' => 'thumbnail' ),
				'qaf_partner_sector' => array( 'label' => 'Sektor', 'meta' => '_qaf_partner_sector', 'type' => 'text', 'sortable' => true ),
				'qaf_partner_programs' => array( 'label' => 'Program', 'meta' => '_qaf_partner_programs', 'type' => 'excerpt' ),
				'qaf_partner_verification' => array( 'label' => 'Status Data', 'meta' => '_qaf_partner_verification', 'type' => 'select', 'sortable' => true ),
				'qaf_partner_url'    => array( 'label' => 'Website', 'meta' => '_qaf_partner_url', 'type' => 'url' ),
			),
			'qaf_vacancy'     => array(
				'qaf_company'  => array( 'label' => 'Perusahaan', 'meta' => '_qaf_company', 'type' => 'text', 'sortable' => true ),
				'qaf_deadline' => array( 'label' => 'Batas Lamaran', 'meta' => '_qaf_deadline', 'type' => 'date', 'sortable' => true ),
				'qaf_apply_url' => array( 'label' => 'Lamaran', 'meta' => '_qaf_apply_url', 'type' => 'url' ),
			),
			'qaf_alumni'      => array(
				'qaf_thumbnail'       => array( 'label' => 'Foto', 'type' => 'thumbnail' ),
				'qaf_graduation_year' => array( 'label' => 'Lulus', 'meta' => '_qaf_graduation_year', 'type' => 'integer', 'sortable' => true ),
				'qaf_current_role'    => array( 'label' => 'Peran Saat Ini', 'meta' => '_qaf_current_role', 'type' => 'text' ),
			),
			'qaf_facility'    => array(
				'qaf_thumbnail'       => array( 'label' => 'Foto', 'type' => 'thumbnail' ),
				'qaf_facility_location' => array( 'label' => 'Lokasi', 'meta' => '_qaf_facility_location', 'type' => 'text' ),
				'qaf_capacity'        => array( 'label' => 'Kapasitas', 'meta' => '_qaf_capacity', 'type' => 'integer', 'sortable' => true ),
				'qaf_facility_status' => array( 'label' => 'Status', 'meta' => '_qaf_facility_status', 'type' => 'select', 'sortable' => true ),
				'qaf_facility_manager' => array( 'label' => 'Penanggung Jawab', 'meta' => '_qaf_facility_manager', 'type' => 'text' ),
			),
		);
	}

	/**
	 * Insert custom columns before WordPress's date column.
	 *
	 * @param array<string,string>              $existing Existing columns.
	 * @param array<string,array<string,mixed>> $columns  New columns.
	 * @return array<string,string>
	 */
	public static function add_columns( $existing, $columns ) {
		$date = isset( $existing['date'] ) ? $existing['date'] : null;
		unset( $existing['date'] );

		foreach ( $columns as $key => $definition ) {
			$existing[ $key ] = $definition['label'];
		}

		if ( null !== $date ) {
			$existing['date'] = $date;
		}

		return $existing;
	}

	/**
	 * Render one custom column.
	 *
	 * @param string                             $column  Column key.
	 * @param int                                $post_id Post ID.
	 * @param array<string,array<string,mixed>> $columns Column definitions.
	 * @return void
	 */
	public static function render_column( $column, $post_id, $columns ) {
		if ( ! isset( $columns[ $column ] ) ) {
			return;
		}

		$definition = $columns[ $column ];
		if ( 'thumbnail' === $definition['type'] ) {
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, array( 56, 56 ), array( 'style' => 'width:56px;height:56px;object-fit:cover;border-radius:6px;' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				echo '<span aria-hidden="true">—</span><span class="screen-reader-text">Tidak ada gambar</span>';
			}
			return;
		}

		$value = get_post_meta( $post_id, $definition['meta'], true );
		if ( ( '' === $value || null === $value ) && array_key_exists( 'default', $definition ) ) {
			$value = $definition['default'];
		}
		if ( '' === $value || null === $value ) {
			if ( isset( $definition['empty_label'] ) && '' !== $definition['empty_label'] ) {
				echo esc_html( $definition['empty_label'] );
				return;
			}
			echo '<span aria-hidden="true">—</span><span class="screen-reader-text">Belum diisi</span>';
			return;
		}

		switch ( $definition['type'] ) {
			case 'boolean':
				echo $value ? esc_html__( 'Ya', 'queen-alfalah-core' ) : esc_html__( 'Tidak', 'queen-alfalah-core' );
				break;
			case 'url':
				printf(
					'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s<span class="screen-reader-text"> (tab baru)</span></a>',
					esc_url( $value ),
					esc_html( self::url_label( $value ) )
				);
				break;
			case 'date':
				echo esc_html( self::format_date( $value, false ) );
				break;
			case 'datetime':
				echo esc_html( self::format_date( $value, true ) );
				break;
			case 'select':
				echo esc_html( self::select_label( $definition['meta'], $value ) );
				break;
			case 'excerpt':
				echo esc_html( wp_trim_words( $value, 10, '…' ) );
				break;
			case 'integer':
				echo esc_html( number_format_i18n( (int) $value ) );
				break;
			default:
				echo esc_html( $value );
				break;
		}
	}

	/**
	 * Expose opted-in meta columns to WordPress sorting controls.
	 *
	 * @param array<string,string>              $sortable Existing sortable map.
	 * @param array<string,array<string,mixed>> $columns  Column definitions.
	 * @return array<string,string>
	 */
	public static function add_sortable_columns( $sortable, $columns ) {
		foreach ( $columns as $key => $definition ) {
			if ( ! empty( $definition['sortable'] ) && ! empty( $definition['meta'] ) ) {
				$sortable[ $key ] = $definition['meta'];
			}
		}
		return $sortable;
	}

	/**
	 * Convert a selected meta-key order into a safe meta value sort.
	 *
	 * @param WP_Query $query Query.
	 * @return void
	 */
	public static function apply_meta_sorting( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$orderby = $query->get( 'orderby' );
		if ( ! is_string( $orderby ) || 0 !== strpos( $orderby, '_qaf_' ) ) {
			return;
		}

		$allowed = array();
		$numeric = array( '_qaf_order', '_qaf_graduation_year', '_qaf_capacity' );
		foreach ( self::get_column_map() as $columns ) {
			foreach ( $columns as $definition ) {
				if ( ! empty( $definition['sortable'] ) && ! empty( $definition['meta'] ) ) {
					$allowed[] = $definition['meta'];
				}
			}
		}

		if ( ! in_array( $orderby, $allowed, true ) ) {
			return;
		}

		$query->set( 'meta_key', $orderby ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$query->set( 'orderby', in_array( $orderby, $numeric, true ) ? 'meta_value_num' : 'meta_value' );
	}

	/**
	 * Create a compact external URL label.
	 *
	 * @param string $url URL.
	 * @return string
	 */
	private static function url_label( $url ) {
		$host = wp_parse_url( $url, PHP_URL_HOST );
		return $host ? $host : 'Buka tautan';
	}

	/**
	 * Format a stored local date/time in the configured site timezone.
	 *
	 * @param string $value     Stored value.
	 * @param bool   $with_time Include time.
	 * @return string
	 */
	private static function format_date( $value, $with_time ) {
		$format = get_option( 'date_format' );
		if ( $with_time ) {
			$format .= ' ' . get_option( 'time_format' );
			$date    = date_create_immutable_from_format( 'Y-m-d\TH:i', $value, wp_timezone() );
		} else {
			$date = date_create_immutable_from_format( 'Y-m-d', $value, wp_timezone() );
		}

		return $date ? wp_date( $format, $date->getTimestamp(), wp_timezone() ) : $value;
	}

	/**
	 * Resolve the human label for a select meta value.
	 *
	 * @param string $meta_key Meta key.
	 * @param string $value    Stored option value.
	 * @return string
	 */
	private static function select_label( $meta_key, $value ) {
		foreach ( QAF_Core_Meta::get_fields() as $fields ) {
			if ( isset( $fields[ $meta_key ]['options'][ $value ] ) ) {
				return $fields[ $meta_key ]['options'][ $value ];
			}
		}
		return $value;
	}
}
