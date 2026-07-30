<?php
/**
 * Versioned, non-destructive achievement importer.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Insert verified achievement records once without replacing editor changes.
 */
final class QAF_Core_Achievements {
	const VERSION_OPTION       = 'qaf_achievements_data_version';
	const IMAGE_VERSION_OPTION = 'qaf_achievements_image_version';
	const SOURCE_META          = '_qaf_achievement_source_id';
	const IMAGE_SOURCE_META    = '_qaf_bundled_achievement_image';

	/**
	 * Attach the safe upgrade routine.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'maybe_upgrade' ), 30 );
	}

	/**
	 * Seed records during plugin activation.
	 *
	 * @return void
	 */
	public static function activate() {
		self::run_upgrade();
	}

	/**
	 * Seed newly versioned records after a plugin update.
	 *
	 * The importer is intentionally restricted to an administrator request.
	 * Public requests never write achievement content.
	 *
	 * @return void
	 */
	public static function maybe_upgrade() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		self::run_upgrade();
	}

	/**
	 * Run text and image importers when their bundled data has a newer version.
	 *
	 * Text and image versions are deliberately independent. A missing/corrupt
	 * image can therefore never prevent valid achievement text from being
	 * marked as imported. Image failures leave only the image version behind,
	 * so a later fixed package can retry them without recreating posts.
	 *
	 * @return void
	 */
	private static function run_upgrade() {
		$data = self::load_data();
		if ( is_wp_error( $data ) ) {
			return;
		}

		$current_version = (string) get_option( self::VERSION_OPTION, '0.0.0' );
		if ( version_compare( $current_version, $data['version'], '<' ) ) {
			$errors = self::insert_missing( $data['items'] );
			$errors = array_merge( $errors, self::correct_known_placeholders( $data['items'] ) );
			if ( empty( $errors ) ) {
				update_option( self::VERSION_OPTION, $data['version'], false );
			}
		}

		$current_image_version = (string) get_option( self::IMAGE_VERSION_OPTION, '0.0.0' );
		if (
			'0.0.0' !== $data['image_version'] &&
			version_compare( $current_image_version, $data['image_version'], '<' )
		) {
			$image_errors = array_merge(
				self::remove_known_incorrect_images( $data['items'] ),
				self::sync_images( $data['items'] )
			);
			if ( empty( $image_errors ) ) {
				update_option( self::IMAGE_VERSION_OPTION, $data['image_version'], false );
			}
		}
	}

	/**
	 * Load and validate the dedicated data file.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	private static function load_data() {
		$path = QAF_CORE_PATH . 'includes/achievement-data.php';
		if ( ! is_readable( $path ) ) {
			return new WP_Error( 'qaf_achievement_data_missing', 'Berkas data prestasi tidak dapat dibaca.' );
		}

		$data = require $path;
		if (
			! is_array( $data ) ||
			empty( $data['version'] ) ||
			! is_string( $data['version'] ) ||
			! isset( $data['items'] ) ||
			! is_array( $data['items'] )
		) {
			return new WP_Error( 'qaf_achievement_data_invalid', 'Struktur berkas data prestasi tidak valid.' );
		}

		$version = sanitize_text_field( $data['version'] );
		if ( ! preg_match( '/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/', $version ) ) {
			return new WP_Error( 'qaf_achievement_version_invalid', 'Versi data prestasi tidak valid.' );
		}

		$image_version = isset( $data['image_version'] ) && is_string( $data['image_version'] )
			? sanitize_text_field( $data['image_version'] )
			: '0.0.0';
		if ( ! preg_match( '/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/', $image_version ) ) {
			// An invalid optional image version must not block the text importer.
			$image_version = '0.0.0';
		}

		return array(
			'version'       => $version,
			'image_version' => $image_version,
			'items'         => $data['items'],
		);
	}

	/**
	 * Insert each missing source ID.
	 *
	 * @param array<int,array<string,mixed>> $items Achievement records.
	 * @return array<int,string> Error messages.
	 */
	private static function insert_missing( $items ) {
		$errors = array();

		foreach ( $items as $index => $item ) {
			if ( ! is_array( $item ) ) {
				$errors[] = sprintf( 'Data prestasi pada indeks %d bukan array.', (int) $index );
				continue;
			}

			$source_id = self::sanitize_source_id( isset( $item['source_id'] ) ? $item['source_id'] : '' );
			$title     = sanitize_text_field( isset( $item['title'] ) ? $item['title'] : '' );
			if ( '' === $source_id || '' === $title ) {
				$errors[] = sprintf( 'Data prestasi pada indeks %d tidak memiliki source_id atau title yang valid.', (int) $index );
				continue;
			}

			// Include posts in Trash so deleting an imported item does not recreate it.
			if ( self::source_exists( $source_id ) ) {
				continue;
			}

			$post_id = self::insert_item( $item, $source_id, $title );
			if ( is_wp_error( $post_id ) ) {
				$errors[] = sprintf( '%s: %s', $source_id, $post_id->get_error_message() );
			}
		}

		return $errors;
	}

	/**
	 * Replace only exact placeholder values bundled by an older data version.
	 *
	 * Editors remain authoritative: each field is changed independently and
	 * only when its current value still exactly matches the declared legacy
	 * value. A manually edited recipient, excerpt, or body is never replaced.
	 *
	 * @param array<int,array<string,mixed>> $items Achievement records.
	 * @return array<int,string> Error messages.
	 */
	private static function correct_known_placeholders( $items ) {
		$errors = array();

		foreach ( $items as $item ) {
			if (
				! is_array( $item )
				|| (
					! array_key_exists( 'legacy_recipient', $item )
					&& ! array_key_exists( 'legacy_excerpt', $item )
					&& ! array_key_exists( 'legacy_content', $item )
				)
			) {
				continue;
			}

			$source_id = self::sanitize_source_id( isset( $item['source_id'] ) ? $item['source_id'] : '' );
			$post_id   = $source_id ? self::find_source_post_id( $source_id ) : 0;
			if ( ! $post_id || 'trash' === get_post_status( $post_id ) ) {
				continue;
			}

			if ( array_key_exists( 'legacy_recipient', $item ) ) {
				$legacy_recipient = self::normalize_recipient( $item['legacy_recipient'] );
				$new_recipient    = self::normalize_recipient( isset( $item['recipient'] ) ? $item['recipient'] : '' );
				$current          = (string) get_post_meta( $post_id, '_qaf_recipient', true );
				if ( $current === $legacy_recipient && $new_recipient !== $legacy_recipient ) {
					update_post_meta( $post_id, '_qaf_recipient', $new_recipient );
				}
			}

			$post = get_post( $post_id );
			if ( ! $post instanceof WP_Post ) {
				continue;
			}

			$update = array( 'ID' => $post_id );
			if ( array_key_exists( 'legacy_excerpt', $item ) ) {
				$legacy_excerpt = sanitize_textarea_field( $item['legacy_excerpt'] );
				$new_excerpt    = sanitize_textarea_field( isset( $item['excerpt'] ) ? $item['excerpt'] : '' );
				if ( trim( $post->post_excerpt ) === trim( $legacy_excerpt ) && $new_excerpt !== $legacy_excerpt ) {
					$update['post_excerpt'] = $new_excerpt;
				}
			}

			if ( array_key_exists( 'legacy_content', $item ) ) {
				$legacy_content = wp_kses_post( $item['legacy_content'] );
				$new_content    = wp_kses_post( isset( $item['content'] ) ? $item['content'] : '' );
				if ( trim( $post->post_content ) === trim( $legacy_content ) && $new_content !== $legacy_content ) {
					$update['post_content'] = $new_content;
				}
			}

			if ( count( $update ) > 1 ) {
				$result = wp_update_post( wp_slash( $update ), true );
				if ( is_wp_error( $result ) ) {
					$errors[] = sprintf( '%s: %s', $source_id, $result->get_error_message() );
				}
			}
		}

		return $errors;
	}

	/**
	 * Determine whether a source was imported in any post status.
	 *
	 * @param string $source_id Stable source identifier.
	 * @return bool
	 */
	private static function source_exists( $source_id ) {
		return (bool) self::find_source_post_id( $source_id );
	}

	/**
	 * Find an imported source in any post status, including Trash.
	 *
	 * Including Trash preserves the user's decision to delete a seeded record.
	 * Callers that modify a post must still explicitly skip trashed results.
	 *
	 * @param string $source_id Stable source identifier.
	 * @return int
	 */
	private static function find_source_post_id( $source_id ) {
		$statuses = array_values( get_post_stati() );
		$matches  = get_posts(
			array(
				'post_type'              => 'qaf_achievement',
				'post_status'            => $statuses,
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'suppress_filters'       => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'meta_key'               => self::SOURCE_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'             => $source_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);

		return empty( $matches ) ? 0 : (int) $matches[0];
	}

	/**
	 * Insert one sanitized achievement record.
	 *
	 * @param array<string,mixed> $item      Source record.
	 * @param string              $source_id Stable source identifier.
	 * @param string              $title     Sanitized post title.
	 * @return int|WP_Error
	 */
	private static function insert_item( $item, $source_id, $title ) {
		$date      = QAF_Core_Meta::sanitize_value(
			isset( $item['date'] ) ? $item['date'] : '',
			array( 'type' => 'date' )
		);
		$recipient = self::normalize_recipient( isset( $item['recipient'] ) ? $item['recipient'] : '' );
		$status    = isset( $item['status'] ) ? sanitize_key( $item['status'] ) : 'draft';
		$statuses  = array( 'draft', 'pending', 'private', 'publish' );
		if ( ! in_array( $status, $statuses, true ) ) {
			$status = 'draft';
		}

		$meta_input = array(
			self::SOURCE_META => $source_id,
		);
		$meta_map   = array(
			'_qaf_level'            => array( 'key' => 'level', 'type' => 'text' ),
			'_qaf_achievement_date' => array( 'key' => 'date', 'type' => 'date' ),
			'_qaf_recipient'        => array( 'key' => 'recipient', 'type' => 'textarea' ),
			'_qaf_organizer'        => array( 'key' => 'organizer', 'type' => 'text' ),
			'_qaf_award'            => array( 'key' => 'award', 'type' => 'text' ),
			'_qaf_field'            => array( 'key' => 'field', 'type' => 'text' ),
			'_qaf_source_url'       => array( 'key' => 'source_url', 'type' => 'url' ),
		);

		foreach ( $meta_map as $meta_key => $definition ) {
			$raw_value = isset( $item[ $definition['key'] ] ) ? $item[ $definition['key'] ] : '';
			if ( '_qaf_recipient' === $meta_key ) {
				$raw_value = $recipient;
			}
			$value = QAF_Core_Meta::sanitize_value( $raw_value, array( 'type' => $definition['type'] ) );
			if ( '' !== $value ) {
				$meta_input[ $meta_key ] = $value;
			}
		}

		$post_data = array(
			'post_type'      => 'qaf_achievement',
			'post_status'    => $status,
			'post_title'     => $title,
			'post_name'      => sanitize_title( isset( $item['slug'] ) ? $item['slug'] : $title ),
			'post_excerpt'   => sanitize_textarea_field( isset( $item['excerpt'] ) ? $item['excerpt'] : '' ),
			'post_content'   => wp_kses_post( isset( $item['content'] ) ? $item['content'] : '' ),
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'meta_input'     => $meta_input,
		);

		if ( $date ) {
			$post_data['post_date'] = $date . ' 12:00:00';
		}

		return wp_insert_post( wp_slash( $post_data ), true );
	}

	/**
	 * Convert one recipient or a list of recipients into editor-friendly lines.
	 *
	 * @param mixed $recipient Raw recipient value.
	 * @return string
	 */
	private static function normalize_recipient( $recipient ) {
		if ( is_array( $recipient ) ) {
			$recipient = array_map( 'sanitize_text_field', $recipient );
			$recipient = array_filter( $recipient, 'strlen' );
			return implode( "\n", $recipient );
		}

		return sanitize_textarea_field( is_scalar( $recipient ) ? (string) $recipient : '' );
	}

	/**
	 * Remove a bundled thumbnail that a previous data version assigned wrongly.
	 *
	 * Only an attachment carrying the plugin's exact bundled-image marker is
	 * detached. A thumbnail selected or replaced by an administrator is never
	 * changed, and the attachment itself remains available in Media Library.
	 *
	 * @param array<int,array<string,mixed>> $items Achievement records.
	 * @return array<int,string> Error messages.
	 */
	private static function remove_known_incorrect_images( $items ) {
		$errors = array();

		foreach ( $items as $index => $item ) {
			if ( ! is_array( $item ) || empty( $item['legacy_image'] ) ) {
				continue;
			}

			$source_id    = self::sanitize_source_id( isset( $item['source_id'] ) ? $item['source_id'] : '' );
			$legacy_image = self::sanitize_image_path( $item['legacy_image'] );
			if ( '' === $source_id || '' === $legacy_image ) {
				$errors[] = sprintf( 'Referensi gambar lama pada indeks %d tidak valid.', (int) $index );
				continue;
			}

			$post_id = self::find_source_post_id( $source_id );
			if ( ! $post_id ) {
				$errors[] = sprintf( '%s: Halaman prestasi sumber belum tersedia untuk koreksi gambar.', $source_id );
				continue;
			}

			if ( 'trash' === get_post_status( $post_id ) ) {
				continue;
			}

			$attachment_id = (int) get_post_thumbnail_id( $post_id );
			if (
				! $attachment_id ||
				$legacy_image !== (string) get_post_meta( $attachment_id, self::IMAGE_SOURCE_META, true )
			) {
				continue;
			}

			if ( ! delete_post_thumbnail( $post_id ) ) {
				$errors[] = sprintf( '%s: Gambar unggulan lama tidak dapat dilepas.', $source_id );
			}
		}

		return $errors;
	}

	/**
	 * Fill empty featured-image slots from bundled achievement images.
	 *
	 * Existing featured images always win. Trashed achievements are not
	 * touched, and no post is restored or otherwise republished.
	 *
	 * Data contract: an item may define `image` as a relative path beneath
	 * assets/images/achievements/. Multiple items may use the same filename;
	 * the corresponding Media Library attachment is imported only once.
	 *
	 * @param array<int,array<string,mixed>> $items Achievement records.
	 * @return array<int,string> Error messages.
	 */
	private static function sync_images( $items ) {
		$errors = array();

		foreach ( $items as $index => $item ) {
			if ( ! is_array( $item ) || empty( $item['image'] ) ) {
				continue;
			}

			$source_id = self::sanitize_source_id( isset( $item['source_id'] ) ? $item['source_id'] : '' );
			$image     = self::sanitize_image_path( $item['image'] );
			if ( '' === $source_id || '' === $image ) {
				$errors[] = sprintf( 'Referensi gambar prestasi pada indeks %d tidak valid.', (int) $index );
				continue;
			}

			$post_id = self::find_source_post_id( $source_id );
			if ( ! $post_id ) {
				$errors[] = sprintf( '%s: Halaman prestasi sumber belum tersedia untuk menerima gambar.', $source_id );
				continue;
			}

			if ( 'trash' === get_post_status( $post_id ) || has_post_thumbnail( $post_id ) ) {
				continue;
			}

			$attachment_id = self::get_or_import_image( $image, $post_id );
			if ( is_wp_error( $attachment_id ) ) {
				$errors[] = sprintf( '%s: %s', $source_id, $attachment_id->get_error_message() );
				continue;
			}

			if ( ! set_post_thumbnail( $post_id, $attachment_id ) ) {
				$errors[] = sprintf( '%s: Gambar unggulan tidak dapat ditetapkan.', $source_id );
			}
		}

		return $errors;
	}

	/**
	 * Reuse a bundled attachment or import it into the Media Library once.
	 *
	 * @param string $relative_path Safe path relative to the achievement asset directory.
	 * @param int    $post_id       Achievement receiving the image.
	 * @return int|WP_Error
	 */
	private static function get_or_import_image( $relative_path, $post_id ) {
		$matches = get_posts(
			array(
				'post_type'              => 'attachment',
				'post_status'            => 'inherit',
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'suppress_filters'       => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'meta_key'               => self::IMAGE_SOURCE_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'             => $relative_path, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		if ( ! empty( $matches ) ) {
			$attachment_id = (int) $matches[0];
			self::maybe_set_attachment_alt( $attachment_id, $post_id );
			return $attachment_id;
		}

		$base_dir = realpath( QAF_CORE_PATH . 'assets/images/achievements' );
		if ( false === $base_dir ) {
			return new WP_Error( 'qaf_achievement_image_directory_missing', 'Direktori gambar prestasi tidak tersedia.' );
		}

		$file = realpath( $base_dir . DIRECTORY_SEPARATOR . str_replace( '/', DIRECTORY_SEPARATOR, $relative_path ) );
		$base = rtrim( wp_normalize_path( $base_dir ), '/' ) . '/';
		if (
			false === $file ||
			0 !== strpos( wp_normalize_path( $file ), $base ) ||
			! is_file( $file ) ||
			! is_readable( $file )
		) {
			return new WP_Error( 'qaf_achievement_image_missing', 'Berkas gambar prestasi tidak ditemukan atau tidak dapat dibaca.' );
		}

		$filetype = wp_check_filetype_and_ext( $file, basename( $file ) );
		if ( empty( $filetype['type'] ) || 0 !== strpos( $filetype['type'], 'image/' ) ) {
			return new WP_Error( 'qaf_achievement_image_type_invalid', 'Jenis berkas gambar prestasi tidak didukung.' );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$temp_file = wp_tempnam( basename( $file ) );
		if ( ! $temp_file || ! copy( $file, $temp_file ) ) {
			if ( $temp_file && file_exists( $temp_file ) ) {
				wp_delete_file( $temp_file );
			}
			return new WP_Error( 'qaf_achievement_image_copy_failed', 'Gambar prestasi tidak dapat disalin ke berkas sementara.' );
		}

		$file_array = array(
			'name'     => basename( $file ),
			'tmp_name' => $temp_file,
		);
		$title       = get_the_title( $post_id );
		$attachment  = media_handle_sideload( $file_array, $post_id, $title );
		if ( is_wp_error( $attachment ) ) {
			if ( file_exists( $temp_file ) ) {
				wp_delete_file( $temp_file );
			}
			return $attachment;
		}

		update_post_meta( $attachment, self::IMAGE_SOURCE_META, $relative_path );
		self::maybe_set_attachment_alt( $attachment, $post_id );

		return (int) $attachment;
	}

	/**
	 * Add descriptive alt text only when an attachment does not already have it.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @param int $post_id       Achievement post ID.
	 * @return void
	 */
	private static function maybe_set_attachment_alt( $attachment_id, $post_id ) {
		if ( '' === (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) {
			update_post_meta(
				$attachment_id,
				'_wp_attachment_image_alt',
				sprintf( 'Dokumentasi prestasi %s', wp_strip_all_tags( get_the_title( $post_id ) ) )
			);
		}
	}

	/**
	 * Validate a portable relative image path and reject directory traversal.
	 *
	 * @param mixed $path Raw image path.
	 * @return string
	 */
	private static function sanitize_image_path( $path ) {
		if ( ! is_scalar( $path ) ) {
			return '';
		}

		$path = str_replace( '\\', '/', trim( (string) $path ) );
		if (
			'' === $path ||
			'/' === substr( $path, 0, 1 ) ||
			false !== strpos( $path, "\0" ) ||
			in_array( '..', explode( '/', $path ), true )
		) {
			return '';
		}

		$segments = array_map( 'sanitize_file_name', explode( '/', $path ) );
		if ( in_array( '', $segments, true ) ) {
			return '';
		}

		return implode( '/', $segments );
	}

	/**
	 * Restrict source identifiers to portable, comparison-safe characters.
	 *
	 * @param mixed $source_id Raw source ID.
	 * @return string
	 */
	private static function sanitize_source_id( $source_id ) {
		$source_id = sanitize_text_field( is_scalar( $source_id ) ? (string) $source_id : '' );
		$source_id = preg_replace( '/[^A-Za-z0-9._:-]+/', '-', trim( $source_id ) );
		return substr( (string) $source_id, 0, 191 );
	}
}
