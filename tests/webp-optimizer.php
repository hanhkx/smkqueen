<?php
/** Static safety contract for the non-destructive WebP optimizer. */

$root   = dirname( __DIR__ );
$main   = file_get_contents( $root . '/queen-alfalah-core/queen-alfalah-core.php' );
$source = file_get_contents( $root . '/queen-alfalah-core/includes/class-qaf-core-webp-optimizer.php' );
$script = file_get_contents( $root . '/queen-alfalah-core/assets/js/webp-optimizer.js' );

$failures = array();
$assert   = static function ( $condition, $message ) use ( &$failures ) {
	if ( ! $condition ) {
		$failures[] = $message;
	}
};

$assert( false !== strpos( $main, 'class-qaf-core-webp-optimizer.php' ), 'Optimizer class is not loaded.' );
$assert( false !== strpos( $main, 'QAF_Core_WebP_Optimizer::init();' ), 'Optimizer hooks are not initialized.' );
$assert( false !== strpos( $source, "array( 'jpg', 'jpeg', 'png' )" ), 'Only safe static source formats should be converted.' );
$assert( false !== strpos( $source, '$destination = $source . \'.webp\';' ), 'WebP must be a sidecar, not overwrite the source.' );
$assert( false === strpos( $source, 'unlink(' ), 'Source deletion must use the WordPress filesystem helper.' );
$assert( false !== strpos( $source, 'wp_delete_file( $source )' ), 'Storage-saving mode does not remove migrated sources.' );
$assert( false !== strpos( $source, 'metadata_exists( \'post\', $attachment_id, self::BACKUP_META )' ), 'Original metadata backup guard is missing.' );
$assert( false !== strpos( $source, 'empty( $result[\'errors\'] )' ), 'Source files must not be deleted after conversion errors.' );
$assert( false !== strpos( $source, 'wp_update_attachment_metadata( $attachment_id, $new_metadata )' ), 'Attachment metadata is not migrated to WebP.' );
$assert( false !== strpos( $source, 'self::replace_content_urls( $file_map )' ), 'Post-body image URLs are not migrated.' );
$assert( false !== strpos( $source, "check_ajax_referer( self::NONCE_ACTION, 'nonce' )" ), 'AJAX batches need nonce verification.' );
$assert( false !== strpos( $source, "current_user_can( 'manage_options' )" ), 'Only administrators may run the migration.' );
$assert( false !== strpos( $source, "add_filter( 'wp_generate_attachment_metadata'" ), 'Future uploads are not optimized.' );
$assert( false !== strpos( $source, "add_filter( 'wp_calculate_image_srcset'" ), 'Responsive images are not served as WebP.' );
$assert( false !== strpos( $source, "HTTP_ACCEPT" ), 'Unsupported clients need an original-image fallback.' );
$assert( false !== strpos( $source, "(?=[\\s\\\"\\'\\(\\)<>]|$)" ), 'Direct URL rewriting must not match inside an existing .webp filename.' );
$assert( false !== strpos( $source, "wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) )" ), 'Server WebP support must be checked.' );
$assert( false !== strpos( $source, 'PROCESSED_META' ), 'Skipped or native WebP attachments need a completed marker.' );
$assert( false !== strpos( $script, 'runBatch(page + 1, force, deleteOriginals)' ), 'The admin runner must process bounded batches.' );
$assert( false !== strpos( $script, "body.set('deleteOriginals', deleteOriginals ? '1' : '0')" ), 'Destructive mode is not explicit per batch.' );
$assert( false !== strpos( $script, 'credentials: \'same-origin\'' ), 'The batch runner must keep the authenticated session.' );

if ( $failures ) {
	fwrite( STDERR, implode( PHP_EOL, $failures ) . PHP_EOL );
	exit( 1 );
}

echo "WebP optimizer contract passed.\n";
