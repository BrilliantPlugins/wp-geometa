<?php
/**
 * Tests if entries are created for the posts in the postmeta_geo table.
 *
 * Loads data into the wordpress databse. 
 */

require_once( dirname( __FILE__ ) . '/__load.php' );

global $wpdb;

print str_pad( "Loading sample data", WP_GEOMETA_TEST_WIDTH, '.' );

$geojson = file_get_contents( WP_GEOMETA_TESTDIR . '/tests/data.geojson' );
$geojson = json_decode( $geojson , true);

$post_ids = array();
foreach( $geojson['features'] as $feature ) {
	$post_id = wp_insert_post( array(
		'post_title' => $feature['properties']['_post_title'],
		'post_type' => 'geo_test'
	) );

	if ( empty( $post_id ) ) {
		fail();
		return;
	}

	$updated_meta = update_post_meta( $post_id, 'wpgeometa_test', $feature );

	foreach( $feature[ 'properties' ] as $prop => $val ) {
		update_post_meta( $post_id, $prop, $val );
	}

	if ( empty( $updated_meta ) ) {
		fail();
		return;
	}

	$post_ids[] = $post_id;
}

$results = $wpdb->get_results( "SELECT post_id FROM {$wpdb->postmeta}_geo WHERE meta_key='wpgeometa_test'" );

$meta_post_ids = array();
foreach ( $results as $res ) {
	$meta_post_ids[] = $res->post_id;
}

if ( ! empty( array_diff( $post_ids, $meta_post_ids ) ) ) {
	fail();
} else {
	pass();
}
