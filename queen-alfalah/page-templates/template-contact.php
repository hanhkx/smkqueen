<?php
/**
 * Template Name: Kontak Sekolah
 * Template Post Type: page
 *
 * Contact information and verified outbound channels. This template does not
 * process form submissions; a dedicated, maintained plugin should be used if
 * the school later needs a contact form.
 *
 * @package Queen_AlFalah
 */

get_header();
queen_alfalah_breadcrumbs();

$queen_address    = (string) queen_alfalah_school_info( 'address' );
$queen_phone      = (string) queen_alfalah_school_info( 'phone' );
$queen_phone_href = preg_replace( '/[^0-9+]/', '', $queen_phone );
$queen_email      = sanitize_email( queen_alfalah_school_info( 'email' ) );
$queen_hours      = (string) queen_alfalah_school_info( 'opening_hours' );
$queen_maps_url   = (string) queen_alfalah_school_info( 'maps_url' );
$queen_whatsapp   = (string) queen_alfalah_school_info( 'whatsapp' );
$queen_socials    = queen_alfalah_social_links();
?>

<main id="main-content" class="site-main">
	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>
		<header class="page-header">
			<div class="container">
				<p class="eyebrow"><?php esc_html_e( 'Hubungi Kami', 'queen-alfalah' ); ?></p>
				<h1><?php echo esc_html( get_the_title() ); ?></h1>
				<p><?php esc_html_e( 'Gunakan kanal resmi sekolah untuk pertanyaan akademik, layanan administrasi, kunjungan, dan pendaftaran.', 'queen-alfalah' ); ?></p>
			</div>
		</header>

		<?php if ( trim( get_the_content() ) ) : ?>
			<section class="section section--compact">
				<div class="container entry-content"><?php the_content(); ?></div>
			</section>
		<?php endif; ?>

		<section class="section section--cream" aria-labelledby="contact-details-title">
			<div class="container">
				<?php queen_alfalah_section_heading( __( 'Kontak Resmi', 'queen-alfalah' ), __( 'Kami siap membantu', 'queen-alfalah' ), __( 'Pastikan Anda menghubungi alamat atau nomor yang tercantum di halaman ini.', 'queen-alfalah' ), 'left', 'contact-details-title' ); ?>

				<address class="card-grid card-grid--3">
					<?php if ( $queen_address ) : ?>
						<section class="widget">
							<h2 class="widget-title"><?php echo queen_alfalah_icon( 'map-pin' ); ?><?php esc_html_e( 'Alamat', 'queen-alfalah' ); ?></h2>
							<p><?php echo esc_html( $queen_address ); ?></p>
							<?php if ( $queen_maps_url ) : ?>
								<a class="button button--outline" href="<?php echo esc_url( $queen_maps_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Buka Peta', 'queen-alfalah' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(tab baru)', 'queen-alfalah' ); ?></span></a>
							<?php endif; ?>
						</section>
					<?php endif; ?>

					<?php if ( $queen_phone || $queen_whatsapp ) : ?>
						<section class="widget">
							<h2 class="widget-title"><?php echo queen_alfalah_icon( 'phone' ); ?><?php esc_html_e( 'Telepon & WhatsApp', 'queen-alfalah' ); ?></h2>
							<?php if ( $queen_phone && $queen_phone_href ) : ?><p><a href="<?php echo esc_url( 'tel:' . $queen_phone_href ); ?>"><?php echo esc_html( $queen_phone ); ?></a></p><?php endif; ?>
							<?php if ( $queen_whatsapp ) : ?>
								<a class="button" href="<?php echo esc_url( queen_alfalah_whatsapp_url( 'Assalamu’alaikum, saya ingin meminta informasi dari SMK Queen Al-Falah.' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Chat WhatsApp', 'queen-alfalah' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(tab baru)', 'queen-alfalah' ); ?></span></a>
							<?php endif; ?>
						</section>
					<?php endif; ?>

					<?php if ( $queen_email || $queen_hours ) : ?>
						<section class="widget">
							<h2 class="widget-title"><?php echo queen_alfalah_icon( 'mail' ); ?><?php esc_html_e( 'Email & Jam Layanan', 'queen-alfalah' ); ?></h2>
							<?php if ( $queen_email ) : ?><p><a href="<?php echo esc_url( 'mailto:' . $queen_email ); ?>"><?php echo esc_html( $queen_email ); ?></a></p><?php endif; ?>
							<?php if ( $queen_hours ) : ?><p><?php echo queen_alfalah_icon( 'clock' ); ?><?php echo esc_html( $queen_hours ); ?></p><?php endif; ?>
						</section>
					<?php endif; ?>
				</address>

				<section class="contact-map" aria-labelledby="contact-map-title">
					<h2 id="contact-map-title"><?php esc_html_e( 'Temui Kami di Peta', 'queen-alfalah' ); ?></h2>
					<p><?php esc_html_e( 'Gunakan peta interaktif untuk melihat lokasi sekolah dan merencanakan rute kunjungan.', 'queen-alfalah' ); ?></p>
					<?php queen_alfalah_school_map( 'contact' ); ?>
				</section>

				<div class="notice">
					<p><strong><?php esc_html_e( 'Keamanan komunikasi:', 'queen-alfalah' ); ?></strong> <?php esc_html_e( 'Tema tidak mengirim atau menyimpan pesan formulir. Jangan mengirim NIK, NISN, kata sandi, data kesehatan, atau dokumen pribadi melalui kanal publik. Gunakan layanan resmi yang diarahkan petugas sekolah.', 'queen-alfalah' ); ?></p>
				</div>
			</div>
		</section>

		<?php if ( $queen_socials ) : ?>
			<section class="section section--compact" aria-labelledby="social-title">
				<div class="container">
					<h2 id="social-title"><?php esc_html_e( 'Media Sosial Resmi', 'queen-alfalah' ); ?></h2>
					<ul class="quick-links">
						<?php foreach ( $queen_socials as $queen_network => $queen_url ) : ?>
							<li><a href="<?php echo esc_url( $queen_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo queen_alfalah_icon( $queen_network ); ?><span><?php echo esc_html( ucfirst( $queen_network ) ); ?><span class="screen-reader-text"> <?php esc_html_e( '(tab baru)', 'queen-alfalah' ); ?></span></span></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</section>
		<?php endif; ?>
	<?php endwhile; ?>
</main>

<?php
get_footer();
