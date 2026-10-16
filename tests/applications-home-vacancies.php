<?php
/** Dependency-free contracts for active applications and homepage vacancies. */

$root       = dirname( __DIR__ );
$front      = file_get_contents( $root . '/queen-alfalah/front-page.php' );
$archive    = file_get_contents( $root . '/queen-alfalah/archive-qaf_service.php' );
$helpers    = file_get_contents( $root . '/queen-alfalah/inc/template-tags.php' );
$customizer = file_get_contents( $root . '/queen-alfalah/inc/customizer.php' );
$styles     = file_get_contents( $root . '/queen-alfalah/style.css' );
$meta       = file_get_contents( $root . '/queen-alfalah-core/includes/class-qaf-core-meta.php' );
$admin      = file_get_contents( $root . '/queen-alfalah-core/includes/class-qaf-core-admin.php' );

$assert = static function ( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
};

$assert( false !== strpos( $helpers, 'function queen_alfalah_service_is_active' ), 'Active-service validator is missing.' );
$assert( false !== strpos( $helpers, "'_qaf_service_status'" ) && false !== strpos( $helpers, "'_qaf_external_url'" ), 'Application query must require active status and a URL.' );
$assert( false !== strpos( $helpers, 'wp_http_validate_url' ), 'Application URLs must be validated before display.' );
$assert( false !== strpos( $archive, 'queen_alfalah_active_service_query_args' ) && false !== strpos( $archive, 'queen_alfalah_service_is_active' ), 'Application directory must use the shared active filter.' );
$assert( false !== strpos( $front, 'queen_alfalah_service_is_active' ), 'Homepage shortcuts must reject inactive or invalid services.' );
$assert( false !== strpos( $meta, 'Pilih Pemeliharaan atau Nonaktif untuk menyembunyikannya tanpa menghapus data' ), 'Application editor guidance is missing.' );
$assert( false !== strpos( $admin, "'qaf_service_status'" ), 'Application status must be visible in the admin list.' );

$assert( false !== strpos( $front, 'home-vacancies' ), 'Homepage vacancy section is missing.' );
$assert( false !== strpos( $front, "'post_type'           => 'qaf_vacancy'" ), 'Homepage vacancy query is missing.' );
$assert( strpos( $front, 'home-vacancies' ) > strpos( $front, 'career-heading' ) && strpos( $front, 'home-vacancies' ) < strpos( $front, 'extra-heading' ), 'Homepage vacancies must appear after the career pathway and before extracurriculars.' );
$assert( false !== strpos( $front, "'_qaf_deadline'" ) && false !== strpos( $front, "'compare' => '>='" ), 'Homepage must keep expired vacancies out of promotion.' );
$assert( false !== strpos( $customizer, 'queen_home_vacancies_show' ) && false !== strpos( $customizer, 'queen_home_vacancies_count' ), 'Homepage vacancy controls are missing.' );
$assert( false !== strpos( $styles, '.home-vacancies__grid' ), 'Homepage vacancy responsive styles are missing.' );

fwrite( STDOUT, "PASS: active applications and configurable homepage vacancies are integrated non-destructively.\n" );
