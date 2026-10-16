<?php
/**
 * Dependency-free behavior checks for focused Waka capabilities and provisioning.
 */

define( 'ABSPATH', __DIR__ . '/wordpress-stub/' );

class WP_Error {
	private $message;
	public function __construct( $code, $message ) {
		unset( $code );
		$this->message = $message;
	}
	public function get_error_message() {
		return $this->message;
	}
}

class WP_User {
	public $ID;
	public $roles;
	public $display_name;
	public $user_login;
	public $user_pass;
	public function __construct( $id, $login, $display_name, $roles = array() ) {
		$this->ID           = (int) $id;
		$this->user_login   = $login;
		$this->display_name = $display_name;
		$this->roles        = $roles;
		$this->user_pass    = '';
	}
}

class WP_Role {
	public $capabilities;
	public function __construct( $capabilities ) {
		$this->capabilities = $capabilities;
	}
	public function has_cap( $capability ) {
		return ! empty( $this->capabilities[ $capability ] );
	}
	public function add_cap( $capability ) {
		$this->capabilities[ $capability ] = true;
	}
	public function remove_cap( $capability ) {
		unset( $this->capabilities[ $capability ] );
	}
}

$GLOBALS['qaf_waka_is_admin']       = true;
$GLOBALS['qaf_waka_options']        = array();
$GLOBALS['qaf_waka_roles']          = array(
	'qaf_waka'      => new WP_Role( array( 'read' => true, 'qaf_access_media_center' => true, 'manage_options' => true, 'edit_posts' => true ) ),
	'qaf_teacher'   => new WP_Role( array( 'read' => true, 'qaf_access_media_center' => true ) ),
	'qaf_staff'     => new WP_Role( array( 'read' => true, 'qaf_access_media_center' => true ) ),
	'administrator' => new WP_Role( array( 'read' => true, 'manage_options' => true, 'edit_posts' => true, 'edit_others_posts' => true, 'publish_posts' => true, 'read_private_posts' => true, 'delete_posts' => true, 'delete_private_posts' => true, 'delete_published_posts' => true, 'delete_others_posts' => true, 'edit_private_posts' => true, 'edit_published_posts' => true, 'manage_categories' => true ) ),
	'editor'        => new WP_Role( array( 'read' => true, 'edit_posts' => true, 'edit_others_posts' => true, 'publish_posts' => true, 'read_private_posts' => true, 'delete_posts' => true, 'delete_private_posts' => true, 'delete_published_posts' => true, 'delete_others_posts' => true, 'edit_private_posts' => true, 'edit_published_posts' => true, 'manage_categories' => true ) ),
	'author'        => new WP_Role( array( 'read' => true, 'edit_posts' => true, 'publish_posts' => true, 'delete_posts' => true, 'delete_published_posts' => true, 'edit_published_posts' => true, 'upload_files' => true ) ),
	'contributor'   => new WP_Role( array( 'read' => true, 'edit_posts' => true, 'delete_posts' => true ) ),
);
$GLOBALS['qaf_waka_users']          = array();
$GLOBALS['qaf_waka_user_meta']      = array();
$GLOBALS['qaf_waka_user_options']   = array();
$GLOBALS['qaf_waka_insert_payload'] = array();
$GLOBALS['qaf_waka_password_count'] = 0;

