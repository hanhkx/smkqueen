<?php
/**
 * Reusable presentation helpers.
 *
 * @package Queen_AlFalah
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Verified defaults plus conservative display copy.
 *
 * @return array<string, mixed>
 */
function queen_alfalah_default_school_settings() {
	return array(
		'school_name'       => 'SMK QUEEN AL-FALAH',
		'legal_name'        => 'SMK Queen Al-Falah Mojo',
		'motto'             => 'Pelopor Teknologi yang Islami',
		'npsn'              => '20574699',
		'accreditation'     => 'B',
		'founded'           => '21 Februari 2011',
		'foundation'        => 'Yayasan YPI Al-Muttaqien',
		'address'           => 'Jl. Raya Kebanan–Ploso, Ds. Ploso, Kec. Mojo, Kab. Kediri, Jawa Timur',
		'phone'             => '0354 4520550',
		'whatsapp'          => '6281222245445',
		'email'             => 'smkqueenalfalah@yahoo.com',
		'opening_hours'     => 'Senin–Sabtu, 08.00–14.00 WIB',
		'principal_name'    => 'Kepala SMK Queen Al-Falah',
		'principal_title'   => 'Kepala Sekolah',
		'principal_message' => 'Pendidikan vokasi yang kuat menyatukan kompetensi, karakter, dan keberanian untuk terus belajar.',
		'vision'            => 'Mencetak siswa yang cerdas, profesional, berdaya saing nasional maupun internasional, dan berakhlaqul karimah.',
		'ppdb_label'        => 'Pendaftaran Santri Baru',
		'ppdb_url'          => 'https://psb.queenalfalah.id/',
		'latitude'          => '-7.9199',
		'longitude'         => '111.9604',
		'maps_url'          => 'https://www.google.com/maps/search/?api=1&query=SMK+Queen+Al+Falah+Mojo+Kediri',
		'instagram'         => '',
		'facebook'          => '',
		'youtube'           => '',
		'tiktok'            => '',
	);
}

/**
 * Retrieve portable plugin settings first, then theme fallback settings.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Optional default.
 * @return mixed
 */
