<?php
/**
 * Site header.
 *
 * @package Queen_AlFalah
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$school_name = queen_alfalah_school_info( 'school_name' );
$phone       = queen_alfalah_school_info( 'phone' );
$email       = queen_alfalah_school_info( 'email' );
$ppdb_url    = queen_alfalah_school_info( 'ppdb_url' );
$notice      = null;

if ( post_type_exists( 'qaf_notice' ) ) {
	$notice_query = new WP_Query(
		array(
			'post_type'              => 'qaf_notice',
			'posts_per_page'         => 1,
			'post_status'            => 'publish',
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'meta_query'             => array(
				'relation' => 'OR',
				array( 'key' => '_qaf_expiry', 'compare' => 'NOT EXISTS' ),
				array( 'key' => '_qaf_expiry', 'value' => '', 'compare' => '=' ),
				array( 'key' => '_qaf_expiry', 'value' => current_time( 'Y-m-d' ), 'compare' => '>=', 'type' => 'DATE' ),
			),
			'orderby'                => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
			'update_post_meta_cache' => true,
		)
	);
	$notice = $notice_query->have_posts() ? $notice_query->posts[0] : null;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main-content"><?php esc_html_e( 'Lewati ke konten utama', 'queen-alfalah' ); ?></a>

<div id="page" class="site">
	<?php if ( $notice ) : ?>
		<div class="announcement-strip" data-dismissible="announcement-<?php echo esc_attr( $notice->ID ); ?>" role="region" aria-label="<?php esc_attr_e( 'Pengumuman penting', 'queen-alfalah' ); ?>">
			<div class="container announcement-strip__inner">
				<span class="announcement-strip__badge announcement-strip__label"><?php esc_html_e( 'Penting', 'queen-alfalah' ); ?></span>
				<p><strong><?php echo esc_html( get_the_title( $notice ) ); ?></strong> <a href="<?php echo esc_url( get_permalink( $notice ) ); ?>"><?php esc_html_e( 'Baca pengumuman', 'queen-alfalah' ); ?><?php echo queen_alfalah_icon( 'arrow-right' ); ?></a></p>
				<button class="announcement-strip__close" type="button" data-dismiss-announcement aria-label="<?php esc_attr_e( 'Tutup pengumuman', 'queen-alfalah' ); ?>"><?php echo queen_alfalah_icon( 'close' ); ?></button>
			</div>
		</div>
	<?php endif; ?>

	<header id="masthead" class="site-header">
		<div class="topbar">
			<div class="container topbar__inner">
				<div class="topbar__contacts topbar__contact">
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo queen_alfalah_icon( 'phone' ); ?><span><?php echo esc_html( $phone ); ?></span></a>
					<a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo queen_alfalah_icon( 'mail' ); ?><span><?php echo esc_html( antispambot( $email ) ); ?></span></a>
				</div>
				<?php if ( has_nav_menu( 'utility' ) ) : ?>
					<nav class="utility-nav" aria-label="<?php esc_attr_e( 'Tautan cepat', 'queen-alfalah' ); ?>">
						<?php wp_nav_menu( array( 'theme_location' => 'utility', 'container' => false, 'depth' => 1, 'fallback_cb' => false ) ); ?>
					</nav>
				<?php else : ?>
					<p class="topbar__motto"><?php echo esc_html( queen_alfalah_school_info( 'motto' ) ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<div class="header-main container">
				<div class="brand">
					<?php if ( has_custom_logo() ) : ?>
						<?php the_custom_logo(); ?>
					<?php else : ?>
						<a class="brand__mark brand__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( $school_name ); ?>">
							<img src="<?php echo esc_url( QUEEN_ALFALAH_URI . '/assets/images/brand-mark.svg' ); ?>" width="58" height="58" alt="">
						</a>
					<?php endif; ?>
					<div class="brand__text">
						<p class="brand__name"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( $school_name ); ?></a></p>
						<p class="brand__tagline"><?php echo esc_html( queen_alfalah_school_info( 'motto' ) ); ?></p>
					</div>
				</div>

				<div class="header-actions">
					<?php if ( post_type_exists( 'qaf_service' ) ) : ?>
						<a class="button button--small button--outline header-apps" href="<?php echo esc_url( queen_alfalah_archive_url( 'qaf_service', 'aplikasi' ) ); ?>"><?php echo queen_alfalah_icon( 'monitor' ); ?><?php esc_html_e( 'Aplikasi', 'queen-alfalah' ); ?></a>
					<?php endif; ?>
					<button class="search-toggle" type="button" aria-expanded="false" aria-controls="header-search">
						<?php echo queen_alfalah_icon( 'search' ); ?><span class="screen-reader-text"><?php esc_html_e( 'Buka pencarian', 'queen-alfalah' ); ?></span>
					</button>
					<?php if ( $ppdb_url ) : ?>
						<a class="button button--small header-ppdb" href="<?php echo esc_url( $ppdb_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( queen_alfalah_school_info( 'ppdb_label' ) ); ?><?php echo queen_alfalah_icon( 'arrow-right' ); ?></a>
					<?php endif; ?>
				</div>
				<button class="menu-toggle" type="button" aria-expanded="false" aria-controls="site-navigation">
					<span class="menu-toggle__icon" aria-hidden="true"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Buka menu', 'queen-alfalah' ); ?></span>
				</button>
		</div>

			<div id="header-search" class="header-search" hidden>
				<div class="container">
					<?php get_search_form(); ?>
				</div>
			</div>

			<div class="nav-wrap">
				<div class="container">
					<nav id="site-navigation" class="site-nav" aria-label="<?php esc_attr_e( 'Navigasi utama', 'queen-alfalah' ); ?>">
						<div class="site-nav__header">
							<span><?php esc_html_e( 'Navigasi', 'queen-alfalah' ); ?></span>
							<strong><?php esc_html_e( 'Menu Utama', 'queen-alfalah' ); ?></strong>
						</div>
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'primary',
								'menu_id'        => 'primary-menu',
								'menu_class'     => 'primary-menu',
								'container'      => false,
								'fallback_cb'    => 'queen_alfalah_menu_fallback',
							)
						);
						?>
						<div class="site-nav__contact">
							<p><?php echo queen_alfalah_icon( 'map-pin' ); ?><?php echo esc_html( queen_alfalah_school_info( 'address' ) ); ?></p>
							<?php if ( queen_alfalah_school_info( 'whatsapp' ) ) : ?>
								<a href="<?php echo esc_url( queen_alfalah_whatsapp_url( 'Assalamu’alaikum, saya ingin bertanya tentang SMK Queen Al-Falah.' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php echo queen_alfalah_icon( 'whatsapp' ); ?><?php esc_html_e( 'Hubungi melalui WhatsApp', 'queen-alfalah' ); ?></a>
							<?php endif; ?>
						</div>
					</nav>
				</div>
			</div>
		</div>
	</header>
