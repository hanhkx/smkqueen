<?php
/** Non-destructive importer for verified additions to Karya Siswa. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class QAF_Core_Student_Work_Updates {
	const VERSION_OPTION    = 'qaf_student_work_updates_version';
	const SOURCE_META       = '_qaf_work_update_source_id';
	const IMAGE_SOURCE_META = '_qaf_work_update_image';
	const LOCK_OPTION       = 'qaf_student_work_updates_lock';

	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'maybe_upgrade' ), 47 );
	}

	public static function activate() {
		self::run_upgrade();
	}

	public static function maybe_upgrade() {
		if ( current_user_can( 'manage_options' ) ) {
			self::run_upgrade();
		}
	}

	private static function run_upgrade() {
		$data = self::load_data();
		if ( is_wp_error( $data ) || version_compare( (string) get_option( self::VERSION_OPTION, '0.0.0' ), $data['version'], '>=' ) ) {
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

	private static function load_data() {
		$path = QAF_CORE_PATH . 'includes/student-work-update-data.php';
		if ( ! is_readable( $path ) ) {
			return new WP_Error( 'qaf_work_update_missing', 'Berkas data Karya Siswa tidak tersedia.' );
		}
		$data = require $path;
		if ( ! is_array( $data ) || empty( $data['version'] ) || ! is_string( $data['version'] ) || ! preg_match( '/^\d+\.\d+\.\d+$/', $data['version'] ) || ! isset( $data['items'] ) || ! is_array( $data['items'] ) ) {
			return new WP_Error( 'qaf_work_update_invalid', 'Struktur data Karya Siswa tidak valid.' );
		}
		return $data;
	}

	private static function insert_missing( $items ) {
		$errors = array();
		foreach ( $items as $item ) {
			$record = self::sanitize_record( $item );
			if ( is_wp_error( $record ) ) {
				$errors[] = $record->get_error_message();
				continue;
			}
			$post_id = self::find_existing( $record );
			if ( ! $post_id ) {
				$post_id = self::insert_record( $record );
				if ( is_wp_error( $post_id ) ) {
					$errors[] = $post_id->get_error_message();
					continue;
				}
			}
			if ( 'trash' === get_post_status( $post_id ) || has_post_thumbnail( $post_id ) ) {
				continue;
			}
			$attachment = self::import_image( $record, $post_id );
			if ( is_wp_error( $attachment ) || ! set_post_thumbnail( $post_id, $attachment ) ) {
				$errors[] = $record['source_id'] . ': gambar unggulan tidak dapat dipasang.';
			}
		}
		return $errors;
	}

	private static function sanitize_record( $item ) {
		if ( ! is_array( $item ) ) {
			return new WP_Error( 'qaf_work_update_row', 'Baris Karya Siswa bukan array.' );
		}
		$record = array(
			'source_id'    => sanitize_text_field( isset( $item['source_id'] ) ? $item['source_id'] : '' ),
			'title'        => sanitize_text_field( isset( $item['title'] ) ? $item['title'] : '' ),
			'slug'         => sanitize_title( isset( $item['slug'] ) ? $item['slug'] : '' ),
			'status'       => sanitize_key( isset( $item['status'] ) ? $item['status'] : 'draft' ),
			'publish_date' => sanitize_text_field( isset( $item['publish_date'] ) ? $item['publish_date'] : '' ),
			'work_date'    => sanitize_text_field( isset( $item['work_date'] ) ? $item['work_date'] : '' ),
			'type'         => sanitize_key( isset( $item['type'] ) ? $item['type'] : '' ),
			'image'        => sanitize_file_name( isset( $item['image'] ) ? $item['image'] : '' ),
			'image_alt'    => sanitize_text_field( isset( $item['image_alt'] ) ? $item['image_alt'] : '' ),
			'creator'      => sanitize_text_field( isset( $item['creator'] ) ? $item['creator'] : '' ),
			'class'        => sanitize_text_field( isset( $item['class'] ) ? $item['class'] : '' ),
			'award'        => sanitize_text_field( isset( $item['award'] ) ? $item['award'] : '' ),
			'source_url'   => esc_url_raw( isset( $item['source_url'] ) ? $item['source_url'] : '' ),
			'evidence_url' => esc_url_raw( isset( $item['evidence_url'] ) ? $item['evidence_url'] : '' ),
			'excerpt'      => sanitize_textarea_field( isset( $item['excerpt'] ) ? $item['excerpt'] : '' ),
			'content'      => wp_kses_post( isset( $item['content'] ) ? $item['content'] : '' ),
		);
		if ( ! preg_match( '/^[A-Za-z0-9._:-]+$/', $record['source_id'] ) || ! $record['title'] || ! $record['slug'] || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $record['publish_date'] ) ) {
			return new WP_Error( 'qaf_work_update_identity', 'Identitas Karya Siswa tidak valid.' );
		}
		if ( ! in_array( $record['status'], array( 'draft', 'pending', 'private', 'publish' ), true ) || ! preg_match( '/^[a-z0-9-]+\.webp$/', $record['image'] ) || ! wp_http_validate_url( $record['source_url'] ) ) {
			return new WP_Error( 'qaf_work_update_fields', 'Field Karya Siswa tidak valid.' );
		}
		return $record;
	}

	private static function find_existing( $record ) {
		$statuses = array_values( get_post_stati() );
		foreach ( array( self::SOURCE_META => $record['source_id'], '_qaf_work_source_url' => $record['source_url'] ) as $key => $value ) {
			$ids = get_posts( array( 'post_type' => 'qaf_work', 'post_status' => $statuses, 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => true, 'meta_key' => $key, 'meta_value' => $value ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
			if ( $ids ) {
				return (int) $ids[0];
			}
		}
		$post = get_page_by_path( $record['slug'], OBJECT, 'qaf_work' );
		return $post instanceof WP_Post ? (int) $post->ID : 0;
	}

	private static function insert_record( $record ) {
		$meta = array(
			self::SOURCE_META       => $record['source_id'],
			'_qaf_work_creator'     => $record['creator'],
			'_qaf_work_class'       => $record['class'],
			'_qaf_work_source_url'  => $record['source_url'],
			'_qaf_work_award'       => $record['award'],
			'_qaf_work_evidence_url' => $record['evidence_url'],
		);
		if ( $record['work_date'] ) {
			$meta['_qaf_work_date'] = $record['work_date'];
		}
		$post_id = wp_insert_post( wp_slash( array(
			'post_type' => 'qaf_work', 'post_status' => $record['status'], 'post_title' => $record['title'],
			'post_name' => $record['slug'], 'post_date' => $record['publish_date'] . ' 12:00:00',
			'post_excerpt' => $record['excerpt'], 'post_content' => $record['content'], 'meta_input' => $meta,
		) ), true );
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}
		$term = term_exists( $record['type'], 'qaf_work_type' );
		if ( ! $term ) {
			$term = wp_insert_term( 'Portofolio', 'qaf_work_type', array( 'slug' => $record['type'] ) );
		}
		if ( is_wp_error( $term ) ) {
			return $term;
		}
		wp_set_object_terms( $post_id, array( is_array( $term ) ? (int) $term['term_id'] : (int) $term ), 'qaf_work_type' );
		return (int) $post_id;
	}

	private static function import_image( $record, $post_id ) {
		$existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => true, 'meta_key' => self::IMAGE_SOURCE_META, 'meta_value' => $record['image'] ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( $existing ) {
			return (int) $existing[0];
		}
		$base = realpath( QAF_CORE_PATH . 'assets/images/student-works' );
		$file = realpath( QAF_CORE_PATH . 'assets/images/student-works/' . $record['image'] );
		if ( ! $base || ! $file || 0 !== strpos( wp_normalize_path( $file ), trailingslashit( wp_normalize_path( $base ) ) ) || ! is_readable( $file ) ) {
			return new WP_Error( 'qaf_work_update_image_missing', 'Gambar Karya Siswa tidak tersedia.' );
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$temp = wp_tempnam( $record['image'] );
		if ( ! $temp || ! copy( $file, $temp ) ) {
			return new WP_Error( 'qaf_work_update_image_copy', 'Gambar Karya Siswa tidak dapat disalin.' );
		}
		$attachment = media_handle_sideload( array( 'name' => $record['image'], 'tmp_name' => $temp ), $post_id, $record['title'] );
		if ( is_wp_error( $attachment ) ) {
			@unlink( $temp );
			return $attachment;
		}
		update_post_meta( $attachment, self::IMAGE_SOURCE_META, $record['image'] );
		update_post_meta( $attachment, '_wp_attachment_image_alt', $record['image_alt'] );
		return (int) $attachment;
	}
}