function add_action() {}
function add_filter() {}
function get_option( $key ) { return isset( $GLOBALS['qaf_waka_options'][ $key ] ) ? $GLOBALS['qaf_waka_options'][ $key ] : false; }
function update_option( $key, $value ) { $GLOBALS['qaf_waka_options'][ $key ] = $value; return true; }
function current_user_can( $capability ) { return 'manage_options' === $capability ? $GLOBALS['qaf_waka_is_admin'] : false; }
function get_role( $role ) { return isset( $GLOBALS['qaf_waka_roles'][ $role ] ) ? $GLOBALS['qaf_waka_roles'][ $role ] : null; }
function add_role( $role, $label, $capabilities ) { unset( $label ); $GLOBALS['qaf_waka_roles'][ $role ] = new WP_Role( $capabilities ); return $GLOBALS['qaf_waka_roles'][ $role ]; }
function username_exists( $username ) {
	foreach ( $GLOBALS['qaf_waka_users'] as $user ) {
		if ( $username === $user->user_login ) { return $user->ID; }
	}
	return false;
}
function get_userdata( $user_id ) { return isset( $GLOBALS['qaf_waka_users'][ $user_id ] ) ? $GLOBALS['qaf_waka_users'][ $user_id ] : false; }
function get_users() { return array_values( $GLOBALS['qaf_waka_users'] ); }
function remove_accents( $value ) { return (string) $value; }
function wp_generate_password( $length ) {
	++$GLOBALS['qaf_waka_password_count'];
	$seed = 'SangatKuat#' . $GLOBALS['qaf_waka_password_count'] . '-';
	return substr( str_pad( $seed, $length, 'Xy9!' ), 0, $length );
}
function wp_insert_user( $payload ) {
	$GLOBALS['qaf_waka_insert_payload'][] = $payload;
	if ( username_exists( $payload['user_login'] ) ) { return new WP_Error( 'exists', 'Username sudah ada.' ); }
	$id              = empty( $GLOBALS['qaf_waka_users'] ) ? 1 : max( array_keys( $GLOBALS['qaf_waka_users'] ) ) + 1;
	$user            = new WP_User( $id, $payload['user_login'], $payload['display_name'], array( $payload['role'] ) );
	$user->user_pass = $payload['user_pass'];
	$GLOBALS['qaf_waka_users'][ $id ] = $user;
	return $id;
}
function update_user_meta( $user_id, $key, $value ) { $GLOBALS['qaf_waka_user_meta'][ $user_id ][ $key ] = $value; return true; }
function get_user_meta( $user_id, $key ) { return isset( $GLOBALS['qaf_waka_user_meta'][ $user_id ][ $key ] ) ? $GLOBALS['qaf_waka_user_meta'][ $user_id ][ $key ] : ''; }
function delete_user_meta( $user_id, $key ) { unset( $GLOBALS['qaf_waka_user_meta'][ $user_id ][ $key ] ); return true; }
function update_user_option( $user_id, $key, $value ) { $GLOBALS['qaf_waka_user_options'][ $user_id ][ $key ] = $value; return true; }
function delete_user_option( $user_id, $key ) { unset( $GLOBALS['qaf_waka_user_options'][ $user_id ][ $key ] ); return true; }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function user_can( $user, $capability ) {
	if ( ! $user instanceof WP_User ) { return false; }
	foreach ( $user->roles as $role ) {
		if ( isset( $GLOBALS['qaf_waka_roles'][ $role ] ) && $GLOBALS['qaf_waka_roles'][ $role ]->has_cap( $capability ) ) { return true; }
	}
	return false;
}
function absint( $value ) { return abs( (int) $value ); }
function get_permalink() { return ''; }
function home_url( $path = '' ) { return 'https://school.test' . $path; }
function admin_url( $path = '' ) { return 'https://school.test/wp-admin/' . $path; }

require dirname( __DIR__ ) . '/queen-alfalah-core/includes/class-qaf-core-post-types.php';
require dirname( __DIR__ ) . '/queen-alfalah-core/includes/class-qaf-core-media-center.php';
require dirname( __DIR__ ) . '/queen-alfalah-core/includes/class-qaf-core-waka-access.php';

$errors = array();
$assert = static function ( $condition, $message ) use ( &$errors ) {
	if ( ! $condition ) { $errors[] = $message; }
};

