<?php
/**
 * Dependency-free behavior checks for the partner-logo importer.
 */

$stub_root = sys_get_temp_dir() . '/qaf-partner-logo-wordpress-stub/';
foreach ( array( 'file.php', 'image.php', 'media.php' ) as $stub_file ) {
	$directory = $stub_root . 'wp-admin/includes/';
	if ( ! is_dir( $directory ) ) {
		mkdir( $directory, 0777, true );
	}
	if ( ! file_exists( $directory . $stub_file ) ) {
		file_put_contents( $directory . $stub_file, "<?php\n" );
	}
}

define( 'ABSPATH', $stub_root );
define( 'QAF_CORE_PATH', dirname( __DIR__ ) . '/queen-alfalah-core/' );
define( 'OBJECT', 'OBJECT' );

class WP_Post {
	public $ID;
	public $post_type;
	public $post_status;
	public $post_name;
	public $post_title;
	public $post_excerpt;
	public $post_content;
	public $post_mime_type;

	public function __construct( $values ) {
		foreach ( $values as $key => $value ) {
			$this->{$key} = $value;
		}
	}
}

class WP_Error {
	private $message;

	public function __construct( $code, $message ) {
		$this->message = $message;
	}

	public function get_error_message() {
		return $this->message;
	}
}

class QAF_Test_WPDB {
	public $options = 'wp_options';

	public function delete( $table, $where, $format = null ) {
		$key = isset( $where['option_name'] ) ? $where['option_name'] : '';
		if (
			$this->options !== $table
			|| ! array_key_exists( $key, $GLOBALS['qaf_test_options'] )
			|| maybe_serialize( $GLOBALS['qaf_test_options'][ $key ] ) !== $where['option_value']
		) {
			return 0;
		}

		unset( $GLOBALS['qaf_test_options'][ $key ] );
		return 1;
	}
}

$GLOBALS['qaf_test_posts']          = array();
$GLOBALS['qaf_test_meta']           = array();
$GLOBALS['qaf_test_thumbnails']     = array();
$GLOBALS['qaf_test_sideload_calls'] = 0;
$GLOBALS['qaf_test_fail_thumbnail'] = array();
$GLOBALS['qaf_test_fail_meta']      = array();
$GLOBALS['qaf_test_options']        = array();
$GLOBALS['qaf_test_uuid_counter']   = 0;
$GLOBALS['wpdb']                    = new QAF_Test_WPDB();

function qaf_test_add_post( $id, $post_type, $status, $slug, $title = '', $mime = '' ) {
	$GLOBALS['qaf_test_posts'][ $id ] = new WP_Post(
		array(
			'ID'             => $id,
			'post_type'      => $post_type,
			'post_status'    => $status,
			'post_name'      => $slug,
			'post_title'     => $title,
			'post_excerpt'   => '',
			'post_content'   => '',
			'post_mime_type' => $mime,
		)
	);
}

