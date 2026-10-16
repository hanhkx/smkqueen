<?php
/**
 * Non-destructive featured-media importer for verified school news.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Attach bundled Instagram covers to their existing WordPress news posts.
 */
final class QAF_Core_News_Media {
	/** Version of the news-media dataset already applied. */
	const VERSION_OPTION = 'qaf_news_media_version';

	/** Stable source marker stored on a news post. */
	const SOURCE_META = '_qaf_news_source_id';

	/** Canonical Instagram post URL stored on a news post. */
	const INSTAGRAM_META = '_qaf_instagram_source_url';

	/** Human-readable image credit stored on a news post. */
	const IMAGE_CREDIT_META = '_qaf_image_credit';

	/** Bundled file marker stored on a Media Library attachment. */
	const IMAGE_SOURCE_META = '_qaf_bundled_news_image';

	/**
	 * Retry a pending package only from an administrator request.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'maybe_upgrade' ), 45 );
	}

	/**
	 * Apply the package during plugin activation when the target posts exist.
	 *
	 * @return void
	 */
	public static function activate() {
		self::run_upgrade();
	}

	/**
	 * Apply newly bundled media after a plugin update.
	 *
	 * Public requests never write posts or Media Library records.
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
	 * Synchronize the dataset exactly once after every item succeeds.
	 *
	 * Missing posts deliberately keep the version pending so importing the
	 * supplied WordPress export later can complete the media package.
	 *
	 * @return void
	 */
	private static function run_upgrade() {
		$data = self::load_data();
		if ( is_wp_error( $data ) ) {
			return;
		}

		$current = (string) get_option( self::VERSION_OPTION, '0.0.0' );
		if ( version_compare( $current, $data['version'], '>=' ) ) {
			return;
		}

		$errors = self::sync_items( $data['items'] );
		if ( empty( $errors ) ) {
			update_option( self::VERSION_OPTION, $data['version'], false );
		}
	}

