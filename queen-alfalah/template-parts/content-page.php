<?php
/**
 * Page content.
 *
 * @package Queen_AlFalah
 */
?>

<?php $queen_fallback_visual = has_post_thumbnail() ? array() : queen_alfalah_fallback_visual( get_the_ID() ); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-entry' ); ?>>
	<header class="entry-header">
		<p class="eyebrow"><?php esc_html_e( 'SMK Queen Al-Falah', 'queen-alfalah' ); ?></p>
		<h1 class="entry-title"><?php echo esc_html( get_the_title() ); ?></h1>
	</header>

	<?php if ( has_post_thumbnail() || $queen_fallback_visual ) : ?>
		<figure class="post-thumbnail">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'full', array( 'decoding' => 'async' ) ); ?>
			<?php else : ?>
				<img src="<?php echo esc_url( $queen_fallback_visual['url'] ); ?>" alt="" width="<?php echo esc_attr( $queen_fallback_visual['width'] ); ?>" height="<?php echo esc_attr( $queen_fallback_visual['height'] ); ?>" decoding="async">
				<figcaption class="post-thumbnail__caption post-thumbnail__caption--illustration">
					<strong><?php echo esc_html( $queen_fallback_visual['label'] ); ?>.</strong>
					<span><?php echo esc_html( $queen_fallback_visual['caption'] ); ?></span>
				</figcaption>
			<?php endif; ?>
		</figure>
	<?php endif; ?>

	<div class="entry-content">
		<?php
		the_content();
		wp_link_pages(
			array(
				'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Halaman konten', 'queen-alfalah' ) . '"><span>' . esc_html__( 'Halaman:', 'queen-alfalah' ) . '</span>',
				'after'  => '</nav>',
			)
		);
		?>
	</div>

	<?php if ( get_edit_post_link() ) : ?>
		<footer class="entry-footer">
			<?php
			edit_post_link(
				esc_html__( 'Sunting halaman', 'queen-alfalah' ),
				'<span class="edit-link">',
				'</span>'
			);
			?>
		</footer>
	<?php endif; ?>
</article>
