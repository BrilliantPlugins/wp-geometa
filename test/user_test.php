<?php

// Tests adding, updating, querying and deleting meta from users
// https://codex.wordpress.org/Class_Reference/WP_User_Query

$user_id_to_test = 3;

require_once(__DIR__ . '/../../../../wp-load.php');
require_once('./../wp-geometa.php');

// Test table creation
print "Creating tables for WP_GeoMeta\n";
activate_wp_geometa();

// Test adding data
print "Adding geometry metadata to user $user_id_to_test\n";
$single_feature = '{ "type": "Feature", "geometry": {"type": "Point", "coordinates": [102.0, 0.5]}, "properties": {"prop0": "value0"} }';
add_user_meta($user_id_to_test,'singlegeom',$single_feature);


print "Updating geometry metadata in user $user_id_to_test\n";
$single_feature = '{ "type": "Feature", "geometry": {"type": "Point", "coordinates": [-93.5, 45]}, "properties": {"prop0": "value0"} }';
update_user_meta($user_id_to_test,'singlegeom',$single_feature,false);

print "Running WP_Query with geo_meta argument\n";
$q = new WP_User_Query( array(
	'meta_query' => array(
		array(
			'key' => 'singlegeom',
			'compare' => 'ST_INTERSECTS',
			'value' => '{"type":"Feature","geometry":{"type":"Point","coordinates":[-93.5,45]}}',
		)
	)
));

print "Actual Query run was:\n";
print "\n" . $q->request . "\n\n";

$authors = $q->get_results();

print "Names of users found were:\n";
foreach($authors as $author){
	$info = get_userdata($author->ID);
	print "\t* " . $info->user_login . "\n";
}

// Test delete
// echo "Deleting test metadata\n";
// delete_user_meta($user_id_to_test,'singlegeom');
