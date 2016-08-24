<?php
/**
 * Deletes all test posts
 */
require_once( dirname( __FILE__ ) . '/__load.php' );

print str_pad( "Testing One Geom Queries with array of values", WP_GEOMETA_TEST_WIDTH, '.' );

// Test for intersection: Should find some records.
$wpq = new WP_Query(array(
	'post_type' => 'geo_test',
	'post_status' => 'any',
	'meta_query' => array(
		array( 
		'key' => 'wpgeometa_test',
		'compare' => 'BETWEEN',
		'value' => array(1,10),
		'geom_op' => 'Dimension',
		'type' => 'NUMERIC',  // 'NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED'. Default value is 'CHAR'. 
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
		'compare' => 'NOT IN',
		'value' => array(1,2,3),
		'geom_op' => 'NumPoints'
	)
	))); 

if ( $wpq->have_posts() ) {
	fail($wpq);
} else {
	pass();
}
