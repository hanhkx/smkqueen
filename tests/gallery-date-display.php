<?php
define( 'ABSPATH', __DIR__ );
function check( $yes, $message ) { if ( ! $yes ) { throw new Exception( $message ); } }
function get_post_meta( $id, $key, $single ) { return $GLOBALS['date']; }
function wp_timezone() { return new DateTimeZone( 'Asia/Jakarta' ); }
function wp_date( $format, $timestamp, $timezone ) { return ( new DateTimeImmutable( '@' . $timestamp ) )->setTimezone( $timezone )->format( $format ); }
function esc_html__( $text, $domain ) { return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $text ) { return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $text ) { return esc_html( $text ); }
require dirname( __DIR__ ) . '/queen-alfalah/inc/template-tags.php';
$GLOBALS['date'] = '2026-08-24';
$html = queen_alfalah_gallery_date_html( 1 );
check( false !== strpos( $html, 'datetime="2026-08-24"' ) && false !== strpos( $html, 'Tanggal album:' ), 'Album date missing' );
foreach ( array('', '2026-02-31', '2026-8-24', '<script>alert(1)</script>') as $date ) {
	$GLOBALS['date'] = $date;
	$html = queen_alfalah_gallery_date_html( 1 );
	check( false !== strpos( $html, 'Tanggal kegiatan belum dicatat' ) && false === strpos( $html, '<time' ) && false === strpos( $html, '<script' ), 'Unknown date must not use publication date or unescaped content' );
}
foreach ( array('front-page.php', 'template-parts/content-card.php') as $file ) {
	check( false !== strpos( file_get_contents( dirname(__DIR__) . '/queen-alfalah/' . $file ), 'queen_alfalah_gallery_date_html' ), 'Gallery consumer missing date display' );
}
echo "PASS: album date labels/time markup on home/cards/detail helper and honest unknown-date display.\n";
