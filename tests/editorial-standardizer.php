<?php
/** Static safety contract for reversible narrative standardization. */

$root   = dirname( __DIR__ );
$main   = file_get_contents( $root . '/queen-alfalah-core/queen-alfalah-core.php' );
$source = file_get_contents( $root . '/queen-alfalah-core/includes/class-qaf-core-editorial-standardizer.php' );

$failures = array();
$assert   = static function ( $condition, $message ) use ( &$failures ) {
	if ( ! $condition ) {
		$failures[] = $message;
	}
};

$assert( false !== strpos( $main, 'class-qaf-core-editorial-standardizer.php' ), 'Standardizer class is not loaded.' );
$assert( false !== strpos( $main, 'QAF_Core_Editorial_Standardizer::init();' ), 'Standardizer hooks are not initialized.' );
$assert( false !== strpos( $source, "'post'            => 'Berita'" ), 'News is missing from the target list.' );
$assert( false !== strpos( $source, "'qaf_gallery'     => 'Galeri'" ), 'Gallery is missing from the target list.' );
$assert( false !== strpos( $source, "'qaf_achievement' => 'Prestasi'" ), 'Achievements are missing from the target list.' );
$assert( false !== strpos( $source, "'qaf_work'        => 'Karya Siswa'" ), 'Student works are missing from the target list.' );
$assert( false !== strpos( $source, "'post_status'            => 'publish'" ), 'Only public posts should be queried.' );
$assert( false !== strpos( $source, 'metadata_exists( \'post\', $post_id, self::BACKUP_META )' ), 'Immutable original backup guard is missing.' );
$assert( false !== strpos( $source, 'add_post_meta( $post_id, self::BACKUP_META, $backup, true )' ), 'Original content is not backed up uniquely.' );
$assert( false !== strpos( $source, 'check_admin_referer( $action )' ), 'Mutation actions are missing nonce verification.' );
$assert( false !== strpos( $source, "'POST' !== ( \$_SERVER['REQUEST_METHOD'] ?? '' )" ), 'Mutation actions are not restricted to POST.' );
$assert( false !== strpos( $source, "current_user_can( 'edit_post', \$post_id )" ), 'Per-post edit permission check is missing.' );
$assert( false !== strpos( $source, 'post_content' ) && false !== strpos( $source, 'post_excerpt' ), 'Content and excerpt are not both handled.' );
$assert( false !== strpos( $source, 'Original HTML, embeds, media, and links remain intact' ), 'Media-preservation contract is not documented.' );
$assert( false !== strpos( $source, 'function rollback()' ), 'Rollback path is missing.' );
$assert( false !== strpos( $source, 'delete_post_meta( $post_id, self::VERSION_META )' ), 'Rollback does not clear the applied-version marker.' );
$assert( false !== strpos( $source, 'topic_lens' ), 'Context-sensitive narrative lens is missing.' );
$assert( false !== strpos( $source, '$source_post = clone $post;' ), 'Later editorial versions must rebuild from the immutable original.' );
$assert( false !== strpos( $source, 'self::keyword_matches( $text, $keyword )' ), 'Short keywords need token-safe matching.' );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ );
}
if ( ! function_exists( 'remove_accents' ) ) {
	function remove_accents( $text ) { return $text; }
}
if ( ! function_exists( 'wp_strip_all_tags' ) ) {
	function wp_strip_all_tags( $text ) { return strip_tags( $text ); }
}
require_once $root . '/queen-alfalah-core/includes/class-qaf-core-editorial-standardizer.php';

$work_lens = QAF_Core_Editorial_Standardizer::topic_lens( 'Video profil karya siswa untuk mengembangkan bakat produksi multimedia' );
$assert( 'Kreativitas yang menjadi kompetensi' === $work_lens['heading'], 'UKS must not match inside the word produksi.' );
$eid_lens = QAF_Core_Editorial_Standardizer::topic_lens( 'Silaturahmi pasca-Idulfitri berlangsung hangat di lingkungan sekolah' );
$assert( 'Karakter dan kebersamaan' === $eid_lens['heading'], 'Idulfitri context must win over a generic school-environment phrase.' );
$competition_lens = QAF_Core_Editorial_Standardizer::topic_lens( 'Budaya kompetisi membangun kesiapan siswa menuju dunia kerja' );
$assert( 'Pengalaman berharga di balik capaian' === $competition_lens['heading'], 'Competition context must win over a secondary world-of-work phrase.' );

if ( $failures ) {
	fwrite( STDERR, implode( PHP_EOL, $failures ) . PHP_EOL );
	exit( 1 );
}

echo "Editorial standardizer contract passed.\n";
