<?php
/**
 * WP-GeoMeta enables Geo metadata for WordPress
 *
 * WP-GeoMeta targets MySQL 5.6 because 5.5 only used MBR based spatial functions
 * https://dev.mysql.com/doc/refman/5.6/en/spatial-relation-functions-object-shapes.html
 *
 * It should still work on 5.5, but you'll be limited in what you can do with it.
 *
 * Plugin Name: WP-GeoMeta
 * Description: Store and search spatial metadata while taking advantage of MySQL spatial types and indexes.
 * Author: Michael Moore
 * Author URI: http://LuminFire.com
 * Version: 0.4.0
 * Text Domain: wp-geometa
 * Domain Path: /lang
 *
 * @package wp-geometa
 */

/**
 * Always include wp-geometa-lib so that it's available for others to use
 */
$wp_geometa_lib_loader = dirname( __FILE__ ) . '/lib/wp-geometa-lib/wp-geometa-lib-loader.php';

if ( file_exists( $wp_geometa_lib_loader ) ) {
	require_once( $wp_geometa_lib_loader );
	register_activation_hook( __FILE__ , array('WP_GeoMeta','install'));
} else {
	error_log( __( "Could not load wp-geometa-lib. You probably cloned wp-geometa from git and didn't check out submodules!", 'wp-geometa' ) );

	if ( is_admin() ) {
		print esc_html__( "Could not load wp-geometa-lib. You probably cloned wp-geometa from git and didn't check out submodules!", 'wp-geometa' );
	}
}

/**
 * Load dashboard stuff if we're on the dashboard page
 */
if ( is_admin() ) {
	require_once( dirname( __FILE__ ) . '/lib/wp-geometa-dash.php' );

	$leaflet_php_loader = dirname( __FILE__ ) . '/lib/leaflet-php/leaflet-php-loader.php';

	if ( file_exists( $leaflet_php_loader ) ) {
		require_once( $leaflet_php_loader );
	} else {
		error_log( __( "Could not load leaflet-php-loader. You probably cloned wp-geometa from git and didn't check out submodules!", 'wp-geometa' ) );
		print esc_html__( "Could not load leaflet-php-loader. You probably cloned wp-geometa from git and didn't check out submodules!", 'wp-geometa' );
	}

	WP_GeoMeta_Dash::get_instance();

	add_action( 'plugins_loaded', 'wpgeometa_load_textdomain' );

	/**
	* Set up the I18N path.
	*/
	function wpgeometa_load_textdomain() {
		load_plugin_textdomain( 'wp-geometa', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}
}