$definitions = QAF_Core_Waka_Access::get_account_definitions();
$source      = file_get_contents( dirname( __DIR__ ) . '/queen-alfalah-core/includes/class-qaf-core-waka-access.php' );
$assert( false !== strpos( $source, "current_user_can( 'manage_options' )" ), 'Provisioner must require manage_options.' );
$assert( false !== strpos( $source, "'POST' !== \$request_method" ), 'Provisioner must reject non-POST requests.' );
$assert( false !== strpos( $source, 'check_admin_referer( self::PROVISION_NONCE_ACTION )' ), 'Provisioner must verify its nonce.' );
preg_match( '/public static function activate\(\) \{(.*?)^\t\}/ms', $source, $activate_method );
$assert( isset( $activate_method[1] ) && false === strpos( $activate_method[1], 'provision_accounts(' ), 'Plugin activation must not create Waka accounts.' );
$assert( 4 === count( $definitions ), 'Exactly four Waka accounts must be defined.' );
$assert( 4 === count( array_unique( array_column( $definitions, 'username' ) ) ), 'Waka usernames must be unique.' );
$assert( 4 === count( array_unique( array_column( $definitions, 'source_id' ) ) ), 'Waka source IDs must be unique.' );
foreach ( $definitions as $definition ) {
	$assert( array() === array_intersect( array( 'email', 'user_email', 'password', 'user_pass' ), array_keys( $definition ) ), 'Definitions must not bundle email addresses or passwords.' );
	$assert( 0 === strpos( $definition['username'], 'waka-' ), 'Every stable username must use the waka- prefix.' );
}

$notice_caps = QAF_Core_Post_Types::get_notice_capabilities();
$assert( 'edit_qaf_notices' === $notice_caps['edit_posts'], 'Notice CPT must use its dedicated edit capability.' );
$assert( 'publish_qaf_notices' === $notice_caps['publish_posts'], 'Notice CPT must use its dedicated publish capability.' );
$assert( 'assign_qaf_notice_terms' === QAF_Core_Post_Types::get_notice_term_capabilities()['assign_terms'], 'Waka must have a dedicated notice-term assignment capability.' );

QAF_Core_Waka_Access::install_capabilities();
$waka_caps = $GLOBALS['qaf_waka_roles']['qaf_waka'];
foreach ( array( 'upload_files', 'edit_qaf_notices', 'edit_others_qaf_notices', 'publish_qaf_notices', 'edit_published_qaf_notices', 'assign_qaf_notice_terms', 'assign_qaf_audience_terms' ) as $capability ) {
	$assert( $waka_caps->has_cap( $capability ), 'Waka is missing capability: ' . $capability );
}
foreach ( array( 'manage_options', 'edit_posts', 'edit_pages', 'edit_users', 'activate_plugins' ) as $capability ) {
	$assert( ! $waka_caps->has_cap( $capability ), 'Waka must not retain broad capability: ' . $capability );
}
$assert( ! $GLOBALS['qaf_waka_roles']['qaf_teacher']->has_cap( 'upload_files' ), 'Guru portal role must remain upload-free.' );
$assert( ! $GLOBALS['qaf_waka_roles']['qaf_staff']->has_cap( 'edit_qaf_notices' ), 'Tendik portal role must remain notice-edit-free.' );
$assert( $GLOBALS['qaf_waka_roles']['editor']->has_cap( 'edit_others_qaf_notices' ), 'Editor compatibility must be preserved.' );
$assert( $GLOBALS['qaf_waka_roles']['author']->has_cap( 'publish_qaf_notices' ), 'Author compatibility must be preserved.' );
$assert( ! $GLOBALS['qaf_waka_roles']['contributor']->has_cap( 'publish_qaf_notices' ), 'Contributor must not gain notice publishing.' );

$existing_username       = new WP_User( 10, 'waka-kurikulum', 'Akun Lama Kurikulum', array( 'subscriber' ) );
$existing_legacy_name    = new WP_User( 11, 'efi-lama', 'Efi Risca Ferbriana Wati, S.Pd.', array( 'subscriber' ) );
$GLOBALS['qaf_waka_users'][10] = $existing_username;
$GLOBALS['qaf_waka_users'][11] = $existing_legacy_name;
$GLOBALS['qaf_waka_user_meta'][10]['preserve_me'] = 'tetap';

