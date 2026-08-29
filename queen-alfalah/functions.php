<?php
/**
 * Queen Al-Falah theme bootstrap.
 *
 * @package Queen_AlFalah
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QUEEN_ALFALAH_VERSION', '1.5.0' );
define( 'QUEEN_ALFALAH_DIR', get_template_directory() );
define( 'QUEEN_ALFALAH_URI', get_template_directory_uri() );

$queen_alfalah_includes = array(
	'/inc/template-tags.php',
	'/inc/customizer.php',
	'/inc/schema.php',
	'/inc/admin-notice.php',
);

foreach ( $queen_alfalah_includes as $queen_alfalah_file ) {
	$queen_alfalah_path = QUEEN_ALFALAH_DIR . $queen_alfalah_file;
	if ( file_exists( $queen_alfalah_path ) ) {
		require_once $queen_alfalah_path;
	}
}

/**
 * Configure theme defaults and WordPress features.
 */
function queen_alfalah_setup() {
	load_theme_textdomain( 'queen-alfalah', QUEEN_ALFALAH_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 96,
			'width'       => 96,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_editor_style( array( 'style.css', 'assets/css/editor-style.css' ) );
	add_image_size( 'queen-card', 720, 480, true );
	add_image_size( 'queen-program', 900, 620, true );
	add_image_size( 'queen-person', 640, 760, true );

	register_nav_menus(
		array(
			'primary'  => __( 'Menu Utama', 'queen-alfalah' ),
			'utility'  => __( 'Menu Atas', 'queen-alfalah' ),
			'services' => __( 'Menu Layanan', 'queen-alfalah' ),
			'footer'   => __( 'Menu Footer', 'queen-alfalah' ),
		)
	);
}
add_action( 'after_setup_theme', 'queen_alfalah_setup' );

/**
 * Set a readable content width for embeds and editor content.
 */
function queen_alfalah_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'queen_alfalah_content_width', 760 );
}
add_action( 'after_setup_theme', 'queen_alfalah_content_width', 0 );

/**
 * Register widget areas.
 */
function queen_alfalah_widgets_init() {
	$areas = array(
		'sidebar-1' => __( 'Sidebar Artikel', 'queen-alfalah' ),
		'footer-1'  => __( 'Footer Kolom 1', 'queen-alfalah' ),
		'footer-2'  => __( 'Footer Kolom 2', 'queen-alfalah' ),
		'footer-3'  => __( 'Footer Kolom 3', 'queen-alfalah' ),
	);

	foreach ( $areas as $id => $name ) {
		register_sidebar(
			array(
				'name'          => $name,
				'id'            => $id,
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);
	}
}
add_action( 'widgets_init', 'queen_alfalah_widgets_init' );

/**
 * Enqueue only local, dependency-free assets.
 */
function queen_alfalah_enqueue_assets() {
	wp_enqueue_style( 'queen-alfalah-style', get_stylesheet_uri(), array(), QUEEN_ALFALAH_VERSION );
	if ( is_post_type_archive( 'qaf_service' ) ) {
		wp_enqueue_style( 'queen-alfalah-applications', QUEEN_ALFALAH_URI . '/assets/css/applications.css', array( 'queen-alfalah-style' ), QUEEN_ALFALAH_VERSION );
	}
	if ( is_singular( 'qaf_gallery' ) ) {
		wp_enqueue_script(
			'queen-alfalah-gallery',
			QUEEN_ALFALAH_URI . '/assets/js/gallery.js',
			array(),
			QUEEN_ALFALAH_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}
	wp_enqueue_script(
		'queen-alfalah-theme',
		QUEEN_ALFALAH_URI . '/assets/js/theme.js',
		array(),
		QUEEN_ALFALAH_VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	wp_localize_script(
		'queen-alfalah-theme',
		'queenTheme',
		array(
			'expand'   => __( 'Buka submenu', 'queen-alfalah' ),
			'collapse' => __( 'Tutup submenu', 'queen-alfalah' ),
			'menuOpen' => __( 'Buka menu', 'queen-alfalah' ),
			'menuClose'=> __( 'Tutup menu', 'queen-alfalah' ),
			'searchOpen' => __( 'Buka pencarian', 'queen-alfalah' ),
			'searchClose' => __( 'Tutup pencarian', 'queen-alfalah' ),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	$primary = sanitize_hex_color( get_theme_mod( 'queen_primary_color', '#0b5d4b' ) );
	$accent  = sanitize_hex_color( get_theme_mod( 'queen_accent_color', '#d4a72c' ) );
	if ( $primary || $accent ) {
		$css = ':root{';
		$css .= $primary ? '--color-primary:' . $primary . ';--color-primary-deep:' . $primary . ';' : '';
		$css .= $accent ? '--color-accent:' . $accent . ';' : '';
		$css .= '}';
		wp_add_inline_style( 'queen-alfalah-style', $css );
	}
}
add_action( 'wp_enqueue_scripts', 'queen_alfalah_enqueue_assets' );

/**
 * Live Customizer preview support.
 */
function queen_alfalah_customize_preview_js() {
	wp_enqueue_script(
		'queen-alfalah-customizer',
		QUEEN_ALFALAH_URI . '/assets/js/customizer-preview.js',
		array( 'customize-preview' ),
		QUEEN_ALFALAH_VERSION,
		true
	);
}
add_action( 'customize_preview_init', 'queen_alfalah_customize_preview_js' );

/**
 * Add context hooks for styling.
 *
 * @param string[] $classes Existing body classes.
 * @return string[]
 */
function queen_alfalah_body_classes( $classes ) {
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}
	if ( get_theme_mod( 'queen_sticky_header', true ) ) {
		$classes[] = 'has-sticky-header';
	}
	return $classes;
}
add_filter( 'body_class', 'queen_alfalah_body_classes' );

/**
 * Keep card excerpts compact.
 */
function queen_alfalah_excerpt_length() {
	return 24;
}
add_filter( 'excerpt_length', 'queen_alfalah_excerpt_length', 20 );

/**
 * Accessible fallback menu used before demo/menu setup.
 */
function queen_alfalah_menu_fallback() {
	$items = array(
		__( 'Beranda', 'queen-alfalah' )          => home_url( '/' ),
		__( 'Profil', 'queen-alfalah' )           => home_url( '/profil-sekolah/' ),
		__( 'Program Keahlian', 'queen-alfalah' ) => home_url( '/program-keahlian/' ),
		__( 'Berita', 'queen-alfalah' )           => home_url( '/berita/' ),
		__( 'PPDB', 'queen-alfalah' )             => home_url( '/ppdb/' ),
		__( 'Kontak', 'queen-alfalah' )           => home_url( '/kontak/' ),
	);

	echo '<ul class="menu menu--fallback">';
	foreach ( $items as $label => $url ) {
		printf( '<li class="menu-item"><a href="%1$s">%2$s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}

/**
 * Add a pingback URL only when relevant.
 */
function queen_alfalah_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">' . "\n", esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'queen_alfalah_pingback_header' );

/**
 * Group bundled editor patterns in the inserter.
 */
function queen_alfalah_register_pattern_category() {
	if ( function_exists( 'register_block_pattern_category' ) ) {
		register_block_pattern_category(
			'queen-alfalah',
			array( 'label' => __( 'Queen Al-Falah', 'queen-alfalah' ) )
		);
	}
}
add_action( 'init', 'queen_alfalah_register_pattern_category' );
