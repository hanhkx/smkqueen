<?php
/** Dependency-free contract for the November student-work addition. */
define( 'ABSPATH', __DIR__ . '/wordpress-stub/' );

$root     = dirname( __DIR__ ) . '/queen-alfalah-core';
$data     = require $root . '/includes/student-work-update-data.php';
$importer = file_get_contents( $root . '/includes/class-qaf-core-student-work-updates.php' );
$check    = static function ( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
};

$check( '1.0.0' === $data['version'] && 1 === count( $data['items'] ), 'Exactly one versioned student work is expected.' );
$item = $data['items'][0];
foreach ( array( 'source_id', 'title', 'slug', 'status', 'publish_date', 'type', 'image', 'creator', 'class', 'award', 'source_url', 'evidence_url', 'excerpt', 'content' ) as $field ) {
	$check( isset( $item[ $field ] ) && '' !== trim( $item[ $field ] ), "Student-work field {$field} is required." );
}
$check( '' === $item['work_date'], 'Unknown work date must remain empty.' );
$check( false !== stripos( $item['award'], 'hasil juara tidak dicantumkan' ), 'The work must not claim an unverified award.' );
$image = $root . '/assets/images/student-works/' . $item['image'];
$check( is_file( $image ) && filesize( $image ) > 10000, 'The source-owned work image is missing.' );
foreach ( array( 'admin_init', 'add_option( self::LOCK_OPTION', "'trash' === get_post_status", 'has_post_thumbnail', "'_qaf_work_source_url'" ) as $marker ) {
	$check( false !== strpos( $importer, $marker ), "Non-destructive importer marker {$marker} is missing." );
}

fwrite( STDOUT, "PASS: the verified totebag work keeps its unknown work date, source image, and non-destructive import safeguards.\n" );
