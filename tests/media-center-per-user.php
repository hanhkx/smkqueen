<?php
/** Dependency-free behavior checks for per-account Pusat Media folders. */
define( 'ABSPATH', __DIR__ );

class WP_User {
	public $ID;
	public $roles;
	public $display_name;
	public function __construct( $id, $name, $roles ) {
		$this->ID           = (int) $id;
		$this->display_name = $name;
		$this->roles        = $roles;
	}
}

$GLOBALS['qaf_media_user_meta'] = array();
$GLOBALS['qaf_media_settings']  = array(
	'media_drive_waka_url'    => 'https://drive.google.com/drive/folders/waka',
	'media_drive_teacher_url' => 'https://drive.google.com/drive/folders/guru',
	'media_drive_staff_url'   => 'https://drive.google.com/drive/folders/tendik',
);

function esc_url_raw( $value, $protocols = null ) {
	unset( $protocols );
	return filter_var( (string) $value, FILTER_VALIDATE_URL ) ? (string) $value : '';
}
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function get_user_meta( $user_id, $key ) {
	return isset( $GLOBALS['qaf_media_user_meta'][ $user_id ][ $key ] ) ? $GLOBALS['qaf_media_user_meta'][ $user_id ][ $key ] : '';
}
function user_can( $user, $capability ) {
	return $user instanceof WP_User && 'manage_options' === $capability && in_array( 'administrator', $user->roles, true );
}
function qaf_core_get_setting( $key, $default = '' ) {
	return isset( $GLOBALS['qaf_media_settings'][ $key ] ) ? $GLOBALS['qaf_media_settings'][ $key ] : $default;
}

require dirname( __DIR__ ) . '/queen-alfalah-core/includes/class-qaf-core-media-center.php';

function verify_media_center( $ok, $label ) {
	if ( ! $ok ) {
		fwrite( STDERR, "FAIL: {$label}\n" );
		exit( 1 );
	}
}

verify_media_center( 'https://drive.google.com/drive/folders/abc' === QAF_Core_Media_Center::sanitize_drive_url( 'https://drive.google.com/drive/folders/abc' ), 'Drive HTTPS URL accepted' );
verify_media_center( 'https://docs.google.com/document/d/abc' === QAF_Core_Media_Center::sanitize_drive_url( 'https://docs.google.com/document/d/abc' ), 'Docs HTTPS URL accepted' );
verify_media_center( '' === QAF_Core_Media_Center::sanitize_drive_url( 'http://drive.google.com/drive/folders/abc' ), 'HTTP rejected' );
verify_media_center( '' === QAF_Core_Media_Center::sanitize_drive_url( 'https://drive.google.com.evil.example/abc' ), 'look-alike host rejected' );

$method = new ReflectionMethod( 'QAF_Core_Media_Center', 'drive_links_for_user' );
$method->setAccessible( true );
$guru = new WP_User( 12, 'Garina Rahmi', array( 'qaf_teacher' ) );

$fallback = $method->invoke( null, $guru );
verify_media_center( 1 === count( $fallback ) && 'Guru' === $fallback[0]['category'], 'division fallback used before personal folder exists' );

$GLOBALS['qaf_media_user_meta'][12][QAF_Core_Media_Center::DRIVE_URL_META] = 'https://drive.google.com/drive/folders/garina';
$personal = $method->invoke( null, $guru );
verify_media_center( 1 === count( $personal ) && 'Pribadi' === $personal[0]['category'], 'personal folder takes priority' );
verify_media_center( 'https://drive.google.com/drive/folders/garina' === $personal[0]['url'], 'personal folder URL returned unchanged' );

$source = file_get_contents( dirname( __DIR__ ) . '/queen-alfalah-core/includes/class-qaf-core-media-center.php' );
verify_media_center( false !== strpos( $source, 'Folder Google Drive akun' ), 'profile field is available' );
verify_media_center( false !== strpos( $source, 'Folder Pusat Media' ), 'user list status column is available' );
verify_media_center( false === strpos( $source, 'deletePermission' ) && false === strpos( $source, 'files->delete' ), 'no cloud deletion hook exists' );

echo "Pusat Media per-account folder contracts passed with strict Drive URLs, personal priority, division fallback, and no deletion hook.\n";