function queen_alfalah_school_info( $key, $default = '' ) {
	$defaults = queen_alfalah_default_school_settings();
	$fallback = array_key_exists( $key, $defaults ) ? $defaults[ $key ] : $default;

	if ( function_exists( 'qaf_core_get_setting' ) ) {
		$plugin_keys = array(
			'school_name' => 'short_name',
			'legal_name'  => 'school_name',
			'founded'     => 'founded_date',
			'ppdb_url'    => 'registration_url',
			'maps_url'    => 'map_url',
			'facebook'    => 'facebook_url',
			'instagram'   => 'instagram_url',
			'youtube'     => 'youtube_url',
			'tiktok'      => 'tiktok_url',
		);
		$plugin_key = isset( $plugin_keys[ $key ] ) ? $plugin_keys[ $key ] : $key;
		$value      = qaf_core_get_setting( $plugin_key, $fallback );

		if ( 'founded' === $key && is_string( $value ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
			$date = DateTimeImmutable::createFromFormat( '!Y-m-d', $value, wp_timezone() );
			if ( $date ) {
				$value = wp_date( 'j F Y', $date->getTimestamp(), wp_timezone() );
			}
		}
		if ( '' !== $value && null !== $value ) {
			return $value;
		}
		if ( in_array( $key, array( 'whatsapp', 'facebook', 'instagram', 'youtube', 'tiktok', 'latitude', 'longitude' ), true ) ) {
			return '';
		}
	}

	return get_theme_mod( 'queen_' . $key, $fallback );
}

/**
 * Normalize one decimal map coordinate.
 *
 * Scientific notation and non-scalar values are rejected so an iframe URL is
 * always assembled from a small, predictable character set.
 *
 * @param mixed $value Coordinate value.
 * @param float $min   Inclusive lower bound.
 * @param float $max   Inclusive upper bound.
 * @return string
 */
function queen_alfalah_normalize_coordinate( $value, $min, $max ) {
	if ( ! is_scalar( $value ) ) {
		return '';
	}

	$value = trim( (string) $value );
	if ( '' === $value || ! preg_match( '/^-?(?:\d+(?:\.\d+)?|\.\d+)$/', $value ) ) {
		return '';
	}

	$coordinate = (float) $value;
	if ( ! is_finite( $coordinate ) || $coordinate < $min || $coordinate > $max ) {
		return '';
	}
	if ( 0.0 === $coordinate ) {
		$coordinate = 0.0;
	}

	return rtrim( rtrim( number_format( $coordinate, 7, '.', '' ), '0' ), '.' );
}

/**
 * Build a fixed-host Google Maps embed URL from validated school coordinates.
 *
 * The editable external map URL is deliberately not used as an iframe source.
 * This keeps arbitrary hosts out of the page while allowing administrators to
 * replace the regular "open map" link independently.
 *
 * @return string
 */
function queen_alfalah_map_embed_url() {
	$latitude  = queen_alfalah_normalize_coordinate( queen_alfalah_school_info( 'latitude' ), -90, 90 );
	$longitude = queen_alfalah_normalize_coordinate( queen_alfalah_school_info( 'longitude' ), -180, 180 );

	if ( '' === $latitude || '' === $longitude ) {
		return '';
	}

	return 'https://www.google.com/maps?q=' . rawurlencode( $latitude . ',' . $longitude ) . '&z=17&output=embed';
}

/**
 * Render the school map with an accessible external-link fallback.
 *
 * @param string $context Optional presentation context: home or contact.
 */
function queen_alfalah_school_map( $context = '' ) {
	$allowed_contexts = array( 'home', 'contact' );
	$context          = in_array( $context, $allowed_contexts, true ) ? $context : '';
	$embed_url        = queen_alfalah_map_embed_url();
	$maps_url         = (string) queen_alfalah_school_info( 'maps_url' );
	$location_label   = __( 'Ploso, Mojo, Kabupaten Kediri', 'queen-alfalah' );
	$class_name       = 'school-map' . ( $context ? ' school-map--' . $context : '' );

	if ( $embed_url ) :
		?>
		<div class="<?php echo esc_attr( $class_name ); ?>">
			<div class="school-map__frame">
				<iframe
					title="<?php esc_attr_e( 'Peta lokasi SMK Queen Al-Falah', 'queen-alfalah' ); ?>"
					src="<?php echo esc_url( $embed_url ); ?>"
					loading="lazy"
					referrerpolicy="strict-origin-when-cross-origin"
					allowfullscreen
				></iframe>
			</div>
			<div class="school-map__footer">
				<span><?php echo queen_alfalah_icon( 'map-pin' ); ?><?php echo esc_html( $location_label ); ?></span>
				<?php if ( $maps_url ) : ?>
					<a href="<?php echo esc_url( $maps_url ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Buka di Google Maps', 'queen-alfalah' ); ?><?php echo queen_alfalah_icon( 'external' ); ?>
						<span class="screen-reader-text"> <?php esc_html_e( '(tab baru)', 'queen-alfalah' ); ?></span>
					</a>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return;
	endif;

	if ( $maps_url ) :
		?>
		<a class="map-card" href="<?php echo esc_url( $maps_url ); ?>" target="_blank" rel="noopener noreferrer">
			<span class="map-card__pin"><?php echo queen_alfalah_icon( 'map-pin' ); ?></span>
			<strong><?php echo esc_html( $location_label ); ?></strong>
			<small><?php esc_html_e( 'Buka lokasi di peta', 'queen-alfalah' ); ?><?php echo queen_alfalah_icon( 'external' ); ?></small>
			<span class="screen-reader-text"> <?php esc_html_e( '(tab baru)', 'queen-alfalah' ); ?></span>
		</a>
		<?php
	endif;
}

/**
 * Get a page URL while remaining useful before demo setup.
 *
 * @param string $slug Page slug.
 * @return string
 */
function queen_alfalah_page_url( $slug ) {
	$page = get_page_by_path( sanitize_title( $slug ) );
	return $page ? get_permalink( $page ) : home_url( '/' . trim( $slug, '/' ) . '/' );
}

/**
 * Resolve a CPT archive, with a stable fallback URL.
 *
 * @param string $post_type Post type.
 * @param string $fallback  Fallback slug.
 * @return string
 */
function queen_alfalah_archive_url( $post_type, $fallback = '' ) {
	$link = get_post_type_archive_link( $post_type );
	if ( $link ) {
		return $link;
	}
	$fallback = $fallback ? $fallback : str_replace( 'qaf_', '', $post_type );
	return home_url( '/' . trim( $fallback, '/' ) . '/' );
}

/**
 * Small inline SVG icon set. All icons are decorative by default.
 *
 * @param string $name  Icon name.
 * @param string $class Optional additional class.
 * @return string
 */
function queen_alfalah_icon( $name, $class = '' ) {
	$paths = array(
		'arrow-right'  => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'arrow-up'     => '<path d="m18 15-6-6-6 6"/>',
		'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
		'menu'         => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'        => '<path d="m6 6 12 12M18 6 6 18"/>',
		'search'       => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
		'phone'        => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.34 1.78.65 2.63a2 2 0 0 1-.45 2.11L8.04 9.73a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.85.31 1.73.53 2.63.65A2 2 0 0 1 22 16.92z"/>',
		'mail'         => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
		'map-pin'      => '<path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0z"/><circle cx="12" cy="10" r="2.5"/>',
		'calendar'     => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>',
		'clock'        => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'users'        => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
		'book'         => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5z"/><path d="M4 5.5v14A2.5 2.5 0 0 0 6.5 22H20"/>',
		'monitor'      => '<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>',
		'briefcase'    => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V4h8v3M3 12h18M10 12v2h4v-2"/>',
		'heart'        => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/>',
		'palette'      => '<path d="M12 3a9 9 0 0 0 0 18h1.5a1.5 1.5 0 0 0 0-3H12a2 2 0 0 1 0-4h4a5 5 0 0 0 0-10z"/><circle cx="7.5" cy="10" r=".7" fill="currentColor"/><circle cx="9.5" cy="6.8" r=".7" fill="currentColor"/><circle cx="14" cy="6.5" r=".7" fill="currentColor"/>',
		'award'        => '<circle cx="12" cy="8" r="6"/><path d="m8.5 13-1.5 9 5-3 5 3-1.5-9"/>',
		'building'     => '<path d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6M8 10h.01M12 10h.01M16 10h.01"/>',
		'folder'       => '<path d="M3 6a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
		'external'     => '<path d="M14 3h7v7M10 14 21 3M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/>',
		'check'        => '<path d="m5 12 4 4L19 6"/>',
		'play'         => '<circle cx="12" cy="12" r="9"/><path d="m10 8 6 4-6 4z"/>',
		'quote'        => '<path d="M10 11H5a4 4 0 0 1 4-4M20 11h-5a4 4 0 0 1 4-4M5 11v6h5v-6M15 11v6h5v-6"/>',
		'whatsapp'     => '<path d="M20.5 11.7a8.5 8.5 0 0 1-12.6 7.5L3 20.5l1.3-4.7a8.5 8.5 0 1 1 16.2-4.1z"/><path d="M8.2 7.7c.2-.4.4-.4.7-.4h.4c.2 0 .4.1.5.4l.7 1.7c.1.3 0 .5-.1.7l-.5.6c-.2.2-.2.4 0 .7.7 1.2 1.7 2.2 3 2.8.3.2.6.1.8-.1l.7-.9c.2-.2.4-.3.7-.2l1.7.8c.3.1.4.3.4.5 0 .3-.2 1.5-1.1 2.1-.6.5-1.4.7-2.3.5-1.1-.2-2.6-.7-4.4-2.3-1.5-1.3-2.6-3-2.9-4.1-.3-1.1 0-2.1.4-2.6z"/>',
		'instagram'    => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".8" fill="currentColor" stroke="none"/>',
		'facebook'     => '<path d="M14 22v-8h3l.5-4H14V8c0-1.2.4-2 2-2h2V2.5c-.5-.1-1.7-.2-3-.2-3 0-5 1.8-5 5.2V10H7v4h3v8z"/>',
		'youtube'      => '<path d="M21.5 7.2a2.5 2.5 0 0 0-1.8-1.8C18.1 5 12 5 12 5s-6.1 0-7.7.4a2.5 2.5 0 0 0-1.8 1.8A26 26 0 0 0 2 12a26 26 0 0 0 .5 4.8 2.5 2.5 0 0 0 1.8 1.8c1.6.4 7.7.4 7.7.4s6.1 0 7.7-.4a2.5 2.5 0 0 0 1.8-1.8A26 26 0 0 0 22 12a26 26 0 0 0-.5-4.8z"/><path d="m10 15 5-3-5-3z"/>',
		'tiktok'       => '<path d="M15 3c.4 2.2 1.7 3.6 4 4v3a8 8 0 0 1-4-1.1v6.4a5.7 5.7 0 1 1-5-5.7v3.1a2.7 2.7 0 1 0 2 2.6V3z"/>',
	);

	if ( ! isset( $paths[ $name ] ) ) {
		$name = 'arrow-right';
	}

	$class = trim( 'icon icon--' . sanitize_html_class( $name ) . ' ' . $class );
	return '<svg class="' . esc_attr( $class ) . '" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $paths[ $name ] . '</svg>';
}

/**
 * Print a consistent section heading.
 *
 * @param string $eyebrow Small label.
 * @param string $title   Section heading.
 * @param string $text    Supporting copy.
 * @param string $align   left or center.
 * @param string $id      Optional heading ID for aria-labelledby.
 */
function queen_alfalah_section_heading( $eyebrow, $title, $text = '', $align = 'left', $id = '' ) {
	$align = in_array( $align, array( 'left', 'center' ), true ) ? $align : 'left';
	?>
	<header class="section-heading section-heading--<?php echo esc_attr( $align ); ?>">
		<?php if ( $eyebrow ) : ?>
			<p class="eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
		<?php endif; ?>
		<h2<?php echo $id ? ' id="' . esc_attr( $id ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h2>
		<?php if ( $text ) : ?>
			<p class="section-heading__text"><?php echo esc_html( $text ); ?></p>
		<?php endif; ?>
	</header>
	<?php
}

/**
 * Retrieve a post meta value with a fallback.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key without or with leading underscore.
 * @param mixed  $default Fallback.
 * @return mixed
 */
function queen_alfalah_meta( $post_id, $key, $default = '' ) {
	$key   = 0 === strpos( $key, '_qaf_' ) ? $key : '_qaf_' . ltrim( $key, '_' );
	$value = get_post_meta( $post_id, $key, true );
	return '' !== $value && null !== $value ? $value : $default;
}

/**
 * Render post date and category metadata.
 */
function queen_alfalah_post_meta() {
	?>
	<div class="entry-meta">
		<span><?php echo queen_alfalah_icon( 'calendar' ); ?><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></span>
		<?php if ( 'post' === get_post_type() && get_the_category_list() ) : ?>
			<span class="entry-meta__category"><?php echo wp_kses_post( get_the_category_list( ', ' ) ); ?></span>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Print breadcrumb navigation.
 */
function queen_alfalah_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	$items   = array();
	$items[] = array( 'label' => __( 'Beranda', 'queen-alfalah' ), 'url' => home_url( '/' ) );

	if ( is_home() ) {
		$items[] = array( 'label' => __( 'Berita', 'queen-alfalah' ), 'url' => '' );
	} elseif ( is_singular() ) {
		$post_type = get_post_type();
		if ( 'post' === $post_type ) {
			$posts_page = (int) get_option( 'page_for_posts' );
			$items[]    = array( 'label' => __( 'Berita', 'queen-alfalah' ), 'url' => $posts_page ? get_permalink( $posts_page ) : home_url( '/berita/' ) );
		} elseif ( 'page' !== $post_type ) {
			$object = get_post_type_object( $post_type );
			if ( $object ) {
				$items[] = array( 'label' => $object->labels->name, 'url' => get_post_type_archive_link( $post_type ) );
			}
		}
		$items[] = array( 'label' => get_the_title(), 'url' => '' );
	} elseif ( is_post_type_archive() ) {
		$items[] = array( 'label' => post_type_archive_title( '', false ), 'url' => '' );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$items[] = array( 'label' => single_term_title( '', false ), 'url' => '' );
	} elseif ( is_search() ) {
		$items[] = array( 'label' => sprintf( __( 'Hasil pencarian: %s', 'queen-alfalah' ), get_search_query() ), 'url' => '' );
	} elseif ( is_404() ) {
		$items[] = array( 'label' => __( 'Halaman tidak ditemukan', 'queen-alfalah' ), 'url' => '' );
	}

	if ( count( $items ) < 2 ) {
		return;
	}
	?>
	<nav class="breadcrumbs container" aria-label="<?php esc_attr_e( 'Breadcrumb', 'queen-alfalah' ); ?>">
		<ol>
			<?php foreach ( $items as $index => $item ) : ?>
				<li>
					<?php if ( $item['url'] && $index < count( $items ) - 1 ) : ?>
						<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
					<?php else : ?>
						<span aria-current="page"><?php echo esc_html( wp_strip_all_tags( $item['label'] ) ); ?></span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	</nav>
	<?php
}

/**
 * Use a local illustration when a post has no thumbnail.
 *
 * Generated category visuals are clearly disclosed by the calling template.
 * Legacy SVGs remain available for the principal and program placeholders.
 *
 * @param string $variant Optional placeholder type.
 * @return string
 */
function queen_alfalah_placeholder( $variant = 'default' ) {
	$generated = array(
		'school'            => 'fallback-school.webp',
		'people'            => 'fallback-people.webp',
		'gallery'           => 'fallback-gallery.webp',
		'achievement'       => 'fallback-achievement.webp',
		'service'           => 'fallback-service.webp',
		'career'            => 'fallback-career.webp',
		'facility'          => 'fallback-facility.webp',
		'facility-digital'  => 'fallback-facility-digital.webp',
		'facility-health'   => 'fallback-facility-health.webp',
		'facility-office'   => 'fallback-facility-office.webp',
		'facility-campus'   => 'fallback-facility-campus.webp',
	);

	if ( isset( $generated[ $variant ] ) ) {
		$relative = '/assets/images/fallback/' . $generated[ $variant ];
		if ( is_readable( QUEEN_ALFALAH_DIR . $relative ) ) {
			return QUEEN_ALFALAH_URI . $relative;
		}
	}

	$legacy  = array( 'default', 'program', 'person', 'gallery' );
	$variant = in_array( $variant, $legacy, true ) ? $variant : 'default';
	return QUEEN_ALFALAH_URI . '/assets/images/placeholder-' . $variant . '.svg';
}

/**
 * Check a portable slug for one of several category hints.
 *
 * @param string        $slug    Post, page, category, or term slug.
 * @param array<string> $needles Category hints.
 * @return bool
 */
function queen_alfalah_slug_matches( $slug, $needles ) {
	$slug = strtolower( (string) $slug );
	foreach ( $needles as $needle ) {
		$needle = strtolower( (string) $needle );
		$match  = strlen( $needle ) <= 3
			? (bool) preg_match( '/(?:^|-)' . preg_quote( $needle, '/' ) . '(?:-|$)/', $slug )
			: false !== strpos( $slug, $needle );
		if ( $match ) {
			return true;
		}
	}
	return false;
}

/**
 * Resolve a focused facility illustration from a facility slug.
 *
 * @param string $slug Facility slug.
 * @return string
 */
function queen_alfalah_facility_visual_variant( $slug ) {
	if ( queen_alfalah_slug_matches( $slug, array( 'kesehatan', 'uks', 'medis', 'klinik' ) ) ) {
		return 'facility-health';
	}
	if ( queen_alfalah_slug_matches( $slug, array( 'mplb', 'kantor', 'kelas', 'administrasi' ) ) ) {
		return 'facility-office';
	}
	if ( queen_alfalah_slug_matches( $slug, array( 'aula', 'auditorium', 'gedung', 'perpustakaan', 'literasi', 'kantin', 'lapangan', 'olahraga' ) ) ) {
		return 'facility-campus';
	}
	if ( queen_alfalah_slug_matches( $slug, array( 'lab', 'komputer', 'tjkt', 'dkv', 'podcast', 'fiber', 'foto', 'drone', 'broadcast', 'editing', 'studio' ) ) ) {
		return 'facility-digital';
	}
	return 'facility';
}

/**
 * Resolve the best bundled fallback category for a post or page.
 *
 * This is presentation-only: it never writes to the Media Library and never
 * replaces an administrator-provided Featured Image.
 *
 * @param int $post_id Optional post ID.
 * @return string
 */
function queen_alfalah_visual_variant( $post_id = 0 ) {
	$post = get_post( $post_id ? $post_id : get_the_ID() );
	if ( ! $post instanceof WP_Post ) {
		return 'school';
	}

	$direct = array(
		'qaf_teacher'     => 'people',
		'qaf_alumni'      => 'people',
		'qaf_program'     => 'facility',
		'qaf_notice'      => 'service',
		'qaf_agenda'      => 'service',
		'qaf_achievement' => 'achievement',
		'qaf_extra'       => 'achievement',
		'qaf_service'     => 'service',
		'qaf_gallery'     => 'gallery',
		'qaf_partner'     => 'career',
		'qaf_vacancy'     => 'career',
	);

	if ( 'qaf_facility' === $post->post_type ) {
		return queen_alfalah_facility_visual_variant( $post->post_name );
	}
	if ( isset( $direct[ $post->post_type ] ) ) {
		return $direct[ $post->post_type ];
	}

	$slug = (string) $post->post_name;
	if ( 'page' === $post->post_type ) {
		if ( queen_alfalah_slug_matches( $slug, array( 'guru', 'tendik', 'struktur', 'organisasi', 'sambutan', 'kepala-sekolah' ) ) ) {
			return 'people';
		}
		if ( queen_alfalah_slug_matches( $slug, array( 'prestasi', 'ekstra' ) ) ) {
			return 'achievement';
		}
		if ( queen_alfalah_slug_matches( $slug, array( 'galeri', 'media-sosial', 'video' ) ) ) {
			return 'gallery';
		}
		if ( queen_alfalah_slug_matches( $slug, array( 'aplikasi', 'pusat-media', 'ppdb', 'spmb', 'pendaftaran', 'informasi', 'kesiswaan' ) ) ) {
			return 'service';
		}
		if ( queen_alfalah_slug_matches( $slug, array( 'mitra', 'industri', 'pkl', 'bkk', 'bursa-kerja', 'lowongan', 'alumni' ) ) ) {
			return 'career';
		}
		if ( queen_alfalah_slug_matches( $slug, array( 'sarana', 'prasarana', 'fasilitas', 'program', 'jurusan' ) ) ) {
			return 'facility';
		}
		return 'school';
	}

	if ( 'post' === $post->post_type ) {
		$categories = get_the_category( $post->ID );
		foreach ( $categories as $category ) {
			$category_slug = isset( $category->slug ) ? (string) $category->slug : '';
			if ( queen_alfalah_slug_matches( $category_slug, array( 'prestasi', 'ekstra', 'lomba' ) ) ) {
				return 'achievement';
			}
			if ( queen_alfalah_slug_matches( $category_slug, array( 'galeri', 'video', 'media' ) ) ) {
				return 'gallery';
			}
			if ( queen_alfalah_slug_matches( $category_slug, array( 'ppdb', 'spmb', 'pengumuman', 'agenda', 'layanan' ) ) ) {
				return 'service';
			}
			if ( queen_alfalah_slug_matches( $category_slug, array( 'karier', 'mitra', 'pkl', 'bkk', 'alumni' ) ) ) {
				return 'career';
			}
			if ( queen_alfalah_slug_matches( $category_slug, array( 'sarana', 'fasilitas', 'laboratorium' ) ) ) {
				return 'facility';
			}
		}
	}

	return 'school';
}

/**
 * Return fallback visual metadata for a post with no Featured Image.
 *
 * @param int $post_id Optional post ID.
 * @return array<string,mixed>
 */
function queen_alfalah_fallback_visual( $post_id = 0 ) {
	$post = get_post( $post_id ? $post_id : get_the_ID() );
	if ( ! $post instanceof WP_Post ) {
		return array();
	}

	$special_url = 'qaf_extra' === $post->post_type ? queen_alfalah_extra_illustration( $post->ID ) : '';
	$variant     = $special_url ? 'activity' : queen_alfalah_visual_variant( $post->ID );
	$url         = $special_url ? $special_url : queen_alfalah_placeholder( $variant );

	return array(
		'url'     => $url,
		'width'   => 1200,
		'height'  => 800,
		'variant' => $variant,
		'label'   => __( 'Ilustrasi', 'queen-alfalah' ),
		'caption' => __( 'Ilustrasi visual sementara; ganti dengan dokumentasi asli sekolah saat tersedia.', 'queen-alfalah' ),
	);
}

/**
 * Return the bundled activity illustration until an editor uploads a photo.
 *
 * The companion plugin owns these optional assets. A regular Featured Image
 * always takes precedence, so school documentation can replace an illustration
 * without changing code.
 *
 * @param int $post_id Extracurricular post ID.
 * @return string
 */
function queen_alfalah_extra_illustration( $post_id = 0 ) {
	$post = get_post( $post_id ? $post_id : get_the_ID() );
	if ( ! $post instanceof WP_Post || 'qaf_extra' !== $post->post_type || ! defined( 'QAF_CORE_PATH' ) || ! defined( 'QAF_CORE_URL' ) ) {
		return '';
	}

	$allowed = array(
		'pramuka',
		'broadcasting',
		'futsal',
		'al-banjari',
		'tenis-meja',
		'bola-voli',
		'desain-web',
		'desain-canva',
		'tata-rias',
		'seni-tari',
		'seni-lukis',
	);
	$slug = in_array( $post->post_name, $allowed, true ) ? $post->post_name : '';
	if ( ! $slug ) {
		return '';
	}

	$relative = 'assets/images/extracurricular/' . $slug . '.webp';
	return is_readable( QAF_CORE_PATH . $relative ) ? QAF_CORE_URL . $relative : '';
}

/**
 * Return the supported Gallery sources and their public labels.
 *
 * @return array<string,string>
 */
function queen_alfalah_gallery_sources() {
	return array(
		'local'     => __( 'Foto/Video Sekolah', 'queen-alfalah' ),
		'instagram' => 'Instagram',
		'tiktok'    => 'TikTok',
		'facebook'  => 'Facebook',
		'youtube'   => 'YouTube',
	);
}

/**
 * Normalize a social hostname for exact comparisons.
 *
 * @param string $host Raw hostname.
 * @return string
 */
function queen_alfalah_gallery_normalize_host( $host ) {
	$host = strtolower( trim( (string) $host, ". \t\n\r\0\x0B" ) );
	foreach ( array( 'www.', 'm.' ) as $prefix ) {
		if ( 0 === strpos( $host, $prefix ) ) {
			return substr( $host, strlen( $prefix ) );
		}
	}

	return $host;
}

/**
 * Infer a provider from a canonical media URL.
 *
 * Used only for entries created before the source selector existed.
 *
 * @param string $url Media URL.
 * @return string
 */
function queen_alfalah_gallery_infer_source( $url ) {
	$url    = esc_url_raw( is_string( $url ) ? $url : '', array( 'https' ) );
	$host   = queen_alfalah_gallery_normalize_host( wp_parse_url( $url, PHP_URL_HOST ) );
	$source = array(
		'instagram.com' => 'instagram',
		'tiktok.com'    => 'tiktok',
		'vm.tiktok.com' => 'tiktok',
		'vt.tiktok.com' => 'tiktok',
		'facebook.com'  => 'facebook',
		'fb.watch'      => 'facebook',
		'youtube.com'   => 'youtube',
		'youtu.be'      => 'youtube',
	);

	return isset( $source[ $host ] ) ? $source[ $host ] : '';
}

/**
 * Resolve one Gallery entry's source without breaking legacy video URLs.
 *
 * @param int $post_id Gallery post ID.
 * @return string
 */
function queen_alfalah_gallery_source( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$sources = queen_alfalah_gallery_sources();
	$stored  = sanitize_key( get_post_meta( $post_id, '_qaf_gallery_source', true ) );

	if ( isset( $sources[ $stored ] ) ) {
		return $stored;
	}

	$inferred = queen_alfalah_gallery_infer_source( get_post_meta( $post_id, '_qaf_video_url', true ) );
	return $inferred ? $inferred : 'local';
}

/**
 * Return a source label for cards and detail pages.
 *
 * @param int $post_id Gallery post ID.
 * @return string
 */
function queen_alfalah_gallery_source_label( $post_id = 0 ) {
	$sources = queen_alfalah_gallery_sources();
	$source  = queen_alfalah_gallery_source( $post_id );

	return isset( $sources[ $source ] ) ? $sources[ $source ] : $sources['local'];
}

/**
 * Validate an old direct-video URL against this WordPress installation.
 *
 * New Gallery entries use a Media Library attachment ID. This narrow fallback
 * keeps existing same-origin/upload URLs working without turning the page into
 * an implicit third-party video request.
 *
 * @param string $url Legacy direct-video URL.
 * @return string
 */
function queen_alfalah_gallery_legacy_video_url( $url ) {
	$url = esc_url_raw( is_string( $url ) ? $url : '', array( 'http', 'https' ) );
	if ( ! $url ) {
		return '';
	}

	$filetype = wp_check_filetype( (string) wp_parse_url( $url, PHP_URL_PATH ) );
	if ( empty( $filetype['type'] ) || 0 !== strpos( $filetype['type'], 'video/' ) ) {
		return '';
	}

	$uploads = wp_get_upload_dir();
	$bases   = array( home_url( '/' ) );
	if ( empty( $uploads['error'] ) && ! empty( $uploads['baseurl'] ) ) {
		$bases[] = $uploads['baseurl'];
	}

	$url_scheme = strtolower( (string) wp_parse_url( $url, PHP_URL_SCHEME ) );
	$url_host   = strtolower( rtrim( (string) wp_parse_url( $url, PHP_URL_HOST ), '.' ) );
	$url_port   = (int) wp_parse_url( $url, PHP_URL_PORT );
	if ( ! $url_scheme || ! $url_host || wp_parse_url( $url, PHP_URL_USER ) || wp_parse_url( $url, PHP_URL_PASS ) ) {
		return '';
	}

	foreach ( array_unique( $bases ) as $base ) {
		$base_scheme = strtolower( (string) wp_parse_url( $base, PHP_URL_SCHEME ) );
		$base_host   = strtolower( rtrim( (string) wp_parse_url( $base, PHP_URL_HOST ), '.' ) );
		$base_port   = (int) wp_parse_url( $base, PHP_URL_PORT );
		if ( $url_scheme === $base_scheme && $url_host === $base_host && $url_port === $base_port ) {
			return $url;
		}
	}

	return '';
}

/**
 * Build a provider-controlled embed configuration from an allowlisted URL.
 *
 * Raw iframe/script HTML is never accepted. Every embed address is derived
 * from a validated canonical post or video URL.
 *
 * @param string $source Selected provider.
 * @param string $url    Canonical public content URL.
 * @return array<string,string>|false
 */
function queen_alfalah_gallery_embed_config( $source, $url ) {
	$url    = esc_url_raw( is_string( $url ) ? $url : '', array( 'https' ) );
	$scheme = strtolower( (string) wp_parse_url( $url, PHP_URL_SCHEME ) );
	$host   = queen_alfalah_gallery_normalize_host( wp_parse_url( $url, PHP_URL_HOST ) );
	$path   = (string) wp_parse_url( $url, PHP_URL_PATH );

	if ( 'https' !== $scheme || ! $host ) {
		return false;
	}

	if ( 'instagram' === $source ) {
		if ( 'instagram.com' !== $host || ! preg_match( '#^/(p|reel|tv)/([A-Za-z0-9_-]+)/?$#', $path, $matches ) ) {
			return false;
		}

		$canonical = sprintf( 'https://www.instagram.com/%s/%s/', $matches[1], $matches[2] );
		return array(
			'kind'      => 'instagram',
			'provider'  => 'instagram',
			'label'     => 'Instagram',
			'canonical' => $canonical,
			'aspect'    => 'portrait',
			'allow'     => '',
		);
	}

	if ( 'tiktok' === $source ) {
		if ( 'tiktok.com' !== $host || ! preg_match( '#^/@[^/]+/video/([0-9]{8,24})/?$#', $path, $matches ) ) {
			return false;
		}

		$embed_url = add_query_arg(
			array(
				'controls'     => '1',
				'autoplay'     => '0',
				'rel'          => '0',
				'music_info'   => '1',
				'description'  => '1',
			),
			'https://www.tiktok.com/player/v1/' . $matches[1]
		);
		return array(
			'kind'      => 'iframe',
			'provider'  => 'tiktok',
			'label'     => 'TikTok',
			'canonical' => $url,
			'embed_url' => $embed_url,
			'aspect'    => 'portrait',
			'allow'     => 'fullscreen',
		);
	}

	if ( 'facebook' === $source ) {
		if ( ! in_array( $host, array( 'facebook.com', 'fb.watch' ), true ) ) {
			return false;
		}

		$facebook_query = array();
		parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $facebook_query );
		$facebook_path = rtrim( $path, '/' );
		if ( 'fb.watch' === $host ) {
			if ( ! preg_match( '#^/[A-Za-z0-9_-]+$#', $facebook_path ) ) {
				return false;
			}
		} else {
			$is_content_path = (bool) preg_match(
				'#^/(?:[^/]+/(?:posts|videos|photos|reels?)/.+|reels?/.+|watch|share/(?:p|v|r)/.+)$#',
				$facebook_path
			);
			$is_content_query = (
				'/photo.php' === $facebook_path && ! empty( $facebook_query['fbid'] )
			) || (
				in_array( $facebook_path, array( '/permalink.php', '/story.php' ), true )
				&& ! empty( $facebook_query['story_fbid'] )
			);
			if ( ! $is_content_path && ! $is_content_query ) {
				return false;
			}
		}

		$is_video = 'fb.watch' === $host
			|| false !== strpos( $path, '/videos/' )
			|| false !== strpos( $path, '/reel/' )
			|| false !== strpos( $path, '/reels/' )
			|| '/watch' === $facebook_path
			|| (bool) preg_match( '#^/share/(?:v|r)/#', $path );
		$endpoint = $is_video
			? 'https://www.facebook.com/plugins/video.php'
			: 'https://www.facebook.com/plugins/post.php';
		$embed_url = add_query_arg(
			array(
				'href'      => $url,
				'show_text' => 'true',
				'width'     => '720',
			),
			$endpoint
		);
		return array(
			'kind'      => 'iframe',
			'provider'  => 'facebook',
			'label'     => 'Facebook',
			'canonical' => $url,
			'embed_url' => $embed_url,
			'aspect'    => $is_video ? 'wide' : 'portrait',
			'allow'     => 'autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share',
		);
	}

	if ( 'youtube' === $source ) {
		if ( ! in_array( $host, array( 'youtube.com', 'youtu.be' ), true ) ) {
			return false;
		}

		$video_id  = '';
		$playlist  = '';
		$is_shorts = false;
		$query     = array();
		parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );

		if ( 'youtu.be' === $host ) {
			$video_id = trim( $path, '/' );
		} elseif ( '/watch' === rtrim( $path, '/' ) ) {
			$video_id = isset( $query['v'] ) && is_scalar( $query['v'] ) ? (string) $query['v'] : '';
		} elseif ( preg_match( '#^/(shorts|live|embed)/([A-Za-z0-9_-]{11})/?$#', $path, $matches ) ) {
			$is_shorts = 'shorts' === $matches[1];
			$video_id  = $matches[2];
		}

		if ( isset( $query['list'] ) && is_scalar( $query['list'] ) ) {
			$playlist = preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $query['list'] );
		}

		if ( $video_id && ! preg_match( '/^[A-Za-z0-9_-]{11}$/', $video_id ) ) {
			$video_id = '';
		}
		if ( $playlist && ! preg_match( '/^[A-Za-z0-9_-]{10,64}$/', $playlist ) ) {
			$playlist = '';
		}
		if ( $playlist && ! $video_id && '/playlist' !== rtrim( $path, '/' ) ) {
			$playlist = '';
		}
		if ( ! $video_id && ! $playlist ) {
			return false;
		}

		$home_scheme = wp_parse_url( home_url( '/' ), PHP_URL_SCHEME );
		$home_host   = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
		$origin      = $home_scheme && $home_host ? $home_scheme . '://' . $home_host : '';
		$embed_url   = $video_id
			? 'https://www.youtube-nocookie.com/embed/' . rawurlencode( $video_id )
			: 'https://www.youtube-nocookie.com/embed';
		$args        = array(
			'autoplay'    => '0',
			'playsinline' => '1',
		);
		if ( $playlist ) {
			$args['listType'] = 'playlist';
			$args['list']     = $playlist;
		}
		if ( $origin ) {
			$args['origin'] = $origin;
		}

		return array(
			'kind'      => 'iframe',
			'provider'  => 'youtube',
			'label'     => 'YouTube',
			'canonical' => $url,
			'embed_url' => add_query_arg( $args, $embed_url ),
			'aspect'    => $is_shorts ? 'portrait' : 'wide',
			'allow'     => 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share',
		);
	}

	return false;
}

