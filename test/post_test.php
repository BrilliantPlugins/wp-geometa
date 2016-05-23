<?php

// Tests adding, updating, querying and deleting meta from posts
// https://codex.wordpress.org/Class_Reference/WP_Query

$post_id_to_test = 48;

require_once(__DIR__ . '/../../../../wp-load.php');
require_once('./../wp-geometa.php');

// Test table creation
print "Creating tables for WP_GeoMeta\n";
activate_wp_geometa();

// Test adding data
print "Adding geometry metadata to post $post_id_to_test\n";
$single_feature = '{ "type": "Feature", "geometry": {"type": "Point", "coordinates": [102.0, 0.5]}, "properties": {"prop0": "value0"} }';
add_post_meta($post_id_to_test,'singlegeom',$single_feature,false);


print "Updating geometry metadata in post $post_id_to_test\n";
$single_feature = '{ "type": "Feature", "geometry": {"type": "Point", "coordinates": [-93.5, 45]}, "properties": {"prop0": "value0"} }';
update_post_meta($post_id_to_test,'singlegeom',$single_feature,false);

print "Running WP_Query with geo_meta argument\n";
$q = new WP_Query( array(
	'geo_meta' => array(
		array(
			'key' => 'singlegeom',
			'compare' => 'ST_INTERSECTS',
			'value' => '{"type":"Feature","geometry":{"type":"Point","coordinates":[-93.5,45]}}',
		)
	)
));

print "Actual Query run was:\n";
print "\n" . $q->request . "\n\n";

print "Titles of posts found were:\n";
while($q->have_posts() ) {
	$q->the_post();
	print "\t* " . get_the_title() . "\n";
}

// Test delete
echo "Deleting test metadata\n";
delete_post_meta($post_id_to_test,'singlegeom');
