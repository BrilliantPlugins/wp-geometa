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
 * Author URI: http://cimbura.com
 * Version: 0.3.0
 * Code Name: Penny Priddy
 * Text Domain: wp-geometa
 * Domain Path: /lang
 *
 * @package wp-geometa
 */



/**
 * Always include wp-geometa-lib so that it's available for others to use
 */
require_once( dirname( __FILE__ ) . '/lib/wp-geometa-lib/wp-geometa-lib-loader.php' );

/**
 * Load dashboard stuff if we're on the dashboard page
 */
if ( is_admin() ) {
	require_once( dirname( __FILE__ ) . '/lib/wp-geometa-dash.php' );
	WP_GeoMeta_Dash::get_instance();

	add_action( 'plugins_loaded', 'wpgeometa_load_textdomain' );

	/**
	* Set up the I18N path.
	*/
	function wpgeometa_load_textdomain() {
		load_plugin_textdomain( 'wp-geometa', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}
}