/**
 * Render local video or a privacy-aware social embed for a Gallery entry.
 *
 * @param int $post_id Gallery post ID.
 * @return string
 */
function queen_alfalah_gallery_media( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	if ( 'qaf_gallery' !== get_post_type( $post_id ) ) {
		return '';
	}

	$source = queen_alfalah_gallery_source( $post_id );
	$url    = get_post_meta( $post_id, '_qaf_video_url', true );

	if ( 'local' === $source ) {
		$attachment_id = absint( get_post_meta( $post_id, '_qaf_gallery_local_video_id', true ) );
		$video_url     = '';
		if ( $attachment_id && 'attachment' === get_post_type( $attachment_id ) && 0 === strpos( (string) get_post_mime_type( $attachment_id ), 'video/' ) ) {
			$video_url = wp_get_attachment_url( $attachment_id );
		}

		if ( ! $video_url && is_string( $url ) && $url ) {
			$video_url = queen_alfalah_gallery_legacy_video_url( $url );
		}

		if ( ! $video_url ) {
			return '';
		}

		return '<section class="gallery-media gallery-media--local" aria-label="' . esc_attr__( 'Video galeri sekolah', 'queen-alfalah' ) . '">'
			. wp_video_shortcode(
				array(
					'src'     => $video_url,
					'preload' => 'metadata',
				)
			)
			. '</section>';
	}

	$config = queen_alfalah_gallery_embed_config( $source, $url );
	if ( ! $config ) {
		if ( ! $url ) {
			return '';
		}

		return '<section class="gallery-media gallery-media--fallback"><p>'
			. esc_html__( 'Pratinjau sosial belum tersedia untuk alamat ini.', 'queen-alfalah' )
			. '</p><a class="button button--outline-dark" href="' . esc_url( $url ) . '" target="_blank" rel="external noopener noreferrer">'
			. esc_html__( 'Buka tautan sumber', 'queen-alfalah' )
			. queen_alfalah_icon( 'external' )
			. '</a></section>';
	}

	$behavior = sanitize_key( get_post_meta( $post_id, '_qaf_gallery_embed_behavior', true ) );
	$behavior = in_array( $behavior, array( 'click', 'auto', 'link' ), true ) ? $behavior : 'click';
	$label    = $config['label'];
	$link     = '<a href="' . esc_url( $config['canonical'] ) . '" target="_blank" rel="external noopener noreferrer">'
		. esc_html( sprintf( __( 'Buka di %s', 'queen-alfalah' ), $label ) )
		. queen_alfalah_icon( 'external' )
		. '</a>';

	if ( 'link' === $behavior ) {
		return '<section class="gallery-media gallery-media--link"><p>'
			. esc_html( sprintf( __( 'Konten ini tersedia di %s.', 'queen-alfalah' ), $label ) )
			. '</p>' . $link . '</section>';
	}

	$content = '';
	if ( 'instagram' === $config['kind'] ) {
		$content = '<blockquote class="instagram-media" data-instgrm-permalink="' . esc_url( $config['canonical'] ) . '" data-instgrm-version="14">'
			. '<a href="' . esc_url( $config['canonical'] ) . '" target="_blank" rel="external noopener noreferrer">'
			. esc_html( sprintf( __( 'Lihat konten %s', 'queen-alfalah' ), $label ) )
			. '</a></blockquote>';
	} else {
		$content = '<iframe title="' . esc_attr( sprintf( __( 'Konten galeri dari %s', 'queen-alfalah' ), $label ) ) . '"'
			. ' data-src="' . esc_url( $config['embed_url'] ) . '" loading="lazy"'
			. ' referrerpolicy="strict-origin-when-cross-origin" allow="' . esc_attr( $config['allow'] ) . '" allowfullscreen></iframe>';
	}

	$hidden         = 'click' === $behavior ? ' hidden' : '';
	$loaded_message = sprintf( __( 'Konten %s mulai dimuat.', 'queen-alfalah' ), $label );
	$error_message  = sprintf( __( 'Pratinjau %s gagal dimuat. Gunakan tautan sumber yang tersedia.', 'queen-alfalah' ), $label );
	$button = 'click' === $behavior
		? '<button class="gallery-embed__consent" type="button" data-gallery-load>'
			. queen_alfalah_icon( $source )
			. '<span><strong>' . esc_html( sprintf( __( 'Muat konten %s', 'queen-alfalah' ), $label ) ) . '</strong>'
			. '<small>' . esc_html__( 'Konten pihak ketiga baru dimuat setelah Anda memilih tombol ini.', 'queen-alfalah' ) . '</small></span></button>'
		: '';

	return '<section class="gallery-media gallery-media--social gallery-media--' . esc_attr( $source ) . '">'
		. '<div class="gallery-embed gallery-embed--' . esc_attr( $config['aspect'] ) . '" data-gallery-embed data-provider="' . esc_attr( $source ) . '" data-behavior="' . esc_attr( $behavior ) . '" data-loaded-message="' . esc_attr( $loaded_message ) . '" data-error-message="' . esc_attr( $error_message ) . '">'
		. $button
		. '<div class="gallery-embed__content" tabindex="-1"' . $hidden . '>' . $content . '</div>'
		. '<p class="screen-reader-text" aria-live="polite" data-gallery-status></p>'
		. '</div>'
		. '<p class="gallery-media__fallback">' . esc_html__( 'Jika pratinjau tidak dapat dimuat karena konten privat, dihapus, atau dibatasi platform, gunakan tautan berikut: ', 'queen-alfalah' ) . $link . '</p>'
		. '</section>';
}

