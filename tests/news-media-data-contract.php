<?php
/**
 * Dependency-free contracts for the verified school-news media dataset.
 *
 * Run with: php tests/news-media-data-contract.php
 */

define( 'ABSPATH', __DIR__ . '/wordpress-stub/' );

$plugin_root = dirname( __DIR__ ) . '/queen-alfalah-core';
$data        = require $plugin_root . '/includes/news-media-data.php';
$image_dir   = realpath( $plugin_root . '/assets/images/news' );

$assert = static function ( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
};

$expected_legacy_sources = array(
	'instagram:Db2CHFFiWrI',
	'instagram:Dbf2FQ6CcIe',
	'instagram:Dbfm6pticAn',
	'instagram:DbkHxoZCSMQ',
	'instagram:Db8WxS0idWH',
	'instagram:DcIU_kwCZ1A',
	'instagram:DcPzkO8iR0g',
	'instagram:DcVPSxuCQ8m',
);

$expected_september = array(
	'instagram:Dcz45RVCd7e' => array(
		'source_url' => 'https://www.instagram.com/p/Dcz45RVCd7e/',
		'post_slug'  => 'semarak-hut-ri-ke-81-putri-smk-queen-al-falah-kebersamaan-dan-keceriaan',
		'image'      => 'semarak-hut-ri-81-putri.jpg',
		'title'      => 'Semarak HUT RI ke-81 Putri: Kebersamaan dan Keceriaan',
	),
	'instagram:Dcz4TJbiZYL' => array(
		'source_url' => 'https://www.instagram.com/p/Dcz4TJbiZYL/',
		'post_slug'  => 'semarak-hut-ri-ke-81-putra-smk-queen-al-falah-satu-semangat-satu-kebersamaan',
		'image'      => 'semarak-hut-ri-81-putra.jpg',
		'title'      => 'Semarak HUT RI ke-81 Putra: Satu Semangat, Satu Kebersamaan',
	),
	'instagram:DdB3xxRCS-r' => array(
		'source_url' => 'https://www.instagram.com/p/DdB3xxRCS-r/',
		'post_slug'  => 'siswa-smk-queen-al-falah-ikuti-kompetisi-pembawa-berita-jtv-kediri',
		'image'      => 'kompetisi-pembawa-berita-jtv-kediri.jpg',
		'title'      => 'Kompetisi Pembawa Berita JTV Kediri',
	),
	'instagram:DdSks0NiW_C' => array(
		'source_url' => 'https://www.instagram.com/p/DdSks0NiW_C/',
		'post_slug'  => 'peringatan-maulid-nabi-muhammad-saw-1448-h-smk-queen-al-falah',
		'image'      => 'maulid-nabi-1448-h.jpg',
		'title'      => 'Peringatan Maulid Nabi Muhammad SAW 1448 H',
	),
);

$assert( is_array( $data ), 'News-media data must return an array.' );
$assert( isset( $data['version'] ) && '1.5.1' === $data['version'], 'News-media data version must be 1.5.1.' );
$assert( isset( $data['items'] ) && is_array( $data['items'] ), 'News-media items must be present.' );
$assert( 15 === count( $data['items'] ), 'The existing records and three verified October news records must all remain present.' );
$assert( false !== $image_dir, 'News image directory must exist.' );

$source_ids      = array();
$post_slugs      = array();
$items_by_source = array();

foreach ( $data['items'] as $index => $item ) {
	$prefix = "Record {$index}";
	$assert( is_array( $item ), "{$prefix} must be an array." );

	foreach ( array( 'source_id', 'post_slug', 'image', 'title', 'alt', 'caption', 'credit', 'source_url' ) as $field ) {
		$assert( isset( $item[ $field ] ) && is_string( $item[ $field ] ) && '' !== trim( $item[ $field ] ), "{$prefix} field {$field} is required." );
	}

	$assert( preg_match( '/^instagram:[A-Za-z0-9_-]+$/', $item['source_id'] ), "{$prefix} source ID is not portable." );
	$assert( preg_match( '/^[a-z0-9-]+$/', $item['post_slug'] ), "{$prefix} post slug is not portable." );
	$assert( preg_match( '/^[a-z0-9-]+\.(?:jpe?g|png|webp)$/', $item['image'] ), "{$prefix} image path must be one local image filename." );
	$assert( false === strpos( $item['image'], '..' ) && false === strpos( $item['image'], '/' ), "{$prefix} image must stay in the bundled news directory." );
	$assert( preg_match( '#^https://www\.instagram\.com/(?:p|reel)/[A-Za-z0-9_-]+/$#', $item['source_url'] ), "{$prefix} source URL must be one canonical Instagram post or Reel URL." );
	$assert( ! in_array( $item['source_id'], $source_ids, true ), "Duplicate source ID {$item['source_id']}." );
	$assert( ! in_array( $item['post_slug'], $post_slugs, true ), "Duplicate post slug {$item['post_slug']}." );

	$source_ids[]                         = $item['source_id'];
	$post_slugs[]                         = $item['post_slug'];
	$items_by_source[ $item['source_id'] ] = $item;

	$file = realpath( $image_dir . DIRECTORY_SEPARATOR . $item['image'] );
	$assert( false !== $file && is_file( $file ) && is_readable( $file ), "{$prefix} bundled image is missing." );
	$assert( 0 === strpos( str_replace( '\\', '/', $file ), rtrim( str_replace( '\\', '/', $image_dir ), '/' ) . '/' ), "{$prefix} image escapes its directory." );
}

