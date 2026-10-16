<?php
/**
 * Static contract checks for the standard editorial article experience.
 */

$root     = dirname( __DIR__ );
$helpers  = file_get_contents( $root . '/queen-alfalah/inc/template-tags.php' );
$template = file_get_contents( $root . '/queen-alfalah/template-parts/content-single.php' );
$style    = file_get_contents( $root . '/queen-alfalah/style.css' );

$failures = array();
$assert   = static function ( $condition, $message ) use ( &$failures ) {
	if ( ! $condition ) {
		$failures[] = $message;
	}
};

$assert( false !== strpos( $helpers, "array( 'post', 'qaf_gallery', 'qaf_achievement', 'qaf_work' )" ), 'Editorial experience must be limited to the four requested content types.' );
$assert( false !== strpos( $helpers, 'function queen_alfalah_article_intro' ), 'Article intro helper is missing.' );
$assert( false !== strpos( $helpers, 'function queen_alfalah_article_cta' ), 'Article CTA helper is missing.' );
$assert( false !== strpos( $helpers, 'function queen_alfalah_related_articles' ), 'Related-article helper is missing.' );
$assert( false !== strpos( $helpers, "'post_status'         => 'publish'" ), 'Related content must only include published posts.' );
$assert( false !== strpos( $helpers, "'post__not_in'        => array( \$post->ID )" ), 'The current article must be excluded from recommendations.' );
$assert( false !== strpos( $helpers, "min( 3, absint( \$limit ) )" ), 'Related content must be capped at three cards.' );
$assert( false !== strpos( $helpers, 'Lihat Berita Terbaru' ), 'News CTA copy is missing.' );
$assert( false !== strpos( $helpers, 'Lihat Semua Galeri' ), 'Gallery CTA copy is missing.' );
$assert( false !== strpos( $helpers, 'Lihat Semua Prestasi' ), 'Achievement CTA copy is missing.' );
$assert( false !== strpos( $helpers, 'Lihat Semua Karya' ), 'Student-work CTA copy is missing.' );

$intro_position   = strpos( $template, 'queen_alfalah_article_intro();' );
$content_position = strpos( $template, '<div class="entry-content">' );
$cta_position     = strpos( $template, 'queen_alfalah_article_cta();' );
$related_position = strpos( $template, 'queen_alfalah_related_articles();' );
$share_position   = strpos( $template, 'queen_alfalah_share_links();' );

$assert( false !== $intro_position && false !== $content_position && $intro_position < $content_position, 'Editorial intro must appear before the stored narrative.' );
$assert( false !== $cta_position && $cta_position > $content_position, 'CTA must appear after the stored narrative.' );
$assert( false !== $related_position && $related_position > $cta_position, 'Related content must appear after the CTA.' );
$assert( false !== $share_position && $share_position > $related_position, 'Existing share actions must remain after editorial recommendations.' );

foreach ( array( '.article-intro', '.article-cta', '.related-articles__grid', '.related-article-card' ) as $selector ) {
	$assert( false !== strpos( $style, $selector ), 'Missing responsive style for ' . $selector . '.' );
}

if ( $failures ) {
	fwrite( STDERR, implode( PHP_EOL, $failures ) . PHP_EOL );
	exit( 1 );
}

echo "Article experience contract passed.\n";
