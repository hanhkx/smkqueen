<?php
/**
 * Centralized school application directory.
 *
 * @package Queen_AlFalah
 */

get_header();
queen_alfalah_breadcrumbs();

$applications = new WP_Query(
	array(
		'post_type'      => 'qaf_service',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		'no_found_rows'  => true,
	)
);
?>

<main id="main-content" class="site-main app-directory">
	<header class="app-directory__hero">
		<div class="container app-directory__hero-inner">
			<div>
				<p class="eyebrow"><?php esc_html_e( 'Layanan Digital Terpadu', 'queen-alfalah' ); ?></p>
				<h1><?php esc_html_e( 'Pusat Aplikasi SMK Queen Al-Falah', 'queen-alfalah' ); ?></h1>
				<p><?php esc_html_e( 'Satu pintu menuju layanan pembelajaran, penilaian, perpustakaan, penerimaan murid, dan gamifikasi edukasi.', 'queen-alfalah' ); ?></p>
			</div>
			<div class="app-directory__shield" aria-hidden="true"><?php echo queen_alfalah_icon( 'monitor' ); ?></div>
		</div>
	</header>

	<section class="section" aria-labelledby="application-list-title">
		<div class="container">
			<?php queen_alfalah_section_heading( __( 'Akses Satu Pintu', 'queen-alfalah' ), __( 'Pilih aplikasi yang ingin digunakan', 'queen-alfalah' ), __( 'Pastikan alamat situs sesuai dan jangan pernah membagikan kata sandi kepada siapa pun.', 'queen-alfalah' ), 'center', 'application-list-title' ); ?>

			<?php if ( $applications->have_posts() ) : ?>
				<div class="application-grid">
					<?php while ( $applications->have_posts() ) : $applications->the_post(); ?>
						<?php
						$app_id      = get_the_ID();
						$app_url     = (string) queen_alfalah_meta( $app_id, 'external_url' );
						$app_icon    = sanitize_key( queen_alfalah_meta( $app_id, 'icon_name', 'monitor' ) );
						$app_status  = sanitize_key( queen_alfalah_meta( $app_id, 'service_status', 'active' ) );
						$app_new_tab = (bool) queen_alfalah_meta( $app_id, 'open_new', true );
						$app_active  = $app_url && 'active' === $app_status;
						$app_excerpt = has_excerpt() ? get_the_excerpt() : wp_trim_words( wp_strip_all_tags( get_the_content() ), 22 );
						$status_text = 'maintenance' === $app_status ? __( 'Pemeliharaan', 'queen-alfalah' ) : ( $app_active ? __( 'Aktif', 'queen-alfalah' ) : __( 'Belum tersedia', 'queen-alfalah' ) );
						?>
						<article class="application-card<?php echo $app_active ? '' : ' application-card--inactive'; ?>">
							<div class="application-card__top">
								<span class="application-card__icon"><?php echo queen_alfalah_icon( $app_icon ); ?></span>
								<span class="application-card__status application-card__status--<?php echo esc_attr( $app_active ? 'active' : $app_status ); ?>"><?php echo esc_html( $status_text ); ?></span>
							</div>
							<h2><?php the_title(); ?></h2>
							<p><?php echo esc_html( $app_excerpt ); ?></p>
							<?php if ( $app_active ) : ?>
								<a class="button application-card__button" href="<?php echo esc_url( $app_url ); ?>"<?php echo $app_new_tab ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php esc_html_e( 'Masuk Aplikasi', 'queen-alfalah' ); ?><?php echo queen_alfalah_icon( $app_new_tab ? 'external' : 'arrow-right' ); ?><?php if ( $app_new_tab ) : ?><span class="screen-reader-text"> <?php esc_html_e( '(tab baru)', 'queen-alfalah' ); ?></span><?php endif; ?></a>
							<?php else : ?>
								<span class="button application-card__button application-card__button--disabled" aria-disabled="true"><?php echo esc_html( $status_text ); ?></span>
							<?php endif; ?>
						</article>
					<?php endwhile; ?>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<div class="notice"><p><?php esc_html_e( 'Daftar aplikasi sedang disiapkan oleh administrator sekolah.', 'queen-alfalah' ); ?></p></div>
			<?php endif; ?>
		</div>
	</section>

	<section class="section section--cream section--compact">
		<div class="container app-security-note">
			<?php echo queen_alfalah_icon( 'check' ); ?>
			<div><h2><?php esc_html_e( 'Akses aman dan resmi', 'queen-alfalah' ); ?></h2><p><?php esc_html_e( 'Gunakan hanya tombol pada halaman ini. Periksa domain tujuan sebelum memasukkan akun dan segera hubungi pengelola jika menemukan tautan mencurigakan.', 'queen-alfalah' ); ?></p></div>
		</div>
	</section>
</main>

<?php get_footer(); ?>
