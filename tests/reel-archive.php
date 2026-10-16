<?php
define( 'ABSPATH', __DIR__ );
define( 'QAF_CORE_PATH', dirname( __DIR__ ) . '/queen-alfalah-core/' );
require QAF_CORE_PATH . 'includes/class-qaf-core-reel-archive.php';
function get_post_stati() { return array( 'publish', 'draft', 'trash' ); }
function get_posts( $args ) {
	if ( ! in_array( 'trash', $args['post_status'], true ) ) { throw new Exception( 'Trash must block duplicate creation.' ); }
	return array_keys( $GLOBALS['meta'] );
}
function get_post_meta( $id, $key ) { return $GLOBALS['meta'][ $id ][ $key ] ?? ''; }
function wp_parse_url( $url, $part ) { return parse_url( $url, $part ); }
function verify( $condition, $message ) { if ( ! $condition ) { throw new Exception( $message ); } }
$data = QAF_Core_Reel_Archive::data();
verify( 25 === count( $data['items'] ), 'Expected 25 official Reels.' );
$codes = array();
foreach ( $data['items'] as $item ) {
	verify( ! isset( $codes[ $item['shortcode'] ] ), 'Duplicate shortcode.' );
	$codes[ $item['shortcode'] ] = true;
	verify( $item['date'] >= '2026-07-01' && $item['date'] <= '2026-09-05', 'Date outside requested period.' );
	verify( 'https://www.instagram.com/smkqueenalfalah_official/reel/' . $item['shortcode'] . '/' === $item['source_url'], 'Wrong source account.' );
}
verify( ! isset( $codes['DcDkUc0SqtS'] ), 'SMA collaborator must be excluded.' );
$GLOBALS['meta'] = array( 1 => array( '_qaf_video_url' => 'https://www.instagram.com/reel/DccNbNiSNs2/' ), 2 => array( '_qaf_manual_reel_shortcode' => 'DaVSz_0pq-Y' ), 3 => array( '_qaf_video_url' => 'https://evil.test/reel/Dcm_rkFJZCy/' ) );
verify( 1 === QAF_Core_Reel_Archive::find_existing( 'DccNbNiSNs2' ), 'Legacy embed not recognized.' );
verify( 2 === QAF_Core_Reel_Archive::find_existing( 'DaVSz_0pq-Y' ), 'Archive marker not recognized.' );
verify( 0 === QAF_Core_Reel_Archive::find_existing( 'Dcm_rkFJZCy' ), 'Untrusted host must not match.' );
echo "PASS: 25 official Reels, exact date range, collaborator exclusion, and legacy/Trash-aware duplicate matching.\n";
