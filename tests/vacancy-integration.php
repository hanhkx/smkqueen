<?php
/** Static integration contracts for the vacancy importer and presentation. */

$root    = dirname( __DIR__ );
$plugin  = file_get_contents( $root . '/queen-alfalah-core/queen-alfalah-core.php' );
$module  = file_get_contents( $root . '/queen-alfalah-core/includes/class-qaf-core-vacancies.php' );
$meta    = file_get_contents( $root . '/queen-alfalah-core/includes/class-qaf-core-meta.php' );
$admin   = file_get_contents( $root . '/queen-alfalah-core/includes/class-qaf-core-admin.php' );
$single  = file_get_contents( $root . '/queen-alfalah/template-parts/content-single.php' );
$card    = file_get_contents( $root . '/queen-alfalah/template-parts/content-card.php' );

$assert = static function ( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
};

$assert( false !== strpos( $plugin, "class-qaf-core-vacancies.php" ), 'Plugin must load the vacancy importer.' );
$assert( false !== strpos( $plugin, 'QAF_Core_Vacancies::init()' ), 'Plugin must initialize the vacancy importer.' );
$assert( false !== strpos( $plugin, 'QAF_Core_Vacancies::activate()' ), 'Activation must apply the vacancy package.' );
$assert( false !== strpos( $module, "'post_status'  => 'publish'" ), 'Approved vacancy records must publish.' );
$assert( false !== strpos( $module, 'has_post_thumbnail' ), 'Existing editor-selected logos must be preserved.' );
$assert( false !== strpos( $module, "'trash' === get_post_status" ), 'Trashed editor content must stay trashed.' );
$assert( false !== strpos( $module, 'Rekrutmen yang sah tidak meminta transfer' ), 'Every imported vacancy must include an anti-scam warning.' );

foreach ( array( '_qaf_vacancy_location', '_qaf_vacancy_employment_type', '_qaf_vacancy_education', '_qaf_vacancy_salary', '_qaf_vacancy_verified_date', '_qaf_vacancy_source_url', '_qaf_vacancy_contact' ) as $field ) {
	$assert( false !== strpos( $meta, $field ), "Meta contract {$field} is missing." );
}

$assert( false !== strpos( $admin, "'qaf_thumbnail' => array( 'label' => 'Logo'" ), 'Vacancy admin table must show company logos.' );
$assert( false !== strpos( $single, "array( 'vacancy_source_url'" ), 'Single vacancy view must link to the original source.' );
$assert( false !== strpos( $single, "array( 'apply_url'" ) && false !== strpos( $single, 'Daftar melalui sumber' ), 'Single vacancy view must include the direct application action.' );
$assert( false !== strpos( $card, "'qaf_vacancy'     => array( 'vacancy_location'" ), 'Vacancy cards must show work location.' );

fwrite( STDOUT, "PASS: vacancy importer, metadata, admin columns, cards, sources, and application actions are integrated.\n" );
