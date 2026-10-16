<?php
/** Dependency-free contracts for the responsive application-style navigation. */

$root        = dirname( __DIR__ );
$header      = file_get_contents( $root . '/queen-alfalah/header.php' );
$styles      = file_get_contents( $root . '/queen-alfalah/style.css' );
$scripts     = file_get_contents( $root . '/queen-alfalah/assets/js/theme.js' );
$template    = file_get_contents( $root . '/queen-alfalah/inc/template-tags.php' );
$functions   = file_get_contents( $root . '/queen-alfalah/functions.php' );
$failures    = array();

$assert = static function ( $condition, $message ) use ( &$failures ) {
	if ( ! $condition ) {
		$failures[] = $message;
	}
};

$assert( false !== strpos( $header, 'class="mobile-dock"' ), 'Mobile dock landmark is missing.' );
$assert( false !== strpos( $header, 'data-mobile-nav-toggle' ), 'Mobile dock menu controller is missing.' );
$assert( false !== strpos( $header, "__( 'Beranda', 'queen-alfalah' )" ), 'Home shortcut is missing.' );
$assert( false !== strpos( $header, "__( 'Berita', 'queen-alfalah' )" ), 'News shortcut is missing.' );
$assert( false !== strpos( $header, "__( 'Aplikasi', 'queen-alfalah' )" ), 'Application shortcut is missing.' );
$assert( false !== strpos( $header, "__( 'PPDB', 'queen-alfalah' )" ), 'PPDB shortcut is missing.' );
$assert( false !== strpos( $header, 'aria-current="page"' ), 'Current-page semantics are missing.' );
$assert( false !== strpos( $styles, '@media (max-width: 1023px)' ), 'Phone/tablet breakpoint is missing.' );
$assert( (bool) preg_match( '/@media \(max-width: 1023px\)[\s\S]*?\.js-enabled \.site-nav \{[\s\S]*?position: fixed;[\s\S]*?visibility: hidden;[\s\S]*?transform: translateX\(100%\);/', $styles ), 'Tablet drawer must override the earlier rem-based desktop navigation state.' );
$assert( false !== strpos( $styles, 'env(safe-area-inset-bottom)' ), 'Safe-area support is missing.' );
$assert( false !== strpos( $styles, 'grid-template-columns: repeat(5' ), 'Five-item dock layout is missing.' );
$assert( false !== strpos( $styles, '@media (min-width: 1024px)' ), 'Desktop-only enhancement breakpoint is missing.' );
$assert( false !== strpos( $scripts, "matchMedia('(min-width: 1024px)')" ), 'JavaScript breakpoint does not match the CSS desktop breakpoint.' );
$assert( false !== strpos( $scripts, "querySelector('[data-mobile-nav-toggle]')" ), 'Dock controller is not wired to the menu drawer.' );
$assert( false !== strpos( $template, "'home'" ) && false !== strpos( $template, "'newspaper'" ), 'Dock SVG icons are missing.' );
$assert( false !== strpos( $functions, "QUEEN_ALFALAH_VERSION', '1.11.0" ), 'Theme asset version was not bumped.' );

if ( $failures ) {
	foreach ( $failures as $failure ) {
		fwrite( STDERR, "FAIL: {$failure}\n" );
	}
	exit( 1 );
}

fwrite( STDOUT, "PASS: responsive mobile/tablet dock, drawer controls, safe areas, and desktop states are present.\n" );
