<?php
/**
 * Plugin Name:       Queen Al-Falah Core
 * Plugin URI:        https://smkqueenalfalah.sch.id/
 * Description:       Model konten, pengaturan sekolah, dan alat penyiapan situs untuk SMK Queen Al-Falah.
 * Version:           1.4.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            SMK Queen Al-Falah
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       queen-alfalah-core
 * Domain Path:       /languages
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QAF_CORE_VERSION', '1.4.0' );
define( 'QAF_CORE_FILE', __FILE__ );
define( 'QAF_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'QAF_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once QAF_CORE_PATH . 'includes/class-qaf-core-post-types.php';
require_once QAF_CORE_PATH . 'includes/class-qaf-core-meta.php';
require_once QAF_CORE_PATH . 'includes/class-qaf-core-settings.php';
require_once QAF_CORE_PATH . 'includes/class-qaf-core-admin.php';
require_once QAF_CORE_PATH . 'includes/class-qaf-core-demo.php';
require_once QAF_CORE_PATH . 'includes/class-qaf-core-applications.php';
require_once QAF_CORE_PATH . 'includes/class-qaf-core-media-center.php';
require_once QAF_CORE_PATH . 'includes/class-qaf-core-organization.php';

/**
 * Retrieve a school setting with a safe fallback.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Optional fallback for unknown keys.
 * @return mixed
 */
function qaf_core_get_setting( $key, $default = null ) {
	return QAF_Core_Settings::get_setting( $key, $default );
}

/**
 * Coordinate plugin modules.
 */
final class QAF_Core {
	/**
	 * Register hooks for every module.
	 *
	 * @return void
	 */
	public static function init() {
		load_plugin_textdomain(
			'queen-alfalah-core',
			false,
			dirname( plugin_basename( QAF_CORE_FILE ) ) . '/languages'
		);

		QAF_Core_Settings::init();
		QAF_Core_Post_Types::init();
		QAF_Core_Meta::init();
		QAF_Core_Admin::init();
		QAF_Core_Demo::init();
		QAF_Core_Applications::init();
		QAF_Core_Media_Center::init();
		QAF_Core_Organization::init();
	}

	/**
	 * Seed portable defaults and generate rewrite rules.
	 *
	 * @return void
	 */
	public static function activate() {
		QAF_Core_Settings::install_defaults();
		QAF_Core_Post_Types::register_post_types();
		QAF_Core_Post_Types::register_taxonomies();
		QAF_Core_Media_Center::activate();
		QAF_Core_Organization::activate();
		flush_rewrite_rules();
	}

	/**
	 * Remove stale rewrite rules without deleting content.
	 *
	 * @return void
	 */
	public static function deactivate() {
		foreach ( array_keys( QAF_Core_Post_Types::get_taxonomies() ) as $taxonomy ) {
			if ( taxonomy_exists( $taxonomy ) ) {
				unregister_taxonomy( $taxonomy );
			}
		}

		foreach ( array_keys( QAF_Core_Post_Types::get_post_types() ) as $post_type ) {
			if ( post_type_exists( $post_type ) ) {
				unregister_post_type( $post_type );
			}
		}

		flush_rewrite_rules();
	}
}

register_activation_hook( QAF_CORE_FILE, array( 'QAF_Core', 'activate' ) );
register_deactivation_hook( QAF_CORE_FILE, array( 'QAF_Core', 'deactivate' ) );
add_action( 'plugins_loaded', array( 'QAF_Core', 'init' ) );
