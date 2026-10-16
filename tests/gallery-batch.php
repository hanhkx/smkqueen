<?php
define( 'ABSPATH', __DIR__ );
require dirname( __DIR__ ) . '/queen-alfalah-core/includes/class-qaf-core-gallery-batch.php';
function check( $value, $message ) { if ( ! $value ) { throw new Exception( $message ); } }
$row = array( 'shortcode' => 'DanPRGwJJMv', 'video' => 'smkqueenalfalah_official_1783688115_3938183545337713455_5484283193.mp4', 'poster' => 'sampul-galeri-38.jpg', 'title' => 'Sosialisasi MPLS', 'caption' => 'Pertemuan wali murid.', 'excerpt' => 'MPLS 2026.' );
check( '' === QAF_Core_Gallery_Batch::validate( array( $row ) ), 'Valid row rejected' );
check( '' !== QAF_Core_Gallery_Batch::validate( array( $row, $row ) ), 'Duplicate accepted' );
check( '' !== QAF_Core_Gallery_Batch::validate( array() ), 'Empty accepted' );
$bad = $row; $bad['video'] = 'smaqueen.mp4';
check( '' !== QAF_Core_Gallery_Batch::validate( array( $bad ) ), 'Wrong account accepted' );
$bad = $row; $bad['poster'] = '../secret.jpg';
check( '' !== QAF_Core_Gallery_Batch::validate( array( $bad ) ), 'Path traversal accepted' );
$bad = $row; $bad['caption'] = '';
check( '' !== QAF_Core_Gallery_Batch::validate( array( $bad ) ), 'Empty caption accepted' );
function get_posts( $args ) { return array_keys( $GLOBALS['files'] ); }
function get_post_meta( $id, $key, $single ) { return $GLOBALS['files'][$id][0]; }
function get_post_mime_type( $id ) { return $GLOBALS['files'][$id][1]; }
function wp_get_attachment_url( $id ) { return 'https://example.test/' . $GLOBALS['files'][$id][0]; }
$GLOBALS['files'] = array( 1 => array( '2026/09/sampul.jpg', 'image/jpeg' ), 2 => array( '2026/09/not-sampul.jpg', 'image/jpeg' ) );
check( 1 === QAF_Core_Gallery_Batch::attachment( 'sampul.jpg', 'image/' ), 'Exact match failed' );
check( 0 === QAF_Core_Gallery_Batch::attachment( 'sampul.jpg', 'video/' ), 'MIME mismatch accepted' );
$GLOBALS['files'][3] = array( '2026/08/sampul.jpg', 'image/jpeg' );
check( 0 === QAF_Core_Gallery_Batch::attachment( 'sampul.jpg', 'image/' ), 'Ambiguous match accepted' );
echo "PASS: manifest validation, account restrictions, duplicate rejection, exact filename/MIME and ambiguous-media rejection.\n";
