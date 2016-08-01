<?php
/**
 * Test querying one geometry and returning a value
 */
require_once( dirname( __FILE__ ) . '/__load.php' );

print str_pad( "Testing One Geom value Queries", WP_GEOMETA_TEST_WIDTH, '.' );

// Test for intersection: Should find some records.
$wpq = new WP_Query(array(
	'post_type' => 'geo_test',
	'post_status' => 'any',
	'meta_query' => array(
		array( 
		'key' => 'wpgeometa_test',
		'compare' => '=',
		'value' => '1',
		'geom_op' => 'IsSimple'
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
		'compare' => '>',
		'value' => '100',
		'geom_op' => 'Dimension',
		'type' => 'NUMERIC'
	)
	))); 

if ( $wpq->have_posts() ) {
	fail($wpq);
} else {
	pass();
}
