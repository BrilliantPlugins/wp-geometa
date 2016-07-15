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
 * Version: 0.0.3
 * Code Name: Perfect Tommy - Let her out?
 *
 * @package WP_GeoMeta
 */

$wp_geometa_version = '0.0.3';
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
	update_option( 'wp_geometa_version', $wp_geometa_version );
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
			update_option( 'wp_geometa_db_version', $wp_geometa_version );
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
				update_option( 'wp_geometa_db_version', $wp_geometa_version );
			}

			/*
			 * If we got downgraded, then the first found wp-geometa will have been
			 * loaded. Lowering the version to this instance's version will allow
             * WP GeoMeta to pick the highest version again on the next run.
			 *
			 * Eg. This is v5 and is the first one that WP finds. v6 is also installed
			 * and v7 was installed. When v7 is no longer found, this (v5) will run since
             * it was the first one found and will set wp_geometa_version to v5.
			 *
			 * On the next run, it would find that v6 is the higher version and would update
			 * wp_geometa_version. On the run after that v6 would be loaded.
			 */
			update_option( 'wp_geometa_version', $wp_geometa_version );
		}
	}
	add_action( 'plugins_loaded', 'wp_geometa_load_older_version' );
}
