<?php
/**
 * Purpose-built school portal homepage.
 *
 * @package Queen_AlFalah
 */

get_header();

$hero_image_id = absint( get_theme_mod( 'queen_hero_image', 0 ) );
$hero_title    = get_theme_mod( 'queen_hero_title', __( 'Berilmu. Terampil. Berakhlaqul Karimah.', 'queen-alfalah' ) );
$hero_text     = get_theme_mod( 'queen_hero_text', __( 'Menyiapkan generasi profesional, adaptif, dan berdaya saing melalui pendidikan vokasi yang terhubung dengan dunia kerja.', 'queen-alfalah' ) );
$program_url   = queen_alfalah_archive_url( 'qaf_program', 'program-keahlian' );
$news_page     = get_option( 'page_for_posts' );
$news_url      = $news_page ? get_permalink( $news_page ) : home_url( '/berita/' );
$landing_bg_id = absint( get_theme_mod( 'queen_landing_background', 0 ) );
$landing_bg    = $landing_bg_id ? wp_get_attachment_image_url( $landing_bg_id, 'full' ) : '';
$bg_mode       = queen_alfalah_sanitize_background_mode( get_theme_mod( 'queen_landing_bg_mode', 'cover' ) );
$bg_position   = queen_alfalah_sanitize_background_position( get_theme_mod( 'queen_landing_bg_position', 'center center' ) );
$bg_overlay    = queen_alfalah_sanitize_overlay( get_theme_mod( 'queen_landing_bg_overlay', 68 ) ) / 100;
$hero_classes  = array( 'hero' );
$hero_style    = '';

if ( $landing_bg ) {
	$hero_classes[] = 'hero--has-background';
	$hero_classes[] = 'hero--bg-' . sanitize_html_class( $bg_mode );
	if ( 'image/gif' === get_post_mime_type( $landing_bg_id ) ) {
		$hero_classes[] = 'hero--animated-background';
	}
	$hero_style = sprintf(
		'--queen-landing-bg:url("%1$s");--queen-landing-overlay:%2$s;--queen-landing-position:%3$s;',
		esc_url_raw( $landing_bg ),
		number_format( $bg_overlay, 2, '.', '' ),
		$bg_position
	);
}
?>

