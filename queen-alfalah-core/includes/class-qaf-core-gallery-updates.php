<?php
/**
 * Non-destructive gallery importer for verified official-channel updates.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add versioned gallery records once without replacing editor-owned content.
 */
final class QAF_Core_Gallery_Updates {
	const VERSION_OPTION    = 'qaf_gallery_updates_version';
	const SOURCE_META       = '_qaf_gallery_update_source_id';
	const IMAGE_SOURCE_META = '_qaf_bundled_gallery_image';
	const LOCK_OPTION       = 'qaf_gallery_updates_lock';

	/** Register the administrator-only upgrade routine. */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'maybe_upgrade' ), 46 );
	}

	/** Import the package during plugin activation. */
	public static function activate() {
		self::run_upgrade();
	}

	/** Apply a newer package from an authenticated administrator request. */
	public static function maybe_upgrade() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		self::run_upgrade();
	}

	/** Insert every missing verified gallery item and advance the version. */
	private static function run_upgrade() {
		$data = self::load_data();
		if ( is_wp_error( $data ) ) {
			return;
		}

		$current = (string) get_option( self::VERSION_OPTION, '0.0.0' );
		if ( version_compare( $current, $data['version'], '>=' ) ) {
			return;
		}

		$lock = array( 'token' => wp_generate_uuid4(), 'created' => time() );
		if ( ! add_option( self::LOCK_OPTION, $lock, '', false ) ) {
			return;
		}

		try {
			$errors = self::insert_missing( $data['items'] );
			if ( empty( $errors ) ) {
				update_option( self::VERSION_OPTION, $data['version'], false );
			}
		} finally {
			if ( get_option( self::LOCK_OPTION ) === $lock ) {
				delete_option( self::LOCK_OPTION );
			}
		}
	}

	/** Load and validate the local data contract. */
	private static function load_data() {
		$path = QAF_CORE_PATH . 'includes/gallery-update-data.php';
		if ( ! is_readable( $path ) ) {
			return new WP_Error( 'qaf_gallery_update_data_missing', 'Berkas pembaruan galeri tidak tersedia.' );
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
			return new WP_Error( 'qaf_gallery_update_data_invalid', 'Struktur pembaruan galeri tidak valid.' );
		}

		return $data;
	}

	/** Insert missing rows while preserving matching and deleted records. */
	private static function insert_missing( $items ) {
		$errors = array();

		foreach ( $items as $index => $item ) {
			$record = self::sanitize_record( $item );
			if ( is_wp_error( $record ) ) {
				$errors[] = sprintf( 'Galeri indeks %d: %s', (int) $index, $record->get_error_message() );
				continue;
			}

			$existing = self::find_existing( $record );
			if ( $existing ) {
				self::publish_due_record( $existing, $record );
				continue;
			}

			$post_id = wp_insert_post(
				wp_slash(
					array(
						'post_type'      => 'qaf_gallery',
						'post_status'    => $record['status'],
						'post_title'     => $record['title'],
						'post_name'      => $record['slug'],
						'post_date'      => $record['date'] . ' 12:00:00',
						'post_excerpt'   => $record['excerpt'],
						'post_content'   => $record['content'],
						'comment_status' => 'closed',
						'ping_status'    => 'closed',
						'meta_input'     => array(
							self::SOURCE_META               => $record['source_id'],
							'_qaf_gallery_source'           => $record['source'],
							'_qaf_gallery_media_type'       => $record['media_type'],
							'_qaf_video_url'                => $record['source_url'],
							'_qaf_gallery_embed_behavior'   => 'click',
							'_qaf_album_date'               => $record['date'],
						),
					)
				),
				true
			);
			if ( is_wp_error( $post_id ) ) {
				$errors[] = sprintf( '%s: %s', $record['source_id'], $post_id->get_error_message() );
				continue;
			}

			if ( $record['terms'] ) {
				$term_result = wp_set_object_terms( $post_id, $record['terms'], 'qaf_gallery_category', true );
				if ( is_wp_error( $term_result ) ) {
					$errors[] = sprintf( '%s: %s', $record['source_id'], $term_result->get_error_message() );
				}
			}

			$attachment_id = self::get_or_import_image( $record, $post_id );
			if ( is_wp_error( $attachment_id ) ) {
				$errors[] = sprintf( '%s: %s', $record['source_id'], $attachment_id->get_error_message() );
				continue;
			}

			if ( ! set_post_thumbnail( $post_id, $attachment_id ) ) {
				$errors[] = sprintf( '%s: gambar unggulan tidak dapat ditetapkan.', $record['source_id'] );
			}
		}

		return $errors;
	}

	/**
	 * Publish a verified same-day item that WordPress scheduled because its
	 * imported display time was later than the server clock.
	 *
	 * @param int                 $post_id Existing gallery ID.
	 * @param array<string,mixed> $record  Sanitized gallery record.
	 * @return void
	 */
	private static function publish_due_record( $post_id, $record ) {
		if (
			'publish' !== $record['status']
			|| 'future' !== get_post_status( $post_id )
			|| $record['date'] > current_time( 'Y-m-d' )
		) {
			return;
		}

		$now = current_time( 'mysql' );
		wp_update_post(
			array(
				'ID'            => $post_id,
				'post_status'   => 'publish',
				'post_date'     => $now,
				'post_date_gmt' => get_gmt_from_date( $now ),
			)
		);
	}

	/** Validate one record before persistence. */
	private static function sanitize_record( $item ) {
		if ( ! is_array( $item ) ) {
			return new WP_Error( 'qaf_gallery_update_row_invalid', 'Baris data bukan array.' );
		}

		$record = array(
			'source_id'     => sanitize_text_field( isset( $item['source_id'] ) ? $item['source_id'] : '' ),
			'title'         => sanitize_text_field( isset( $item['title'] ) ? $item['title'] : '' ),
			'slug'          => sanitize_title( isset( $item['slug'] ) ? $item['slug'] : '' ),
			'status'        => sanitize_key( isset( $item['status'] ) ? $item['status'] : 'draft' ),
			'date'          => sanitize_text_field( isset( $item['date'] ) ? $item['date'] : '' ),
			'source'        => sanitize_key( isset( $item['source'] ) ? $item['source'] : '' ),
			'media_type'    => sanitize_key( isset( $item['media_type'] ) ? $item['media_type'] : '' ),
			'source_url'    => esc_url_raw( isset( $item['source_url'] ) ? $item['source_url'] : '' ),
			'image'         => sanitize_file_name( isset( $item['image'] ) ? $item['image'] : '' ),
			'image_alt'     => sanitize_text_field( isset( $item['image_alt'] ) ? $item['image_alt'] : '' ),
			'image_caption' => sanitize_text_field( isset( $item['image_caption'] ) ? $item['image_caption'] : '' ),
			'excerpt'       => sanitize_textarea_field( isset( $item['excerpt'] ) ? $item['excerpt'] : '' ),
			'content'       => wp_kses_post( isset( $item['content'] ) ? $item['content'] : '' ),
			'terms'         => isset( $item['terms'] ) && is_array( $item['terms'] )
				? array_values( array_filter( array_map( 'sanitize_text_field', $item['terms'] ) ) )
				: array(),
		);

		if ( ! preg_match( '/^[A-Za-z0-9._:-]+$/', $record['source_id'] ) || ! $record['title'] || ! $record['slug'] ) {
			return new WP_Error( 'qaf_gallery_update_identity_invalid', 'Identitas galeri tidak valid.' );
		}
		if ( ! in_array( $record['status'], array( 'draft', 'pending', 'private', 'publish' ), true ) ) {
			return new WP_Error( 'qaf_gallery_update_status_invalid', 'Status galeri tidak valid.' );
		}
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $record['date'] ) ) {
			return new WP_Error( 'qaf_gallery_update_date_invalid', 'Tanggal galeri tidak valid.' );
		}
		if ( ! in_array( $record['source'], array( 'instagram', 'youtube', 'facebook', 'tiktok', 'local' ), true ) ) {
			return new WP_Error( 'qaf_gallery_update_source_invalid', 'Sumber galeri tidak valid.' );
		}
		if ( ! in_array( $record['media_type'], array( 'photo', 'video', 'mixed' ), true ) ) {
			return new WP_Error( 'qaf_gallery_update_media_type_invalid', 'Jenis media galeri tidak valid.' );
		}
		if ( ! wp_http_validate_url( $record['source_url'] ) || 'https' !== wp_parse_url( $record['source_url'], PHP_URL_SCHEME ) ) {
			return new WP_Error( 'qaf_gallery_update_url_invalid', 'URL sumber galeri tidak valid.' );
		}
		if ( ! preg_match( '/^[a-z0-9-]+\.(?:jpe?g|png|webp)$/', $record['image'] ) ) {
			return new WP_Error( 'qaf_gallery_update_image_invalid', 'Nama gambar galeri tidak valid.' );
		}

		return $record;
	}

	/** Find source, URL, or slug matches across every post status, including Trash. */
	private static function find_existing( $record ) {
		$statuses = array_values( get_post_stati() );
		foreach ( array( self::SOURCE_META => $record['source_id'], '_qaf_video_url' => $record['source_url'] ) as $meta_key => $meta_value ) {
			$ids = get_posts(
				array(
					'post_type'      => 'qaf_gallery',
					'post_status'    => $statuses,
					'posts_per_page' => 1,
					'fields'         => 'ids',
					'no_found_rows'  => true,
					'meta_key'       => $meta_key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value'     => $meta_value, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				)
			);
			if ( $ids ) {
				return (int) $ids[0];
			}
		}

		$post = get_page_by_path( $record['slug'], OBJECT, 'qaf_gallery' );
		return $post instanceof WP_Post ? (int) $post->ID : 0;
	}

	/** Import one bundled WebP/JPEG/PNG into the Media Library. */
	private static function get_or_import_image( $record, $post_id ) {
		$ids = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_key'       => self::IMAGE_SOURCE_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $record['image'], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		if ( $ids && 0 === strpos( (string) get_post_mime_type( $ids[0] ), 'image/' ) ) {
			return (int) $ids[0];
		}

		$base = realpath( QAF_CORE_PATH . 'assets/images/gallery' );
		$file = realpath( QAF_CORE_PATH . 'assets/images/gallery/' . $record['image'] );
		if ( ! $base || ! $file || 0 !== strpos( wp_normalize_path( $file ), trailingslashit( wp_normalize_path( $base ) ) ) || ! is_readable( $file ) ) {
			return new WP_Error( 'qaf_gallery_update_image_missing', 'Berkas gambar galeri tidak tersedia.' );
		}

		$type = wp_check_filetype_and_ext( $file, basename( $file ) );
		if ( empty( $type['type'] ) || 0 !== strpos( $type['type'], 'image/' ) ) {
			return new WP_Error( 'qaf_gallery_update_image_type', 'Format gambar galeri tidak didukung.' );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$temp = wp_tempnam( $record['image'] );
		if ( ! $temp || ! copy( $file, $temp ) ) {
			return new WP_Error( 'qaf_gallery_update_image_copy', 'Gambar galeri tidak dapat disalin.' );
		}

		$sideload = array( 'name' => $record['image'], 'tmp_name' => $temp );
		$attachment_id = media_handle_sideload( $sideload, $post_id, $record['image_caption'] );
		if ( is_wp_error( $attachment_id ) ) {
			@unlink( $temp );
			return $attachment_id;
		}

		update_post_meta( $attachment_id, self::IMAGE_SOURCE_META, $record['image'] );
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $record['image_alt'] );

		return (int) $attachment_id;
	}
}
