<?php
/**
 * Dependency-free checks for the centralized fallback visual resolver.
 */

define( 'ABSPATH', __DIR__ . '/wordpress-stub/' );
define( 'QUEEN_ALFALAH_DIR', dirname( __DIR__ ) . '/queen-alfalah' );
define( 'QUEEN_ALFALAH_URI', 'https://example.test/wp-content/themes/queen-alfalah' );

class WP_Post {
	public $ID;
	public $post_type;
	public $post_name;

	public function __construct( $id, $post_type, $post_name ) {
		$this->ID        = $id;
		$this->post_type = $post_type;
		$this->post_name = $post_name;
	}
}

$GLOBALS['qaf_test_posts']      = array();
$GLOBALS['qaf_test_categories'] = array();

function get_post( $post_id ) {
	return isset( $GLOBALS['qaf_test_posts'][ $post_id ] ) ? $GLOBALS['qaf_test_posts'][ $post_id ] : null;
}

function get_the_ID() {
	return 0;
}

function get_the_category( $post_id ) {
	return isset( $GLOBALS['qaf_test_categories'][ $post_id ] ) ? $GLOBALS['qaf_test_categories'][ $post_id ] : array();
}

function get_theme_mod( $key, $fallback = '' ) {
	return $fallback;
}

require dirname( __DIR__ ) . '/queen-alfalah/inc/template-tags.php';

$assert = static function ( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
};

$facility_expectations = array(
	'lab-dkv'                                     => 'facility-digital',
	'lab-tjkt'                                    => 'facility-digital',
	'ruang-praktikum-layanan-kesehatan'           => 'facility-health',
	'ruang-kantor-praktikum-mplb'                 => 'facility-office',
	'ruang-podcast'                               => 'facility-digital',
	'splicer-fiber-optic-praktikum-tjkt'          => 'facility-digital',
	'studio-foto-praktikum-dkv'                   => 'facility-digital',
	'praktikum-pesawat-tanpa-awak-drone-dkv'      => 'facility-digital',
	'alat-penunjang-layanan-kesehatan'            => 'facility-health',
	'peralatan-kantor-praktikum-mplb'             => 'facility-office',
	'ruang-editing-podcast'                       => 'facility-digital',
	'alat-produksi-broadcasting'                  => 'facility-digital',
	'aula-berkapasitas-300'                       => 'facility-campus',
	'ruang-kelas-nyaman'                          => 'facility-office',
	'gedung-kebanan'                              => 'facility-campus',
	'gedung-kraton'                               => 'facility-campus',
	'laboratorium-komputer'                       => 'facility-digital',
	'perpustakaan-dan-ruang-literasi'             => 'facility-campus',
	'usaha-kesehatan-sekolah-uks'                 => 'facility-health',
	'kantin-sekolah'                              => 'facility-campus',
	'sarana-prasarana'                            => 'facility',
);

foreach ( $facility_expectations as $slug => $expected ) {
	$assert( $expected === queen_alfalah_facility_visual_variant( $slug ), "Facility {$slug} must use {$expected}." );
}

$GLOBALS['qaf_test_posts'][1] = new WP_Post( 1, 'qaf_teacher', 'ririn-rohmatul-umah-s-pd' );
$GLOBALS['qaf_test_posts'][2] = new WP_Post( 2, 'qaf_gallery', 'video-kegiatan-01' );
$GLOBALS['qaf_test_posts'][3] = new WP_Post( 3, 'qaf_achievement', 'juara-lbb-putri' );
$GLOBALS['qaf_test_posts'][4] = new WP_Post( 4, 'page', 'bursa-kerja-khusus' );
$GLOBALS['qaf_test_posts'][5] = new WP_Post( 5, 'page', 'profil-sekolah' );
$GLOBALS['qaf_test_posts'][6] = new WP_Post( 6, 'page', 'informasi' );

$assert( 'people' === queen_alfalah_visual_variant( 1 ), 'Teacher must use the people illustration.' );
$assert( 'gallery' === queen_alfalah_visual_variant( 2 ), 'Gallery must use the gallery poster.' );
$assert( 'achievement' === queen_alfalah_visual_variant( 3 ), 'Achievement must use the achievement illustration.' );
$assert( 'career' === queen_alfalah_visual_variant( 4 ), 'BKK page must use the career illustration.' );
$assert( 'school' === queen_alfalah_visual_variant( 5 ), 'Profile page must use the general school illustration.' );
$assert( 'service' === queen_alfalah_visual_variant( 6 ), 'Information page must use the service illustration.' );

$expected_files = array(
	'fallback-achievement.webp',
	'fallback-career.webp',
	'fallback-facility-campus.webp',
	'fallback-facility-digital.webp',
	'fallback-facility-health.webp',
	'fallback-facility-office.webp',
	'fallback-facility.webp',
	'fallback-gallery.webp',
	'fallback-people.webp',
	'fallback-school.webp',
	'fallback-service.webp',
);

$visual_dir = QUEEN_ALFALAH_DIR . '/assets/images/fallback';
foreach ( $expected_files as $filename ) {
	$file = $visual_dir . '/' . $filename;
	$assert( is_file( $file ) && is_readable( $file ), "Missing bundled visual {$filename}." );
	$assert( filesize( $file ) > 30000 && filesize( $file ) < 250000, "Unexpected file size for {$filename}." );
	$dimensions = getimagesize( $file );
	$assert( is_array( $dimensions ) && 1200 === $dimensions[0] && 800 === $dimensions[1], "{$filename} must be 1200x800." );
}

$assert(
	false !== strpos( queen_alfalah_placeholder( 'gallery' ), '/assets/images/fallback/fallback-gallery.webp' ),
	'Generated gallery fallback must take precedence over the legacy SVG.'
);

fwrite( STDOUT, "PASS: 21 facilities and all supported content families resolve to optimized fallback visuals.\n" );
