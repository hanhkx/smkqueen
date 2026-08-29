<?php
/**
 * Queen Al-Falah Core uninstall routine.
 *
 * School content, terms, menus, media references, and non-secret settings are
 * intentionally retained. Removing a presentation/helper plugin must never erase
 * institutional records. Revocable Instagram credentials are the exception and
 * are removed so a deleted plugin does not leave a live external access token.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

wp_clear_scheduled_hook( 'qaf_instagram_gallery_sync_event' );
delete_option( 'qaf_instagram_gallery_sync' );
delete_option( 'qaf_instagram_gallery_sync_state' );
delete_option( 'qaf_instagram_gallery_sync_lock' );
delete_transient( 'qaf_instagram_gallery_sync_lock' );

// Institutional content and all non-secret plugin data remain preserved.
