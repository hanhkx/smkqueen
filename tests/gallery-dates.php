<?php
/** Album-date sorting contract, executed against an in-memory SQL fixture. */
define( 'ABSPATH', __DIR__ );
function check( $condition, $message ) { if ( ! $condition ) { throw new Exception( $message ); } }
function is_admin() { return $GLOBALS['admin'] ?? false; }
class GalleryQuery {
	private $type; public $singular = false;
	function __construct( $type = 'qaf_gallery' ) { $this->type = $type; }
	function get( $key ) { return 'post_type' === $key ? $this->type : null; }
	function is_singular() { return $this->singular; }
	function is_tax( $name ) { return false; }
}
require dirname( __DIR__ ) . '/queen-alfalah-core/includes/class-qaf-core-gallery-dates.php';
check( QAF_Core_Gallery_Dates::valid_date( '2026-07-15' ), 'Valid date rejected' );
foreach ( array( '', '2026-02-29', '2026-13-01', '15-07-2026', '2026-7-15', array() ) as $date ) {
	check( ! QAF_Core_Gallery_Dates::valid_date( $date ), 'Invalid date accepted' );
}
check( QAF_Core_Gallery_Dates::valid_date( '2024-02-29' ), 'Leap day rejected' );
$GLOBALS['wpdb'] = (object) array( 'posts' => 'posts', 'postmeta' => 'postmeta' );
$query = new GalleryQuery();
$order = QAF_Core_Gallery_Dates::order_by_album_date( 'posts.post_date DESC', $query );
$db = new SQLite3( ':memory:' );
$db->createFunction( 'regexp', static function( $pattern, $value ) { return preg_match( '~' . $pattern . '~', (string) $value ); }, 2 );
$db->exec( 'CREATE TABLE posts (ID INTEGER PRIMARY KEY, post_date TEXT)' );
$db->exec( 'CREATE TABLE postmeta (post_id INTEGER, meta_key TEXT, meta_value TEXT)' );
$db->exec( "INSERT INTO posts VALUES (1,'2026-09-06'),(2,'2026-08-25'),(3,'2026-09-07'),(4,'2026-09-08'),(5,'2026-09-06'),(6,'2026-09-09'),(7,'2026-09-01'),(8,'2026-09-01'),(9,'2026-09-01')" );
$db->exec( "INSERT INTO postmeta VALUES (1,'_qaf_album_date','2026-07-15'),(2,'_qaf_album_date','2026-08-25'),(3,'_qaf_album_date',''),(5,'_qaf_album_date','2026-07-15'),(5,'_qaf_album_date','2026-07-15'),(6,'_qaf_album_date','invalid')" );
$db->exec( "INSERT INTO postmeta VALUES (7,'_qaf_album_date','2026-08-32'),(8,'_qaf_album_date','2026-02-31'),(9,'_qaf_album_date','2024-02-29')" );
$result = $db->query( 'SELECT ID FROM posts ORDER BY ' . $order );
$ids = array(); while ( $row = $result->fetchArray( SQLITE3_ASSOC ) ) { $ids[] = $row['ID']; }
check( array( 2, 5, 1, 9, 7, 8, 3, 4, 6 ) === $ids, 'Dated albums must stay newest first; undated/invalid albums follow oldest publication first with ascending ID ties' );
$result = $db->query( 'SELECT ID FROM posts ORDER BY ' . $order . ' LIMIT 2 OFFSET 2' );
$ids = array(); while ( $row = $result->fetchArray( SQLITE3_ASSOC ) ) { $ids[] = $row['ID']; }
check( array( 1, 9 ) === $ids, 'Pagination changed' );
$result = $db->query( 'SELECT ID FROM posts ORDER BY ' . $order . ' LIMIT 3 OFFSET 4' );
$ids = array(); while ( $row = $result->fetchArray( SQLITE3_ASSOC ) ) { $ids[] = $row['ID']; }
check( array( 7, 8, 3 ) === $ids, 'First undated page must contain the oldest published albums, not newly published ones' );
check( 'original' === QAF_Core_Gallery_Dates::order_by_album_date( 'original', new GalleryQuery( 'post' ) ), 'News query changed' );
$query->singular = true;
check( 'original' === QAF_Core_Gallery_Dates::order_by_album_date( 'original', $query ), 'Singular query changed' );
$GLOBALS['admin'] = true;
check( 'original' === QAF_Core_Gallery_Dates::order_by_album_date( 'original', new GalleryQuery() ), 'Admin query changed' );
$good = array( 'shortcode' => 'DanPRGwJJMv', 'album_date' => '2026-07-15', 'evidence' => 'Teks video: 15 JULI 2026' );
check( '' === QAF_Core_Gallery_Dates::validate_rows( array( $good ) ), 'Valid date manifest rejected' );
check( '' !== QAF_Core_Gallery_Dates::validate_rows( array( $good, $good ) ), 'Duplicate date source accepted' );
$bad = $good; $bad['album_date'] = '2026-02-30';
check( '' !== QAF_Core_Gallery_Dates::validate_rows( array( $bad ) ), 'Invalid manifest date accepted' );
$bad = $good; $bad['evidence'] = '';
check( '' !== QAF_Core_Gallery_Dates::validate_rows( array( $bad ) ), 'Date without evidence accepted' );
echo "PASS: album-date SQL ordering, pagination, missing dates, duplicate metadata, scope, strict dates and manifest validation.\n";
