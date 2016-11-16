<?php
/**
 * Load lat/lng meta values and see if geom values are created for them
 *
 * Loads data into the wordpress databse. 
 */

require_once( dirname( __FILE__ ) . '/__load.php' );

global $wpdb;

print str_pad( "Loading LatLng data", WP_GEOMETA_TEST_WIDTH, '.' );

// 3 Data load cases
// 1) Add geo_latitude / geo_longitude for 2 posts (should become geo_ )
// 2) Add geo_latitude, but not geo_longitude for 2 posts (should not become geo_)
// 3) Add another lat/lng named pair and add lat/lng for 2 posts


WP_GeoMeta::add_latlng_field( 'myplugin_lat', 'myplugin_lng', 'myplugin_geo' );

$test_posts = get_posts( array( 'post_type' => 'geo_test', 'posts_per_page' => 6, 'post_status' => 'any') );
foreach( $test_posts as $idx => $test_post ) {
	if ( $idx < 2 ) {
		update_post_meta( $test_post->ID, 'geo_latitude', 44.9778 );
		update_post_meta( $test_post->ID, 'geo_longitude', -93.2650 );
	} else if ( $idx < 4 ) {
		update_post_meta( $test_post->ID, 'geo_latitude', 40.9778 );
	} else {
		update_post_meta( $test_post->ID, 'myplugin_lat', 44.00 );
		update_post_meta( $test_post->ID, 'myplugin_lng', -93.00);
	}
}

$results = $wpdb->get_results( "SELECT post_id FROM {$wpdb->postmeta}_geo WHERE meta_key='myplugin_geo'" );

if ( count( $results ) === 2 ) {
	pass();
} else {
	fail();
}
