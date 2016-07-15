<?php

define( 'WP_GEOMETA_TESTDIR', dirname( __FILE__ ) . '/../' );
define( 'WP_GEOMETA_TEST_WIDTH', 60 );

// Load WordPress.
require_once( WP_GEOMETA_TESTDIR . '/../../../../wp-load.php');

// Load WP GeoMeta in case it's not active.
require_once( WP_GEOMETA_TESTDIR . '/../wp-geometa.php');

// A post type for testing with.
$args = array(
	"label" => __( 'GeoTests', '' ),
	"labels" => array(
		"name" => __( 'GeoTests', '' ),
		"singular_name" => __( 'GeoTest', '' ),
	),
	"description" => "",
	"public" => true,
	"show_ui" => true,
	"show_in_rest" => false,
	"rest_base" => "",
	"has_archive" => false,
	"show_in_menu" => true,
	"exclude_from_search" => false,
	"capability_type" => "post",
	"map_meta_cap" => true,
	"hierarchical" => false,
	"rewrite" => array( "slug" => "geo_test", "with_front" => true ),
	"query_var" => true,

	"supports" => array( "title", "editor", "thumbnail" ),                
);
register_post_type( "geo_test", $args );


