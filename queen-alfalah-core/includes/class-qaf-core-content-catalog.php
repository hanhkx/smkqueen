<?php
/**
 * Versioned starter catalogs for facilities, industry partners, and activities.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fill the three public catalogs without replacing editor-owned information.
 */
final class QAF_Core_Content_Catalog {
	const FACILITY_VERSION_OPTION = 'qaf_facility_catalog_version';
	const PARTNER_VERSION_OPTION  = 'qaf_partner_catalog_version';
	const EXTRA_VERSION_OPTION    = 'qaf_extra_catalog_version';

	/** Run safe catalog upgrades from an administrator request. */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'maybe_upgrade' ), 35 );
	}

	/** Seed every bundled catalog during plugin activation. */
	public static function activate() {
		self::upgrade_dataset( 'facility-data.php', 'qaf_facility', '_qaf_facility_seed_key', self::FACILITY_VERSION_OPTION, false );
		self::upgrade_dataset( 'partner-data.php', 'qaf_partner', '_qaf_partner_seed_key', self::PARTNER_VERSION_OPTION, false );
		self::upgrade_dataset( 'extracurricular-data.php', 'qaf_extra', '_qaf_extra_seed_key', self::EXTRA_VERSION_OPTION, true );
	}

	/** Apply newly bundled records after an update. */
	public static function maybe_upgrade() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		self::activate();
	}

	/**
	 * Import a single versioned dataset when its version advances.
	 *
	 * @param string $filename       Data file name below includes/.
	 * @param string $post_type      Destination post type.
	 * @param string $seed_meta      Stable ownership marker.
	 * @param string $version_option Stored version option.
	 * @param bool   $enrich_existing Whether missing fields on matching entries may be filled.
	 * @return void
	 */
	private static function upgrade_dataset( $filename, $post_type, $seed_meta, $version_option, $enrich_existing ) {
		$data = self::load_dataset( $filename );
		if ( is_wp_error( $data ) ) {
			return;
		}

		$current = (string) get_option( $version_option, '0.0.0' );
		if ( version_compare( $current, $data['version'], '>=' ) ) {
			return;
		}

		$errors = self::import_items( $data['items'], $post_type, $seed_meta, $enrich_existing );
		if ( empty( $errors ) ) {
			update_option( $version_option, $data['version'], false );
		}
	}

	/**
	 * Load a local PHP data array and enforce its top-level contract.
	 *
	 * @param string $filename Data filename.
	 * @return array<string,mixed>|WP_Error
	 */
	private static function load_dataset( $filename ) {
		$path = QAF_CORE_PATH . 'includes/' . basename( $filename );
		if ( ! is_readable( $path ) ) {
			return new WP_Error( 'qaf_catalog_missing', 'Berkas katalog tidak dapat dibaca.' );
		}

		$data = require $path;
		if (
			! is_array( $data )
			|| empty( $data['version'] )
			|| ! is_string( $data['version'] )
			|| ! preg_match( '/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/', $data['version'] )
			|| ! isset( $data['items'] )
			|| ! is_array( $data['items'] )
		) {
			return new WP_Error( 'qaf_catalog_invalid', 'Struktur atau versi katalog tidak valid.' );
		}

		return $data;
	}

	/**
	 * Insert missing rows and optionally enrich matching activity records.
	 *
	 * @param array<int,array<string,mixed>> $items           Dataset items.
	 * @param string                         $post_type       Destination post type.
	 * @param string                         $seed_meta       Stable marker.
	 * @param bool                           $enrich_existing Fill only empty fields.
	 * @return array<int,string>
	 */
	private static function import_items( $items, $post_type, $seed_meta, $enrich_existing ) {
		$errors = array();

		foreach ( $items as $index => $item ) {
			if ( ! is_array( $item ) ) {
				$errors[] = sprintf( 'Katalog %s indeks %d bukan array.', $post_type, (int) $index );
				continue;
			}

			$seed_key = self::sanitize_seed_key( isset( $item['seed_key'] ) ? $item['seed_key'] : '' );
			$title    = sanitize_text_field( isset( $item['title'] ) ? $item['title'] : '' );
			$slug     = sanitize_title( isset( $item['slug'] ) ? $item['slug'] : $title );
			if ( ! $seed_key || ! $title || ! $slug ) {
				$errors[] = sprintf( 'Katalog %s indeks %d tidak memiliki identitas valid.', $post_type, (int) $index );
				continue;
			}

			$match = self::find_existing( $post_type, $seed_meta, $seed_key, $slug );
			if ( $match['post_id'] ) {
				if ( 'trash' === get_post_status( $match['post_id'] ) ) {
					continue;
				}

				if ( 'seed' === $match['type'] ) {
					$migration_error = self::migrate_seed_corrections( $match['post_id'], $item, $post_type );
					if ( $migration_error ) {
						$errors[] = $migration_error;
					}
				}

				if ( $enrich_existing && in_array( $match['type'], array( 'seed', 'demo' ), true ) ) {
					$enrich_error = self::enrich_extracurricular( $match['post_id'], $item, $seed_meta, $seed_key, $match['type'] );
					if ( $enrich_error ) {
						$errors[] = $enrich_error;
					}
				}

				if ( 'seed' === $match['type'] || 'demo' === $match['type'] ) {
					$term_error = self::assign_terms( $match['post_id'], isset( $item['terms'] ) ? $item['terms'] : array() );
					if ( $term_error ) {
						$errors[] = $term_error;
					}
				}
				continue;
			}

			$post_id = self::insert_item( $item, $post_type, $seed_meta, $seed_key, $title, $slug );
			if ( is_wp_error( $post_id ) ) {
				$errors[] = sprintf( '%s: %s', $seed_key, $post_id->get_error_message() );
				continue;
			}

			$term_error = self::assign_terms( $post_id, isset( $item['terms'] ) ? $item['terms'] : array() );
			if ( $term_error ) {
				$errors[] = $term_error;
			}
		}

		return $errors;
	}

	/**
	 * Apply bundled factual corrections only while a seeded value is untouched.
	 *
	 * Dataset rows may provide legacy_excerpt, legacy_content, and legacy_meta.
	 * Exact matching protects every administrator edit made after import.
	 *
	 * @param int                 $post_id   Existing seeded post.
	 * @param array<string,mixed> $item      Dataset item.
	 * @param string              $post_type Destination post type.
	 * @return string Empty on success, error text otherwise.
	 */
	private static function migrate_seed_corrections( $post_id, $item, $post_type ) {
		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post ) {
			return '';
		}

		$updates = array( 'ID' => $post_id );
		if ( isset( $item['legacy_excerpt'], $item['excerpt'] ) ) {
			$legacy_excerpt = sanitize_textarea_field( $item['legacy_excerpt'] );
			$new_excerpt    = sanitize_textarea_field( $item['excerpt'] );
			if ( trim( $post->post_excerpt ) === trim( $legacy_excerpt ) && trim( $post->post_excerpt ) !== trim( $new_excerpt ) ) {
				$updates['post_excerpt'] = $new_excerpt;
			}
		}

		if ( isset( $item['legacy_content'], $item['content'] ) ) {
			$legacy_content = wp_kses_post( $item['legacy_content'] );
			$new_content    = wp_kses_post( $item['content'] );
			if ( trim( $post->post_content ) === trim( $legacy_content ) && trim( $post->post_content ) !== trim( $new_content ) ) {
				$updates['post_content'] = $new_content;
			}
		}

		if ( count( $updates ) > 1 ) {
			$result = wp_update_post( wp_slash( $updates ), true );
			if ( is_wp_error( $result ) ) {
				return sprintf( '%s: %s', get_the_title( $post_id ), $result->get_error_message() );
			}
		}

		$legacy_meta = isset( $item['legacy_meta'] ) && is_array( $item['legacy_meta'] ) ? $item['legacy_meta'] : array();
		$new_meta    = self::sanitize_meta( $post_type, isset( $item['meta'] ) ? $item['meta'] : array() );
		$all_fields  = QAF_Core_Meta::get_fields();
		$fields      = isset( $all_fields[ $post_type ] ) ? $all_fields[ $post_type ] : array();
		foreach ( $legacy_meta as $meta_key => $legacy_value ) {
			if ( ! isset( $fields[ $meta_key ], $new_meta[ $meta_key ] ) ) {
				continue;
			}

			$legacy_value = QAF_Core_Meta::sanitize_value( $legacy_value, $fields[ $meta_key ] );
			$current      = get_post_meta( $post_id, $meta_key, true );
			if ( (string) $current === (string) $legacy_value && (string) $current !== (string) $new_meta[ $meta_key ] ) {
				$updated = update_post_meta( $post_id, $meta_key, $new_meta[ $meta_key ] );
				if ( false === $updated ) {
					return sprintf( '%s: koreksi field %s gagal disimpan.', get_the_title( $post_id ), $meta_key );
				}
			}
		}

		return '';
	}

	/**
	 * Locate a catalog-owned record, a legacy demo activity, or a slug collision.
	 *
	 * @param string $post_type Post type.
	 * @param string $seed_meta Seed meta key.
	 * @param string $seed_key  Seed value.
	 * @param string $slug      Stable slug.
	 * @return array{post_id:int,type:string}
	 */
	private static function find_existing( $post_type, $seed_meta, $seed_key, $slug ) {
		$statuses = array_values( get_post_stati() );
		$ids      = get_posts(
			array(
				'post_type'              => $post_type,
				'post_status'            => $statuses,
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'suppress_filters'       => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'meta_key'               => $seed_meta, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'             => $seed_key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		if ( $ids ) {
			return array( 'post_id' => (int) $ids[0], 'type' => 'seed' );
		}

		if ( 'qaf_extra' === $post_type ) {
			$demo_ids = get_posts(
				array(
					'post_type'              => $post_type,
					'post_status'            => $statuses,
					'posts_per_page'         => 1,
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'suppress_filters'       => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'meta_key'               => QAF_Core_Demo::MARKER_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value'             => 'extra:' . $slug, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				)
			);
			if ( $demo_ids ) {
				return array( 'post_id' => (int) $demo_ids[0], 'type' => 'demo' );
			}
		}

		$post = get_page_by_path( $slug, OBJECT, $post_type );
		return $post instanceof WP_Post
			? array( 'post_id' => (int) $post->ID, 'type' => 'slug' )
			: array( 'post_id' => 0, 'type' => '' );
	}

	/**
	 * Insert one sanitized catalog item.
	 *
	 * @param array<string,mixed> $item      Source row.
	 * @param string              $post_type Post type.
	 * @param string              $seed_meta Seed meta key.
	 * @param string              $seed_key  Seed value.
	 * @param string              $title     Sanitized title.
	 * @param string              $slug      Sanitized slug.
	 * @return int|WP_Error
	 */
	private static function insert_item( $item, $post_type, $seed_meta, $seed_key, $title, $slug ) {
		$status = isset( $item['status'] ) ? sanitize_key( $item['status'] ) : 'draft';
		if ( ! in_array( $status, array( 'draft', 'pending', 'private', 'publish' ), true ) ) {
			$status = 'draft';
		}

		$meta_input               = self::sanitize_meta( $post_type, isset( $item['meta'] ) ? $item['meta'] : array() );
		$meta_input[ $seed_meta ] = $seed_key;
		$post_data                = array(
			'post_type'      => $post_type,
			'post_status'    => $status,
			'post_title'     => $title,
			'post_name'      => $slug,
			'post_excerpt'   => sanitize_textarea_field( isset( $item['excerpt'] ) ? $item['excerpt'] : '' ),
			'post_content'   => wp_kses_post( isset( $item['content'] ) ? $item['content'] : '' ),
			'menu_order'     => isset( $item['menu_order'] ) ? absint( $item['menu_order'] ) : 0,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'meta_input'     => $meta_input,
		);

		return wp_insert_post( wp_slash( $post_data ), true );
	}

	/**
	 * Fill empty activity fields and replace only the exact untouched demo body.
	 *
	 * @param int                 $post_id   Existing activity.
	 * @param array<string,mixed> $item      Source row.
	 * @param string              $seed_meta Seed meta key.
	 * @param string              $seed_key  Seed value.
	 * @param string              $match_type Ownership match type.
	 * @return string Empty on success, error text otherwise.
	 */
	private static function enrich_extracurricular( $post_id, $item, $seed_meta, $seed_key, $match_type ) {
		if ( 'demo' !== $match_type && 'seed' !== $match_type ) {
			return '';
		}

		$meta = self::sanitize_meta( 'qaf_extra', isset( $item['meta'] ) ? $item['meta'] : array() );
		foreach ( $meta as $meta_key => $value ) {
			if ( ! metadata_exists( 'post', $post_id, $meta_key ) || '' === get_post_meta( $post_id, $meta_key, true ) ) {
				update_post_meta( $post_id, $meta_key, $value );
			}
		}

		if ( ! metadata_exists( 'post', $post_id, $seed_meta ) ) {
			update_post_meta( $post_id, $seed_meta, $seed_key );
		}

		$post           = get_post( $post_id );
		$legacy_excerpt = sanitize_textarea_field( isset( $item['legacy_excerpt'] ) ? $item['legacy_excerpt'] : '' );
		$legacy_content = '<!-- wp:paragraph --><p>' . esc_html( $legacy_excerpt ) . '</p><!-- /wp:paragraph --><!-- wp:paragraph --><p><strong>Administrator:</strong> lengkapi jadwal, pembina, syarat peserta, capaian, serta dokumentasi yang telah mendapat izin.</p><!-- /wp:paragraph -->';
		if (
			$post instanceof WP_Post
			&& 'draft' === $post->post_status
			&& $legacy_excerpt
			&& trim( $post->post_excerpt ) === trim( $legacy_excerpt )
			&& trim( $post->post_content ) === trim( $legacy_content )
		) {
			$result = wp_update_post(
				wp_slash(
					array(
						'ID'           => $post_id,
						'post_status'  => 'publish',
						'post_excerpt' => sanitize_textarea_field( isset( $item['excerpt'] ) ? $item['excerpt'] : '' ),
						'post_content' => wp_kses_post( isset( $item['content'] ) ? $item['content'] : '' ),
					)
				),
				true
			);
			if ( is_wp_error( $result ) ) {
				return sprintf( '%s: %s', $seed_key, $result->get_error_message() );
			}
		}

		return '';
	}

	/**
	 * Sanitize structured meta using the central field definitions.
	 *
	 * @param string $post_type Post type.
	 * @param mixed  $raw_meta  Raw meta map.
	 * @return array<string,mixed>
	 */
	private static function sanitize_meta( $post_type, $raw_meta ) {
		$all_fields = QAF_Core_Meta::get_fields();
		$fields     = isset( $all_fields[ $post_type ] ) ? $all_fields[ $post_type ] : array();
		$raw_meta   = is_array( $raw_meta ) ? $raw_meta : array();
		$clean      = array();

		foreach ( $raw_meta as $meta_key => $raw_value ) {
			if ( ! isset( $fields[ $meta_key ] ) ) {
				continue;
			}

			$value = QAF_Core_Meta::sanitize_value( $raw_value, $fields[ $meta_key ] );
			if ( '' === $value && ! in_array( $fields[ $meta_key ]['type'], array( 'integer', 'boolean' ), true ) ) {
				continue;
			}
			$clean[ $meta_key ] = $value;
		}

		return $clean;
	}

	/**
	 * Apply terms by taxonomy without replacing unrelated terms.
	 *
	 * @param int   $post_id Post ID.
	 * @param mixed $terms   Taxonomy-to-name map.
	 * @return string Empty on success, error text otherwise.
	 */
	private static function assign_terms( $post_id, $terms ) {
		if ( ! is_array( $terms ) ) {
			return '';
		}

		foreach ( $terms as $taxonomy => $names ) {
			if ( ! taxonomy_exists( $taxonomy ) || ! is_array( $names ) ) {
				continue;
			}
			$names  = array_values( array_filter( array_map( 'sanitize_text_field', $names ) ) );
			$result = wp_set_object_terms( $post_id, $names, $taxonomy, true );
			if ( is_wp_error( $result ) ) {
				return sprintf( '%s: %s', get_the_title( $post_id ), $result->get_error_message() );
			}
		}

		return '';
	}

	/**
	 * Normalize a portable ownership marker.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	private static function sanitize_seed_key( $value ) {
		$value = sanitize_text_field( is_scalar( $value ) ? (string) $value : '' );
		$value = preg_replace( '/[^A-Za-z0-9._:-]+/', '-', trim( $value ) );
		return substr( (string) $value, 0, 191 );
	}
}
