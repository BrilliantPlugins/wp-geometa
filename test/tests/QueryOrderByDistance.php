<?php
require_once( dirname( __FILE__ ) . '/__load.php' );

print str_pad( "Testing OrderBy Distance", WP_GEOMETA_TEST_WIDTH, '.' );

$capabilities = WP_GeoUtil::get_capabilities();

if ( !in_array('st_distance',$capabilities) ){
	unsupported( 'ST_Distance' );
	return;
}

$wpq = new WP_Query(array(
	'post_type' => 'geo_test',
	'posts_per_page' => -1,
	'orderby' => 'distance',
	'order' => 'ASC',
	'post_status' => 'any',
	'meta_query' => array(
		'distance' => array( 
			'key' => 'wpgeometa_test',
			'compare' => 'ST_Distance',
			'value' => '{"type":"Feature","geometry":{"type":"Polygon","coordinates":[[[-1.26,1.08],[-1.26,1.09],[-1.21,1.09],[-1.21,1.08],[-1.26,1.08]]]}}',
			'type' => 'DECIMAL(10,7)'
		)
	))); 

if ( ! $wpq->have_posts() ) {
	fail($wpq);
	return;
} else {

	// See if our distance is in fact increasing
	$maxVal = -1;
	while ( $wpq->have_posts() ) {
		$wpq->the_post();
		$post_id = get_the_ID();
		$res = $wpdb->get_var( $wpdb->prepare( 'SELECT ST_Distance( meta_value, GeomFromText( \'POLYGON ((-1.26 1.08, -1.26 1.09, -1.21 1.09, -1.21 1.08, -1.26 1.08))\', 4326)) FROM ' . $wpdb->postmeta . '_geo WHERE post_id=%s', array( $post_id ) ) );

		if ( $res < $maxVal ) {
			fail( $wpq );
			return;
		} 

		$maxVal = $res;
	}
}

$wpq = new WP_Query(array(
	'post_type' => 'geo_test',
	'posts_per_page' => -1,
	'orderby' => array( 'distance' => 'DESC' ),
	'post_status' => 'any',
	'meta_query' => array(
		'distance' => array( 
			'key' => 'wpgeometa_test',
			'compare' => 'ST_Distance',
			'value' => '{"type":"Feature","geometry":{"type":"Polygon","coordinates":[[[-1.26,1.08],[-1.26,1.09],[-1.21,1.09],[-1.21,1.08],[-1.26,1.08]]]}}',
			'type' => 'DECIMAL(10,7)'
		)
	))); 

if ( ! $wpq->have_posts() ) {
	fail($wpq);
	return;
} else {

	// See if our distance is in fact increasing
	$minVal = 100000000000;
	while ( $wpq->have_posts() ) {
		$wpq->the_post();
		$post_id = get_the_ID();
		$res = $wpdb->get_var( $wpdb->prepare( 'SELECT ST_Distance( meta_value, GeomFromText( \'POLYGON ((-1.26 1.08, -1.26 1.09, -1.21 1.09, -1.21 1.08, -1.26 1.08))\', 4326)) FROM ' . $wpdb->postmeta . '_geo WHERE post_id=%s', array( $post_id ) ) );

		if ( $res > $minVal) {
			fail( $wpq );
			return;
		} 

		$minVal = $res;
	}
}



pass();
