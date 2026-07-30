<?php
/**
 * Card used by post, search, and custom post type archives.
 *
 * @package Queen_AlFalah
 */

$queen_post_type      = get_post_type();
$queen_type_object    = get_post_type_object( $queen_post_type );
$queen_is_program     = 'qaf_program' === $queen_post_type;
$queen_is_achievement = 'qaf_achievement' === $queen_post_type;
$queen_is_gallery     = 'qaf_gallery' === $queen_post_type;
$queen_card_class     = $queen_is_program ? 'program-card' : 'news-card';
$queen_article_class  = $queen_card_class . ( $queen_is_achievement ? ' achievement-card' : '' );
$queen_image_size     = $queen_is_program ? 'queen-program' : 'queen-card';
$queen_placeholder    = 'default';

if ( in_array( $queen_post_type, array( 'qaf_teacher', 'qaf_alumni' ), true ) ) {
	$queen_placeholder = 'person';
} elseif ( 'qaf_program' === $queen_post_type ) {
	$queen_placeholder = 'program';
} elseif ( 'qaf_gallery' === $queen_post_type ) {
	$queen_placeholder = 'gallery';
}

$queen_card_label = $queen_type_object ? $queen_type_object->labels->singular_name : __( 'Informasi', 'queen-alfalah' );

if ( 'post' === $queen_post_type ) {
	$queen_categories = get_the_category();
	if ( $queen_categories ) {
		$queen_card_label = $queen_categories[0]->name;
	}
} else {
	$queen_taxonomies = get_object_taxonomies( $queen_post_type, 'names' );
	foreach ( $queen_taxonomies as $queen_taxonomy ) {
		$queen_terms = get_the_terms( get_the_ID(), $queen_taxonomy );
		if ( $queen_terms && ! is_wp_error( $queen_terms ) ) {
			$queen_card_label = $queen_terms[0]->name;
			break;
		}
	}
}

if ( $queen_is_gallery ) {
	$queen_gallery_source = queen_alfalah_gallery_source( get_the_ID() );
	$queen_card_label     = queen_alfalah_gallery_source_label( get_the_ID() );
	$queen_article_class .= ' gallery-card gallery-card--' . sanitize_html_class( $queen_gallery_source );
}

$queen_compact_meta = array(
	'qaf_program'     => array( 'program_code', __( 'Kode program', 'queen-alfalah' ) ),
	'qaf_teacher'     => array( 'role', __( 'Jabatan', 'queen-alfalah' ) ),
	'qaf_notice'      => array( 'priority', __( 'Prioritas', 'queen-alfalah' ) ),
	'qaf_agenda'      => array( 'start_date', __( 'Waktu', 'queen-alfalah' ) ),
	'qaf_extra'       => array( 'schedule', __( 'Jadwal', 'queen-alfalah' ) ),
	'qaf_gallery'     => array( 'album_date', __( 'Tanggal dokumentasi', 'queen-alfalah' ) ),
	'qaf_partner'     => array( 'partner_sector', __( 'Sektor', 'queen-alfalah' ) ),
	'qaf_vacancy'     => array( 'deadline', __( 'Batas lamaran', 'queen-alfalah' ) ),
	'qaf_alumni'      => array( 'graduation_year', __( 'Tahun lulus', 'queen-alfalah' ) ),
	'qaf_facility'    => array( 'capacity', __( 'Kapasitas/jumlah', 'queen-alfalah' ) ),
);

$queen_meta_value = '';
$queen_meta_label = '';
$queen_achievement_summary = array();
$queen_extra_illustration = 'qaf_extra' === $queen_post_type ? queen_alfalah_extra_illustration( get_the_ID() ) : '';

