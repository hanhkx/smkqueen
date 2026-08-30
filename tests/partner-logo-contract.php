<?php
/**
 * Dependency-free contract checks for bundled partner image data.
 */

define( 'ABSPATH', __DIR__ . '/wordpress-stub/' );

function esc_html( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
}

$plugin_root = dirname( __DIR__ ) . '/queen-alfalah-core';
$data        = require $plugin_root . '/includes/partner-data.php';
$base_dir    = realpath( $plugin_root . '/assets/images/partners' );

$assert = static function ( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
};

$assert( is_array( $data ), 'Partner data must return an array.' );
$assert( isset( $data['image_version'] ) && preg_match( '/^\d+\.\d+\.\d+$/', $data['image_version'] ), 'Image version must be semantic.' );
$assert( isset( $data['images'] ) && is_array( $data['images'] ), 'Partner image records must be present.' );
$assert( 15 === count( $data['images'] ), 'Exactly 15 unique partner identities are expected.' );
$assert( false !== $base_dir, 'Partner image directory must exist.' );

$expected = array(
	'asterix-comp',
	'beneficia-tech',
	'candradimuka-digital',
	'cv-besar-anugrah-djaya',
	'cv-nusantara-media-mandiri',
	'fa-cinema',
	'jtv-kediri',
	'lp3i-college-kediri',
	'ourweb',
	'pt-alfiz',
	'pt-jwb',
	'rs-bhayangkara-kediri',
	'rsu-arga-husada',
	'terra-computer-system-kediri',
	'uptd-puskesmas-mojo',
);

$expected_live_targets = array(
	'asterix-comp',
	'beneficia-tech',
	'candradimuka-digital',
	'cv-besar-anugrah-djaya',
	'cv-nusantara-media-mandiri',
	'fa-cinema',
	'fa-cinema-id',
	'jtv',
	'jtv-kediri',
	'lp3i-college-kediri',
	'ourweb',
	'pt-alfiz',
	'pt-jwb',
	'rs-bhayangkara-kediri',
	'rsu-arga-husada',
	'rumah-sakit-arga-husada',
	'terra-computer-system-kediri',
	'uptd-puskesmas-mojo',
);

$slugs   = array();
$images  = array();
$targets = array();
foreach ( $data['images'] as $index => $record ) {
	$prefix = "Record {$index}";
	$assert( is_array( $record ), "{$prefix} must be an array." );
	foreach ( array( 'slug', 'image', 'title', 'alt', 'caption', 'credit' ) as $field ) {
		$assert( isset( $record[ $field ] ) && is_string( $record[ $field ] ) && '' !== trim( $record[ $field ] ), "{$prefix} field {$field} is required." );
	}

	$assert( preg_match( '/^[a-z0-9-]+$/', $record['slug'] ), "{$prefix} slug is not portable." );
	$assert( preg_match( '/^[a-z0-9-]+\.webp$/', $record['image'] ), "{$prefix} image path must be one WebP filename." );
	$assert( ! in_array( $record['slug'], $slugs, true ), "Duplicate slug {$record['slug']}." );
	$assert( ! in_array( $record['image'], $images, true ), "Duplicate image marker {$record['image']}." );
	$slugs[]  = $record['slug'];
	$images[] = $record['image'];
	$targets[] = $record['slug'];

	$source_url = isset( $record['source_url'] ) ? $record['source_url'] : '';
	$assert( '' === $source_url || 0 === strpos( $source_url, 'https://' ), "{$prefix} source URL must be HTTPS or empty." );

	$aliases = isset( $record['aliases'] ) ? $record['aliases'] : array();
	$assert( is_array( $aliases ), "{$prefix} aliases must be an array." );
	$assert( count( $aliases ) === count( array_unique( $aliases ) ), "{$prefix} aliases must be unique." );
	foreach ( $aliases as $alias ) {
		$assert( is_string( $alias ) && preg_match( '/^[a-z0-9-]+$/', $alias ), "{$prefix} alias is not portable." );
		$assert( $alias !== $record['slug'], "{$prefix} must not repeat its canonical slug as an alias." );
		$targets[] = $alias;
	}

	$file = realpath( $base_dir . DIRECTORY_SEPARATOR . $record['image'] );
	$assert( false !== $file && is_file( $file ) && is_readable( $file ), "{$prefix} bundled image is missing." );
	$assert( 0 === strpos( str_replace( '\\', '/', $file ), rtrim( str_replace( '\\', '/', $base_dir ), '/' ) . '/' ), "{$prefix} image escapes its directory." );
	$assert( filesize( $file ) > 1024, "{$prefix} image is unexpectedly small." );
	$dimensions = getimagesize( $file );
	$assert( is_array( $dimensions ) && 1200 === $dimensions[0] && 800 === $dimensions[1], "{$prefix} image must be 1200x800." );
}

sort( $slugs );
sort( $expected );
$assert( $expected === $slugs, 'Partner image slugs do not match the expected directory.' );

sort( $targets );
sort( $expected_live_targets );
$assert( $expected_live_targets === $targets, 'Canonical and alias records must cover all 18 live WordPress partner slugs.' );

fwrite( STDOUT, "PASS: 15 partner identities cover all 18 live slugs and use valid 1200x800 images.\n" );
