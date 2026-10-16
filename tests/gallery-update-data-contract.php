<?php
/** Dependency-free contract for verified official-channel gallery updates. */

define( 'ABSPATH', __DIR__ . '/wordpress-stub/' );

$plugin_root = dirname( __DIR__ ) . '/queen-alfalah-core';
$data        = require $plugin_root . '/includes/gallery-update-data.php';
$image_dir   = realpath( $plugin_root . '/assets/images/gallery' );
$importer    = file_get_contents( $plugin_root . '/includes/class-qaf-core-gallery-updates.php' );

$assert = static function ( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
};

$assert( is_array( $data ), 'Gallery update data must return an array.' );
$assert( '1.1.1' === $data['version'], 'Gallery update data version must be 1.1.1.' );
$assert( isset( $data['items'] ) && 2 === count( $data['items'] ), 'The verified YouTube and October Instagram gallery items are expected.' );
$assert( false !== $image_dir, 'Gallery image directory must exist.' );

$items_by_source = array();
foreach ( $data['items'] as $item ) {
	foreach ( array( 'source_id', 'title', 'slug', 'status', 'date', 'source', 'media_type', 'source_url', 'image', 'image_alt', 'image_caption', 'excerpt', 'content', 'terms' ) as $field ) {
		$assert( array_key_exists( $field, $item ), "Gallery field {$field} is required." );
	}
	$items_by_source[ $item['source_id'] ] = $item;
	$image = realpath( $image_dir . DIRECTORY_SEPARATOR . $item['image'] );
	$assert( false !== $image && is_file( $image ) && is_readable( $image ), "Bundled gallery image {$item['image']} is missing." );
	$size = getimagesize( $image );
	$assert( is_array( $size ) && $size[0] >= 480 && $size[1] >= 270, "Bundled gallery image {$item['image']} is too small." );
}

$item = $items_by_source['youtube:wMWFsuDjzhQ:design-brief-dkv'];
$assert( 'youtube:wMWFsuDjzhQ:design-brief-dkv' === $item['source_id'], 'The stable YouTube source ID must match the official video.' );
$assert( 'https://www.youtube.com/watch?v=wMWFsuDjzhQ' === $item['source_url'], 'The source URL must point to the official school-channel video.' );
$assert( '2026-09-14' === $item['date'], 'The album date must use the verified YouTube publication date.' );
$assert( 'youtube' === $item['source'] && 'video' === $item['media_type'], 'The item must render as a YouTube video.' );
$assert( 'publish' === $item['status'], 'The verified item is approved for publication.' );

$october = $items_by_source['instagram:DeihO7LJ7yE:yuk-happy-di-sekolah'];
$assert( 'https://www.instagram.com/reel/DeihO7LJ7yE/' === $october['source_url'], 'The October gallery source must use the official Reel URL.' );
$assert( '2026-10-16' === $october['date'], 'The October album date must use the verified Instagram upload date.' );
$assert( 'instagram' === $october['source'] && 'video' === $october['media_type'], 'The October item must render as an Instagram video.' );
$assert( 'publish' === $october['status'], 'The verified October gallery item is approved for publication.' );

$assert( false !== $importer, 'Gallery importer source must be readable.' );
$assert( false !== strpos( $importer, 'admin_init' ), 'Gallery updates must run only from an administrator request.' );
$assert( false !== strpos( $importer, 'add_option( self::LOCK_OPTION' ), 'Gallery importer must claim an atomic lock.' );
$assert( false !== strpos( $importer, "'_qaf_video_url'" ), 'Gallery importer must deduplicate by canonical video URL.' );
$assert( false !== strpos( $importer, "'post_status'    => " . '$statuses' ), 'Gallery importer must search every post status, including Trash.' );
$assert( false !== strpos( $importer, 'set_post_thumbnail' ), 'Gallery importer must set the verified bundled thumbnail.' );
$assert( false !== strpos( $importer, 'publish_due_record' ), 'Same-day verified gallery records must not remain scheduled because of server-clock differences.' );

fwrite( STDOUT, "PASS: verified YouTube and October Instagram gallery updates are portable, dated, illustrated, and non-destructive.\n" );
