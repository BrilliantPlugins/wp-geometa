<?php
/*
 * This should test queries with nested meta_query objects
 */
require_once( dirname( __FILE__ ) . '/__load.php' );

print str_pad( "Testing nested meta clauses", WP_GEOMETA_TEST_WIDTH, '.' );


$wpq = new WP_Query( array(
	'posts_per_page' => -1,
	'post_type'  => 'geo_test',
	'post_status' => 'any',
	'meta_query' => array(
		'relation' => 'OR',
		array(
			'key'     => 'wpgeometa_test',
			'compare' => 'INTERSECTS', // Test Post 6
			'value'   => '{"type":"Feature","geometry":{"type":"Polygon","coordinates":[[[-93.240187168558,45.086756059204],[-93.240187168558,45.094512267863],[-93.227398395975,45.094512267863],[-93.227398395975,45.086756059204],[-93.240187168558,45.086756059204]]]}}'
		),
		array(
			'relation' => 'AND',
			array(
				'key' => 'wpgeometa_test',
				'compare' => 'INTERSECTS', // Test Post 2
				'value' => '{"type":"Feature","geometry":{"type":"Polygon","coordinates":[[[-93.255769014577,45.083593183085],[-93.255769014577,45.086199046195],[-93.249717951039,45.086199046195],[-93.249717951039,45.083593183085],[-93.255769014577,45.083593183085]]]}}',
			),
			array(
				'key' => 'wpgeometa_test',
				'compare' => 'INTERSECTS', // Test Post 5
				'value' => '{"type":"Feature","geometry":{"type":"Polygon","coordinates":[[[-93.251945972879,45.08257454104],[-93.251945972879,45.086877258065],[-93.244907856424,45.086877258065],[-93.244907856424,45.08257454104],[-93.251945972879,45.08257454104]]]}}'
			),
		),
	),
) );

// Results should include the 3 geometries from above (Test Posts 2, 5 and 6) as well as these additional test posts which are intersected: (Test Posts 3, 8, 16 and 17)
if ( $wpq->post_count != 7 ) {

	if ( WP_GEOMETA_DEBUG > 1 ) {
		while( $wpq->have_posts() ) {
			$wpq->the_post();
			print get_the_title() . "\n";
		}
	}

	fail($wpq);

	return;
}

pass();