/**
 * Build a safe WhatsApp deep link.
 *
 * @param string $message Prefilled message.
 * @return string
 */
function queen_alfalah_whatsapp_url( $message = '' ) {
	$number = preg_replace( '/\D+/', '', (string) queen_alfalah_school_info( 'whatsapp' ) );
	if ( ! $number ) {
		return '';
	}
	if ( 0 === strpos( $number, '0' ) ) {
		$number = '62' . substr( $number, 1 );
	}
	$url = 'https://wa.me/' . $number;
	if ( $message ) {
		$url .= '?text=' . rawurlencode( $message );
	}
	return $url;
}

/**
 * Public social links configured for the school.
 *
 * @return array<string,string>
 */
function queen_alfalah_social_links() {
	$links = array();
	foreach ( array( 'instagram', 'facebook', 'youtube', 'tiktok' ) as $network ) {
		$url = queen_alfalah_school_info( $network );
		if ( $url ) {
			$links[ $network ] = $url;
		}
	}
	return $links;
}

/**
 * Render links to share the current public URL without loading trackers.
 */
function queen_alfalah_share_links() {
	$url   = rawurlencode( get_permalink() );
	$title = rawurlencode( wp_strip_all_tags( get_the_title() ) );
	$links = array(
		'WhatsApp' => 'https://wa.me/?text=' . $title . '%20' . $url,
		'Facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . $url,
		'Telegram' => 'https://t.me/share/url?url=' . $url . '&text=' . $title,
	);
	?>
	<div class="share-links" aria-label="<?php esc_attr_e( 'Bagikan artikel', 'queen-alfalah' ); ?>">
		<span><?php esc_html_e( 'Bagikan:', 'queen-alfalah' ); ?></span>
		<?php foreach ( $links as $label => $link ) : ?>
			<a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $label ); ?><span class="screen-reader-text"> <?php esc_html_e( '(tab baru)', 'queen-alfalah' ); ?></span></a>
		<?php endforeach; ?>
	</div>
	<?php
}
