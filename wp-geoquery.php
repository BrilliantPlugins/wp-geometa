<?php

/**
 * WP-GeoQuery enables Geo metadata for WordPress
 *
 * WP-GeoQuery targets MySQL 5.6 because 5.5 only used MBR based spatial functions
 * https://dev.mysql.com/doc/refman/5.6/en/spatial-relation-functions-object-shapes.html
 *
 * Plugin Name: WP-GeoQuery
 * Author: Michael Moore
 * Author URI: http://cimbura.com
 * Version: 0.0.1
 */
require_once(__DIR__ . '/lib/geoPHP/geoPHP.inc');
require_once(__DIR__ . '/lib/wp-geoquery.php');
require_once(__DIR__ . '/lib/wp-geometa.php');

// This will initialize both classes
WP_GeoMeta::get_instance();
WP_GeoQuery::get_instance();

register_activation_hook(__FILE__, 'activate_wp_brilliant_geo');
function activate_wp_brilliant_geo(){
	$wpgeo = WP_GeoMeta::get_instance();
	$wpgeo->create_geo_table();
}
