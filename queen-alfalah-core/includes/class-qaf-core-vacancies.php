<?php
/**
 * Append-only importer for the curated BKK vacancy package.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class QAF_Core_Vacancies {
	/** Dataset version already applied. */
	const VERSION_OPTION = 'qaf_vacancy_dataset_version';

	/** Stable source marker on vacancy posts. */
	const SOURCE_META = '_qaf_vacancy_source_id';

	/** Bundled logo marker on Media Library attachments. */
	const IMAGE_META = '_qaf_bundled_vacancy_image';

	/** Register the admin-only upgrade runner. */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'maybe_upgrade' ), 48 );
	}

	/** Apply the dataset while the plugin is activated. */
	public static function activate() {
		self::run_upgrade();
	}

	/** Retry a pending package only for administrators. */
	public static function maybe_upgrade() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		self::run_upgrade();
	}

	/** Import every missing record and persist the version only on success. */
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
	 * Load the local data contract.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	private static function load_data() {
		$path = QAF_CORE_PATH . 'includes/vacancy-data.php';
		if ( ! is_readable( $path ) ) {
			return new WP_Error( 'qaf_vacancy_data_missing', 'Berkas data lowongan tidak dapat dibaca.' );
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
			return new WP_Error( 'qaf_vacancy_data_invalid', 'Struktur data lowongan tidak valid.' );
		}

		return $data;
	}

	/**
	 * Synchronize records without deleting or overwriting editor content.
	 *
	 * @param array<int,array<string,mixed>> $items Dataset rows.
	 * @return array<int,string>
	 */
	private static function sync_items( $items ) {
		$errors = array();

		foreach ( $items as $index => $item ) {
			$record = self::sanitize_record( $item );
			if ( is_wp_error( $record ) ) {
				$errors[] = sprintf( 'Lowongan indeks %d: %s', (int) $index, $record->get_error_message() );
				continue;
			}

			$post_id = self::find_post( $record['source_id'], $record['slug'] );
			if ( ! $post_id ) {
				$post_id = self::create_post( $record );
				if ( is_wp_error( $post_id ) ) {
					$errors[] = sprintf( '%s: %s', $record['source_id'], $post_id->get_error_message() );
					continue;
				}
			}

			if ( 'trash' === get_post_status( $post_id ) ) {
				continue;
			}

			self::fill_metadata( $post_id, $record );
			self::set_field_term( $post_id, $record['field'] );

			if ( has_post_thumbnail( $post_id ) ) {
				continue;
			}

			$attachment_id = self::get_or_import_image( $record, $post_id );
			if ( is_wp_error( $attachment_id ) ) {
				$errors[] = sprintf( '%s: %s', $record['source_id'], $attachment_id->get_error_message() );
				continue;
			}

			if ( ! set_post_thumbnail( $post_id, $attachment_id ) ) {
				$errors[] = sprintf( '%s: logo perusahaan tidak dapat dijadikan gambar unggulan.', $record['source_id'] );
			}
		}

		return $errors;
	}

	/**
	 * Validate and sanitize one dataset row.
	 *
	 * @param mixed $item Raw row.
	 * @return array<string,string>|WP_Error
	 */
	private static function sanitize_record( $item ) {
		if ( ! is_array( $item ) ) {
			return new WP_Error( 'qaf_vacancy_row_invalid', 'Baris data bukan array.' );
		}

		$record = array(
			'source_id'     => self::sanitize_source_id( isset( $item['source_id'] ) ? $item['source_id'] : '' ),
			'slug'          => sanitize_title( isset( $item['slug'] ) ? $item['slug'] : '' ),
			'title'         => sanitize_text_field( isset( $item['title'] ) ? $item['title'] : '' ),
			'company'       => sanitize_text_field( isset( $item['company'] ) ? $item['company'] : '' ),
			'field'         => sanitize_text_field( isset( $item['field'] ) ? $item['field'] : '' ),
			'location'      => sanitize_text_field( isset( $item['location'] ) ? $item['location'] : '' ),
			'employment'    => sanitize_text_field( isset( $item['employment'] ) ? $item['employment'] : '' ),
			'education'     => sanitize_text_field( isset( $item['education'] ) ? $item['education'] : '' ),
			'salary'        => sanitize_text_field( isset( $item['salary'] ) ? $item['salary'] : '' ),
			'verified_date' => self::sanitize_date( isset( $item['verified_date'] ) ? $item['verified_date'] : '' ),
			'source_name'   => sanitize_text_field( isset( $item['source_name'] ) ? $item['source_name'] : '' ),
			'source_url'    => self::sanitize_https_url( isset( $item['source_url'] ) ? $item['source_url'] : '' ),
			'apply_url'     => self::sanitize_https_url( isset( $item['apply_url'] ) ? $item['apply_url'] : '' ),
			'contact'       => sanitize_text_field( isset( $item['contact'] ) ? $item['contact'] : '' ),
			'image'         => self::sanitize_image_path( isset( $item['image'] ) ? $item['image'] : '' ),
			'image_alt'     => sanitize_text_field( isset( $item['image_alt'] ) ? $item['image_alt'] : '' ),
			'image_caption' => sanitize_textarea_field( isset( $item['image_caption'] ) ? $item['image_caption'] : '' ),
			'image_credit'  => sanitize_text_field( isset( $item['image_credit'] ) ? $item['image_credit'] : '' ),
			'excerpt'       => sanitize_textarea_field( isset( $item['excerpt'] ) ? $item['excerpt'] : '' ),
			'content'       => wp_kses_post( isset( $item['content'] ) ? $item['content'] : '' ),
		);

		$required = array(
			'source_id', 'slug', 'title', 'company', 'field', 'location', 'employment',
			'education', 'salary', 'verified_date', 'source_name', 'source_url', 'apply_url',
			'image', 'image_alt', 'image_caption', 'image_credit', 'excerpt', 'content',
		);
		foreach ( $required as $field ) {
			if ( '' === trim( (string) $record[ $field ] ) ) {
				return new WP_Error( 'qaf_vacancy_field_missing', sprintf( 'Field wajib %s kosong atau tidak valid.', $field ) );
			}
		}

		return $record;
	}

	/** Create one missing public vacancy. */
	private static function create_post( $record ) {
		$content  = $record['content'];
		$content .= '<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->';
		$content .= '<!-- wp:paragraph {"className":"qaf-vacancy-safety"} --><p class="qaf-vacancy-safety"><strong>Catatan keamanan:</strong> status lowongan dapat berubah sewaktu-waktu. Periksa kembali sumber sebelum melamar. Rekrutmen yang sah tidak meminta transfer, top-up, atau data perbankan.</p><!-- /wp:paragraph -->';

		return wp_insert_post(
			wp_slash(
				array(
					'post_type'    => 'qaf_vacancy',
					'post_status'  => 'publish',
					'post_author'  => get_current_user_id() ? get_current_user_id() : 1,
					'post_name'    => $record['slug'],
					'post_title'   => $record['title'],
					'post_excerpt' => $record['excerpt'],
					'post_content' => $content,
				)
			),
			true
		);
	}

	/** Find an existing record by marker, then by slug. */
	private static function find_post( $source_id, $slug ) {
		$matches = get_posts(
			array(
				'post_type'              => 'qaf_vacancy',
				'post_status'            => array_values( get_post_stati() ),
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
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

		$post = get_page_by_path( $slug, OBJECT, 'qaf_vacancy' );
		return $post instanceof WP_Post ? (int) $post->ID : 0;
	}

	/** Fill only missing structured fields so later editor changes win. */
	private static function fill_metadata( $post_id, $record ) {
		$values = array(
			self::SOURCE_META                => $record['source_id'],
			'_qaf_company'                   => $record['company'],
			'_qaf_vacancy_location'          => $record['location'],
			'_qaf_vacancy_employment_type'   => $record['employment'],
			'_qaf_vacancy_education'         => $record['education'],
			'_qaf_vacancy_salary'            => $record['salary'],
			'_qaf_vacancy_verified_date'     => $record['verified_date'],
			'_qaf_vacancy_source_name'       => $record['source_name'],
			'_qaf_vacancy_source_url'        => $record['source_url'],
			'_qaf_apply_url'                  => $record['apply_url'],
			'_qaf_vacancy_contact'           => $record['contact'],
			'_qaf_vacancy_image_credit'      => $record['image_credit'],
		);

		foreach ( $values as $key => $value ) {
			if ( '' === (string) $value ) {
				continue;
			}
			if ( ! metadata_exists( 'post', $post_id, $key ) || '' === (string) get_post_meta( $post_id, $key, true ) ) {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}

	/** Add the related program term without removing existing terms. */
	private static function set_field_term( $post_id, $field ) {
		if ( taxonomy_exists( 'qaf_vacancy_field' ) ) {
			wp_set_object_terms( $post_id, array( $field ), 'qaf_vacancy_field', true );
		}
	}

	/** Reuse or import a bundled company logo. */
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
				'meta_key'               => self::IMAGE_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'             => $record['image'], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);

		if ( $matches ) {
			$attachment_id = (int) $matches[0];
			self::fill_attachment( $attachment_id, $record );
			return $attachment_id;
		}

		$file = self::resolve_image( $record['image'] );
		if ( is_wp_error( $file ) ) {
			return $file;
		}

		$filetype = wp_check_filetype_and_ext( $file, basename( $file ) );
		if ( empty( $filetype['type'] ) || 0 !== strpos( $filetype['type'], 'image/' ) ) {
			return new WP_Error( 'qaf_vacancy_image_type_invalid', 'Jenis berkas logo tidak didukung.' );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$temp_file = wp_tempnam( basename( $file ) );
		if ( ! $temp_file || ! copy( $file, $temp_file ) ) {
			if ( $temp_file && file_exists( $temp_file ) ) {
				wp_delete_file( $temp_file );
			}
			return new WP_Error( 'qaf_vacancy_image_copy_failed', 'Logo perusahaan tidak dapat disalin.' );
		}

		$attachment_id = media_handle_sideload(
			array( 'name' => basename( $file ), 'tmp_name' => $temp_file ),
			$post_id,
			$record['image_credit'],
			array(
				'post_title'   => 'Logo ' . $record['company'],
				'post_excerpt' => $record['image_caption'],
				'post_content' => $record['image_credit'],
			)
		);
		if ( is_wp_error( $attachment_id ) ) {
			if ( file_exists( $temp_file ) ) {
				wp_delete_file( $temp_file );
			}
			return $attachment_id;
		}

		update_post_meta( $attachment_id, self::IMAGE_META, $record['image'] );
		self::fill_attachment( (int) $attachment_id, $record );
		return (int) $attachment_id;
	}

	/** Fill missing Media Library fields. */
	private static function fill_attachment( $attachment_id, $record ) {
		$attachment = get_post( $attachment_id );
		if ( ! $attachment instanceof WP_Post || 'attachment' !== $attachment->post_type ) {
			return;
		}

		$updates = array( 'ID' => $attachment_id );
		if ( '' === trim( $attachment->post_excerpt ) ) {
			$updates['post_excerpt'] = $record['image_caption'];
		}
		if ( '' === trim( $attachment->post_content ) ) {
			$updates['post_content'] = $record['image_credit'];
		}
		if ( count( $updates ) > 1 ) {
			wp_update_post( wp_slash( $updates ) );
		}
		if ( '' === (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) {
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', $record['image_alt'] );
		}
	}

	/** Resolve an image inside the bundled vacancy directory. */
	private static function resolve_image( $relative_path ) {
		$base_dir = realpath( QAF_CORE_PATH . 'assets/images/vacancies' );
		if ( false === $base_dir ) {
			return new WP_Error( 'qaf_vacancy_image_directory_missing', 'Direktori logo lowongan tidak tersedia.' );
		}

		$file = realpath( $base_dir . DIRECTORY_SEPARATOR . str_replace( '/', DIRECTORY_SEPARATOR, $relative_path ) );
		$base = rtrim( wp_normalize_path( $base_dir ), '/' ) . '/';
		if ( false === $file || 0 !== strpos( wp_normalize_path( $file ), $base ) || ! is_file( $file ) || ! is_readable( $file ) ) {
			return new WP_Error( 'qaf_vacancy_image_missing', 'Berkas logo lowongan tidak ditemukan.' );
		}

		return $file;
	}

	/** Restrict stable IDs to comparison-safe characters. */
	private static function sanitize_source_id( $value ) {
		$value = sanitize_text_field( is_scalar( $value ) ? (string) $value : '' );
		$value = preg_replace( '/[^A-Za-z0-9._:-]+/', '-', trim( $value ) );
		return substr( (string) $value, 0, 191 );
	}

	/** Validate a date in the WordPress date input format. */
	private static function sanitize_date( $value ) {
		$value = is_scalar( $value ) ? trim( (string) $value ) : '';
		return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ? $value : '';
	}

	/** Allow canonical HTTPS URLs only. */
	private static function sanitize_https_url( $value ) {
		return esc_url_raw( is_scalar( $value ) ? trim( (string) $value ) : '', array( 'https' ) );
	}

	/** Reject absolute paths and traversal. */
	private static function sanitize_image_path( $value ) {
		if ( ! is_scalar( $value ) ) {
			return '';
		}

		$value = str_replace( '\\', '/', trim( (string) $value ) );
		if ( '' === $value || '/' === substr( $value, 0, 1 ) || false !== strpos( $value, "\0" ) || in_array( '..', explode( '/', $value ), true ) ) {
			return '';
		}

		$segments = array_map( 'sanitize_file_name', explode( '/', $value ) );
		return in_array( '', $segments, true ) ? '' : implode( '/', $segments );
	}
}
