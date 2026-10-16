<?php
/**
 * Contract checks for Gallery video poster rendering.
 */

$template_tags = file_get_contents( dirname( __DIR__ ) . '/queen-alfalah/inc/template-tags.php' );

if ( false === $template_tags ) {
	fwrite( STDERR, "FAIL: Theme template tags could not be read.\n" );
	exit( 1 );
}

$checks = array(
	'Local Gallery video reads the featured image ID.' => false !== strpos( $template_tags, 'get_post_thumbnail_id( $post_id )' ),
	'Local Gallery video resolves the full poster URL.' => false !== strpos( $template_tags, "wp_get_attachment_image_url( \$poster_id, 'full' )" ),
	'Poster is passed to the WordPress video shortcode.' => false !== strpos( $template_tags, "\$video_args['poster'] = \$poster_url" ),
	'Video keeps metadata-only preload.'               => false !== strpos( $template_tags, "'preload' => 'metadata'" ),
);

foreach ( $checks as $message => $passed ) {
	if ( ! $passed ) {
		fwrite( STDERR, 'FAIL: ' . $message . "\n" );
		exit( 1 );
	}
}

echo "Gallery video poster contract checks passed.\n";
