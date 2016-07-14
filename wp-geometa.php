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

$wp_geometa_version = '0.0.2';
$wp_geometa_max_version = get_option( 'wp_geometa_version', '0.0.0' );
$wp_geometa_db_version = get_option( 'wp_geometa_db_version', '0.0.0' );
$wp_geometa_load_this_instance = false;

/**
 * -1 means that our version is lower.
 * 0 means they are equal.
 * 1 means our version is higher.
 */
$wp_geometa_version_status = version_compare( $wp_geometa_version, $wp_geometa_max_version );

if ( 1 === $wp_geometa_version_status ) {
	// This will cause our version to get loaded next time.
	update_option( 'wp_geometa_version', $wp_geometa_version, false );
} else if ( 0 === $wp_geometa_version_status ) {

	// Other instances of WP_GeoMeta shouldn't have loaded these classes
	// unless they're the same version as this instance.
	if ( ! class_exists( 'WP_GeoMeta' ) ) {
		require_once( dirname( __FILE__ ) . '/lib/wp-geoquery.php' );
		require_once( dirname( __FILE__ ) . '/lib/wp-geometa.php' );
		$wpgeo = WP_GeoMeta::get_instance();
		$wpgq = WP_GeoQuery::get_instance();

		// Since we just got loaded, make sure that the database reflects any
		// changes that the latest version of WP_GeoMeta might have added.
		if ( version_compare( $wp_geometa_version, $wp_geometa_db_version ) > 0 ) {
			$wpgeo->create_geo_tables();
			$wpgeo->populate_geo_tables();
			update_option( 'wp_geometa_db_version', $wp_geometa_version, false );
		}
	}
}

/**
 * There's a chance that someone installed a newere version of the plugin,
 * (or a plugin that used the library) which caused the option to get set,
 * then removed that plugin, which would mean that we aren't loading the
 * usual way.
 *
 * Add an action to try to load our classes after the rest of the plugins
 * get a chance to load.
 */


if ( ! function_exists( 'wp_geometa_load_older_version' ) ) {
	/**
	 * Load this instance's libraries.
	 */
	function wp_geometa_load_older_version() {
		if ( ! class_exists( 'WP_GeoMeta' ) ) {
			require_once( dirname( __FILE__ ) . '/lib/wp-geoquery.php' );
			require_once( dirname( __FILE__ ) . '/lib/wp-geometa.php' );
			$wpgeo = WP_GeoMeta::get_instance();
			$wpgq = WP_GeoQuery::get_instance();

			// Since we just got loaded, make sure that the database reflects any
			// changes that the latest version of WP_GeoMeta might have added.
			if ( version_compare( $wp_geometa_version, $wp_geometa_db_version ) > 0 ) {
				$wpgeo->create_geo_tables();
				$wpgeo->populate_geo_tables();
				update_option( 'wp_geometa_db_version', $wp_geometa_version, false );
			}
		}
	}
	add_action( 'plugins_loaded', 'wp_geometa_load_older_version' );
}
