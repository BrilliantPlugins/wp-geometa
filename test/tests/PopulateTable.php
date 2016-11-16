<?php
/**
 * Empty the meta table, then repopulate and see if we get the values we expect
 *
 * Loads data into the wordpress databse. 
 */

require_once( dirname( __FILE__ ) . '/__load.php' );

global $wpdb;

print str_pad( "Populating tables with existing data", WP_GEOMETA_TEST_WIDTH, '.' );

WP_GeoMeta::add_latlng_field( 'myplugin_lat', 'myplugin_lng', 'myplugin_geo' );

$wpgm = WP_GeoMeta::get_instance();

$wpgm->populate_geo_tables();

pass();