if ( $queen_is_achievement ) {
	$queen_achievement_award = queen_alfalah_meta( get_the_ID(), 'award' );
	$queen_achievement_level = queen_alfalah_meta( get_the_ID(), 'level' );
	$queen_achievement_date  = queen_alfalah_meta( get_the_ID(), 'achievement_date' );

	if ( $queen_achievement_award ) {
		$queen_achievement_summary['award'] = $queen_achievement_award;
	}

	if ( $queen_achievement_level ) {
		$queen_achievement_summary['level'] = $queen_achievement_level;
	}

	if ( $queen_achievement_date ) {
		$queen_date = DateTimeImmutable::createFromFormat( 'Y-m-d', $queen_achievement_date, wp_timezone() );
		$queen_achievement_summary['date'] = array(
			'raw'       => $queen_achievement_date,
			'formatted' => $queen_date
				? wp_date( get_option( 'date_format' ), $queen_date->getTimestamp(), wp_timezone() )
				: $queen_achievement_date,
		);
	}
} elseif ( isset( $queen_compact_meta[ $queen_post_type ] ) ) {
	$queen_meta_key   = $queen_compact_meta[ $queen_post_type ][0];
	$queen_meta_label = $queen_compact_meta[ $queen_post_type ][1];
	$queen_meta_value = queen_alfalah_meta( get_the_ID(), $queen_meta_key );

	if ( $queen_meta_value && in_array( $queen_meta_key, array( 'start_date', 'deadline', 'album_date' ), true ) ) {
		$queen_date_format = 'start_date' === $queen_meta_key ? 'Y-m-d\TH:i' : 'Y-m-d';
		$queen_date        = DateTimeImmutable::createFromFormat( $queen_date_format, $queen_meta_value, wp_timezone() );
		if ( $queen_date ) {
			$queen_meta_value = wp_date(
				'start_date' === $queen_meta_key ? get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) : get_option( 'date_format' ),
				$queen_date->getTimestamp(),
				wp_timezone()
			);
		}
	} elseif ( 'priority' === $queen_meta_key ) {
		$queen_priorities = array(
			'normal'   => __( 'Normal', 'queen-alfalah' ),
			'penting'  => __( 'Penting', 'queen-alfalah' ),
			'mendesak' => __( 'Mendesak', 'queen-alfalah' ),
		);
		$queen_meta_value = isset( $queen_priorities[ $queen_meta_value ] ) ? $queen_priorities[ $queen_meta_value ] : $queen_meta_value;
	}
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $queen_article_class ); ?>>
	<a class="<?php echo esc_attr( $queen_card_class . '__media' ); ?>" href="<?php echo esc_url( get_permalink() ); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( $queen_image_size, array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
		<?php elseif ( $queen_extra_illustration ) : ?>
			<img src="<?php echo esc_url( $queen_extra_illustration ); ?>" alt="" width="1200" height="800" loading="lazy" decoding="async">
		<?php else : ?>
			<img src="<?php echo esc_url( queen_alfalah_placeholder( $queen_placeholder ) ); ?>" alt="" width="720" height="480" loading="lazy" decoding="async">
		<?php endif; ?>
		<span class="<?php echo esc_attr( $queen_card_class . ( $queen_is_program ? '__label' : '__category' ) ); ?>"><?php echo esc_html( $queen_card_label ); ?></span>
	</a>

	<div class="<?php echo esc_attr( $queen_card_class . '__content' ); ?>">
		<div class="<?php echo esc_attr( $queen_card_class . '__meta' . ( $queen_is_achievement ? ' achievement-card__meta' : '' ) ); ?>">
			<?php if ( $queen_is_achievement && $queen_achievement_summary ) : ?>
				<?php if ( isset( $queen_achievement_summary['award'] ) ) : ?>
					<strong class="achievement-card__award">
						<span class="screen-reader-text"><?php esc_html_e( 'Juara atau penghargaan: ', 'queen-alfalah' ); ?></span>
						<?php echo esc_html( $queen_achievement_summary['award'] ); ?>
					</strong>
				<?php endif; ?>
				<?php if ( isset( $queen_achievement_summary['level'] ) ) : ?>
					<span class="achievement-card__scope">
						<span class="screen-reader-text"><?php esc_html_e( 'Ruang lingkup: ', 'queen-alfalah' ); ?></span>
						<?php echo esc_html( $queen_achievement_summary['level'] ); ?>
					</span>
				<?php endif; ?>
				<?php if ( isset( $queen_achievement_summary['date'] ) ) : ?>
					<time class="achievement-card__date" datetime="<?php echo esc_attr( $queen_achievement_summary['date']['raw'] ); ?>">
						<span class="screen-reader-text"><?php esc_html_e( 'Tanggal prestasi: ', 'queen-alfalah' ); ?></span>
						<?php echo esc_html( $queen_achievement_summary['date']['formatted'] ); ?>
					</time>
				<?php endif; ?>
			<?php elseif ( $queen_meta_value ) : ?>
				<span><span class="screen-reader-text"><?php echo esc_html( $queen_meta_label . ': ' ); ?></span><?php echo esc_html( $queen_meta_value ); ?></span>
			<?php else : ?>
				<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			<?php endif; ?>
		</div>

		<h2 class="<?php echo esc_attr( $queen_card_class . '__title' ); ?>"><a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a></h2>

		<?php if ( has_excerpt() || get_the_content() ) : ?>
			<div class="<?php echo esc_attr( $queen_card_class . '__excerpt' ); ?>"><?php echo wp_kses_post( wpautop( get_the_excerpt() ) ); ?></div>
		<?php endif; ?>

		<a class="<?php echo esc_attr( $queen_card_class . '__link' ); ?>" href="<?php echo esc_url( get_permalink() ); ?>">
			<?php esc_html_e( 'Selengkapnya', 'queen-alfalah' ); ?>
			<span class="screen-reader-text">: <?php echo esc_html( get_the_title() ); ?></span>
		</a>
	</div>
</article>
