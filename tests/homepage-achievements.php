<?php
/**
 * Dependency-free contracts for the homepage achievement showcase.
 */

$front_page = file_get_contents( dirname( __DIR__ ) . '/queen-alfalah/front-page.php' );

$assert = static function ( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
};

$assert( false !== $front_page, 'Homepage template must be readable.' );
$assert( false !== strpos( $front_page, 'aria-labelledby="achievement-heading"' ), 'Homepage must expose an accessible achievement section.' );
$assert( false !== strpos( $front_page, "__( 'Prestasi SMK Queen Al-Falah', 'queen-alfalah' )" ), 'Homepage achievement heading must use the requested school name.' );
$assert( false !== strpos( $front_page, "post_type_exists( 'qaf_achievement' )" ), 'Homepage must remain safe when the companion plugin is inactive.' );
$assert( (bool) preg_match( "/'post_type'\s*=>\s*'qaf_achievement'/", $front_page ), 'Homepage must query the achievement post type.' );
$assert( (bool) preg_match( "/'posts_per_page'\s*=>\s*12/", $front_page ), 'Homepage must scan enough recent records to avoid duplicate event cards.' );
$assert( false !== strpos( $front_page, "'meta_key'       => '_qaf_achievement_date'" ), 'Homepage must order achievements by their structured date.' );
$assert( false !== strpos( $front_page, "'meta_value' => 'DESC'" ), 'Homepage must show the newest dated achievements first.' );
$assert( false !== strpos( $front_page, "'ID'         => 'ASC'" ), 'Ties must keep the first curated winner from a shared event.' );
$assert( false !== strpos( $front_page, "queen_alfalah_meta( get_the_ID(), 'source_url' )" ), 'Homepage must compare source URLs for event diversity.' );
$assert( false !== strpos( $front_page, 'isset( $achievement_sources[ $achievement_key ] )' ), 'Homepage must suppress duplicate event sources.' );
$assert( false !== strpos( $front_page, '$achievement_count < 3' ), 'Homepage must limit the showcase to three cards.' );
$assert( false !== strpos( $front_page, "get_template_part( 'template-parts/content', 'card' )" ), 'Homepage must reuse the accessible achievement card template.' );
$assert( false !== strpos( $front_page, 'wp_reset_postdata();' ), 'Homepage must restore the global post after the custom query.' );
$assert( false !== strpos( $front_page, "queen_alfalah_archive_url( 'qaf_achievement', 'prestasi' )" ), 'Homepage must link to the complete achievement archive.' );
$assert( false !== strpos( $front_page, "esc_html_e( 'Prestasi akan segera ditampilkan'" ), 'Homepage must provide an honest empty state.' );

$achievement_position = strpos( $front_page, 'aria-labelledby="achievement-heading"' );
$stats_position       = strpos( $front_page, 'aria-labelledby="stats-heading"' );
$assert( false !== $stats_position && $achievement_position < $stats_position, 'Achievement showcase must appear before the school statistics section.' );

fwrite( STDOUT, "PASS: homepage shows three recent, source-diverse achievements with an archive link and safe fallback.\n" );
