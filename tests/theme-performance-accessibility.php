<?php
/** Dependency-free contracts for October performance and accessibility improvements. */

$root       = dirname( __DIR__ );
$front_page = file_get_contents( $root . '/queen-alfalah/front-page.php' );
$customizer = file_get_contents( $root . '/queen-alfalah/inc/customizer.php' );
$functions  = file_get_contents( $root . '/queen-alfalah/functions.php' );
$styles     = file_get_contents( $root . '/queen-alfalah/style.css' );
$failures   = array();

$assert = static function ( $condition, $message ) use ( &$failures ) {
	if ( ! $condition ) {
		$failures[] = $message;
	}
};

$assert( false !== strpos( $customizer, "'queen_landing_background_video'" ), 'The replaceable landing video setting is missing.' );
$assert( false !== strpos( $customizer, "'mime_type'   => 'video'" ), 'The landing video control must accept Media Library videos.' );
$assert( false !== strpos( $front_page, "array( 'video/mp4', 'video/webm' )" ), 'The hero must restrict background videos to MP4 or WebM.' );
$assert( false !== strpos( $front_page, 'class="hero__background-video" autoplay muted loop playsinline' ), 'The background video must autoplay silently, loop, and support inline playback.' );
$assert( false !== strpos( $front_page, 'preload="metadata"' ), 'The background video must not eagerly preload its full file.' );
$assert( false !== strpos( $front_page, '$landing_bg && ! $landing_video' ), 'The large GIF/image background must not load while a video is active.' );
$assert( (bool) preg_match( '/@media \(prefers-reduced-motion: reduce\)[\s\S]*?\.hero__background-video \{\s*display: none;/', $styles ), 'Reduced-motion users must not receive animated hero playback.' );
$assert( false !== strpos( $styles, '.announcement-strip' ) && false !== strpos( $styles, 'color: var(--qa-emerald-950);' ), 'The announcement strip needs high-contrast text.' );
$assert( false !== strpos( $styles, '.journey-card .eyebrow' ) && false !== strpos( $styles, 'color: var(--qa-emerald-800);' ), 'Journey eyebrows need accessible contrast.' );
$assert( false !== strpos( $styles, '.site-footer .widget-title' ) && false !== strpos( $styles, 'color: var(--qa-gold-200);' ), 'Footer headings need accessible contrast.' );
$assert( false !== strpos( $functions, 'function queen_alfalah_meta_description()' ), 'A fallback meta description function is missing.' );
$assert( false !== strpos( $functions, "add_action( 'wp_head', 'queen_alfalah_meta_description', 2 )" ), 'The fallback meta description is not connected to wp_head.' );

if ( $failures ) {
	foreach ( $failures as $failure ) {
		fwrite( STDERR, "FAIL: {$failure}\n" );
	}
	exit( 1 );
}

fwrite( STDOUT, "PASS: optimized hero media, reduced-motion support, contrast fixes, and fallback SEO metadata are present.\n" );
