<?php
/** Public student work catalogue with native taxonomy navigation and pagination. */
get_header();
queen_alfalah_breadcrumbs();
$queen_work_terms = get_terms( array( 'taxonomy' => 'qaf_work_type', 'hide_empty' => false ) );
$queen_work_current = is_tax( 'qaf_work_type' ) ? get_queried_object_id() : 0;
?>
<main id="main-content" class="site-main">
	<header class="archive-header"><div class="container">
		<p class="eyebrow"><?php esc_html_e( 'Kesiswaan · Ruang Kreativitas', 'queen-alfalah' ); ?></p>
		<h1><?php esc_html_e( 'Karya Siswa SMK Queen Al-Falah', 'queen-alfalah' ); ?></h1>
		<p><?php esc_html_e( 'Esai, artikel, film pendek, dan portofolio: ruang apresiasi proses belajar serta kreativitas siswa. Kredit pembuat dan capaian dicantumkan berdasarkan sumber yang terkonfirmasi.', 'queen-alfalah' ); ?></p>
		<nav class="gallery-source-filters" aria-label="<?php esc_attr_e( 'Jenis karya siswa', 'queen-alfalah' ); ?>">
			<a href="<?php echo esc_url( get_post_type_archive_link( 'qaf_work' ) ); ?>"<?php echo ! $queen_work_current ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'Semua Karya', 'queen-alfalah' ); ?></a>
			<?php if ( ! is_wp_error( $queen_work_terms ) ) : foreach ( $queen_work_terms as $queen_work_term ) :
				$queen_work_url = get_term_link( $queen_work_term );
				if ( is_wp_error( $queen_work_url ) ) { continue; }
			?>
			<a href="<?php echo esc_url( $queen_work_url ); ?>"<?php echo (int) $queen_work_current === (int) $queen_work_term->term_id ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $queen_work_term->name ); ?></a>
			<?php endforeach; endif; ?>
		</nav>
	</div></header>
	<div class="container section">
		<?php if ( have_posts() ) : ?>
		<div class="archive-grid"><?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/content', 'card' ); endwhile; ?></div>
		<?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => __( 'Sebelumnya', 'queen-alfalah' ), 'next_text' => __( 'Berikutnya', 'queen-alfalah' ) ) ); ?>
		<?php else : ?><p><?php esc_html_e( 'Karya untuk kategori ini sedang disiapkan. Jelajahi kategori lainnya untuk melihat karya yang sudah diterbitkan.', 'queen-alfalah' ); ?></p><?php endif; ?>
	</div>
</main>
<?php get_footer();
