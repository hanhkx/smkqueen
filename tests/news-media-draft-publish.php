<?php
/**
 * Dependency-free behavior checks for publishing one exact matching draft.
 */

define( 'ABSPATH', __DIR__ . '/wordpress-stub/' );

class WP_Post {
	public $ID = 0;
	public $post_type = 'post';
	public $post_status = 'draft';
	public $post_title = '';
	public $post_excerpt = '';
	public $post_content = '';
}

class WP_Error {
	private $message;
	public function __construct( $code, $message ) { unset( $code ); $this->message = $message; }
	public function get_error_message() { return $this->message; }
}

$GLOBALS['qaf_test_post'] = new WP_Post();
$GLOBALS['qaf_test_post']->ID = 604;
$GLOBALS['qaf_test_update'] = array();
$GLOBALS['qaf_test_terms'] = array();

function get_post( $post_id ) { unset( $post_id ); return $GLOBALS['qaf_test_post']; }
function get_gmt_from_date( $date ) { return $date; }
function wp_slash( $value ) { return $value; }
function wp_update_post( $update, $wp_error = false ) { unset( $wp_error ); $GLOBALS['qaf_test_update'] = $update; return (int) $update['ID']; }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function term_exists( $term, $taxonomy ) { unset( $term, $taxonomy ); return array( 'term_id' => 7 ); }
function wp_insert_term( $term, $taxonomy ) { unset( $term, $taxonomy ); return array( 'term_id' => 7 ); }
function wp_set_post_terms( $post_id, $terms, $taxonomy, $append = false ) { $GLOBALS['qaf_test_terms'][] = compact( 'post_id', 'terms', 'taxonomy', 'append' ); return array( 7 ); }

require dirname( __DIR__ ) . '/queen-alfalah-core/includes/class-qaf-core-news-media.php';

$assert = static function ( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
};

$method = new ReflectionMethod( 'QAF_Core_News_Media', 'publish_matching_draft' );
$method->setAccessible( true );
$record = array(
	'post' => array(
		'title' => 'Judul Terverifikasi',
		'excerpt' => 'Ringkasan terverifikasi.',
		'content' => '<p>Narasi terverifikasi.</p>',
		'date' => '2026-10-05 12:00:00',
		'category' => 'Berita',
		'tags' => array( 'Timnas Indonesia', 'sportivitas' ),
	),
);

$GLOBALS['qaf_test_post']->post_title = 'Judul Editor';
$GLOBALS['qaf_test_post']->post_excerpt = 'Ringkasan editor.';
$GLOBALS['qaf_test_post']->post_content = '<p>Narasi editor.</p>';
$result = $method->invoke( null, 604, $record );
$assert( 604 === $result, 'An exact draft must be published.' );
$assert( 'publish' === $GLOBALS['qaf_test_update']['post_status'], 'The matching item must become published.' );
$assert( '2026-10-05 12:00:00' === $GLOBALS['qaf_test_update']['post_date'], 'The verified source date must become the publication date.' );
$assert( ! isset( $GLOBALS['qaf_test_update']['post_title'] ), 'An editor title must not be replaced.' );
$assert( ! isset( $GLOBALS['qaf_test_update']['post_excerpt'] ), 'An editor excerpt must not be replaced.' );
$assert( ! isset( $GLOBALS['qaf_test_update']['post_content'] ), 'Editor content must not be replaced.' );
$assert( 2 === count( $GLOBALS['qaf_test_terms'] ), 'Verified category and tags must be appended.' );

$GLOBALS['qaf_test_post']->post_title = '';
$GLOBALS['qaf_test_post']->post_excerpt = '';
$GLOBALS['qaf_test_post']->post_content = '';
$GLOBALS['qaf_test_update'] = array();
$method->invoke( null, 604, $record );
$assert( 'Judul Terverifikasi' === $GLOBALS['qaf_test_update']['post_title'], 'A missing title must be filled.' );
$assert( 'Ringkasan terverifikasi.' === $GLOBALS['qaf_test_update']['post_excerpt'], 'A missing excerpt must be filled.' );
$assert( '<p>Narasi terverifikasi.</p>' === $GLOBALS['qaf_test_update']['post_content'], 'Missing content must be filled.' );

$GLOBALS['qaf_test_post']->post_status = 'publish';
$assert( is_wp_error( $method->invoke( null, 604, $record ) ), 'An already published item must not pass the draft-only updater.' );

fwrite( STDOUT, "PASS: exact matching drafts publish without replacing editor copy, while empty fields receive verified content.\n" );
