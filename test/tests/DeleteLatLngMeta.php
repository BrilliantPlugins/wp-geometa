<?php
/**
 * Load lat/lng meta values and see if geom values are created for them
 *
 * Loads data into the wordpress databse. 
 */

require_once( dirname( __FILE__ ) . '/__load.php' );

global $wpdb;

WP_GeoMeta::add_latlng_field( 'myplugin_lat', 'myplugin_lng', 'myplugin_geo' );

print str_pad( "Deleting LatLng data", WP_GEOMETA_TEST_WIDTH, '.' );

$results = $wpdb->get_results( "SELECT post_id FROM {$wpdb->postmeta}_geo WHERE meta_key='myplugin_geo'", ARRAY_A );

if ( 0 === count( $results ) ) {
	fail();
	return;
}

foreach( $results as $res ) {
	delete_post_meta( $res['post_id'], 'myplugin_lat' );
}

$results = $wpdb->get_results( "SELECT post_id FROM {$wpdb->postmeta}_geo WHERE meta_key='myplugin_geo'" );

if ( 0 !== count( $results ) ) {
	fail();
	return;
}

pass();
