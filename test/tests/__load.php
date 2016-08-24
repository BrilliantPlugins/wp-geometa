<?php

define( 'WP_GEOMETA_TESTDIR', dirname( __FILE__ ) . '/../' );
define( 'WP_GEOMETA_TEST_WIDTH', 60 );
if ( !defined( 'WP_GEOMETA_DEBUG' ) ) {
	define( 'WP_GEOMETA_DEBUG', 1 );
}

// Load WordPress.
$wp_load = WP_GEOMETA_TESTDIR . '/../../../../wp-load.php';
if ( ! file_exists( $wp_load ) ) {
	die( "Couldn't find wp-load. Tests are meant to be run when WP_GeoMeta is installed as a plugin.\n" ); 
}
require_once( $wp_load );
defined('ABSPATH') or die('No direct access');

// Load WP GeoMeta in case it's not active.
require_once( WP_GEOMETA_TESTDIR . '/../wp-geometa.php');
require_once( WP_GEOMETA_TESTDIR . '/tests/__SqlFormatter.php');

// A post type for testing with.
$args = array(
	"label" => 'GeoTests',
	"labels" => array(
		"name" => 'GeoTests',
		"singular_name" => 'GeoTest',
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

function fail( $wpq = null ) {
	$sty = getenv('STY');
	if ( empty( $sty ) ) {
		print "😡\n";
	} else {
		print ":-(\n";
	}

	if ( WP_GEOMETA_DEBUG > 0) {
		$bt = debug_backtrace();
		$caller = array_shift($bt);
		print "\n" . basename($caller['file']) . ':' . $caller['line'] . "\n";

		prettyQuery( $wpq );
	}
}

function pass(){
	$sty = getenv('STY');
	if ( empty( $sty ) ) {
		print "😎\n";
	} else {
		print ":-)\n";
	}
}

function unsupported( $unsupported_func = '' ) {
	$sty = getenv('STY');
	if ( empty( $sty ) ) {
		print "😐\n";
	} else {
		print ":-|\n";
	}

	if ( WP_GEOMETA_DEBUG > 0 ) {
		$bt = debug_backtrace();
		$caller = array_shift($bt);
		print "\n" . basename($caller['file']) . ':' . $caller['line'] . "\n";

		print "This test uses $unsupported_func, a function not supported by your install\n";
	}
}

function prettyQuery( $wpq = null ) {
	if ( !empty( $wpq ) ) {
		ob_start();
		print "\n" . SqlFormatter::format($wpq->request) . "\n";
		$sql = ob_get_clean();
		$sql = str_replace("\n","\n\t",$sql);
		print $sql;
	}
}