	/**
	 * Load and validate the local data contract.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	private static function load_data() {
		$path = QAF_CORE_PATH . 'includes/news-media-data.php';
		if ( ! is_readable( $path ) ) {
			return new WP_Error( 'qaf_news_media_data_missing', 'Berkas data media berita tidak dapat dibaca.' );
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
			return new WP_Error( 'qaf_news_media_data_invalid', 'Struktur atau versi data media berita tidak valid.' );
		}

		return $data;
	}

	/**
	 * Fill only empty source metadata and featured-image slots.
	 *
	 * @param array<int,array<string,mixed>> $items News-media records.
	 * @return array<int,string> Error messages.
	 */
	private static function sync_items( $items ) {
		$errors = array();

		foreach ( $items as $index => $item ) {
			$record = self::sanitize_record( $item );
			if ( is_wp_error( $record ) ) {
				$errors[] = sprintf( 'Media berita indeks %d: %s', (int) $index, $record->get_error_message() );
				continue;
			}

			$post_id = self::find_news_post( $record['source_id'], $record['post_slug'], $record['source_url'], $record['post'] );
			if ( ! $post_id && ! empty( $record['post'] ) ) {
				$post_id = self::create_news_post( $record );
				if ( is_wp_error( $post_id ) ) {
					$errors[] = sprintf( '%s: %s', $record['source_id'], $post_id->get_error_message() );
					continue;
				}
			}

			if ( ! $post_id ) {
				$errors[] = sprintf( '%s: berita tujuan belum tersedia.', $record['source_id'] );
				continue;
			}

			if ( 'trash' === get_post_status( $post_id ) ) {
				continue;
			}

			self::publish_due_post( $post_id, $record );

			if ( ! empty( $record['publish_matching_draft'] ) && ! empty( $record['post'] ) && 'draft' === get_post_status( $post_id ) ) {
				$published = self::publish_matching_draft( $post_id, $record );
				if ( is_wp_error( $published ) ) {
					$errors[] = sprintf( '%s: %s', $record['source_id'], $published->get_error_message() );
					continue;
				}
			}

			self::fill_post_meta( $post_id, $record );

			// An editor-selected featured image is authoritative and is never replaced.
			if ( has_post_thumbnail( $post_id ) ) {
				continue;
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
	 * Publish a verified same-day article when WordPress converted the desired
	 * published state into a future post because of server-clock differences.
	 *
	 * @param int                 $post_id Existing post ID.
	 * @param array<string,mixed> $record  Sanitized record.
	 * @return void
	 */
	private static function publish_due_post( $post_id, $record ) {
		if (
			empty( $record['post'] )
			|| 'publish' !== $record['post']['status']
			|| 'future' !== get_post_status( $post_id )
			|| substr( $record['post']['date'], 0, 10 ) > current_time( 'Y-m-d' )
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

	/**
	 * Validate one dataset row before it reaches WordPress persistence APIs.
	 *
	 * @param mixed $item Raw row.
	 * @return array<string,string>|WP_Error
	 */
	private static function sanitize_record( $item ) {
		if ( ! is_array( $item ) ) {
			return new WP_Error( 'qaf_news_media_row_invalid', 'Baris data bukan array.' );
		}

		$record = array(
			'source_id'  => self::sanitize_source_id( isset( $item['source_id'] ) ? $item['source_id'] : '' ),
			'post_slug'  => sanitize_title( isset( $item['post_slug'] ) ? $item['post_slug'] : '' ),
			'image'      => self::sanitize_image_path( isset( $item['image'] ) ? $item['image'] : '' ),
			'title'      => sanitize_text_field( isset( $item['title'] ) ? $item['title'] : '' ),
			'alt'        => sanitize_text_field( isset( $item['alt'] ) ? $item['alt'] : '' ),
			'caption'    => sanitize_textarea_field( isset( $item['caption'] ) ? $item['caption'] : '' ),
			'credit'     => sanitize_text_field( isset( $item['credit'] ) ? $item['credit'] : '' ),
			'source_url' => self::sanitize_instagram_url( isset( $item['source_url'] ) ? $item['source_url'] : '' ),
			'publish_matching_draft' => ! empty( $item['publish_matching_draft'] ),
			'post'       => self::sanitize_post_payload( isset( $item['post'] ) ? $item['post'] : array() ),
		);

		foreach ( array( 'source_id', 'post_slug', 'image', 'title', 'alt', 'caption', 'credit', 'source_url' ) as $field ) {
			$value = $record[ $field ];
			if ( '' === $value ) {
				return new WP_Error( 'qaf_news_media_field_missing', 'Ada field wajib yang kosong atau tidak valid.' );
			}
		}

		if ( is_wp_error( $record['post'] ) ) {
			return $record['post'];
		}

		return $record;
	}

	/**
	 * Sanitize an optional news-post payload.
	 *
	 * Records without this payload remain media-only updates for posts that
	 * already exist. A complete payload may create one missing verified post.
	 *
	 * @param mixed $payload Raw post payload.
	 * @return array<string,mixed>|WP_Error
	 */
	private static function sanitize_post_payload( $payload ) {
		if ( empty( $payload ) ) {
			return array();
		}

		if ( ! is_array( $payload ) ) {
			return new WP_Error( 'qaf_news_post_payload_invalid', 'Data berita baru bukan array.' );
		}

		$date = isset( $payload['date'] ) && is_scalar( $payload['date'] ) ? trim( (string) $payload['date'] ) : '';
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date ) ) {
			return new WP_Error( 'qaf_news_post_date_invalid', 'Tanggal berita baru tidak valid.' );
		}

		$status = isset( $payload['status'] ) && is_scalar( $payload['status'] ) ? sanitize_key( (string) $payload['status'] ) : '';
		if ( ! in_array( $status, array( 'draft', 'publish' ), true ) ) {
			return new WP_Error( 'qaf_news_post_status_invalid', 'Status berita baru tidak didukung.' );
		}

		$post = array(
			'title'      => sanitize_text_field( isset( $payload['title'] ) ? $payload['title'] : '' ),
			'excerpt'    => sanitize_textarea_field( isset( $payload['excerpt'] ) ? $payload['excerpt'] : '' ),
			'content'    => wp_kses_post( isset( $payload['content'] ) ? $payload['content'] : '' ),
			'date'       => $date,
			'status'     => $status,
			'category'   => sanitize_text_field( isset( $payload['category'] ) ? $payload['category'] : '' ),
			'tags'       => array(),
		);

		if ( isset( $payload['tags'] ) && is_array( $payload['tags'] ) ) {
			$post['tags'] = array_values( array_filter( array_map( 'sanitize_text_field', $payload['tags'] ) ) );
		}

		foreach ( array( 'title', 'excerpt', 'content', 'category' ) as $field ) {
			if ( '' === trim( (string) $post[ $field ] ) ) {
				return new WP_Error( 'qaf_news_post_field_missing', 'Ada field berita baru yang kosong.' );
			}
		}

		return $post;
	}

	/**
	 * Create one missing, verified news post without changing existing posts.
	 *
	 * @param array<string,mixed> $record Sanitized media and post record.
	 * @return int|WP_Error
	 */
	private static function create_news_post( $record ) {
		$payload = $record['post'];
		$term    = term_exists( $payload['category'], 'category' );

		if ( ! $term ) {
			$term = wp_insert_term( $payload['category'], 'category' );
		}

		if ( is_wp_error( $term ) ) {
			return $term;
		}

		$category_id = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
		$post_id     = wp_insert_post(
			wp_slash(
				array(
					'post_type'     => 'post',
					'post_status'   => $payload['status'],
					'post_author'   => get_current_user_id() ? get_current_user_id() : 1,
					'post_name'     => $record['post_slug'],
					'post_title'    => $payload['title'],
					'post_excerpt'  => $payload['excerpt'],
					'post_content'  => $payload['content'],
					'post_date'     => $payload['date'],
					'post_date_gmt' => get_gmt_from_date( $payload['date'] ),
					'post_category' => array( $category_id ),
					'tags_input'    => $payload['tags'],
				)
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		self::fill_post_meta( (int) $post_id, $record );
		return (int) $post_id;
	}

	/**
	 * Complete and publish one exact matching draft without replacing editor copy.
	 *
	 * Only empty title, excerpt, and content fields receive the verified package
	 * copy. The source date becomes the publication date, while existing terms
	 * are preserved and the verified category/tags are appended.
	 *
	 * @param int                 $post_id Existing draft ID.
	 * @param array<string,mixed> $record  Sanitized media and post record.
	 * @return int|WP_Error
	 */
	private static function publish_matching_draft( $post_id, $record ) {
		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post || 'post' !== $post->post_type || 'draft' !== $post->post_status ) {
			return new WP_Error( 'qaf_news_draft_mismatch', 'Berita yang ditemukan bukan draf Berita yang dapat dipublikasikan.' );
		}

		$payload = $record['post'];
		$update  = array(
			'ID'            => $post_id,
			'post_status'   => 'publish',
			'post_date'     => $payload['date'],
			'post_date_gmt' => get_gmt_from_date( $payload['date'] ),
		);

		if ( '' === trim( (string) $post->post_title ) ) {
			$update['post_title'] = $payload['title'];
		}
		if ( '' === trim( (string) $post->post_excerpt ) ) {
			$update['post_excerpt'] = $payload['excerpt'];
		}
		if ( '' === trim( (string) $post->post_content ) ) {
			$update['post_content'] = $payload['content'];
		}

		$updated = wp_update_post( wp_slash( $update ), true );
		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

		$term = term_exists( $payload['category'], 'category' );
		if ( ! $term ) {
			$term = wp_insert_term( $payload['category'], 'category' );
		}
		if ( is_wp_error( $term ) ) {
			return $term;
		}

		$category_id = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
		wp_set_post_terms( $post_id, array( $category_id ), 'category', true );
		wp_set_post_terms( $post_id, $payload['tags'], 'post_tag', true );

		return (int) $updated;
	}

	/**
	 * Find an existing post by importer marker, then by the exported slug.
	 *
	 * @param string $source_id Stable source marker.
	 * @param string $post_slug Exported post slug.
	 * @param string $source_url Canonical source URL.
	 * @param array<string,mixed> $payload Optional verified post payload.
	 * @return int
	 */
	private static function find_news_post( $source_id, $post_slug, $source_url, $payload ) {
		$matches = get_posts(
			array(
				'post_type'              => 'post',
				'post_status'            => array_values( get_post_stati() ),
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

		if ( $matches ) {
			return (int) $matches[0];
		}

		$matches = get_posts(
			array(
				'post_type'              => 'post',
				'post_status'            => array_values( get_post_stati() ),
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'suppress_filters'       => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'meta_key'               => self::INSTAGRAM_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'             => $source_url, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);

		if ( $matches ) {
			return (int) $matches[0];
		}

		$post = get_page_by_path( $post_slug, OBJECT, 'post' );
		if ( $post instanceof WP_Post ) {
			return (int) $post->ID;
		}

		if ( ! empty( $payload['title'] ) ) {
			$matches = get_posts(
				array(
					'post_type'              => 'post',
					'post_status'            => 'draft',
					'title'                  => $payload['title'],
					'posts_per_page'         => 1,
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'ignore_sticky_posts'    => true,
					'suppress_filters'       => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				)
			);
			if ( $matches ) {
				return (int) $matches[0];
			}
		}

		return 0;
	}

	/**
	 * Add only missing post metadata; editor changes always win.
	 *
	 * @param int                  $post_id News post ID.
	 * @param array<string,string> $record  Sanitized record.
	 * @return void
	 */
	private static function fill_post_meta( $post_id, $record ) {
		$values = array(
			self::SOURCE_META       => $record['source_id'],
			self::INSTAGRAM_META    => $record['source_url'],
			self::IMAGE_CREDIT_META => $record['credit'],
		);

		foreach ( $values as $meta_key => $value ) {
			if ( ! metadata_exists( 'post', $post_id, $meta_key ) || '' === (string) get_post_meta( $post_id, $meta_key, true ) ) {
				update_post_meta( $post_id, $meta_key, $value );
			}
		}
	}

	/**
	 * Reuse an imported attachment or copy one local bundled image into uploads.
	 *
	 * @param array<string,string> $record  Sanitized record.
	 * @param int                  $post_id News post receiving the image.
	 * @return int|WP_Error
	 */
	private static function get_or_import_image( $record, $post_id ) {
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
				'meta_value'             => $record['image'], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);

		if ( $matches ) {
			$attachment_id = (int) $matches[0];
			self::fill_attachment_details( $attachment_id, $record );
			return $attachment_id;
		}

		$file = self::resolve_image_file( $record['image'] );
		if ( is_wp_error( $file ) ) {
			return $file;
		}

		$filetype = wp_check_filetype_and_ext( $file, basename( $file ) );
		if ( empty( $filetype['type'] ) || 0 !== strpos( $filetype['type'], 'image/' ) ) {
			return new WP_Error( 'qaf_news_media_image_type_invalid', 'Jenis berkas gambar berita tidak didukung.' );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$temp_file = wp_tempnam( basename( $file ) );
		if ( ! $temp_file || ! copy( $file, $temp_file ) ) {
			if ( $temp_file && file_exists( $temp_file ) ) {
				wp_delete_file( $temp_file );
			}
			return new WP_Error( 'qaf_news_media_image_copy_failed', 'Gambar berita tidak dapat disalin ke berkas sementara.' );
		}

		$file_array = array(
			'name'     => basename( $file ),
			'tmp_name' => $temp_file,
		);
		$attachment = media_handle_sideload(
			$file_array,
			$post_id,
			$record['credit'],
			array(
				'post_title'   => $record['title'],
				'post_excerpt' => $record['caption'],
				'post_content' => $record['credit'],
			)
		);
		if ( is_wp_error( $attachment ) ) {
			if ( file_exists( $temp_file ) ) {
				wp_delete_file( $temp_file );
			}
			return $attachment;
		}

		update_post_meta( $attachment, self::IMAGE_SOURCE_META, $record['image'] );
		self::fill_attachment_details( (int) $attachment, $record );

		return (int) $attachment;
	}

	/**
	 * Add title, caption, credit, and alt text only while each field is empty.
	 *
	 * @param int                  $attachment_id Attachment ID.
	 * @param array<string,string> $record        Sanitized record.
	 * @return void
	 */
	private static function fill_attachment_details( $attachment_id, $record ) {
		$attachment = get_post( $attachment_id );
		if ( ! $attachment instanceof WP_Post || 'attachment' !== $attachment->post_type ) {
			return;
		}

		$updates = array( 'ID' => $attachment_id );
		if ( '' === trim( $attachment->post_title ) ) {
			$updates['post_title'] = $record['title'];
		}
		if ( '' === trim( $attachment->post_excerpt ) ) {
			$updates['post_excerpt'] = $record['caption'];
		}
		if ( '' === trim( $attachment->post_content ) ) {
			$updates['post_content'] = $record['credit'];
		}

		if ( count( $updates ) > 1 ) {
			wp_update_post( wp_slash( $updates ) );
		}

		if ( '' === (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) {
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', $record['alt'] );
		}
	}

	/**
	 * Resolve a safe relative path below the bundled news-image directory.
	 *
	 * @param string $relative_path Sanitized relative path.
	 * @return string|WP_Error
	 */
	private static function resolve_image_file( $relative_path ) {
		$base_dir = realpath( QAF_CORE_PATH . 'assets/images/news' );
		if ( false === $base_dir ) {
			return new WP_Error( 'qaf_news_media_directory_missing', 'Direktori gambar berita tidak tersedia.' );
		}

		$file = realpath( $base_dir . DIRECTORY_SEPARATOR . str_replace( '/', DIRECTORY_SEPARATOR, $relative_path ) );
		$base = rtrim( wp_normalize_path( $base_dir ), '/' ) . '/';
		if (
			false === $file
			|| 0 !== strpos( wp_normalize_path( $file ), $base )
			|| ! is_file( $file )
			|| ! is_readable( $file )
		) {
			return new WP_Error( 'qaf_news_media_image_missing', 'Berkas gambar berita tidak ditemukan atau tidak dapat dibaca.' );
		}

		return $file;
	}

	/**
	 * Restrict a source identifier to comparison-safe characters.
	 *
	 * @param mixed $source_id Raw source ID.
	 * @return string
	 */
	private static function sanitize_source_id( $source_id ) {
		$source_id = sanitize_text_field( is_scalar( $source_id ) ? (string) $source_id : '' );
		$source_id = preg_replace( '/[^A-Za-z0-9._:-]+/', '-', trim( $source_id ) );
		return substr( (string) $source_id, 0, 191 );
	}

	/**
	 * Reject absolute paths and directory traversal.
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
			'' === $path
			|| '/' === substr( $path, 0, 1 )
			|| false !== strpos( $path, "\0" )
			|| in_array( '..', explode( '/', $path ), true )
		) {
			return '';
		}

		$segments = array_map( 'sanitize_file_name', explode( '/', $path ) );
		return in_array( '', $segments, true ) ? '' : implode( '/', $segments );
	}

	/**
	 * Accept one canonical HTTPS Instagram post URL.
	 *
	 * @param mixed $value Raw URL.
	 * @return string
	 */
	private static function sanitize_instagram_url( $value ) {
		$url = esc_url_raw( is_scalar( $value ) ? trim( (string) $value ) : '', array( 'https' ) );
		if ( '' === $url ) {
			return '';
		}

		$host = strtolower( rtrim( (string) wp_parse_url( $url, PHP_URL_HOST ), '.' ) );
		$path = (string) wp_parse_url( $url, PHP_URL_PATH );
		if ( ! in_array( $host, array( 'instagram.com', 'www.instagram.com' ), true ) ) {
			return '';
		}

		return preg_match( '#^/(?:p|reel)/[A-Za-z0-9_-]+/?$#', $path ) ? $url : '';
	}
}