foreach ( $expected_legacy_sources as $source_id ) {
	$assert( isset( $items_by_source[ $source_id ] ), "Existing source {$source_id} must be preserved." );
}

foreach ( $expected_september as $source_id => $expected ) {
	$assert( isset( $items_by_source[ $source_id ] ), "September source {$source_id} is missing." );
	$item = $items_by_source[ $source_id ];

	foreach ( $expected as $field => $value ) {
		$assert( $value === $item[ $field ], "{$source_id} must use the verified {$field}." );
	}

	$public_copy = implode( ' ', array( $item['title'], $item['alt'], $item['caption'] ) );
	if ( 'instagram:DdB3xxRCS-r' !== $source_id ) {
		$assert( ! preg_match( '/\b(?:juara|peringkat|pemenang|menang|meraih|prestasi)\b/i', $public_copy ), "{$source_id} must not imply an award or rank." );
	}
}

$maulid = $items_by_source['instagram:DdSks0NiW_C'];
$assert( isset( $maulid['post'] ) && is_array( $maulid['post'] ), 'The latest verified feed item must include one post payload.' );
foreach ( array( 'title', 'excerpt', 'content', 'date', 'status', 'category', 'tags' ) as $field ) {
	$assert( isset( $maulid['post'][ $field ] ), "Maulid post field {$field} is required." );
}
$assert( 'publish' === $maulid['post']['status'], 'The approved latest-news payload must publish.' );
$assert( false !== strpos( $maulid['post']['content'], 'tanggal unggahan dokumentasi' ), 'The article must distinguish upload date from event date.' );

$figur = $items_by_source['instagram:DeIN3TVTdlC'];
$assert( isset( $figur['post'] ) && is_array( $figur['post'] ), 'The Arabic Speech finalist item must include one post payload.' );
$assert( 'publish' === $figur['post']['status'], 'The verified finalist article must publish.' );
$assert( false !== strpos( $figur['post']['content'], 'Ana Nur Lailatul Khoiriyah' ), 'The verified coach must be credited.' );
$assert( false !== strpos( $figur['post']['content'], 'National Speech Contest' ), 'The verified competition name must be present.' );

$timnas = $items_by_source['instagram:DeIPMAazojM'];
$assert( isset( $timnas['post'] ) && is_array( $timnas['post'] ), 'The Timnas appreciation item must include one post payload.' );
$assert( 'publish' === $timnas['post']['status'], 'The Timnas appreciation article must publish.' );
$assert( ! empty( $timnas['publish_matching_draft'] ), 'The exact matching Timnas draft must be completed instead of duplicated.' );
$assert( false !== strpos( $timnas['post']['content'], 'tidak dimaksudkan sebagai verifikasi independen' ), 'The article must distinguish the school message from independent tournament verification.' );
$assert( false === strpos( $timnas['post']['title'], 'Juara' ), 'The editorial title must not independently assert an unverified championship result.' );

$happy = $items_by_source['instagram:DeihO7LJ7yE'];
$assert( isset( $happy['post'] ) && is_array( $happy['post'] ), 'The Yuk Happy di Sekolah Reel must include one post payload.' );
$assert( 'publish' === $happy['post']['status'], 'The verified Yuk Happy article must publish.' );
$assert( '2026-10-16 10:07:03' === $happy['post']['date'], 'The Yuk Happy article must use the verified upload timestamp.' );
$assert( false !== strpos( $happy['post']['content'], 'tanggal unggahan sumber' ), 'The article must identify its date as the source upload date.' );
$assert( false === stripos( $happy['post']['content'], 'juara' ), 'The lifestyle Reel must not be presented as an achievement.' );

fwrite( STDOUT, "PASS: 15 news-media records preserve earlier work and add the verified October articles with official Instagram images.\n" );
