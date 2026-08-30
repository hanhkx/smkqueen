<?php
/**
 * Optional, administrator-configured site verification and analytics tags.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render Google integrations only after a valid identifier is saved.
 */
final class QAF_Core_Site_Integrations {
	/**
	 * Attach front-end hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_google_analytics' ), 5 );
		add_action( 'wp_head', array( __CLASS__, 'print_search_console_verification' ), 1 );
		add_filter( 'script_loader_tag', array( __CLASS__, 'ensure_analytics_async' ), 10, 3 );
	}

	/**
	 * Load Google Analytics 4 only when a valid Measurement ID is configured.
	 *
	 * @return void
	 */
	public static function enqueue_google_analytics() {
		$measurement_id = strtoupper( trim( (string) QAF_Core_Settings::get_setting( 'google_analytics_id', '' ) ) );
		if ( ! preg_match( '/^G-[A-Z0-9]{6,20}$/', $measurement_id ) ) {
			return;
		}

		$enabled = (bool) apply_filters( 'qaf_core_enable_google_analytics', true, $measurement_id );
		if ( ! $enabled ) {
			return;
		}

		$handle = 'qaf-google-analytics';
		$url    = 'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode( $measurement_id );
		wp_enqueue_script( $handle, $url, array(), null, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Remote service endpoint must not receive a WordPress version.
		wp_script_add_data( $handle, 'strategy', 'async' );

		$inline  = 'window.dataLayer=window.dataLayer||[];';
		$inline .= 'function gtag(){window.dataLayer.push(arguments);}';
		$inline .= "gtag('js',new Date());";
		$inline .= "gtag('config'," . wp_json_encode( $measurement_id ) . ');';
		wp_add_inline_script( $handle, $inline, 'after' );
	}

	/**
	 * Add an async fallback for WordPress 6.2, before script strategies existed.
	 *
	 * Newer WordPress releases render the strategy registered above. The scoped
	 * filter keeps the declared minimum version non-blocking without modifying
	 * any other script tag.
	 *
	 * @param string $tag    Script HTML.
	 * @param string $handle Registered script handle.
	 * @param string $src    Script URL.
	 * @return string
	 */
	public static function ensure_analytics_async( $tag, $handle, $src ) {
		if (
			'qaf-google-analytics' !== $handle
			|| false === strpos( $src, 'https://www.googletagmanager.com/gtag/js?' )
			|| preg_match( '/\sasync(?:=|\s|>)/i', $tag )
		) {
			return $tag;
		}

		$async_tag = preg_replace( '/^<script\s/i', '<script async ', $tag, 1 );
		return is_string( $async_tag ) ? $async_tag : $tag;
	}

	/**
	 * Print the Search Console verification token in the document head.
	 *
	 * @return void
	 */
	public static function print_search_console_verification() {
		$token = trim( (string) QAF_Core_Settings::get_setting( 'google_site_verification', '' ) );
		if ( ! preg_match( '/^[A-Za-z0-9_-]{10,200}$/', $token ) ) {
			return;
		}

		printf( '<meta name="google-site-verification" content="%s">' . "\n", esc_attr( $token ) );
	}
}
