<?php
/** Run: php tests/theme-share-links.php */
define( 'ABSPATH', __DIR__ . '/' );
$GLOBALS['share_post'] = (object) array( 'ID' => 87, 'post_type' => 'post', 'post_status' => 'publish', 'post_password' => '' );
function get_post( $post = null ) { return $post ?: $GLOBALS['share_post']; }
function is_post_publicly_viewable( $post ) { return 'publish' === $post->post_status && 'qaf_private' !== $post->post_type; }
function is_preview() { return ! empty( $GLOBALS['share_preview'] ); }
function get_permalink() { return 'https://school.test/berita/karya?bagian=1&bahasa=id'; }
function get_the_title() { return '<b>Karya &amp; Prestasi</b> "Queen" – siswa'; }
function get_the_ID() { return $GLOBALS['share_post']->ID; }
function wp_strip_all_tags( $text ) { return strip_tags( $text ); }
function wp_specialchars_decode( $text, $flags = ENT_NOQUOTES ) { return htmlspecialchars_decode( $text, $flags ); }
function __( $text ) { return $text; }
function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $text ) { return esc_html( $text ); }
function esc_url( $text ) { return esc_attr( $text ); }
function esc_html_e( $text ) { echo esc_html( $text ); }
function esc_attr_e( $text ) { echo esc_attr( $text ); }
function absint( $value ) { return abs( (int) $value ); }
function sanitize_html_class( $text ) { return preg_replace( '/[^a-z0-9_-]/i', '', $text ); }
require dirname( __DIR__ ) . '/queen-alfalah/inc/template-tags.php';
$errors = array();
function check_share( $condition, $message ) { if ( ! $condition ) { $GLOBALS['errors'][] = $message; } }
function render_share() { ob_start(); queen_alfalah_share_links(); return ob_get_clean(); }
$html = render_share();
$dom = new DOMDocument();
@$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $html );
$xpath = new DOMXPath( $dom );
check_share( 'Karya & Prestasi "Queen" – siswa' === $xpath->query( '//section' )->item( 0 )->getAttribute( 'data-share-title' ), 'The share title must be decoded once and escaped safely.' );
$links = $xpath->query( '//a' );
check_share( 4 === $links->length, 'Exactly four direct network links are required.' );
$expected = array( 'wa.me', 'www.facebook.com', 't.me', 'x.com' );
foreach ( $links as $index => $link ) {
	check_share( $expected[ $index ] === parse_url( $link->getAttribute( 'href' ), PHP_URL_HOST ), 'Wrong network destination.' );
	parse_str( parse_url( $link->getAttribute( 'href' ), PHP_URL_QUERY ), $query );
	check_share( false !== strpos( implode( ' ', $query ), get_permalink() ), 'The canonical URL must survive decoding, including query delimiters.' );
	check_share( 1 === $link->getElementsByTagName( 'svg' )->length, 'Each network needs a local SVG icon.' );
	check_share( false !== strpos( $link->getAttribute( 'rel' ), 'noopener' ) && false !== strpos( $link->getAttribute( 'rel' ), 'noreferrer' ), 'External links must isolate their opener.' );
	check_share( false !== strpos( $link->getAttribute( 'aria-label' ), 'tab baru' ), 'New tabs need accessible disclosure.' );
}
check_share( false !== strpos( $html, 'data-share-copy' ) && false !== strpos( $html, 'data-share-native' ), 'Copy and native-share controls are required.' );
check_share( false !== strpos( $html, 'role="status"' ), 'Copy feedback must be announced accessibly.' );
check_share( false !== strpos( $html, 'readonly' ) && false !== strpos( $html, '<label' ), 'Clipboard failure needs a labelled manual-copy field.' );
check_share( false === strpos( $html, '<script' ) && false === strpos( $html, '<iframe' ), 'Sharing must not load trackers or third-party embeds.' );
$css = file_get_contents( dirname( __DIR__ ) . '/queen-alfalah/style.css' );
check_share( false !== strpos( $css, 'min-height: max(2.75rem, 44px)' ), 'Touch targets must stay at least 44px even when the root font is smaller.' );
foreach ( array( 'post', 'qaf_gallery', 'qaf_achievement', 'qaf_notice', 'qaf_teacher', 'qaf_facility', 'qaf_program', 'qaf_extra', 'qaf_partner', 'qaf_agenda', 'qaf_service', 'qaf_vacancy', 'qaf_alumni' ) as $type ) {
	$GLOBALS['share_post']->post_type = $type;
	check_share( '' !== trim( render_share() ), 'Public type missing: ' . $type );
}
foreach ( array( 'page', 'attachment', 'qaf_private' ) as $type ) {
	$GLOBALS['share_post']->post_type = $type;
	check_share( '' === trim( render_share() ), 'Sharing must be omitted for ' . $type );
}
$GLOBALS['share_post']->post_type = 'post';
foreach ( array( 'draft', 'private', 'future', 'trash', 'pending' ) as $status ) {
	$GLOBALS['share_post']->post_status = $status;
	check_share( '' === trim( render_share() ), 'Non-public status must not be shareable: ' . $status );
}
$GLOBALS['share_post']->post_status = 'publish';
$GLOBALS['share_post']->post_password = 'protected';
check_share( '' === trim( render_share() ), 'Password-protected entries must not be promoted even after unlocking.' );
$GLOBALS['share_post']->post_password = '0';
check_share( '' === trim( render_share() ), 'A zero-like password still protects a post.' );
$GLOBALS['share_post']->post_password = '';
$GLOBALS['share_preview'] = true;
check_share( '' === trim( render_share() ), 'Preview content must not be shared.' );
if ( $errors ) { fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL ); exit( 1 ); }
echo "Theme share-link checks passed.\n";