$result = QAF_Core_Waka_Access::provision_accounts();
$assert( ! is_wp_error( $result ), 'Administrator provisioning should return a result.' );
$assert( 2 === count( $result['created'] ), 'Only the two genuinely missing Waka accounts should be created.' );
$assert( 2 === count( $result['skipped'] ), 'Existing username and matching legacy display name must both be skipped.' );
$assert( 'tetap' === $GLOBALS['qaf_waka_user_meta'][10]['preserve_me'], 'Existing account metadata must not be changed.' );
$assert( array( 'subscriber' ) === $GLOBALS['qaf_waka_users'][10]->roles, 'Existing account role must not be changed.' );

$passwords = array();
foreach ( $result['created'] as $account ) {
	$passwords[] = $account['password'];
	$assert( 32 === strlen( $account['password'] ), 'Each generated password must contain 32 characters.' );
	$assert( array( QAF_Core_Waka_Access::ROLE ) === $GLOBALS['qaf_waka_users'][ $account['user_id'] ]->roles, 'New account must use only the Waka role.' );
	$assert( '1' === $GLOBALS['qaf_waka_user_meta'][ $account['user_id'] ][ QAF_Core_Waka_Access::FORCE_PASSWORD_META ], 'New account must require a password change.' );
	$assert( true === $GLOBALS['qaf_waka_user_options'][ $account['user_id'] ]['default_password_nag'], 'WordPress password nag must be enabled.' );
}
$assert( count( $passwords ) === count( array_unique( $passwords ) ), 'Generated account passwords must be unique.' );
foreach ( $GLOBALS['qaf_waka_insert_payload'] as $payload ) {
	$assert( ! isset( $payload['user_email'] ), 'Provisioning must not invent an email address.' );
}

$source_matched_id = $result['created'][0]['user_id'];
$GLOBALS['qaf_waka_users'][ $source_matched_id ]->user_login = 'username-yang-disunting';
$GLOBALS['qaf_waka_users'][ $source_matched_id ]->display_name = 'Nama Tampilan yang Disunting';
$second = QAF_Core_Waka_Access::provision_accounts();
$assert( 0 === count( $second['created'] ) && 4 === count( $second['skipped'] ), 'Provisioning must be idempotent and preserve an account matched by its source ID.' );

$GLOBALS['qaf_waka_is_admin'] = false;
$denied = QAF_Core_Waka_Access::provision_accounts();
$assert( is_wp_error( $denied ), 'Non-administrator provisioning must be rejected.' );

$teacher = new WP_User( 20, 'guru', 'Guru', array( 'qaf_teacher' ) );
$staff   = new WP_User( 21, 'tendik', 'Tendik', array( 'qaf_staff' ) );
$waka    = $GLOBALS['qaf_waka_users'][ array_key_last( $GLOBALS['qaf_waka_users'] ) ];
$assert( QAF_Core_Media_Center::is_portal_only_user( $teacher ), 'Guru must remain portal-only.' );
$assert( QAF_Core_Media_Center::is_portal_only_user( $staff ), 'Tendik must remain portal-only.' );
$assert( ! QAF_Core_Media_Center::is_portal_only_user( $waka ), 'Waka must be allowed into its focused admin workflow.' );
$assert( 'https://school.test/pusat-media/' === QAF_Core_Media_Center::login_redirect( 'default', '', $teacher ), 'Guru login must still redirect to Pusat Media.' );
$assert( 'https://school.test/wp-admin/profile.php?qaf-password=change' === QAF_Core_Waka_Access::login_redirect( 'default', '', $waka ), 'New Waka login must require a password change first.' );

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Waka access tests passed.\n";
