<?php
/**
 * Queries posts and orders them two different ways
 *
 * https://make.wordpress.org/core/2015/03/30/query-improvements-in-wp-4-2-orderby-and-meta_query/
 *
 * There are four methods for defining orderby
 * https://core.trac.wordpress.org/ticket/31265

     * --'orderby' should accept space-separated values ('orderby' => 'comment_user_ID comment_ID') as well as the array syntax introduced to WP_Query in #17065. See #30478.-- [done]
     * meta_value, meta_key, meta_type, and meta_compare should all be supported, and should be translated to a meta_query clause in the same way (using WP_Meta_Query::parse_query_vars(), I suppose)
     * orderby=meta_value, orderby=meta_value_num, and orderby=somekey when meta_key=somekey should be supported. See #27887.
     * orderby=clausename, where 'clausename' is the array-key identifier of a meta_query clause. See #31045 for how this was done in WP_Query. 


	 WP-GeoMeta is going to try to support the following syntaxes for orderby for now: 


	 // Order by metavalue with no filter on meta data
	 WP_Query(
		 'orderby' => 'metaident',
		 'meta_query' => array(
			 'metaident' => array(
				 'key' => 'metakey',
				 'geom_op' => 'spatial_function'
				 )
			 )
		 );


	// Order by metavalue with a single-geometry spatial function
	WP_Query(
		'orderby' => 'metaident',
		'meta_query' => array(
			'metaident' => array(
				'key' => 'metakey',
				'value' => '14',
				'compare' => '>=',
				'geom_op' => 'spatial_function'
				'type' => 'NUMERIC'
				)
			)
		);


	// Order by metavalue with a two-geometry spatial function
	WP_Query(
		'orderby' => 'metaident',
		'meta_query' => array(
			'metaident' => array(
				'key' => 'metakey',
				'value' => '14',
				'compare' => 'spatial_function,
				'type' => 'NUMERIC'
				)
			)
		);

	// the same thing, but with array notation should work too
	'orderby' => array('metaident' => 'DESC'),
	'orderby' => array('metaident' => 'ASC'),
 */
require_once( dirname( __FILE__ ) . '/__load.php' );

print str_pad( "Testing OrderBy", WP_GEOMETA_TEST_WIDTH, '.' );

/**
 * $join = apply_filters_ref_array( 'posts_join', array( $join, &$this ) );
 * $join = apply_filters_ref_array( 'posts_join_paged', array( $join, &$this ) );
 *
 * $orderby = apply_filters_ref_array( 'posts_orderby', array( $orderby, &$this ) );
 * $clauses = (array) apply_filters_ref_array( 'posts_clauses', array( compact( $pieces ), &$this ) );
 *
 * $orderby = apply_filters_ref_array( 'posts_orderby_request', array( $orderby, &$this ) );
 * $clauses = (array) apply_filters_ref_array( 'posts_clauses_request', array( compact( $pieces ), &$this ) );
 *
 */

// Test for intersection: Should find some records.
$wpq = new WP_Query(array(
	'post_type' => 'geo_test',
	'posts_per_page' => -1,
	// 'orderby' => ARRAY( 'dimensions' => 'DESC',  'titlemeta' => 'ASC' ),
	'orderby' => 'dimensions titlemeta',
	'post_status' => 'any',
	'meta_query' => array(
		'dimensions' => array( 
			'key' => 'wpgeometa_test',
			'geom_op' => 'Dimension',
			// 'type' => 'NUMERIC'
		),
		'titlemeta' => array( 
			'key' => '_post_title',
			'compare' => 'LIKE',
			'value' => 'TEST',
		)
	))); 

if ( ! $wpq->have_posts() ) {
	fail($wpq);
	return;
} else {

	// See if our dimension is in fact decrementing 
	$minVal = 99;
	while ( $wpq->have_posts() ) {
		$wpq->the_post();
		$post_id = get_the_ID();
		$res = $wpdb->get_var( $wpdb->prepare( 'SELECT Dimension(meta_value) FROM ' . $wpdb->postmeta . '_geo WHERE post_id=%s', array( $post_id ) ) );

		if ( $res > $minVal) {
			fail( $wpq );
			return;
		} 

		$minVal = $res;
	}
}

// Test for intersection: Should find some records.
$wpq = new WP_Query(array(
	'post_type' => 'geo_test',
	'posts_per_page' => -1,
	'orderby' => ARRAY( 'dimensions' => 'ASC',  'titlemeta' => 'ASC' ),
	// 'orderby' => 'dimensions titlemeta',
	'post_status' => 'any',
	'meta_query' => array(
		'dimensions' => array( 
			'key' => 'wpgeometa_test',
			'geom_op' => 'Dimension',
			// 'type' => 'NUMERIC'
		),
		'titlemeta' => array( 
			'key' => '_post_title',
			'compare' => 'LIKE',
			'value' => 'TEST',
		)
	))); 

if ( ! $wpq->have_posts() ) {
	fail($wpq);
	return;
} else {

	// See if our dimension is in fact decrementing 
	$maxVal = 0;
	while ( $wpq->have_posts() ) {
		$wpq->the_post();
		$post_id = get_the_ID();
		$res = $wpdb->get_var( $wpdb->prepare( 'SELECT Dimension(meta_value) FROM ' . $wpdb->postmeta . '_geo WHERE post_id=%s', array( $post_id ) ) );

		if ( $res < $maxVal) {
			fail( $wpq );
			return;
		} 

		$maxVal = $res;
	}
}

pass();
