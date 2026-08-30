<?php
/**
 * Dependency-free checks for the fixed-host school map embed.
 */

define( 'ABSPATH', __DIR__ . '/wordpress-stub/' );

$GLOBALS['qaf_test_settings'] = array();

function qaf_core_get_setting( $key, $fallback = '' ) {
	return array_key_exists( $key, $GLOBALS['qaf_test_settings'] )
		? $GLOBALS['qaf_test_settings'][ $key ]
		: $fallback;
}

function get_theme_mod( $key, $fallback = '' ) {
	return $fallback;
}

function __( $text, $domain = '' ) {
	return $text;
}

function esc_attr( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}

function esc_attr_e( $text, $domain = '' ) {
	echo esc_attr( $text );
}

function esc_html( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}

function esc_html_e( $text, $domain = '' ) {
	echo esc_html( $text );
}

function esc_url( $value ) {
	return esc_attr( $value );
}

function sanitize_html_class( $value ) {
	return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $value );
}

require dirname( __DIR__ ) . '/queen-alfalah/inc/template-tags.php';

$assert = static function ( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
};

$assert( '-7.9199' === queen_alfalah_normalize_coordinate( '-7.9199000', -90, 90 ), 'Trailing zeroes must be normalized.' );
$assert( '0' === queen_alfalah_normalize_coordinate( '-0.000', -90, 90 ), 'Negative zero must be normalized.' );
$assert( '' === queen_alfalah_normalize_coordinate( '91', -90, 90 ), 'Out-of-range latitude must be rejected.' );
$assert( '' === queen_alfalah_normalize_coordinate( '1e2', -180, 180 ), 'Scientific notation must be rejected.' );
$assert( '' === queen_alfalah_normalize_coordinate( '1"><script>', -180, 180 ), 'Markup must be rejected.' );

$GLOBALS['qaf_test_settings'] = array(
	'latitude'  => '-7.9199',
	'longitude' => '111.9604',
	'map_url'   => 'https://attacker.example/embed-me',
);

$embed_url = queen_alfalah_map_embed_url();
$assert(
	'https://www.google.com/maps?q=-7.9199%2C111.9604&z=17&output=embed' === $embed_url,
	'Embed URL must be assembled from coordinates on the fixed Google host.'
);
$assert( false === strpos( $embed_url, 'attacker.example' ), 'Editable map URL must never become the iframe source.' );

ob_start();
queen_alfalah_school_map( 'home' );
$map_markup = ob_get_clean();
$assert( false !== strpos( $map_markup, '<iframe' ), 'Valid coordinates must render an iframe.' );
$assert( false !== strpos( $map_markup, 'https://www.google.com/maps?q=-7.9199%2C111.9604' ), 'Rendered iframe must use the fixed Google URL.' );
$assert( false === strpos( $map_markup, 'src="https://attacker.example' ), 'Rendered iframe must not use the editable external URL.' );

$GLOBALS['qaf_test_settings']['latitude'] = 'not-a-coordinate';
$assert( '' === queen_alfalah_map_embed_url(), 'Invalid coordinates must activate the regular-link fallback.' );

$GLOBALS['qaf_test_settings'] = array(
	'latitude'  => '',
	'longitude' => '',
);
$assert( '' === queen_alfalah_school_info( 'latitude' ), 'An explicitly empty plugin latitude must remain empty.' );
$assert( '' === queen_alfalah_school_info( 'longitude' ), 'An explicitly empty plugin longitude must remain empty.' );
$assert( '' === queen_alfalah_map_embed_url(), 'Empty coordinates must disable the embed and activate the regular-link fallback.' );

ob_start();
queen_alfalah_school_map( 'contact' );
$fallback_markup = ob_get_clean();
$assert( false === strpos( $fallback_markup, '<iframe' ), 'Empty coordinates must not render an iframe.' );
$assert( false !== strpos( $fallback_markup, 'class="map-card"' ), 'Empty coordinates must render the regular-link fallback.' );

$GLOBALS['qaf_test_settings'] = array(
	'latitude'  => '-7.9199',
	'longitude' => '',
);
$assert( '' === queen_alfalah_map_embed_url(), 'A partial coordinate pair must not produce an embed URL.' );

fwrite( STDOUT, "PASS: map coordinates are validated and the embed uses a fixed Google host.\n" );