<main id="main-content" class="site-main home-main">
	<section class="<?php echo esc_attr( implode( ' ', $hero_classes ) ); ?>"<?php echo $hero_style ? ' style="' . esc_attr( $hero_style ) . '"' : ''; ?> aria-labelledby="hero-title">
		<div class="container hero__inner">
			<div class="hero__content" data-reveal>
				<p class="hero__eyebrow"><?php echo esc_html( get_theme_mod( 'queen_hero_kicker', __( 'Sekolah Vokasi Berbasis Pesantren', 'queen-alfalah' ) ) ); ?></p>
				<h1 id="hero-title" class="hero__title"><?php echo esc_html( $hero_title ); ?></h1>
				<p class="hero__lead"><?php echo esc_html( $hero_text ); ?></p>
				<div class="hero__actions">
					<a class="button" href="<?php echo esc_url( get_theme_mod( 'queen_hero_primary_url', queen_alfalah_school_info( 'ppdb_url' ) ) ); ?>" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( get_theme_mod( 'queen_hero_primary_label', __( 'Daftar Sekarang', 'queen-alfalah' ) ) ); ?><?php echo queen_alfalah_icon( 'arrow-right' ); ?>
					</a>
					<a class="button button--outline" href="<?php echo esc_url( get_theme_mod( 'queen_hero_secondary_url', $program_url ) ); ?>">
						<?php echo esc_html( get_theme_mod( 'queen_hero_secondary_label', __( 'Jelajahi Program', 'queen-alfalah' ) ) ); ?>
					</a>
				</div>
				<ul class="hero-facts" aria-label="<?php esc_attr_e( 'Identitas singkat sekolah', 'queen-alfalah' ); ?>">
					<li><?php echo queen_alfalah_icon( 'check' ); ?><?php echo esc_html( sprintf( __( 'NPSN %s', 'queen-alfalah' ), queen_alfalah_school_info( 'npsn' ) ) ); ?></li>
					<li><?php echo queen_alfalah_icon( 'check' ); ?><?php echo esc_html( sprintf( __( 'Akreditasi %s', 'queen-alfalah' ), queen_alfalah_school_info( 'accreditation' ) ) ); ?></li>
					<li><?php echo queen_alfalah_icon( 'check' ); ?><?php echo esc_html( sprintf( __( 'Berdiri %s', 'queen-alfalah' ), queen_alfalah_school_info( 'founded' ) ) ); ?></li>
				</ul>
			</div>

			<div class="hero__visual" data-reveal>
				<?php if ( $hero_image_id ) : ?>
					<?php echo wp_get_attachment_image( $hero_image_id, 'full', false, array( 'loading' => 'eager', 'fetchpriority' => 'high', 'alt' => queen_alfalah_school_info( 'school_name' ) ) ); ?>
				<?php else : ?>
					<img src="<?php echo esc_url( QUEEN_ALFALAH_URI . '/assets/images/hero-school.svg' ); ?>" width="760" height="620" loading="eager" fetchpriority="high" alt="<?php esc_attr_e( 'Ilustrasi sekolah vokasi SMK Queen Al-Falah', 'queen-alfalah' ); ?>">
				<?php endif; ?>
				<div class="hero__badge"><strong><?php esc_html_e( '4 Program Keahlian', 'queen-alfalah' ); ?></strong><br><?php esc_html_e( 'Teknologi, bisnis, desain, dan kesehatan', 'queen-alfalah' ); ?></div>
			</div>
		</div>
	</section>

	<section class="section section--compact quick-access" aria-labelledby="quick-access-title">
		<div class="container">
			<h2 id="quick-access-title" class="screen-reader-text"><?php esc_html_e( 'Akses cepat', 'queen-alfalah' ); ?></h2>
			<ul class="quick-links">
				<?php
				$service_query = post_type_exists( 'qaf_service' ) ? new WP_Query(
					array(
						'post_type'      => 'qaf_service',
						'posts_per_page' => 6,
						'post_status'    => 'publish',
						'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
						'no_found_rows'  => true,
					)
				) : null;

				if ( $service_query && $service_query->have_posts() ) :
					while ( $service_query->have_posts() ) :
						$service_query->the_post();
						$service_url  = queen_alfalah_meta( get_the_ID(), 'external_url', get_permalink() );
						$service_icon = queen_alfalah_meta( get_the_ID(), 'icon_name', 'external' );
						$new_tab      = (bool) queen_alfalah_meta( get_the_ID(), 'open_new', false );
						?>
						<li class="quick-links__item">
							<a href="<?php echo esc_url( $service_url ); ?>"<?php echo $new_tab ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
								<span class="quick-links__icon"><?php echo queen_alfalah_icon( $service_icon ); ?></span>
								<span><?php the_title(); ?></span>
							</a>
						</li>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<?php
					$quick_links = array(
						array( 'label' => __( 'Pendaftaran', 'queen-alfalah' ), 'url' => queen_alfalah_school_info( 'ppdb_url' ), 'icon' => 'book', 'external' => true ),
						array( 'label' => __( 'Program Keahlian', 'queen-alfalah' ), 'url' => $program_url, 'icon' => 'monitor', 'external' => false ),
						array( 'label' => __( 'Pengumuman', 'queen-alfalah' ), 'url' => queen_alfalah_archive_url( 'qaf_notice', 'pengumuman' ), 'icon' => 'calendar', 'external' => false ),
						array( 'label' => __( 'Agenda', 'queen-alfalah' ), 'url' => queen_alfalah_archive_url( 'qaf_agenda', 'agenda' ), 'icon' => 'clock', 'external' => false ),
						array( 'label' => __( 'Bursa Kerja', 'queen-alfalah' ), 'url' => queen_alfalah_archive_url( 'qaf_vacancy', 'lowongan' ), 'icon' => 'briefcase', 'external' => false ),
						array( 'label' => __( 'Kontak Sekolah', 'queen-alfalah' ), 'url' => queen_alfalah_page_url( 'kontak' ), 'icon' => 'phone', 'external' => false ),
					);
					foreach ( $quick_links as $link ) :
						?>
						<li class="quick-links__item"><a href="<?php echo esc_url( $link['url'] ); ?>"<?php echo $link['external'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><span class="quick-links__icon"><?php echo queen_alfalah_icon( $link['icon'] ); ?></span><span><?php echo esc_html( $link['label'] ); ?></span></a></li>
					<?php endforeach; ?>
				<?php endif; ?>
			</ul>
		</div>
	</section>

	<section class="section section--cream" aria-labelledby="principal-heading">
		<div class="container principal">
			<div class="principal__portrait" data-reveal>
				<?php
				$principal_photo = absint( get_theme_mod( 'queen_principal_photo', 0 ) );
				if ( $principal_photo ) {
					echo wp_get_attachment_image( $principal_photo, 'queen-person', false, array( 'loading' => 'lazy', 'alt' => queen_alfalah_school_info( 'principal_name' ) ) );
				} else {
					printf( '<img src="%1$s" width="640" height="760" loading="lazy" alt="%2$s">', esc_url( queen_alfalah_placeholder( 'person' ) ), esc_attr__( 'Placeholder foto kepala sekolah; silakan ganti melalui Customizer', 'queen-alfalah' ) );
				}
				?>
			</div>
			<div class="principal__content" data-reveal>
				<p class="eyebrow"><?php esc_html_e( 'Sambutan Kepala Sekolah', 'queen-alfalah' ); ?></p>
				<h2 id="principal-heading"><?php esc_html_e( 'Menumbuhkan kompetensi, menjaga nilai.', 'queen-alfalah' ); ?></h2>
				<blockquote class="principal__quote">“<?php echo esc_html( queen_alfalah_school_info( 'principal_message' ) ); ?>”</blockquote>
				<p class="principal__name"><?php echo esc_html( queen_alfalah_school_info( 'principal_name' ) ); ?></p>
				<p class="principal__role"><?php echo esc_html( queen_alfalah_school_info( 'principal_title' ) ); ?></p>
				<p><?php echo esc_html( sprintf( __( '%s memadukan pendidikan vokasi dengan pembiasaan nilai pesantren agar lulusan siap bekerja, melanjutkan studi, maupun berwirausaha.', 'queen-alfalah' ), queen_alfalah_school_info( 'school_name' ) ) ); ?></p>
				<a class="text-link" href="<?php echo esc_url( queen_alfalah_page_url( 'sambutan-kepala-sekolah' ) ); ?>"><?php esc_html_e( 'Baca sambutan lengkap', 'queen-alfalah' ); ?><?php echo queen_alfalah_icon( 'arrow-right' ); ?></a>
			</div>
		</div>
	</section>

	<section class="section" aria-labelledby="program-heading">
		<div class="container">
			<?php queen_alfalah_section_heading( __( 'Pilihan Kompetensi', 'queen-alfalah' ), __( 'Program Keahlian', 'queen-alfalah' ), __( 'Empat jalur belajar vokasi untuk menyiapkan kompetensi yang relevan dengan dunia kerja dan kehidupan.', 'queen-alfalah' ), 'left', 'program-heading' ); ?>
			<div class="card-grid card-grid--4">
				<?php
				$program_query = post_type_exists( 'qaf_program' ) ? new WP_Query(
					array(
						'post_type'      => 'qaf_program',
						'posts_per_page' => 4,
						'post_status'    => 'publish',
						'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
						'no_found_rows'  => true,
					)
				) : null;

				if ( $program_query && $program_query->have_posts() ) :
					while ( $program_query->have_posts() ) :
						$program_query->the_post();
						$gender = queen_alfalah_meta( get_the_ID(), 'program_gender' );
						?>
						<article <?php post_class( 'program-card' ); ?>>
							<a class="program-card__media" href="<?php the_permalink(); ?>">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'queen-card-contain', array( 'loading' => 'lazy' ) ); ?>
								<?php else : ?>
									<img src="<?php echo esc_url( queen_alfalah_placeholder( 'program' ) ); ?>" width="900" height="620" loading="lazy" alt="">
									<span class="visual-disclosure"><?php esc_html_e( 'Ilustrasi', 'queen-alfalah' ); ?></span>
								<?php endif; ?>
								<?php if ( $gender ) : ?><span class="program-card__label"><?php echo esc_html( $gender ); ?></span><?php endif; ?>
							</a>
							<div class="program-card__content">
								<?php if ( queen_alfalah_meta( get_the_ID(), 'program_code' ) ) : ?><p class="program-code"><?php echo esc_html( queen_alfalah_meta( get_the_ID(), 'program_code' ) ); ?></p><?php endif; ?>
								<h3 class="program-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<p class="program-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
								<a class="program-card__link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Lihat program', 'queen-alfalah' ); ?></a>
							</div>
						</article>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<?php
					$fallback_programs = array(
						array( 'code' => 'TJKT', 'name' => __( 'Teknik Jaringan Komputer dan Telekomunikasi', 'queen-alfalah' ), 'gender' => __( 'Putra', 'queen-alfalah' ), 'icon' => 'monitor', 'text' => __( 'Jaringan, perangkat komputer, layanan telekomunikasi, dan administrasi sistem.', 'queen-alfalah' ) ),
						array( 'code' => 'MPLB', 'name' => __( 'Manajemen Perkantoran dan Layanan Bisnis', 'queen-alfalah' ), 'gender' => __( 'Putri', 'queen-alfalah' ), 'icon' => 'briefcase', 'text' => __( 'Administrasi modern, layanan bisnis, komunikasi, dan pengelolaan dokumen digital.', 'queen-alfalah' ) ),
						array( 'code' => 'DKV', 'name' => __( 'Desain Komunikasi Visual', 'queen-alfalah' ), 'gender' => __( 'Putra & Putri', 'queen-alfalah' ), 'icon' => 'palette', 'text' => __( 'Desain grafis, fotografi, video, animasi, dan produksi media kreatif.', 'queen-alfalah' ) ),
						array( 'code' => 'LK', 'name' => __( 'Layanan Kesehatan', 'queen-alfalah' ), 'gender' => __( 'Putri', 'queen-alfalah' ), 'icon' => 'heart', 'text' => __( 'Keterampilan dasar layanan kesehatan dengan sikap empatik dan profesional.', 'queen-alfalah' ) ),
					);
					foreach ( $fallback_programs as $program ) :
						?>
						<article class="program-card program-card--fallback">
							<div class="program-card__content">
								<span class="program-card__icon"><?php echo queen_alfalah_icon( $program['icon'] ); ?></span>
								<p class="program-code"><?php echo esc_html( $program['code'] ); ?> · <?php echo esc_html( $program['gender'] ); ?></p>
								<h3 class="program-card__title"><a href="<?php echo esc_url( $program_url ); ?>"><?php echo esc_html( $program['name'] ); ?></a></h3>
								<p class="program-card__excerpt"><?php echo esc_html( $program['text'] ); ?></p>
								<a class="program-card__link" href="<?php echo esc_url( $program_url ); ?>"><?php esc_html_e( 'Lihat program', 'queen-alfalah' ); ?></a>
							</div>
						</article>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<div class="section-actions"><a class="button button--outline-dark" href="<?php echo esc_url( $program_url ); ?>"><?php esc_html_e( 'Semua program keahlian', 'queen-alfalah' ); ?><?php echo queen_alfalah_icon( 'arrow-right' ); ?></a></div>
		</div>
	</section>

	<section class="section section--cream" aria-labelledby="information-heading">
		<div class="container">
			<?php queen_alfalah_section_heading( __( 'Tetap Terhubung', 'queen-alfalah' ), __( 'Pengumuman & Agenda', 'queen-alfalah' ), __( 'Informasi penting dan jadwal kegiatan sekolah dalam satu tempat.', 'queen-alfalah' ), 'left', 'information-heading' ); ?>
			<div class="info-panels">
				<section class="info-panel card" aria-labelledby="notice-heading">
					<div class="info-panel__header"><h3 id="notice-heading"><?php esc_html_e( 'Pengumuman Terkini', 'queen-alfalah' ); ?></h3><a href="<?php echo esc_url( queen_alfalah_archive_url( 'qaf_notice', 'pengumuman' ) ); ?>"><?php esc_html_e( 'Lihat semua', 'queen-alfalah' ); ?></a></div>
					<?php
					$notice_home_query = post_type_exists( 'qaf_notice' ) ? new WP_Query(
						array(
							'post_type'      => 'qaf_notice',
							'posts_per_page' => 3,
							'post_status'    => 'publish',
							'no_found_rows'  => true,
							'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
						)
					) : null;
					?>
					<?php if ( $notice_home_query && $notice_home_query->have_posts() ) : ?>
						<ul class="notice-list">
							<?php while ( $notice_home_query->have_posts() ) : $notice_home_query->the_post(); ?>
								<li><time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( get_the_date( 'd M Y' ) ); ?></time><h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4><p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 14 ) ); ?></p></li>
							<?php endwhile; ?>
						</ul>
						<?php wp_reset_postdata(); ?>
					<?php else : ?>
						<div class="empty-state empty-state--small"><p><?php esc_html_e( 'Belum ada pengumuman yang diterbitkan.', 'queen-alfalah' ); ?></p></div>
					<?php endif; ?>
				</section>

				<section class="info-panel card" aria-labelledby="agenda-heading">
					<div class="info-panel__header"><h3 id="agenda-heading"><?php esc_html_e( 'Agenda Terdekat', 'queen-alfalah' ); ?></h3><a href="<?php echo esc_url( queen_alfalah_archive_url( 'qaf_agenda', 'agenda' ) ); ?>"><?php esc_html_e( 'Lihat semua', 'queen-alfalah' ); ?></a></div>
					<?php
					$agenda_query = post_type_exists( 'qaf_agenda' ) ? new WP_Query(
						array(
							'post_type'      => 'qaf_agenda',
							'posts_per_page' => 4,
							'post_status'    => 'publish',
							'no_found_rows'  => true,
							'meta_key'       => '_qaf_start_date',
							'orderby'        => 'meta_value',
							'order'          => 'ASC',
						)
					) : null;
					?>
					<?php if ( $agenda_query && $agenda_query->have_posts() ) : ?>
						<ul class="agenda-list">
							<?php while ( $agenda_query->have_posts() ) : $agenda_query->the_post(); ?>
								<?php $agenda_stamp = strtotime( queen_alfalah_meta( get_the_ID(), 'start_date', get_the_date( 'Y-m-d' ) ) ); ?>
								<li class="agenda-list__item">
									<time class="agenda-list__date" datetime="<?php echo esc_attr( wp_date( 'c', $agenda_stamp ) ); ?>"><span class="agenda-list__day"><?php echo esc_html( wp_date( 'd', $agenda_stamp ) ); ?></span><span class="agenda-list__month"><?php echo esc_html( wp_date( 'M', $agenda_stamp ) ); ?></span></time>
									<div class="agenda-list__content"><h4 class="agenda-list__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4><p class="agenda-list__meta"><?php echo queen_alfalah_icon( 'map-pin' ); ?><?php echo esc_html( queen_alfalah_meta( get_the_ID(), 'location', __( 'SMK Queen Al-Falah', 'queen-alfalah' ) ) ); ?></p></div>
								</li>
							<?php endwhile; ?>
						</ul>
						<?php wp_reset_postdata(); ?>
					<?php else : ?>
						<div class="empty-state empty-state--small"><p><?php esc_html_e( 'Belum ada agenda yang dijadwalkan.', 'queen-alfalah' ); ?></p></div>
					<?php endif; ?>
				</section>
			</div>
		</div>
	</section>

	<section class="section" aria-labelledby="news-heading">
		<div class="container">
			<?php queen_alfalah_section_heading( __( 'Kabar Queen', 'queen-alfalah' ), __( 'Berita Terbaru', 'queen-alfalah' ), __( 'Cerita kegiatan, pembelajaran, karya, dan pencapaian warga sekolah.', 'queen-alfalah' ), 'left', 'news-heading' ); ?>
			<div class="card-grid card-grid--3">
				<?php
				$news_query = new WP_Query(
					array(
						'post_type'           => 'post',
						'posts_per_page'      => 3,
						'post_status'         => 'publish',
						'ignore_sticky_posts' => true,
						'no_found_rows'       => true,
					)
				);
				if ( $news_query->have_posts() ) :
					while ( $news_query->have_posts() ) : $news_query->the_post();
						get_template_part( 'template-parts/content', 'card' );
					endwhile;
					wp_reset_postdata();
				else :
					?>
					<div class="empty-state card-grid__full"><h3><?php esc_html_e( 'Kabar sekolah segera hadir', 'queen-alfalah' ); ?></h3><p><?php esc_html_e( 'Artikel yang telah diterbitkan akan muncul otomatis di bagian ini.', 'queen-alfalah' ); ?></p><?php if ( current_user_can( 'edit_posts' ) ) : ?><a class="button button--small" href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>"><?php esc_html_e( 'Tulis berita', 'queen-alfalah' ); ?></a><?php endif; ?></div>
				<?php endif; ?>
			</div>
			<div class="section-actions"><a class="button button--outline-dark" href="<?php echo esc_url( $news_url ); ?>"><?php esc_html_e( 'Lihat semua berita', 'queen-alfalah' ); ?><?php echo queen_alfalah_icon( 'arrow-right' ); ?></a></div>
		</div>
	</section>

	<section class="section section--emerald" aria-labelledby="stats-heading">
		<div class="container stats-section">
			<div class="stats-section__intro">
				<p class="eyebrow"><?php esc_html_e( 'Identitas Sekolah', 'queen-alfalah' ); ?></p>
				<h2 id="stats-heading"><?php esc_html_e( 'Queen Al-Falah dalam data', 'queen-alfalah' ); ?></h2>
				<p><?php echo esc_html( queen_alfalah_school_info( 'vision' ) ); ?></p>
				<?php if ( get_theme_mod( 'queen_stats_updated' ) ) : ?><small><?php echo esc_html( get_theme_mod( 'queen_stats_updated' ) ); ?></small><?php endif; ?>
			</div>
			<div class="stats-grid">
				<?php
				$stats = array(
					array( get_theme_mod( 'queen_stat_1_value', '4' ), get_theme_mod( 'queen_stat_1_label', __( 'Program Keahlian', 'queen-alfalah' ) ) ),
					array( get_theme_mod( 'queen_stat_2_value', '2011' ), get_theme_mod( 'queen_stat_2_label', __( 'Tahun Berdiri', 'queen-alfalah' ) ) ),
					array( get_theme_mod( 'queen_stat_3_value', 'B' ), get_theme_mod( 'queen_stat_3_label', __( 'Akreditasi', 'queen-alfalah' ) ) ),
					array( get_theme_mod( 'queen_stat_4_value', '20574699' ), get_theme_mod( 'queen_stat_4_label', __( 'NPSN', 'queen-alfalah' ) ) ),
				);
				foreach ( $stats as $stat ) :
					?>
					<div class="stat-card"><strong class="stat-card__number"><?php echo esc_html( $stat[0] ); ?></strong><small class="stat-card__label"><?php echo esc_html( $stat[1] ); ?></small></div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="section section--cream" aria-labelledby="career-heading">
		<div class="container">
			<?php queen_alfalah_section_heading( __( 'Dari Sekolah ke Dunia Nyata', 'queen-alfalah' ), __( 'Jalur Pengembangan Karier', 'queen-alfalah' ), __( 'Pembelajaran vokasi dirancang untuk menghubungkan keterampilan, pengalaman industri, dan rencana masa depan.', 'queen-alfalah' ), 'left', 'career-heading' ); ?>
			<div class="card-grid card-grid--3 journey-grid">
				<article class="card journey-card"><span class="journey-card__icon"><?php echo queen_alfalah_icon( 'building' ); ?></span><p class="eyebrow"><?php esc_html_e( 'Pengalaman Kerja', 'queen-alfalah' ); ?></p><h3><?php esc_html_e( 'PKL & Mitra Industri', 'queen-alfalah' ); ?></h3><p><?php esc_html_e( 'Mengenal budaya kerja, menerapkan kompetensi, dan membangun jejaring profesional sejak sekolah.', 'queen-alfalah' ); ?></p><a class="text-link" href="<?php echo esc_url( queen_alfalah_archive_url( 'qaf_partner', 'mitra-industri' ) ); ?>"><?php esc_html_e( 'Jelajahi kemitraan', 'queen-alfalah' ); ?><?php echo queen_alfalah_icon( 'arrow-right' ); ?></a></article>
				<article class="card journey-card"><span class="journey-card__icon"><?php echo queen_alfalah_icon( 'award' ); ?></span><p class="eyebrow"><?php esc_html_e( 'Bukti Kompetensi', 'queen-alfalah' ); ?></p><h3><?php esc_html_e( 'Sertifikasi & Prestasi', 'queen-alfalah' ); ?></h3><p><?php esc_html_e( 'Menguatkan portofolio melalui asesmen kompetensi, karya nyata, lomba, dan proyek kolaboratif.', 'queen-alfalah' ); ?></p><a class="text-link" href="<?php echo esc_url( queen_alfalah_archive_url( 'qaf_achievement', 'prestasi' ) ); ?>"><?php esc_html_e( 'Lihat prestasi', 'queen-alfalah' ); ?><?php echo queen_alfalah_icon( 'arrow-right' ); ?></a></article>
				<article class="card journey-card"><span class="journey-card__icon"><?php echo queen_alfalah_icon( 'briefcase' ); ?></span><p class="eyebrow"><?php esc_html_e( 'Setelah Lulus', 'queen-alfalah' ); ?></p><h3><?php esc_html_e( 'BKK, Alumni & Lowongan', 'queen-alfalah' ); ?></h3><p><?php esc_html_e( 'Informasi peluang kerja dan jejaring alumni untuk membantu lulusan mengambil langkah berikutnya.', 'queen-alfalah' ); ?></p><a class="text-link" href="<?php echo esc_url( queen_alfalah_archive_url( 'qaf_vacancy', 'lowongan' ) ); ?>"><?php esc_html_e( 'Cari lowongan', 'queen-alfalah' ); ?><?php echo queen_alfalah_icon( 'arrow-right' ); ?></a></article>
			</div>
		</div>
	</section>

	<section class="section" aria-labelledby="extra-heading">
		<div class="container">
			<?php queen_alfalah_section_heading( __( 'Tumbuh Bersama', 'queen-alfalah' ), __( 'Ekstrakurikuler', 'queen-alfalah' ), __( 'Ruang untuk mengasah kepemimpinan, kreativitas, sportivitas, keterampilan, dan khidmah.', 'queen-alfalah' ), 'left', 'extra-heading' ); ?>
			<div class="extra-grid">
				<?php
				$extra_query = post_type_exists( 'qaf_extra' ) ? new WP_Query(
					array( 'post_type' => 'qaf_extra', 'posts_per_page' => 7, 'post_status' => 'publish', 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ), 'no_found_rows' => true )
				) : null;
				if ( $extra_query && $extra_query->have_posts() ) :
					while ( $extra_query->have_posts() ) : $extra_query->the_post(); ?>
						<a class="extra-chip" href="<?php the_permalink(); ?>"><?php echo queen_alfalah_icon( 'users' ); ?><span><?php the_title(); ?></span></a>
					<?php endwhile; wp_reset_postdata(); ?>
				<?php else : ?>
					<?php foreach ( array( 'Pramuka', 'Broadcasting', 'Futsal', 'Al Banjari', 'Tenis Meja', 'Bola Voli', 'Desain Web' ) as $activity ) : ?>
						<a class="extra-chip" href="<?php echo esc_url( queen_alfalah_archive_url( 'qaf_extra', 'ekstrakurikuler' ) ); ?>"><?php echo queen_alfalah_icon( 'users' ); ?><span><?php echo esc_html( $activity ); ?></span></a>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<div class="section-actions"><a class="button button--outline-dark" href="<?php echo esc_url( queen_alfalah_archive_url( 'qaf_extra', 'ekstrakurikuler' ) ); ?>"><?php esc_html_e( 'Semua ekstrakurikuler', 'queen-alfalah' ); ?></a></div>
		</div>
	</section>

	<section class="section section--cream" aria-labelledby="gallery-heading">
		<div class="container">
			<?php queen_alfalah_section_heading( __( 'Momen di Queen', 'queen-alfalah' ), __( 'Galeri Kegiatan', 'queen-alfalah' ), __( 'Dokumentasi pembelajaran, karya, kegiatan pesantren, dan kebersamaan warga sekolah.', 'queen-alfalah' ), 'left', 'gallery-heading' ); ?>
			<?php
			$gallery_query = post_type_exists( 'qaf_gallery' ) ? new WP_Query(
				array( 'post_type' => 'qaf_gallery', 'posts_per_page' => 6, 'post_status' => 'publish', 'no_found_rows' => true )
			) : null;
			?>
			<div class="gallery-grid">
				<?php if ( $gallery_query && $gallery_query->have_posts() ) : ?>
					<?php while ( $gallery_query->have_posts() ) : $gallery_query->the_post(); ?>
						<a href="<?php the_permalink(); ?>">
							<?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'queen-card-square', array( 'loading' => 'lazy', 'alt' => get_the_title() ) ); else : ?><img src="<?php echo esc_url( queen_alfalah_placeholder( 'gallery' ) ); ?>" width="1200" height="800" loading="lazy" alt=""><span class="visual-disclosure"><?php esc_html_e( 'Ilustrasi', 'queen-alfalah' ); ?></span><?php endif; ?>
							<span class="gallery-caption">
								<span><?php the_title(); ?></span>
								<small class="gallery-caption__source"><?php echo esc_html( queen_alfalah_gallery_source_label( get_the_ID() ) ); ?></small>
							</span>
						</a>
					<?php endwhile; wp_reset_postdata(); ?>
				<?php else : ?>
					<a class="gallery-placeholder" href="<?php echo esc_url( queen_alfalah_archive_url( 'qaf_gallery', 'galeri' ) ); ?>"><img src="<?php echo esc_url( queen_alfalah_placeholder( 'gallery' ) ); ?>" width="1200" height="800" loading="lazy" alt=""><span class="visual-disclosure"><?php esc_html_e( 'Ilustrasi', 'queen-alfalah' ); ?></span><span class="gallery-caption"><?php esc_html_e( 'Galeri sekolah akan tampil di sini', 'queen-alfalah' ); ?></span></a>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="section section--compact" aria-labelledby="ppdb-cta-heading">
		<div class="container">
			<div class="cta-banner">
				<div><p class="eyebrow"><?php esc_html_e( 'Mulai Perjalananmu', 'queen-alfalah' ); ?></p><h2 id="ppdb-cta-heading"><?php esc_html_e( 'Siap menjadi bagian dari Queen Al-Falah?', 'queen-alfalah' ); ?></h2><p><?php esc_html_e( 'Pelajari program keahlian, siapkan persyaratan, dan hubungi panitia bila membutuhkan bantuan memilih jalur pendidikan.', 'queen-alfalah' ); ?></p></div>
				<div class="cta-banner__actions"><a class="button" href="<?php echo esc_url( queen_alfalah_school_info( 'ppdb_url' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( queen_alfalah_school_info( 'ppdb_label' ) ); ?><?php echo queen_alfalah_icon( 'arrow-right' ); ?></a><?php if ( queen_alfalah_school_info( 'whatsapp' ) ) : ?><a class="button button--outline" href="<?php echo esc_url( queen_alfalah_whatsapp_url( 'Assalamu’alaikum, saya ingin berkonsultasi tentang pendaftaran SMK Queen Al-Falah.' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Konsultasi WhatsApp', 'queen-alfalah' ); ?></a><?php endif; ?></div>
			</div>
		</div>
	</section>

	<section class="section section--emerald contact-section" aria-labelledby="contact-heading">
		<div class="container contact-panel">
			<div class="contact-panel__content">
				<p class="eyebrow"><?php esc_html_e( 'Temui Kami', 'queen-alfalah' ); ?></p>
				<h2 id="contact-heading"><?php esc_html_e( 'Informasi & Kontak Sekolah', 'queen-alfalah' ); ?></h2>
				<ul class="contact-list">
					<li><?php echo queen_alfalah_icon( 'map-pin' ); ?><span><?php echo esc_html( queen_alfalah_school_info( 'address' ) ); ?></span></li>
					<li><?php echo queen_alfalah_icon( 'phone' ); ?><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', queen_alfalah_school_info( 'phone' ) ) ); ?>"><?php echo esc_html( queen_alfalah_school_info( 'phone' ) ); ?></a></li>
					<li><?php echo queen_alfalah_icon( 'mail' ); ?><a href="mailto:<?php echo esc_attr( antispambot( queen_alfalah_school_info( 'email' ) ) ); ?>"><?php echo esc_html( antispambot( queen_alfalah_school_info( 'email' ) ) ); ?></a></li>
					<li><?php echo queen_alfalah_icon( 'clock' ); ?><span><?php echo esc_html( queen_alfalah_school_info( 'opening_hours' ) ); ?></span></li>
				</ul>
				<a class="button" href="<?php echo esc_url( queen_alfalah_page_url( 'kontak' ) ); ?>"><?php esc_html_e( 'Buka halaman kontak', 'queen-alfalah' ); ?><?php echo queen_alfalah_icon( 'arrow-right' ); ?></a>
			</div>
			<?php queen_alfalah_school_map( 'home' ); ?>
		</div>
	</section>
</main>

<?php
get_footer();
