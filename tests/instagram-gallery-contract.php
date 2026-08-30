<?php
/**
 * Small dependency-free contract check for Instagram URL and media filtering.
 *
 * Run with: php tests/instagram-gallery-contract.php
 */

define( 'ABSPATH', __DIR__ . '/' );

if ( ! function_exists( 'wp_parse_url' ) ) {
	function wp_parse_url( $url, $component = -1 ) {
		return parse_url( $url, $component );
	}
}

require dirname( __DIR__ ) . '/queen-alfalah-core/includes/class-qaf-core-instagram-gallery.php';

$failures = array();

$expectations = array(
	array(
		'actual'   => QAF_Core_Instagram_Gallery::canonical_permalink( 'https://www.instagram.com/reel/DcckRIDJX-q/?igsh=example' ),
		'expected' => 'https://www.instagram.com/reel/DcckRIDJX-q/',
		'label'    => 'Reel URL is canonicalized',
	),
	array(
		'actual'   => QAF_Core_Instagram_Gallery::canonical_permalink( 'https://instagram.com/p/Abc_123/' ),
		'expected' => 'https://www.instagram.com/p/Abc_123/',
		'label'    => 'Post URL is canonicalized',
	),
	array(
		'actual'   => QAF_Core_Instagram_Gallery::canonical_permalink( 'https://instagram.com.evil.example/reel/Abc_123/' ),
		'expected' => '',
		'label'    => 'Look-alike host is rejected',
	),
	array(
		'actual'   => QAF_Core_Instagram_Gallery::canonical_permalink( 'http://www.instagram.com/reel/Abc_123/' ),
		'expected' => '',
		'label'    => 'Non-HTTPS URL is rejected',
	),
	array(
		'actual'   => QAF_Core_Instagram_Gallery::canonical_permalink( 'https://www.instagram.com/smkqueenalfalah_official/' ),
		'expected' => '',
		'label'    => 'Profile URL is rejected',
	),
);

foreach ( $expectations as $expectation ) {
	if ( $expectation['actual'] !== $expectation['expected'] ) {
		$failures[] = $expectation['label'] . ': expected ' . var_export( $expectation['expected'], true ) . ', got ' . var_export( $expectation['actual'], true );
	}
}

$media_expectations = array(
	array(
		'actual'   => QAF_Core_Instagram_Gallery::is_video_record( array( 'media_type' => 'VIDEO', 'permalink' => 'https://www.instagram.com/p/Abc_123/' ) ),
		'expected' => true,
		'label'    => 'VIDEO media is imported',
	),
	array(
		'actual'   => QAF_Core_Instagram_Gallery::is_video_record( array( 'media_type' => 'IMAGE', 'permalink' => 'https://www.instagram.com/reel/Abc_123/' ) ),
		'expected' => true,
		'label'    => 'Reel permalink remains a safe fallback',
	),
	array(
		'actual'   => QAF_Core_Instagram_Gallery::is_video_record( array( 'media_type' => 'IMAGE', 'permalink' => 'https://www.instagram.com/p/Abc_123/' ) ),
		'expected' => false,
		'label'    => 'Ordinary image is skipped',
	),
);

foreach ( $media_expectations as $expectation ) {
	if ( $expectation['actual'] !== $expectation['expected'] ) {
		$failures[] = $expectation['label'];
	}
}

if ( $failures ) {
	fwrite( STDERR, implode( PHP_EOL, $failures ) . PHP_EOL );
	exit( 1 );
}

fwrite( STDOUT, 'Instagram gallery contract checks passed.' . PHP_EOL );
