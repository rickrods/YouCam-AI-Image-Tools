<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://my-portfolio-app-sepia-kappa.vercel.app
 * @since      1.0.0
 *
 * @package    Youcam_Ai_Image_Tools
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

wp_clear_scheduled_hook( 'ycait_cleanup_usage' );

global $wpdb;
$prefixes = array(
	$wpdb->esc_like( 'ycait_' ) . '%',
	$wpdb->esc_like( '_transient_ycait_' ) . '%',
	$wpdb->esc_like( '_transient_timeout_ycait_' ) . '%',
);
$query = $wpdb->prepare(
	"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s",
	$prefixes
);
$plugin_options = $wpdb->get_col( $query );

// Delete plugin settings, counters, and database-backed transient values.
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s",
		$prefixes
	)
);

// Remove matching entries from persistent object caches as well.
foreach ( $plugin_options as $option_name ) {
	if ( 0 === strpos( $option_name, '_transient_timeout_ycait_' ) ) {
		continue;
	}
	if ( 0 === strpos( $option_name, '_transient_ycait_' ) ) {
		$transient_name = substr( $option_name, strlen( '_transient_' ) );
		wp_cache_delete( $transient_name, 'transient' );
		continue;
	}
	wp_cache_delete( $option_name, 'options' );
}
wp_cache_delete( 'alloptions', 'options' );
wp_cache_delete( 'notoptions', 'options' );
