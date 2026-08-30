<?php
/**
 * Versioned, non-destructive Guru and Tendik importer.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add only staff profiles that are absent from the existing directory.
 */
final class QAF_Core_Staff_Directory {
	const VERSION_OPTION = 'qaf_staff_directory_data_version';
	const LOCK_OPTION    = 'qaf_staff_directory_upgrade_lock';
	const LOCK_TTL       = 300;
	const SOURCE_META    = '_qaf_staff_source_id';

	/** Register the administrator-only upgrade routine. */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'maybe_upgrade' ), 32 );
	}

	/** Add missing profiles during plugin activation. */
	public static function activate() {
		self::run_upgrade();
	}

	/** Run a pending data upgrade from an authenticated administration request. */
	public static function maybe_upgrade() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		self::run_upgrade();
	}

	/** Import one newer dataset while holding an atomic, short-lived lock. */
	private static function run_upgrade() {
		$data = self::load_data();
		if ( is_wp_error( $data ) ) {
			return;
		}

		$current_version = (string) get_option( self::VERSION_OPTION, '0.0.0' );
		if ( ! version_compare( $current_version, $data['version'], '<' ) ) {
			return;
		}

		$lock = self::acquire_upgrade_lock();
		if ( false === $lock ) {
			return;
		}

		try {
			// A second request may have completed while this request waited for a stale lock.
			$current_version = (string) get_option( self::VERSION_OPTION, '0.0.0' );
			if ( ! version_compare( $current_version, $data['version'], '<' ) ) {
				return;
			}

			$errors = self::insert_missing( $data['items'] );
			if ( empty( $errors ) ) {
				update_option( self::VERSION_OPTION, $data['version'], false );
			}
		} finally {
			self::release_upgrade_lock( $lock );
		}
	}

	/**
	 * Load and validate the append-only staff dataset.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	private static function load_data() {
		$path = QAF_CORE_PATH . 'includes/staff-directory-data.php';
		if ( ! is_readable( $path ) ) {
			return new WP_Error( 'qaf_staff_data_missing', 'Berkas data Guru dan Tendik tidak dapat dibaca.' );
		}

		$data = require $path;
		if (
			! is_array( $data ) ||
			empty( $data['version'] ) ||
			! is_string( $data['version'] ) ||
			! isset( $data['items'] ) ||
			! is_array( $data['items'] )
		) {
			return new WP_Error( 'qaf_staff_data_invalid', 'Struktur berkas data Guru dan Tendik tidak valid.' );
		}

		$version = sanitize_text_field( $data['version'] );
		if ( ! preg_match( '/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/', $version ) ) {
			return new WP_Error( 'qaf_staff_version_invalid', 'Versi data Guru dan Tendik tidak valid.' );
		}

		return array(
			'version' => $version,
			'items'   => $data['items'],
		);
	}

	/**
	 * Insert only records that do not match an existing source, name, alias, or slug.
	 *
	 * Existing posts are indexed across every status, including Trash. No field,
	 * term, status, thumbnail, or source marker is ever written to an old post.
	 *
	 * @param array<int,array<string,mixed>> $items Staff records.
	 * @return array<int,string> Error messages.
	 */
	private static function insert_missing( $items ) {
		$errors  = array();
		$indexes = self::existing_indexes();

		foreach ( $items as $index => $item ) {
			if ( ! is_array( $item ) ) {
				$errors[] = sprintf( 'Data Guru/Tendik pada indeks %d bukan array.', (int) $index );
				continue;
			}

			$source_id = self::sanitize_source_id( isset( $item['source_id'] ) ? $item['source_id'] : '' );
			$title     = sanitize_text_field( isset( $item['title'] ) ? $item['title'] : '' );
			$slug      = sanitize_title( isset( $item['slug'] ) ? $item['slug'] : $title );
			if ( '' === $source_id || '' === $title || '' === $slug ) {
				$errors[] = sprintf( 'Data Guru/Tendik pada indeks %d tidak memiliki source_id, title, atau slug yang valid.', (int) $index );
				continue;
			}

			if ( self::item_exists( $item, $source_id, $title, $slug, $indexes ) ) {
				continue;
			}

			$post_id = self::insert_item( $item, $source_id, $title, $slug );
			if ( is_wp_error( $post_id ) ) {
				$errors[] = sprintf( '%s: %s', $source_id, $post_id->get_error_message() );
				continue;
			}

			// Keep this pass idempotent even if a malformed dataset repeats a record.
			$indexes['sources'][ $source_id ] = (int) $post_id;
			$indexes['slugs'][ $slug ]         = (int) $post_id;
			$name_key = self::normalize_name( $title );
			if ( $name_key ) {
				$indexes['names'][ $name_key ] = (int) $post_id;
			}
		}

		return $errors;
	}

	/** Build source, name, and slug indexes for every existing profile status. */
	private static function existing_indexes() {
		$indexes = array(
			'sources' => array(),
			'names'   => array(),
			'slugs'   => array(),
		);
		$posts   = get_posts(
			array(
				'post_type'              => 'qaf_teacher',
				'post_status'            => array_values( get_post_stati() ),
				'posts_per_page'         => -1,
				'no_found_rows'          => true,
				'orderby'                => 'ID',
				'order'                  => 'ASC',
				'suppress_filters'       => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
			)
		);

		foreach ( $posts as $post ) {
			if ( ! $post instanceof WP_Post ) {
				continue;
			}

			$source_id = self::sanitize_source_id( get_post_meta( $post->ID, self::SOURCE_META, true ) );
			$name_key  = self::normalize_name( $post->post_title );
			$slug      = sanitize_title( $post->post_name );
			if ( $source_id && ! isset( $indexes['sources'][ $source_id ] ) ) {
				$indexes['sources'][ $source_id ] = (int) $post->ID;
			}
			if ( $name_key && ! isset( $indexes['names'][ $name_key ] ) ) {
				$indexes['names'][ $name_key ] = (int) $post->ID;
			}
			if ( $slug && ! isset( $indexes['slugs'][ $slug ] ) ) {
				$indexes['slugs'][ $slug ] = (int) $post->ID;
			}

			$old_slugs = get_post_meta( $post->ID, '_wp_old_slug', false );
			foreach ( is_array( $old_slugs ) ? $old_slugs : array() as $old_slug ) {
				$old_slug = sanitize_title( $old_slug );
				if ( $old_slug && ! isset( $indexes['slugs'][ $old_slug ] ) ) {
					$indexes['slugs'][ $old_slug ] = (int) $post->ID;
				}
			}
		}

		return $indexes;
	}

	/** Determine whether one source is already represented without mutating it. */
	private static function item_exists( $item, $source_id, $title, $slug, $indexes ) {
		if ( isset( $indexes['sources'][ $source_id ] ) || isset( $indexes['slugs'][ $slug ] ) ) {
			return true;
		}

		$names = array( $title );
		if ( isset( $item['aliases'] ) && is_array( $item['aliases'] ) ) {
			$names = array_merge( $names, $item['aliases'] );
		}

		foreach ( $names as $name ) {
			$name = sanitize_text_field( is_scalar( $name ) ? (string) $name : '' );
			$key  = self::normalize_name( $name );
			if ( $key && isset( $indexes['names'][ $key ] ) ) {
				return true;
			}

			$alias_slug = sanitize_title( $name );
			if ( $alias_slug && isset( $indexes['slugs'][ $alias_slug ] ) ) {
				return true;
			}
		}

		return false;
	}

	/** Insert one new, sanitized profile and its directory terms. */
	private static function insert_item( $item, $source_id, $title, $slug ) {
		$staff_type = isset( $item['staff_type'] ) && 'Tendik' === $item['staff_type'] ? 'Tendik' : 'Guru';
		$role       = sanitize_text_field( isset( $item['role'] ) ? $item['role'] : '' );
		$subject    = sanitize_text_field( isset( $item['subject'] ) ? $item['subject'] : '' );
		$duty       = sanitize_textarea_field( isset( $item['duty'] ) ? $item['duty'] : '' );
		$order      = absint( isset( $item['order'] ) ? $item['order'] : 0 );
		$status     = isset( $item['status'] ) ? sanitize_key( $item['status'] ) : 'publish';
		if ( ! in_array( $status, array( 'draft', 'pending', 'private', 'publish' ), true ) ) {
			$status = 'draft';
		}

		$type_label = 'Tendik' === $staff_type ? 'tenaga kependidikan' : 'guru';
		$excerpt    = sprintf( '%s merupakan %s SMK Queen Al-Falah dengan tugas sebagai %s.', $title, $type_label, $role );
		$content    = sprintf(
			'<!-- wp:paragraph --><p><strong>%1$s</strong> tercantum dalam Struktur Kelembagaan SMK Queen Al-Falah Tahun Pelajaran 2026/2027 sebagai %2$s.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Jabatan dan Tanggung Jawab</h2><!-- /wp:heading --><!-- wp:paragraph --><p>%3$s</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Bidang/Unit Kerja</h2><!-- /wp:heading --><!-- wp:paragraph --><p>%4$s</p><!-- /wp:paragraph --><!-- wp:paragraph --><p><em>Foto resmi dan detail profil dapat dilengkapi melalui menu Guru &amp; Tendik tanpa memengaruhi data struktur organisasi.</em></p><!-- /wp:paragraph -->',
			esc_html( $title ),
			esc_html( $role ),
			esc_html( $duty ),
			esc_html( $subject )
		);

		$post_id = wp_insert_post(
			wp_slash(
				array(
					'post_type'      => 'qaf_teacher',
					'post_status'    => $status,
					'post_title'     => $title,
					'post_name'      => $slug,
					'post_excerpt'   => sanitize_textarea_field( $excerpt ),
					'post_content'   => wp_kses_post( $content ),
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'meta_input'     => array(
						self::SOURCE_META => $source_id,
						'_qaf_role'       => $role,
						'_qaf_subject'    => $subject,
						'_qaf_order'      => $order,
					),
				)
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		$staff_term = 'Tendik' === $staff_type ? 'Tenaga Kependidikan' : 'Guru';
		$staff_set  = wp_set_object_terms( $post_id, $staff_term, 'qaf_staff_type', false );
		$unit_set   = wp_set_object_terms( $post_id, $subject, 'qaf_subject', false );
		if ( is_wp_error( $staff_set ) || is_wp_error( $unit_set ) ) {
			$error = is_wp_error( $staff_set ) ? $staff_set : $unit_set;
			wp_delete_post( $post_id, true );
			return new WP_Error( 'qaf_staff_terms_failed', $error->get_error_message() );
		}

		return (int) $post_id;
	}

	/** Normalize titles while ignoring degrees, punctuation, and honorifics. */
	private static function normalize_name( $name ) {
		$name = remove_accents( wp_strip_all_tags( (string) $name ) );
		$name = preg_replace( '/,.*/u', '', $name );
		$name = strtolower( $name );
		$name = preg_replace( '/[^a-z0-9]+/', ' ', $name );
		$name = trim( preg_replace( '/\s+/', ' ', $name ) );
		do {
			$previous = $name;
			$name     = preg_replace( '/^(ns|h|ust|drs|dr|kh)\s+/', '', $name );
		} while ( $name !== $previous );

		return trim( $name );
	}

	/** Claim one importer lock atomically. */
	private static function acquire_upgrade_lock() {
		$lock = array(
			'token'   => wp_generate_uuid4(),
			'created' => time(),
		);

		if ( add_option( self::LOCK_OPTION, $lock, '', false ) ) {
			return $lock;
		}

		$existing = get_option( self::LOCK_OPTION, false );
		$created  = is_array( $existing ) && isset( $existing['created'] ) ? (int) $existing['created'] : 0;
		if ( $created > time() - self::LOCK_TTL ) {
			return false;
		}

		if ( ! self::delete_exact_lock( $existing ) ) {
			return false;
		}

		return add_option( self::LOCK_OPTION, $lock, '', false ) ? $lock : false;
	}

	/** Release only the lock payload claimed by this request. */
	private static function release_upgrade_lock( $lock ) {
		self::delete_exact_lock( $lock );
	}

	/** Delete one lock only when its serialized value has not changed. */
	private static function delete_exact_lock( $lock ) {
		global $wpdb;

		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s",
				self::LOCK_OPTION,
				maybe_serialize( $lock )
			)
		);
		wp_cache_delete( self::LOCK_OPTION, 'options' );

		return 1 === (int) $deleted;
	}

	/** Restrict source identifiers to portable comparison-safe characters. */
	private static function sanitize_source_id( $source_id ) {
		$source_id = sanitize_text_field( is_scalar( $source_id ) ? (string) $source_id : '' );
		$source_id = preg_replace( '/[^A-Za-z0-9._:-]+/', '-', trim( $source_id ) );
		return substr( (string) $source_id, 0, 191 );
	}
}
