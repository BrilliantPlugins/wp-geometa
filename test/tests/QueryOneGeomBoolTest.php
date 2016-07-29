<?php
/**
 * Deletes all test posts
 */
require_once( dirname( __FILE__ ) . '/__load.php' );

print str_pad( "Testing One Geom Bool Queries", WP_GEOMETA_TEST_WIDTH, '.' );

// Test for intersection: Should find some records.
$wpq = new WP_Query(array(
	'post_type' => 'geo_test',
	'post_status' => 'any',
	'meta_query' => array(
		array( 
		'key' => 'wpgeometa_test',
		'compare' => 'IsSimple'
	)
	))); 

if ( ! $wpq->have_posts() ) {
	fail($wpq);
	return;
}
// Test for intersection: Should not find any records.  
$wpq = new WP_Query(array( 
	'post_type' => 'geo_test', 
	'post_status' => 'any', 
	'meta_query' => array( 
		array( 
		'key' => 'wpgeometa_test',
		'compare' => 'ST_IsEmpty'
		)
	))); 

if ( $wpq->have_posts() ) {
	fail($wpq);
} else {
	pass();
}
