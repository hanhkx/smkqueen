<?php
/** Dependency-free contracts for the curated Bursa Kerja package. */

define( 'ABSPATH', __DIR__ . '/wordpress-stub/' );

$plugin_root = dirname( __DIR__ ) . '/queen-alfalah-core';
$data        = require $plugin_root . '/includes/vacancy-data.php';
$image_dir   = realpath( $plugin_root . '/assets/images/vacancies' );

$assert = static function ( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
};

$assert( is_array( $data ), 'Vacancy data must return an array.' );
$assert( isset( $data['version'] ) && '2.0.0' === $data['version'], 'Vacancy dataset version must be 2.0.0.' );
$assert( isset( $data['items'] ) && 10 === count( $data['items'] ), 'Ten curated vacancies must be present.' );
$assert( false !== $image_dir, 'Vacancy image directory must exist.' );

$programs   = array();
$source_ids = array();
$slugs      = array();
$hosts      = array();

foreach ( $data['items'] as $index => $item ) {
	$prefix = "Record {$index}";
	$assert( is_array( $item ), "{$prefix} must be an array." );

	$required = array(
		'source_id', 'slug', 'title', 'company', 'field', 'location', 'employment',
		'education', 'salary', 'verified_date', 'source_name', 'source_url', 'apply_url',
		'image', 'image_alt', 'image_caption', 'image_credit', 'excerpt', 'content',
	);
	foreach ( $required as $field ) {
		$assert( isset( $item[ $field ] ) && is_string( $item[ $field ] ) && '' !== trim( $item[ $field ] ), "{$prefix} field {$field} is required." );
	}

	$assert( preg_match( '/^(?:glints|linkedin):[A-Za-z0-9-]+$/', $item['source_id'] ), "{$prefix} source ID must be stable." );
	$assert( preg_match( '/^[a-z0-9-]+$/', $item['slug'] ), "{$prefix} slug must be portable." );
	$assert( in_array( $item['verified_date'], array( '2026-09-17', '2026-10-07' ), true ), "{$prefix} must expose an approved explicit verification date." );
	$assert( preg_match( '#^https://#', $item['source_url'] ) && preg_match( '#^https://#', $item['apply_url'] ), "{$prefix} source and apply links must use HTTPS." );
	$assert( false !== stripos( $item['education'], 'SMK' ), "{$prefix} must be relevant to SMK graduates." );
	$assert( ! in_array( $item['source_id'], $source_ids, true ), "Duplicate source ID {$item['source_id']}." );
	$assert( ! in_array( $item['slug'], $slugs, true ), "Duplicate slug {$item['slug']}." );
	$assert( false === strpos( $item['image'], '/' ) && false === strpos( $item['image'], '..' ), "{$prefix} image must stay in the bundled directory." );

	$file = realpath( $image_dir . DIRECTORY_SEPARATOR . $item['image'] );
	$assert( false !== $file && is_file( $file ) && filesize( $file ) > 1024, "{$prefix} company logo is missing or empty." );
	$image_info = getimagesize( $file );
	$assert( false !== $image_info && $image_info[0] > 0 && $image_info[1] > 0, "{$prefix} company logo must be a valid raster image." );

	$programs[]   = $item['field'];
	$source_ids[] = $item['source_id'];
	$slugs[]      = $item['slug'];
	$hosts[]      = strtolower( (string) parse_url( $item['source_url'], PHP_URL_HOST ) );
}

foreach ( array( 'DKV', 'TJKT', 'MPLB', 'Layanan Kesehatan' ) as $program ) {
	$assert( in_array( $program, $programs, true ), "Program {$program} must have at least one vacancy." );
}

$assert( count( array_filter( $hosts, static function ( $host ) { return false !== strpos( $host, 'glints.com' ); } ) ) >= 1, 'The batch must include Glints sources.' );
$assert( count( array_filter( $hosts, static function ( $host ) { return false !== strpos( $host, 'linkedin.com' ); } ) ) >= 1, 'The batch must include LinkedIn sources.' );

fwrite( STDOUT, "PASS: ten append-only vacancies cover all four programs with valid logos, structured details, and direct Glints/LinkedIn source links.\n" );
