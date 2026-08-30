<?php
/**
 * Contract checks for square card thumbnails and non-destructive image fitting.
 */

$theme_root   = dirname( __DIR__ ) . '/queen-alfalah';
$css          = file_get_contents( $theme_root . '/style.css' );
$functions    = file_get_contents( $theme_root . '/functions.php' );
$card_template = file_get_contents( $theme_root . '/template-parts/content-card.php' );
$front_page   = file_get_contents( $theme_root . '/front-page.php' );
$errors       = array();

if ( false === $css || false === $functions || false === $card_template || false === $front_page ) {
	fwrite( STDERR, "FAIL: Theme source could not be read.\n" );
	exit( 1 );
}

/** Return the combined declarations for CSS rules containing a selector. */
function qaf_css_rule( $source, $selector ) {
	$declarations = array();
	if ( preg_match_all( '/([^{}]+)\{([^{}]*)\}/m', $source, $matches, PREG_SET_ORDER ) ) {
		foreach ( $matches as $match ) {
			if ( false !== strpos( $match[1], $selector ) ) {
				$declarations[] = $match[2];
			}
		}
	}
	return implode( "\n", $declarations );
}

/** Add a readable failure when a declaration contract is absent. */
function qaf_expect_css( $condition, $message, &$errors ) {
	if ( ! $condition ) {
		$errors[] = $message;
	}
}

$card_media = qaf_css_rule( $css, '.news-card__media' );
qaf_expect_css( '' !== $card_media, 'Card media rule is missing.', $errors );
qaf_expect_css( (bool) preg_match( '/aspect-ratio\s*:\s*1(?:\s*\/\s*1)?\s*;/', $card_media ), 'Card media must use a 1:1 aspect ratio.', $errors );
qaf_expect_css( (bool) preg_match( '/flex\s*:\s*0\s+0\s+auto\s*;/', $card_media ), 'Card media must not flex-shrink away from its 1:1 ratio.', $errors );

foreach ( array( '.news-card__image', '.program-card__media', '.program-card__image' ) as $selector ) {
	qaf_expect_css( false !== strpos( $css, $selector ), $selector . ' must remain covered by the shared card-media rule.', $errors );
}

$card_images = qaf_css_rule( $css, '.news-card__media img' );
qaf_expect_css( (bool) preg_match( '/width\s*:\s*100%\s*;/', $card_images ), 'Card images must fill the media width.', $errors );
qaf_expect_css( (bool) preg_match( '/height\s*:\s*100%\s*;/', $card_images ), 'Card images must fill the media height.', $errors );
qaf_expect_css( (bool) preg_match( '/object-fit\s*:\s*cover\s*;/', $card_images ), 'Photographic cards must keep cover as the default.', $errors );

$program_images = qaf_css_rule( $css, '.program-card__media img' );
qaf_expect_css( (bool) preg_match( '/object-fit\s*:\s*contain\s*;/', $program_images ), 'Program logos must be fully visible with object-fit: contain.', $errors );

$people_and_partner_images = qaf_css_rule( $css, '.news-card.type-qaf_teacher .news-card__media img' );
qaf_expect_css( (bool) preg_match( '/object-fit\s*:\s*contain\s*;/', $people_and_partner_images ), 'Teacher/staff and partner images must be fully visible with object-fit: contain.', $errors );
qaf_expect_css( false !== strpos( $css, '.news-card.type-qaf_partner .news-card__media img' ), 'Partner logos must share the non-cropping image rule.', $errors );

$gallery_images = qaf_css_rule( $css, '.gallery-grid img' );
qaf_expect_css( (bool) preg_match( '/aspect-ratio\s*:\s*1(?:\s*\/\s*1)?\s*;/', $gallery_images ), 'Gallery thumbnails must remain 1:1.', $errors );
qaf_expect_css( (bool) preg_match( '/object-fit\s*:\s*cover\s*;/', $gallery_images ), 'Gallery thumbnails must keep object-fit: cover.', $errors );

qaf_expect_css( (bool) preg_match( "/add_image_size\(\s*'queen-card-square'\s*,\s*720\s*,\s*720\s*,\s*true\s*\)/", $functions ), 'The theme must register a square cropped card image size.', $errors );
qaf_expect_css( (bool) preg_match( "/add_image_size\(\s*'queen-card-contain'\s*,\s*900\s*,\s*900\s*,\s*false\s*\)/", $functions ), 'The theme must register an uncropped image size for people and logos.', $errors );
qaf_expect_css( false !== strpos( $card_template, "'qaf_program', 'qaf_teacher', 'qaf_partner'" ), 'Program, teacher/staff, and partner cards must use the uncropped image path.', $errors );
qaf_expect_css( false !== strpos( $card_template, "'queen-card-contain' : 'queen-card-square'" ), 'Card templates must select the square or uncropped image size explicitly.', $errors );
qaf_expect_css( false !== strpos( $front_page, "the_post_thumbnail( 'queen-card-contain'" ), 'Homepage program logos must request the uncropped image size.', $errors );
qaf_expect_css( false !== strpos( $front_page, "the_post_thumbnail( 'queen-card-square'" ), 'Homepage gallery thumbnails must request the square image size.', $errors );

if ( $errors ) {
	foreach ( $errors as $error ) {
		fwrite( STDERR, 'FAIL: ' . $error . "\n" );
	}
	exit( 1 );
}

echo "Thumbnail ratio contract checks passed.\n";
