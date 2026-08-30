<?php
/**
 * Dependency-free behavior checks for the staff-directory upgrade lock.
 */

define( 'ABSPATH', __DIR__ . '/wordpress-stub/' );

$GLOBALS['qaf_staff_lock_options'] = array();
$GLOBALS['qaf_staff_lock_uuid']    = 0;

function wp_generate_uuid4() {
	$GLOBALS['qaf_staff_lock_uuid']++;
	return sprintf( '00000000-0000-4000-8000-%012d', $GLOBALS['qaf_staff_lock_uuid'] );
}

function add_option( $name, $value, $deprecated = '', $autoload = 'yes' ) {
	unset( $deprecated, $autoload );
	if ( array_key_exists( $name, $GLOBALS['qaf_staff_lock_options'] ) ) {
		return false;
	}

	$GLOBALS['qaf_staff_lock_options'][ $name ] = $value;
	return true;
}

function get_option( $name, $default = false ) {
	return array_key_exists( $name, $GLOBALS['qaf_staff_lock_options'] )
		? $GLOBALS['qaf_staff_lock_options'][ $name ]
		: $default;
}

function maybe_serialize( $value ) {
	return is_array( $value ) || is_object( $value ) ? serialize( $value ) : $value;
}

function wp_cache_delete( $key, $group = '' ) {
	unset( $key, $group );
	return true;
}

class QAF_Staff_Lock_Test_WPDB {
	public $options = 'wp_options';

	public function prepare( $query, $name, $value ) {
		return array(
			'query' => $query,
			'name'  => $name,
			'value' => $value,
		);
	}

	public function query( $prepared ) {
		if (
			! is_array( $prepared )
			|| ! array_key_exists( $prepared['name'], $GLOBALS['qaf_staff_lock_options'] )
			|| maybe_serialize( $GLOBALS['qaf_staff_lock_options'][ $prepared['name'] ] ) !== $prepared['value']
		) {
			return 0;
		}

		unset( $GLOBALS['qaf_staff_lock_options'][ $prepared['name'] ] );
		return 1;
	}
}

$GLOBALS['wpdb'] = new QAF_Staff_Lock_Test_WPDB();

require dirname( __DIR__ ) . '/queen-alfalah-core/includes/class-qaf-core-staff-directory.php';

$assert = static function ( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
};

$acquire = new ReflectionMethod( 'QAF_Core_Staff_Directory', 'acquire_upgrade_lock' );
$release = new ReflectionMethod( 'QAF_Core_Staff_Directory', 'release_upgrade_lock' );
$acquire->setAccessible( true );
$release->setAccessible( true );

$first = $acquire->invoke( null );
$assert( is_array( $first ) && ! empty( $first['token'] ), 'The first request must acquire the lock atomically.' );
$assert( false === $acquire->invoke( null ), 'A concurrent request must not acquire an active lock.' );
$release->invoke( null, $first );
$assert( ! isset( $GLOBALS['qaf_staff_lock_options'][ QAF_Core_Staff_Directory::LOCK_OPTION ] ), 'The owner must release its exact lock.' );

$stale = array(
	'token'   => 'stale-request',
	'created' => time() - QAF_Core_Staff_Directory::LOCK_TTL - 1,
);
$GLOBALS['qaf_staff_lock_options'][ QAF_Core_Staff_Directory::LOCK_OPTION ] = $stale;
$replacement = $acquire->invoke( null );
$assert( is_array( $replacement ) && $replacement['token'] !== $stale['token'], 'An abandoned stale lock must be recovered.' );

$release->invoke( null, $stale );
$assert( $replacement === $GLOBALS['qaf_staff_lock_options'][ QAF_Core_Staff_Directory::LOCK_OPTION ], 'A delayed stale owner must not delete the replacement lock.' );
$release->invoke( null, $replacement );
$assert( ! isset( $GLOBALS['qaf_staff_lock_options'][ QAF_Core_Staff_Directory::LOCK_OPTION ] ), 'The replacement owner must be able to release its lock.' );

$GLOBALS['qaf_staff_lock_options'][ QAF_Core_Staff_Directory::LOCK_OPTION ] = 'malformed-abandoned-lock';
$recovered = $acquire->invoke( null );
$assert( is_array( $recovered ), 'Malformed abandoned lock data must not block upgrades forever.' );
$release->invoke( null, $recovered );

fwrite( STDOUT, "PASS: staff-directory lock is atomic, recovers stale state, and rejects delayed releases.\n" );
