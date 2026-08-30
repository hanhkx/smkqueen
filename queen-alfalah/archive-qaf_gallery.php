<?php
/**
 * Configurable local and social-media Gallery archive.
 *
 * @package Queen_AlFalah
 */

$queen_gallery_sources = queen_alfalah_gallery_sources();
$queen_gallery_filters = array( '' => __( 'Semua', 'queen-alfalah' ) ) + $queen_gallery_sources;
$queen_gallery_current = isset( $_GET['sumber'] ) ? sanitize_key( wp_unslash( $_GET['sumber'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only archive filter.
if ( ! array_key_exists( $queen_gallery_current, $queen_gallery_filters ) ) {
	$queen_gallery_current = '';
}

$queen_gallery_archive_url = get_post_type_archive_link( 'qaf_gallery' );
if ( ! $queen_gallery_archive_url ) {
	$queen_gallery_archive_url = queen_alfalah_archive_url( 'qaf_gallery', 'galeri' );
}

get_header();
queen_alfalah_breadcrumbs();
?>

<main id="main-content" class="site-main">
	<header class="archive-header">
		<div class="container">
			<p class="eyebrow"><?php esc_html_e( 'Dokumentasi Sekolah Terpadu', 'queen-alfalah' ); ?></p>
			<h1><?php esc_html_e( 'Galeri SMK Queen Al-Falah', 'queen-alfalah' ); ?></h1>
			<p><?php esc_html_e( 'Jelajahi foto dan video milik sekolah serta dokumentasi publik dari Instagram, TikTok, Facebook, dan YouTube.', 'queen-alfalah' ); ?></p>
			<nav class="gallery-source-filters" aria-label="<?php esc_attr_e( 'Saring galeri berdasarkan sumber', 'queen-alfalah' ); ?>">
				<?php foreach ( $queen_gallery_filters as $queen_source_key => $queen_source_label ) : ?>
					<?php
					$queen_filter_url = $queen_source_key
						? add_query_arg( 'sumber', $queen_source_key, $queen_gallery_archive_url )
						: $queen_gallery_archive_url;
					?>
					<a href="<?php echo esc_url( $queen_filter_url ); ?>"<?php echo $queen_gallery_current === $queen_source_key ? ' aria-current="page"' : ''; ?>>
						<?php echo esc_html( $queen_source_label ); ?>
					</a>
				<?php endforeach; ?>
			</nav>
		</div>
	</header>

	<div class="<?php echo esc_attr( 'container content-area' . ( is_active_sidebar( 'sidebar-1' ) ? ' content-area--with-sidebar' : '' ) ); ?>">
		<div>
			<?php if ( have_posts() ) : ?>
				<div class="archive-grid">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/content', 'card' );
					endwhile;
					?>
				</div>

				<?php
				the_posts_pagination(
					array(
						'mid_size'           => 1,
						'prev_text'          => __( 'Sebelumnya', 'queen-alfalah' ),
						'next_text'          => __( 'Berikutnya', 'queen-alfalah' ),
						'screen_reader_text' => __( 'Navigasi halaman galeri', 'queen-alfalah' ),
					)
				);
				?>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content', 'none' ); ?>
			<?php endif; ?>
		</div>

		<?php get_sidebar(); ?>
	</div>
</main>

<?php
get_footer();
