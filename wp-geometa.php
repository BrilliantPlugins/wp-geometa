<?php
/**
 * WP-GeoMeta enables Geo metadata for WordPress
 *
 * WP-GeoMeta targets MySQL 5.6 because 5.5 only used MBR based spatial functions
 * https://dev.mysql.com/doc/refman/5.6/en/spatial-relation-functions-object-shapes.html
 *
 * Plugin Name: WP-GeoMeta
 * Author: Michael Moore
 * Author URI: http://cimbura.com
 * Version: 0.0.2
 * Code Name: New Jersey - yeh, I can dance.
 *
 * @package WP_GeoMeta
 */

/**
 * This is the admin_init callback. If the plugin is active and
 * we don't have the needed PHP version, then we deacivate it.
 */
function wp_geometa_admin_init() {
	if ( version_compare( phpversion(), '5.3.0', '<' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( esc_html__( 'WP GeoMeta requires PHP %1$s or higher.','wp-spatial-capabilities-check' , '5.3.0' ) );
	} else {

		// This is where the actual plugin starts to get loaded.
		require_once( dir( __FILE__ ) . '/lib/wp-geometa-loader.php' );
	}
}
add_action( 'admin_init' , 'wp_geometa_admin_init' );

/**
 * This is the plugin activation function.
 *
 * It also checks version numbers.
 */
function wp_geometa_activation_func() {
	if ( ! version_compare( phpversion(), '5.3.0', '<' ) ) {
		if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );

			add_action( 'admin_notices',  esc_html__( 'WP GeoMeta requires PHP %1$s or higher.','wp-spatial-capabilities-check' , '5.3.0' ) );

			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}
	}
}
register_activation_hook( __FILE__, 'wp_geometa_activation_hook' );
