<?php
/**
 * In-memory synchronization check for deduplication and editor preservation.
 *
 * Run with: php tests/instagram-gallery-sync.php
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'QAF_CORE_VERSION', 'test' );
define( 'MINUTE_IN_SECONDS', 60 );
define( 'DAY_IN_SECONDS', 86400 );
define( 'HOUR_IN_SECONDS', 3600 );

$qaf_test_options = array(
	'qaf_instagram_gallery_sync' => array(
		'user_id'             => '1234567890',
		'access_token'        => 'TEST_TOKEN_DO_NOT_USE',
		'auto_sync'           => 0,
		'post_status'         => 'draft',
		'max_items'           => 10,
		'download_thumbnails' => 0,
		'token_saved_at'      => '',
		'last_refresh_at'     => '',
	),
);
$qaf_test_transients = array();
$qaf_test_posts      = array();
$qaf_test_meta       = array();
$qaf_test_next_id    = 100;
$qaf_test_bearer_verified = false;
$qaf_test_simulate_refresh_race = false;

class WP_Error {
	private $code;
	private $message;
	public function __construct( $code, $message ) {
		$this->code    = $code;
		$this->message = $message;
	}
	public function get_error_message() {
		return $this->message;
	}
}

function __( $text ) { return $text; }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function wp_parse_args( $args, $defaults = array() ) { return array_merge( $defaults, is_array( $args ) ? $args : array() ); }
function absint( $value ) { return abs( (int) $value ); }
function sanitize_key( $value ) { return preg_replace( '/[^a-z0-9_-]/', '', strtolower( (string) $value ) ); }
function sanitize_text_field( $value ) { return trim( preg_replace( '/\s+/', ' ', strip_tags( (string) $value ) ) ); }
function sanitize_textarea_field( $value ) { return trim( strip_tags( (string) $value ) ); }
function sanitize_title( $value ) { return trim( preg_replace( '/[^a-z0-9]+/', '-', strtolower( (string) $value ) ), '-' ); }
function esc_html( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function wpautop( $value ) { return '<p>' . str_replace( "\n\n", '</p><p>', (string) $value ) . '</p>'; }
function wp_trim_words( $value, $count, $more = null ) {
	$words = preg_split( '/\s+/', trim( (string) $value ) );
	return count( $words ) > $count ? implode( ' ', array_slice( $words, 0, $count ) ) . $more : implode( ' ', $words );
}
function wp_slash( $value ) { return $value; }
function wp_timezone() { return new DateTimeZone( 'Asia/Jakarta' ); }
function wp_date( $format, $timestamp, $timezone = null ) {
	$date = new DateTimeImmutable( '@' . $timestamp );
	return $date->setTimezone( $timezone ? $timezone : wp_timezone() )->format( $format );
}
function get_gmt_from_date( $value ) { return $value; }
function get_current_user_id() { return 1; }
function home_url( $path = '' ) { return 'https://school.test' . $path; }

function get_option( $name, $default = false ) {
	global $qaf_test_options;
	if ( 'date_format' === $name ) {
		return 'Y-m-d';
	}
	if ( 'time_format' === $name ) {
		return 'H:i';
	}
	return array_key_exists( $name, $qaf_test_options ) ? $qaf_test_options[ $name ] : $default;
}
function update_option( $name, $value ) {
	global $qaf_test_options;
	$qaf_test_options[ $name ] = $value;
	return true;
}
function add_option( $name, $value ) {
	global $qaf_test_options;
	if ( array_key_exists( $name, $qaf_test_options ) ) {
		return false;
	}
	$qaf_test_options[ $name ] = $value;
	return true;
}
function delete_option( $name ) {
	global $qaf_test_options;
	unset( $qaf_test_options[ $name ] );
	return true;
}
function wp_generate_uuid4() { return '00000000-0000-4000-8000-000000000001'; }
function get_transient( $name ) {
	global $qaf_test_transients;
	return isset( $qaf_test_transients[ $name ] ) ? $qaf_test_transients[ $name ] : false;
}
function set_transient( $name, $value ) {
	global $qaf_test_transients;
	$qaf_test_transients[ $name ] = $value;
	return true;
}
function delete_transient( $name ) {
	global $qaf_test_transients;
	unset( $qaf_test_transients[ $name ] );
	return true;
}

function add_query_arg( $args, $url ) {
	return $url . ( false === strpos( $url, '?' ) ? '?' : '&' ) . http_build_query( $args, '', '&', PHP_QUERY_RFC3986 );
}
function wp_safe_remote_get( $url, $args = array() ) {
	global $qaf_test_bearer_verified, $qaf_test_simulate_refresh_race, $qaf_test_options;
	if ( 0 === strpos( $url, 'https://graph.instagram.com/refresh_access_token?' ) ) {
		if ( ! $qaf_test_simulate_refresh_race ) {
			return new WP_Error( 'unexpected_refresh', 'Refresh was not expected.' );
		}
		$qaf_test_options['qaf_instagram_gallery_sync']['access_token'] = 'ADMIN_REPLACEMENT_TOKEN';
		$qaf_test_options['qaf_instagram_gallery_sync']['post_status']  = 'publish';
		return array(
			'response' => array( 'code' => 200 ),
			'body'     => json_encode( array( 'access_token' => 'REFRESHED_OLD_TOKEN', 'expires_in' => 5184000 ) ),
		);
	}
	if ( 0 !== strpos( $url, 'https://graph.instagram.com/v26.0/1234567890/media?' ) ) {
		return new WP_Error( 'unexpected_url', 'Unexpected API URL.' );
	}
	if ( false !== strpos( $url, 'TEST_TOKEN_DO_NOT_USE' ) || empty( $args['headers']['Authorization'] ) || 'Bearer TEST_TOKEN_DO_NOT_USE' !== $args['headers']['Authorization'] ) {
		return new WP_Error( 'token_transport', 'Media token was not isolated in the Authorization header.' );
	}
	$qaf_test_bearer_verified = true;
	$records = array(
		array(
			'id'            => '9001',
			'caption'       => "Kegiatan siswa terbaru\nDokumentasi sekolah.",
			'media_type'    => 'VIDEO',
			'permalink'     => 'https://www.instagram.com/reel/Reel_9001/?igsh=test',
			'timestamp'     => '2026-08-28T08:00:00+0000',
			'thumbnail_url' => '',
		),
		array(
			'id'         => '9002',
			'caption'    => 'Foto biasa',
			'media_type' => 'IMAGE',
			'permalink'  => 'https://www.instagram.com/p/Photo_9002/',
			'timestamp'  => '2026-08-27T08:00:00+0000',
		),
		array(
			'id'            => '9003',
			'caption'       => 'Video feed sekolah',
			'media_type'    => 'VIDEO',
			'permalink'     => 'https://www.instagram.com/p/Video_9003/',
			'timestamp'     => '2026-08-26T08:00:00+0000',
			'thumbnail_url' => '',
		),
	);
	return array(
		'response' => array( 'code' => 200 ),
		'body'     => json_encode( array( 'data' => $records ) ),
	);
}
function wp_remote_retrieve_response_code( $response ) { return isset( $response['response']['code'] ) ? $response['response']['code'] : 0; }
function wp_remote_retrieve_body( $response ) { return isset( $response['body'] ) ? $response['body'] : ''; }

function get_posts( $args ) {
	global $qaf_test_posts, $qaf_test_meta;
	$found = array();
	foreach ( $qaf_test_posts as $id => $post ) {
		if ( $post['post_type'] !== $args['post_type'] || ! in_array( $post['post_status'], $args['post_status'], true ) ) {
			continue;
		}
		if ( isset( $args['meta_key'] ) ) {
			$value = isset( $qaf_test_meta[ $id ][ $args['meta_key'] ] ) ? $qaf_test_meta[ $id ][ $args['meta_key'] ] : null;
			$match = (string) $value === (string) $args['meta_value'];
		} else {
			$match = isset( $qaf_test_meta[ $id ] ) && array_key_exists( '_qaf_video_url', $qaf_test_meta[ $id ] );
		}
		if ( $match ) {
			$found[] = $id;
		}
	}
	return -1 === $args['posts_per_page'] ? $found : array_slice( $found, 0, 1 );
}
function wp_insert_post( $data ) {
	global $qaf_test_posts, $qaf_test_next_id;
	$id                    = ++$qaf_test_next_id;
	$data['ID']            = $id;
	$qaf_test_posts[ $id ] = $data;
	return $id;
}
function get_post_status( $post_id ) {
	global $qaf_test_posts;
	return isset( $qaf_test_posts[ $post_id ]['post_status'] ) ? $qaf_test_posts[ $post_id ]['post_status'] : false;
}
function get_post_meta( $post_id, $key, $single = false ) {
	global $qaf_test_meta;
	$value = isset( $qaf_test_meta[ $post_id ][ $key ] ) ? $qaf_test_meta[ $post_id ][ $key ] : '';
	return $single ? $value : array( $value );
}
function update_post_meta( $post_id, $key, $value ) {
	global $qaf_test_meta;
	$qaf_test_meta[ $post_id ][ $key ] = $value;
	return true;
}
function metadata_exists( $type, $post_id, $key ) {
	global $qaf_test_meta;
	return isset( $qaf_test_meta[ $post_id ] ) && array_key_exists( $key, $qaf_test_meta[ $post_id ] );
}
function has_post_thumbnail() { return false; }

require dirname( __DIR__ ) . '/queen-alfalah-core/includes/class-qaf-core-instagram-gallery.php';

function qaf_test_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, $message . PHP_EOL );
		exit( 1 );
	}
}

$qaf_test_posts[50] = array(
	'ID'          => 50,
	'post_type'   => 'qaf_gallery',
	'post_status' => 'draft',
	'post_title'  => 'Entri manual lama',
);
$qaf_test_meta[50] = array(
	'_qaf_gallery_source'         => 'instagram',
	'_qaf_gallery_media_type'     => 'video',
	'_qaf_gallery_embed_behavior' => 'link',
	'_qaf_video_url'              => 'https://instagram.com/reel/Reel_9001/?igsh=old-share-code',
);

$first = QAF_Core_Instagram_Gallery::sync( 'test' );
qaf_test_assert( ! is_wp_error( $first ), 'First synchronization returned an error.' );
qaf_test_assert( 3 === $first['checked'], 'Expected three checked records.' );
qaf_test_assert( 2 === $first['videos'], 'Expected two videos.' );
qaf_test_assert( 1 === $first['created'], 'Expected one new Gallery entry.' );
qaf_test_assert( 1 === $first['updated'], 'Expected the equivalent manual URL to be enriched.' );
qaf_test_assert( 2 === count( $qaf_test_posts ), 'Unexpected post count after first synchronization.' );

$first_id = array_key_first( $qaf_test_posts );
qaf_test_assert( 'instagram' === $qaf_test_meta[ $first_id ]['_qaf_gallery_source'], 'Instagram source metadata was not stored.' );
qaf_test_assert( 'video' === $qaf_test_meta[ $first_id ]['_qaf_gallery_media_type'], 'Video metadata was not stored.' );
qaf_test_assert( 'link' === $qaf_test_meta[ $first_id ]['_qaf_gallery_embed_behavior'], 'Administrator embed behavior was overwritten.' );
qaf_test_assert( 'https://instagram.com/reel/Reel_9001/?igsh=old-share-code' === $qaf_test_meta[ $first_id ]['_qaf_video_url'], 'Equivalent administrator permalink was overwritten.' );
qaf_test_assert( 'click' === $qaf_test_meta[101]['_qaf_gallery_embed_behavior'], 'New entry did not receive privacy-first embed behavior.' );
qaf_test_assert( 'https://www.instagram.com/p/Video_9003/' === $qaf_test_meta[101]['_qaf_video_url'], 'New entry permalink was not canonicalized.' );
qaf_test_assert( $qaf_test_bearer_verified, 'Authorization header was not verified.' );

$qaf_test_posts[ $first_id ]['post_title']  = 'Judul hasil suntingan administrator';
$qaf_test_posts[ $first_id ]['post_status'] = 'publish';

$second = QAF_Core_Instagram_Gallery::sync( 'test' );
qaf_test_assert( ! is_wp_error( $second ), 'Second synchronization returned an error.' );
qaf_test_assert( 0 === $second['created'], 'Second synchronization created duplicates.' );
qaf_test_assert( 2 === $second['skipped'], 'Existing synchronized entries should be skipped.' );
qaf_test_assert( 2 === count( $qaf_test_posts ), 'Post count changed during deduplication check.' );
qaf_test_assert( 'Judul hasil suntingan administrator' === $qaf_test_posts[ $first_id ]['post_title'], 'Administrator title was overwritten.' );
qaf_test_assert( 'publish' === $qaf_test_posts[ $first_id ]['post_status'], 'Administrator status was overwritten.' );

$qaf_test_options['qaf_instagram_gallery_sync']['token_saved_at'] = gmdate( 'c', time() - 3 * DAY_IN_SECONDS );
$qaf_test_simulate_refresh_race = true;
$third = QAF_Core_Instagram_Gallery::sync( 'test' );
qaf_test_assert( ! is_wp_error( $third ), 'Refresh race synchronization returned an error.' );
qaf_test_assert( 'ADMIN_REPLACEMENT_TOKEN' === $qaf_test_options['qaf_instagram_gallery_sync']['access_token'], 'A concurrently replaced token was resurrected.' );
qaf_test_assert( 'publish' === $qaf_test_options['qaf_instagram_gallery_sync']['post_status'], 'Concurrent administrator settings were rolled back.' );
qaf_test_assert( false === get_option( QAF_Core_Instagram_Gallery::LOCK_KEY, false ), 'Synchronization lock was not released.' );

fwrite( STDOUT, 'Instagram gallery synchronization checks passed.' . PHP_EOL );
