<?php

require_once( __DIR__ . '/lib/wp-geoquery.php' );
require_once( __DIR__ . '/lib/wp-geometa.php' );

// This will initialize both classes.
WP_GeoMeta::get_instance();
WP_GeoQuery::get_instance();

register_activation_hook( __FILE__, 'wp_geometa_activate' );

/**
 * Handle plugin activation. Create tables and pre-populate them
 * with any existing geo data.
 */
function wp_geometa_activate() {
	$wpgeo = WP_GeoMeta::get_instance();
	$wpgeo->create_geo_table();
	$wpgeo->populate_geo_tables();
}
