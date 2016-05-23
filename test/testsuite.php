<?php

// Don't require plugin to be activated to run tests
require_once(__DIR__ . '/../../../../wp-load.php');
require_once('./../wp-geometa.php');

global $wpdb;

// Test table creation
print "Creating Geo Tables\n";
activate_wp_geometa();

$geo_tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}_%_geo'",ARRAY_N);

if(count($geo_tables) !== 4){
	die("Couldn't create geo tables\n");
}else {
	foreach($geo_tables as $gt){
		print "\tCreated table {$gt[0]}\n";
	}
}

print "Creating Test Objects\n";

// wp_insert_post()
// wp_insert_post()
// wp_insert_post()
// 
// wp_insert_comment()
// wp_insert_comment()
// wp_insert_comment()
// wp_insert_comment()
// wp_insert_comment()
//
// wp_insert_term()
// wp_insert_term()
// wp_insert_term()
// wp_insert_term()
// wp_insert_term()
//
// wp_insert_user()
// wp_insert_user()
// wp_insert_user()

print "Creating Test Meta Data\n";

print "Running Test Meta Queries\n";

print "Cleaning Up Test Data\n";
