<?php
/**
 * Dependency-free checks for official social links on post details.
 *
 * Run with: php tests/theme-post-social-links.php
 */

define( 'ABSPATH', __DIR__ . '/' );

$GLOBALS['qaf_test_settings'] = array(
	'short_name'    => 'SMK Queen Test',
	'instagram_url' => 'https://www.instagram.com/smkqueen.test/',
	'facebook_url'  => 'https://www.facebook.com/smkqueen.test/',
	'youtube_url'   => '',
	'tiktok_url'    => '',
);
$GLOBALS['qaf_test_post'] = (object) array(
	'ID'        => 87,
	'post_type' => 'post',
);

function qaf_core_get_setting( $key, $default = '' ) {
	return array_key_exists( $key, $GLOBALS['qaf_test_settings'] ) ? $GLOBALS['qaf_test_settings'][ $key ] : $default;
}

function get_theme_mod( $key, $default = '' ) {
	return $default;
}

function get_post( $post = null ) {
	return is_object( $post ) ? $post : $GLOBALS['qaf_test_post'];
}

function get_post_type_object( $post_type ) {
	$types = array(
		'post'            => (object) array( '_builtin' => true, 'public' => true, 'publicly_queryable' => true ),
		'page'            => (object) array( '_builtin' => true, 'public' => true, 'publicly_queryable' => true ),
		'qaf_achievement' => (object) array( '_builtin' => false, 'public' => true, 'publicly_queryable' => true ),
		'qaf_private'     => (object) array( '_builtin' => false, 'public' => false, 'publicly_queryable' => false ),
	);
	return isset( $types[ $post_type ] ) ? $types[ $post_type ] : null;
}

function apply_filters( $tag, $value ) {
	return $value;
}

function get_the_ID() {
	return (int) $GLOBALS['qaf_test_post']->ID;
}

function __( $text ) {
	return $text;
}

function esc_html__( $text ) {
	return $text;
}

function esc_html_e( $text ) {
	echo esc_html( $text );
}

function esc_attr__( $text ) {
	return $text;
}

function esc_html( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}

function esc_attr( $text ) {
	return esc_html( $text );
}

function esc_url( $url ) {
	return (string) $url;
}

function sanitize_html_class( $class ) {
	return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $class );
}

function absint( $value ) {
	return abs( (int) $value );
}

require dirname( __DIR__ ) . '/queen-alfalah/inc/template-tags.php';

$errors = array();
$assert = static function ( $condition, $message ) use ( &$errors ) {
	if ( ! $condition ) {
		$errors[] = $message;
	}
};

$links = queen_alfalah_social_links();
$assert( array( 'instagram', 'facebook' ) === array_keys( $links ), 'Only configured official networks should be returned.' );

ob_start();
queen_alfalah_post_social_links();
$markup = ob_get_clean();

$assert( false !== strpos( $markup, 'Ikuti Media Sosial Resmi' ), 'The social component needs a visible heading.' );
$assert( false !== strpos( $markup, 'https://www.instagram.com/smkqueen.test/' ), 'Configured Instagram URL is missing.' );
$assert( false !== strpos( $markup, 'https://www.facebook.com/smkqueen.test/' ), 'Configured Facebook URL is missing.' );
$assert( false === strpos( $markup, '>YouTube<' ), 'An empty YouTube setting must not create a button.' );
$assert( false === strpos( $markup, '>TikTok<' ), 'An empty TikTok setting must not create a button.' );
$assert( false !== strpos( $markup, 'target="_blank"' ), 'Official accounts should open in a separate tab.' );
$assert( false !== strpos( $markup, 'rel="external noopener noreferrer"' ), 'External links need opener protections.' );
$assert( false !== strpos( $markup, 'aria-labelledby="entry-social-title-87"' ), 'The component needs an accessible unique label.' );

$assert( true === queen_alfalah_supports_post_social_links(), 'Standard posts should be supported.' );
$assert( true === queen_alfalah_supports_post_social_links( (object) array( 'ID' => 88, 'post_type' => 'qaf_achievement' ) ), 'Public custom post types should be supported.' );
$assert( false === queen_alfalah_supports_post_social_links( (object) array( 'ID' => 89, 'post_type' => 'page' ) ), 'Static pages should remain under their own template controls.' );
$assert( false === queen_alfalah_supports_post_social_links( (object) array( 'ID' => 90, 'post_type' => 'qaf_private' ) ), 'Private custom post types must not be exposed.' );

$single = file_get_contents( dirname( __DIR__ ) . '/queen-alfalah/template-parts/content-single.php' );
$css    = file_get_contents( dirname( __DIR__ ) . '/queen-alfalah/style.css' );
$assert( false !== strpos( $single, 'queen_alfalah_supports_post_social_links()' ), 'The shared single template must guard the component by post type.' );
$assert( false !== strpos( $single, 'queen_alfalah_post_social_links()' ), 'The shared single template must render the component for old and future posts.' );
$assert( false !== strpos( $css, '.entry-social__list' ), 'Responsive social-button layout CSS is missing.' );
$assert( false !== strpos( $css, 'min-height: 2.75rem' ), 'Social buttons need a touch-friendly minimum height.' );
$assert( false !== strpos( $css, '@media (prefers-reduced-motion: reduce)' ), 'Social-button motion needs a reduced-motion fallback.' );

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

fwrite( STDOUT, 'Theme post social-link checks passed.' . PHP_EOL );
