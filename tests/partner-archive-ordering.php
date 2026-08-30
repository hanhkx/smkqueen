<?php
/**
 * Dependency-free check for deterministic partner archive pagination.
 */

define( 'ABSPATH', __DIR__ . '/wordpress-stub/' );

$GLOBALS['qaf_test_is_admin'] = false;

function is_admin() {
	return $GLOBALS['qaf_test_is_admin'];
}

class WP_Query {
	public $vars = array();
	private $post_type;
	private $main;

	public function __construct( $post_type, $main = true ) {
		$this->post_type = $post_type;
		$this->main      = $main;
	}

	public function is_main_query() {
		return $this->main;
	}

	public function is_post_type_archive( $post_type ) {
		return $this->post_type === $post_type;
	}

	public function set( $key, $value ) {
		$this->vars[ $key ] = $value;
	}

	public function get( $key ) {
		return isset( $this->vars[ $key ] ) ? $this->vars[ $key ] : null;
	}
}

require dirname( __DIR__ ) . '/queen-alfalah-core/includes/class-qaf-core-post-types.php';

$assert = static function ( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
};

$partner = new WP_Query( 'qaf_partner' );
QAF_Core_Post_Types::prepare_public_archives( $partner );
$assert(
	array(
		'menu_order' => 'ASC',
		'title'      => 'ASC',
		'ID'         => 'ASC',
	) === $partner->get( 'orderby' ),
	'Partner archive must use a deterministic three-part order.'
);

$secondary = new WP_Query( 'qaf_partner', false );
QAF_Core_Post_Types::prepare_public_archives( $secondary );
$assert( null === $secondary->get( 'orderby' ), 'Secondary queries must remain untouched.' );

$GLOBALS['qaf_test_is_admin'] = true;
$admin = new WP_Query( 'qaf_partner' );
QAF_Core_Post_Types::prepare_public_archives( $admin );
$assert( null === $admin->get( 'orderby' ), 'Admin queries must remain untouched.' );

fwrite( STDOUT, "PASS: partner archive ordering is deterministic and scoped to the public main query.\n" );
