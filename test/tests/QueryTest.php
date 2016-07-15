<?php
/**
 * Deletes all test posts
 */
require_once( dirname( __FILE__ ) . '/__load.php' );

print str_pad( "Testing WP_Query", WP_GEOMETA_TEST_WIDTH, '.' );

$wpq = new WP_Query(array(
	'post_type' => 'geo_test',
	'post_status' => 'any',
	'meta_query' => array(
		array( 
		'key' => 'wpgeometa_test',
		'compare' => 'INTERSECTS',
		'value' => '{"type":"Feature","geometry":{"type":"Polygon","coordinates":[[[-93.267731667438,45.089788984497],[-93.267731667438,45.092818717985],[-93.213829995075,45.092818717985],[-93.213829995075,45.089788984497],[-93.267731667438,45.089788984497]]]}}'
	)
	))); 

if ( $wpq->have_posts() ) {
	print "😎\n";
} else {
	print "😞\n";
}