function add_action() {}
function current_user_can() { return true; }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function absint( $value ) { return abs( (int) $value ); }
function wp_slash( $value ) { return $value; }
function wp_kses_post( $value ) { return (string) $value; }
function esc_html( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' ); }
function sanitize_text_field( $value ) { return trim( preg_replace( '/\s+/', ' ', strip_tags( is_scalar( $value ) ? (string) $value : '' ) ) ); }
function sanitize_textarea_field( $value ) { return trim( strip_tags( is_scalar( $value ) ? (string) $value : '' ) ); }
function sanitize_key( $value ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', (string) $value ) ); }
function sanitize_title( $value ) {
	$value = strtolower( strip_tags( is_scalar( $value ) ? (string) $value : '' ) );
	$value = preg_replace( '/[^a-z0-9]+/', '-', $value );
	return trim( $value, '-' );
}
function sanitize_file_name( $value ) { return preg_replace( '/[^A-Za-z0-9._-]/', '-', basename( (string) $value ) ); }
function wp_generate_uuid4() { ++$GLOBALS['qaf_test_uuid_counter']; return '00000000-0000-4000-8000-' . str_pad( (string) $GLOBALS['qaf_test_uuid_counter'], 12, '0', STR_PAD_LEFT ); }
function esc_url_raw( $value, $protocols = null ) {
	$value  = trim( is_scalar( $value ) ? (string) $value : '' );
	$scheme = strtolower( (string) parse_url( $value, PHP_URL_SCHEME ) );
	if ( '' === $value || ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
		return '';
	}
	if ( is_array( $protocols ) && ! in_array( $scheme, $protocols, true ) ) {
		return '';
	}
	return $value;
}
function wp_normalize_path( $value ) { return str_replace( '\\', '/', (string) $value ); }
function maybe_serialize( $value ) { return is_array( $value ) || is_object( $value ) ? serialize( $value ) : $value; }
function wp_cache_delete() { return true; }
function get_post_stati() { return array( 'publish' => 'publish', 'draft' => 'draft', 'trash' => 'trash', 'inherit' => 'inherit' ); }
function get_post( $id ) { return isset( $GLOBALS['qaf_test_posts'][ $id ] ) ? $GLOBALS['qaf_test_posts'][ $id ] : null; }
function get_post_status( $id ) { $post = get_post( $id ); return $post ? $post->post_status : false; }
function get_post_mime_type( $id ) { $post = get_post( $id ); return $post ? $post->post_mime_type : false; }
function get_the_title( $id ) { $post = get_post( $id ); return $post ? $post->post_title : ''; }
function get_page_by_path( $slug, $output, $post_type ) {
	foreach ( $GLOBALS['qaf_test_posts'] as $post ) {
		if ( $post_type === $post->post_type && $slug === $post->post_name ) {
			return $post;
		}
	}
	return null;
}
function get_posts( $args ) {
	$matches = array();
	foreach ( $GLOBALS['qaf_test_posts'] as $id => $post ) {
		if ( isset( $args['post_type'] ) && $args['post_type'] !== $post->post_type ) {
			continue;
		}
		if ( isset( $args['post_status'] ) ) {
			$statuses = is_array( $args['post_status'] ) ? $args['post_status'] : array( $args['post_status'] );
			if ( ! in_array( $post->post_status, $statuses, true ) ) {
				continue;
			}
		}
		if ( isset( $args['meta_key'] ) ) {
			$current = get_post_meta( $id, $args['meta_key'], true );
			if ( (string) $current !== (string) $args['meta_value'] ) {
				continue;
			}
		}
		$matches[] = 'ids' === $args['fields'] ? (int) $id : $post;
		if ( isset( $args['posts_per_page'] ) && $args['posts_per_page'] > 0 && count( $matches ) >= $args['posts_per_page'] ) {
			break;
		}
	}
	return $matches;
}
function metadata_exists( $type, $id, $key ) { return isset( $GLOBALS['qaf_test_meta'][ $id ] ) && array_key_exists( $key, $GLOBALS['qaf_test_meta'][ $id ] ); }
function get_post_meta( $id, $key, $single = false ) { return metadata_exists( 'post', $id, $key ) ? $GLOBALS['qaf_test_meta'][ $id ][ $key ] : ''; }
function update_post_meta( $id, $key, $value ) {
	if ( ! empty( $GLOBALS['qaf_test_fail_meta'][ $id ][ $key ] ) ) {
		--$GLOBALS['qaf_test_fail_meta'][ $id ][ $key ];
		return false;
	}
	$GLOBALS['qaf_test_meta'][ $id ][ $key ] = $value;
	return true;
}
function has_post_thumbnail( $id ) { return isset( $GLOBALS['qaf_test_thumbnails'][ $id ] ) && 0 < $GLOBALS['qaf_test_thumbnails'][ $id ]; }
function set_post_thumbnail( $id, $attachment_id ) {
	if ( ! empty( $GLOBALS['qaf_test_fail_thumbnail'][ $id ] ) ) {
		--$GLOBALS['qaf_test_fail_thumbnail'][ $id ];
		return false;
	}
	if ( ! get_post( $attachment_id ) ) {
		return false;
	}
	$GLOBALS['qaf_test_thumbnails'][ $id ] = (int) $attachment_id;
	return true;
}
function wp_update_post( $updates, $wp_error = false ) {
	$id = isset( $updates['ID'] ) ? (int) $updates['ID'] : 0;
	if ( ! get_post( $id ) ) {
		return new WP_Error( 'missing', 'Post missing.' );
	}
	foreach ( $updates as $key => $value ) {
		if ( 'ID' !== $key ) {
			$GLOBALS['qaf_test_posts'][ $id ]->{$key} = $value;
		}
	}
	return $id;
}
function wp_delete_attachment( $id, $force_delete = false ) {
	unset( $GLOBALS['qaf_test_posts'][ $id ], $GLOBALS['qaf_test_meta'][ $id ], $GLOBALS['qaf_test_thumbnails'][ $id ] );
	return true;
}
function wp_check_filetype_and_ext( $file, $filename ) {
	$extension = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
	$types     = array( 'webp' => 'image/webp', 'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg' );
	return array( 'ext' => $extension, 'type' => isset( $types[ $extension ] ) ? $types[ $extension ] : '' );
}
function wp_tempnam( $filename ) { return tempnam( sys_get_temp_dir(), 'qaf-logo-' ); }
function wp_delete_file( $file ) { return file_exists( $file ) ? unlink( $file ) : true; }
function media_handle_sideload( $file_array, $parent_id, $description, $post_data ) {
	++$GLOBALS['qaf_test_sideload_calls'];
	$id = 900 + $GLOBALS['qaf_test_sideload_calls'];
	qaf_test_add_post( $id, 'attachment', 'inherit', sanitize_title( $post_data['post_title'] ), $post_data['post_title'], 'image/webp' );
	$GLOBALS['qaf_test_posts'][ $id ]->post_excerpt = $post_data['post_excerpt'];
	$GLOBALS['qaf_test_posts'][ $id ]->post_content = $post_data['post_content'];
	if ( file_exists( $file_array['tmp_name'] ) ) {
		unlink( $file_array['tmp_name'] );
	}
	return $id;
}
function get_option( $key, $default = false ) { return array_key_exists( $key, $GLOBALS['qaf_test_options'] ) ? $GLOBALS['qaf_test_options'][ $key ] : $default; }
function add_option( $key, $value = '', $deprecated = '', $autoload = null ) {
	if ( array_key_exists( $key, $GLOBALS['qaf_test_options'] ) ) {
		return false;
	}
	$GLOBALS['qaf_test_options'][ $key ] = $value;
	return true;
}
function update_option( $key, $value = '', $autoload = null ) { $GLOBALS['qaf_test_options'][ $key ] = $value; return true; }
function delete_option( $key ) { unset( $GLOBALS['qaf_test_options'][ $key ] ); return true; }
function taxonomy_exists() { return false; }
function wp_set_object_terms() { return array(); }

require QAF_CORE_PATH . 'includes/class-qaf-core-content-catalog.php';

$assert = static function ( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
};

$record = static function ( $slug, $image, $aliases = array(), $seed_key = '' ) {
	return array(
		'seed_key'  => $seed_key,
		'slug'      => $slug,
		'aliases'   => $aliases,
		'image'     => $image,
		'title'     => 'Logo ' . $slug,
		'alt'       => 'Alt ' . $slug,
		'caption'   => 'Caption ' . $slug,
		'credit'    => 'Credit ' . $slug,
		'source_url' => 'https://example.com/' . $slug,
	);
};

$method = new ReflectionMethod( 'QAF_Core_Content_Catalog', 'sync_partner_images' );
$method->setAccessible( true );

// Canonical and legacy aliases share one import; Trash and admin choices win.
qaf_test_add_post( 10, 'qaf_partner', 'publish', 'jtv-kediri', 'JTV Kediri' );
qaf_test_add_post( 11, 'qaf_partner', 'publish', 'jtv', 'JTV legacy' );
qaf_test_add_post( 12, 'qaf_partner', 'publish', 'jtv-admin', 'JTV admin image' );
qaf_test_add_post( 13, 'qaf_partner', 'trash', 'jtv-trash', 'JTV trash' );
qaf_test_add_post( 777, 'attachment', 'inherit', 'editor-image', 'Editor image', 'image/jpeg' );
update_post_meta( 10, '_qaf_partner_seed_key', 'partner:jtv-kediri' );
$GLOBALS['qaf_test_thumbnails'][12] = 777;

$errors = $method->invoke( null, array( $record( 'jtv-kediri', 'jtv-kediri.webp', array( 'jtv', 'jtv-admin', 'jtv-trash' ), 'partner:jtv-kediri' ) ) );
$assert( empty( $errors ), 'Initial canonical/alias import should succeed.' );
$assert( 1 === $GLOBALS['qaf_test_sideload_calls'], 'One attachment should be sideloaded.' );
$assert( $GLOBALS['qaf_test_thumbnails'][10] === $GLOBALS['qaf_test_thumbnails'][11], 'Canonical and legacy alias should share one attachment.' );
$assert( 777 === $GLOBALS['qaf_test_thumbnails'][12], 'An administrator thumbnail must be preserved.' );
$assert( ! isset( $GLOBALS['qaf_test_thumbnails'][13] ), 'A Trash target must remain untouched.' );

$first_attachment = $GLOBALS['qaf_test_thumbnails'][10];
$assert( 'jtv-kediri.webp' === get_post_meta( $first_attachment, '_qaf_bundled_partner_logo', true ), 'Attachment marker must be stored.' );
$assert( 'Alt jtv-kediri' === get_post_meta( $first_attachment, '_wp_attachment_image_alt', true ), 'Alt text must be stored.' );

$errors = $method->invoke( null, array( $record( 'jtv-kediri', 'jtv-kediri.webp', array( 'jtv' ), 'partner:jtv-kediri' ) ) );
$assert( empty( $errors ) && 1 === $GLOBALS['qaf_test_sideload_calls'], 'A second pass must be idempotent.' );

// A marked attachment is reused and its editor-owned details are preserved.
qaf_test_add_post( 20, 'qaf_partner', 'publish', 'ourweb', 'OurWeb' );
qaf_test_add_post( 950, 'attachment', 'inherit', 'ourweb-editor', 'Editor title', 'image/webp' );
update_post_meta( 950, '_qaf_bundled_partner_logo', 'ourweb.webp' );
update_post_meta( 950, '_wp_attachment_image_alt', 'Editor alt' );
update_post_meta( 950, '_qaf_partner_logo_source_url', 'https://editor.example/source' );
$errors = $method->invoke( null, array( $record( 'ourweb', 'ourweb.webp' ) ) );
$assert( empty( $errors ), 'Marked attachment reuse should succeed.' );
$assert( 950 === $GLOBALS['qaf_test_thumbnails'][20], 'Existing marked attachment should be reused.' );
$assert( 1 === $GLOBALS['qaf_test_sideload_calls'], 'Reuse must not sideload a duplicate.' );
$assert( 'Editor title' === get_post( 950 )->post_title, 'Editor attachment title must be preserved.' );
$assert( 'Editor alt' === get_post_meta( 950, '_wp_attachment_image_alt', true ), 'Editor alt text must be preserved.' );
$assert( 'https://editor.example/source' === get_post_meta( 950, '_qaf_partner_logo_source_url', true ), 'Editor source URL must be preserved.' );
$assert( 'Caption ourweb' === get_post( 950 )->post_excerpt, 'An empty caption may be enriched.' );

// An explicit alias can be the only existing target.
qaf_test_add_post( 30, 'qaf_partner', 'publish', 'legacy-only', 'Legacy only' );
$errors = $method->invoke( null, array( $record( 'missing-canonical', 'candradimuka-digital.webp', array( 'legacy-only' ) ) ) );
$assert( empty( $errors ) && has_post_thumbnail( 30 ), 'An alias-only post should receive its image.' );
$assert( 2 === $GLOBALS['qaf_test_sideload_calls'], 'Alias-only import should create one new attachment.' );

// Missing/traversal files fail safely without changing the target.
qaf_test_add_post( 40, 'qaf_partner', 'publish', 'missing-logo', 'Missing logo' );
$errors = $method->invoke( null, array( $record( 'missing-logo', 'not-present.webp' ) ) );
$assert( 1 === count( $errors ) && ! has_post_thumbnail( 40 ), 'Missing files must fail without assigning a thumbnail.' );
$errors = $method->invoke( null, array( $record( 'missing-logo', '../jtv-kediri.webp' ) ) );
$assert( 1 === count( $errors ) && ! has_post_thumbnail( 40 ), 'Directory traversal must be rejected.' );

// A failed thumbnail assignment leaves the attachment reusable on retry.
qaf_test_add_post( 50, 'qaf_partner', 'publish', 'fail-target', 'Fail target' );
$GLOBALS['qaf_test_fail_thumbnail'][50] = 1;
$errors = $method->invoke( null, array( $record( 'fail-target', 'pt-alfiz.webp' ) ) );
$assert( 1 === count( $errors ) && ! has_post_thumbnail( 50 ), 'Assignment failure must be reported.' );
$assert( 3 === $GLOBALS['qaf_test_sideload_calls'], 'Failed assignment should still import only one attachment.' );
$errors = $method->invoke( null, array( $record( 'fail-target', 'pt-alfiz.webp' ) ) );
$assert( empty( $errors ) && has_post_thumbnail( 50 ), 'Retry should reuse the attachment and succeed.' );
$assert( 3 === $GLOBALS['qaf_test_sideload_calls'], 'Retry must not duplicate the Media Library attachment.' );

// Required attachment metadata failures remain retryable and do not leave an orphan marker.
qaf_test_add_post( 60, 'qaf_partner', 'publish', 'beneficia-tech', 'Beneficia Tech' );
$GLOBALS['qaf_test_fail_meta'][904]['_wp_attachment_image_alt'] = 1;
$errors = $method->invoke( null, array( $record( 'beneficia-tech', 'beneficia-tech.webp' ) ) );
$assert( 1 === count( $errors ) && ! has_post_thumbnail( 60 ), 'A required metadata failure must block thumbnail assignment.' );
$assert( ! get_post( 904 ), 'A failed new attachment must be removed so the retry cannot create a marked orphan.' );
$errors = $method->invoke( null, array( $record( 'beneficia-tech', 'beneficia-tech.webp' ) ) );
$assert( empty( $errors ) && has_post_thumbnail( 60 ), 'A metadata failure should succeed on the next pass.' );
$assert( 5 === $GLOBALS['qaf_test_sideload_calls'], 'The retry should create one clean replacement attachment.' );

// A live dataset lock blocks a concurrent pass; a stale lock is recovered safely.
$upgrade = new ReflectionMethod( 'QAF_Core_Content_Catalog', 'upgrade_partner_images' );
$upgrade->setAccessible( true );
$GLOBALS['qaf_test_options']['qaf_partner_catalog_image_lock'] = array(
	'token'       => 'another-request',
	'acquired_at' => time(),
);
$upgrade->invoke( null );
$assert( '0.0.0' === get_option( 'qaf_partner_catalog_image_version', '0.0.0' ), 'A live lock must defer the dataset upgrade.' );
$assert( 5 === $GLOBALS['qaf_test_sideload_calls'], 'A deferred concurrent pass must not sideload media.' );
$assert( 'another-request' === get_option( 'qaf_partner_catalog_image_lock' )['token'], 'A request must not release a lock it does not own.' );

$GLOBALS['qaf_test_options']['qaf_partner_catalog_image_lock']['acquired_at'] = time() - 901;
$upgrade->invoke( null );
$assert( '1.0.0' === get_option( 'qaf_partner_catalog_image_version', '0.0.0' ), 'A stale lock should be recovered and the upgrade completed.' );
$assert( false === get_option( 'qaf_partner_catalog_image_lock', false ), 'The request must release its own lock after completion.' );
$assert( 5 === $GLOBALS['qaf_test_sideload_calls'], 'Lock recovery must preserve importer idempotency.' );

// A timed-out request must not delete a replacement lock acquired by a new owner.
$release = new ReflectionMethod( 'QAF_Core_Content_Catalog', 'release_partner_image_lock' );
$release->setAccessible( true );
$expired = array( 'token' => 'expired-owner', 'acquired_at' => time() - 901 );
$replacement = array( 'token' => 'replacement-owner', 'acquired_at' => time() );
$GLOBALS['qaf_test_options']['qaf_partner_catalog_image_lock'] = $replacement;
$release->invoke( null, $expired );
$assert( $replacement === get_option( 'qaf_partner_catalog_image_lock' ), 'Conditional release must preserve a replacement owner lock.' );
delete_option( 'qaf_partner_catalog_image_lock' );

fwrite( STDOUT, "PASS: partner logo importer is non-destructive, metadata-safe, idempotent, and concurrency-locked.\n" );
