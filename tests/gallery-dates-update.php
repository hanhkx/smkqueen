<?php
/** Metadata-only importer at WordPress persistence and permission boundaries. */
define( 'ABSPATH', __DIR__ );
function check( $yes, $message ) { if ( ! $yes ) { throw new Exception( $message ); } }
class QAF_Core_Reel_Archive { const SOURCE_META = '_qaf_manual_reel_shortcode'; }
function get_posts( $args ) { return array_keys( array_filter( $GLOBALS['records'], static function( $r ) { return 'qaf_gallery' === $r['type']; } ) ); }
function get_post_stati() { return array( 'publish', 'draft', 'trash', 'private' ); }
function get_post_meta( $id, $key, $single ) { return $GLOBALS['records'][$id]['meta'][$key] ?? ''; }
function get_post_type( $id ) { return $GLOBALS['records'][$id]['type']; }
function get_post_status( $id ) { return $GLOBALS['records'][$id]['status']; }
function wp_parse_url( $url, $part ) { return parse_url( $url, $part ); }
function current_user_can( $cap, $id = 0 ) { return 'manage_options' === $cap ? ( $GLOBALS['manager'] ?? false ) : 4 !== $id; }
function metadata_exists( $kind, $id, $key ) { return array_key_exists( $key, $GLOBALS['records'][$id]['meta'] ); }
function update_post_meta( $id, $key, $value ) {
	if ( ( $GLOBALS['fail_key'] ?? '' ) === $key ) { return false; }
	$GLOBALS['records'][$id]['meta'][$key] = stripslashes( $value ); return true;
}
function sanitize_textarea_field( $text ) { return trim( strip_tags( $text ) ); }
function wp_slash( $text ) { return addslashes( $text ); }
function wp_die( $message, $title = '', $args = array() ) { throw new RuntimeException( $message ); }
function check_admin_referer( $action ) { throw new RuntimeException( 'Invalid nonce' ); }
require dirname( __DIR__ ) . '/queen-alfalah-core/includes/class-qaf-core-gallery-dates.php';
$GLOBALS['records'] = array();
foreach ( array( 1 => 'publish', 2 => 'draft', 3 => 'trash', 4 => 'publish' ) as $id => $status ) {
	$GLOBALS['records'][$id] = array( 'type' => 'qaf_gallery', 'status' => $status, 'title' => 'Existing title', 'post_date' => '2026-09-06', 'content' => 'Existing caption', 'meta' => array( '_qaf_manual_reel_shortcode' => 'ExampleCode' . $id, '_qaf_album_date' => '2026-07-03', '_qaf_gallery_local_video_id' => '100' ) );
}
$row = static function( $code ) { return array( 'shortcode' => $code, 'album_date' => '2026-07-02', 'evidence' => "Banner: Guru's workshop, 2 Juli 2026" ); };
$before = $GLOBALS['records'];
$log = QAF_Core_Gallery_Dates::apply_rows( array( $row('ExampleCode1'), $row('ExampleCode2'), $row('ExampleCode3'), $row('ExampleCode4'), $row('UnknownCode') ) );
check( 5 === count( $log ), 'Each row needs outcome' );
foreach ( array(1,2) as $id ) {
	check( '2026-07-02' === get_post_meta( $id, '_qaf_album_date', true ), 'Date not updated' );
	check( '2026-07-03' === get_post_meta( $id, '_qaf_album_date_before_review', true ), 'Original date not backed up' );
	check( $row('ExampleCode1')['evidence'] === get_post_meta( $id, '_qaf_album_date_evidence', true ), 'Evidence changed' );
	foreach ( array('type','status','title','post_date','content') as $key ) { check( $before[$id][$key] === $GLOBALS['records'][$id][$key], 'Non-date field changed' ); }
	check( '100' === get_post_meta( $id, '_qaf_gallery_local_video_id', true ), 'Media changed' );
}
check( $before[3] === $GLOBALS['records'][3] && $before[4] === $GLOBALS['records'][4], 'Trash or forbidden entry changed' );
$GLOBALS['records'][5] = $before[1]; $GLOBALS['records'][5]['status'] = 'trash';
$snapshot = $GLOBALS['records'];
QAF_Core_Gallery_Dates::apply_rows( array( $row('ExampleCode1') ) );
check( $snapshot === $GLOBALS['records'], 'Ambiguous published+Trash source changed' );
unset( $GLOBALS['records'][5] );
check( 0 === QAF_Core_Gallery_Dates::find_target( 'exampleCode1' ), 'Case-insensitive shortcode accepted' );
$GLOBALS['records'][5] = $before[1]; $GLOBALS['records'][5]['meta'] = array( '_qaf_video_url' => 'https://www.instagram.com/reel/UrlExampleCode/' );
check( 5 === QAF_Core_Gallery_Dates::find_target( 'UrlExampleCode' ), 'Legacy exact URL not matched' );
check( 0 === QAF_Core_Gallery_Dates::find_target( 'UrlExample' ), 'Prefix URL incorrectly matched' );
$retry = $row('ExampleCode1'); $retry['album_date'] = '2026-07-01';
QAF_Core_Gallery_Dates::apply_rows( array( $retry ) );
check( '2026-07-03' === get_post_meta( 1, '_qaf_album_date_before_review', true ), 'Original backup overwritten on retry' );
foreach ( array( '_qaf_album_date_before_review', '_qaf_album_date_evidence' ) as $failure ) {
	$GLOBALS['records'][1] = $before[1]; $GLOBALS['fail_key'] = $failure;
	QAF_Core_Gallery_Dates::apply_rows( array( $row('ExampleCode1') ) );
	check( '2026-07-03' === get_post_meta( 1, '_qaf_album_date', true ), 'Date changed despite backup/evidence write failure' );
}
$GLOBALS['fail_key'] = '';
$snapshot = $GLOBALS['records'];
foreach ( array( array(false,'POST','Akses ditolak.'), array(true,'GET','POST diperlukan.'), array(true,'POST','Invalid nonce') ) as $case ) {
	$GLOBALS['manager'] = $case[0]; $_SERVER['REQUEST_METHOD'] = $case[1];
	try { QAF_Core_Gallery_Dates::process(); throw new Exception('Unsafe request accepted'); }
	catch ( RuntimeException $error ) { check( $case[2] === $error->getMessage(), 'Wrong security failure' ); }
	check( $snapshot === $GLOBALS['records'], 'Rejected request wrote data' );
}
echo "PASS: metadata-only updates, original backups, unchanged drafts/media/content, unknown/Trash/ambiguous/forbidden records, exact source matching, write failures, request/permission/nonce guards.\n";
