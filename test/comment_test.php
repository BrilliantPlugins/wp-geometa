<?php
// Tests adding, updating, querying and deleting meta from comment
// https://codex.wordpress.org/Class_Reference/WP_Comment_Query

$comment_id_to_test = 4;

require_once(__DIR__ . '/../../../../wp-load.php');
require_once('./../wp-geometa.php');

// Test table creation
print "Creating tables for WP_GeoMeta\n";
activate_wp_geometa();

// Test adding data
print "Adding geometry metadata to comment $comment_id_to_test\n";
$single_feature = '{ "type": "Feature", "geometry": {"type": "Point", "coordinates": [102.0, 0.5]}, "properties": {"prop0": "value0"} }';
add_comment_meta($comment_id_to_test,'singlegeom',$single_feature);


print "Updating geometry metadata in comment $comment_id_to_test\n";
$single_feature = '{ "type": "Feature", "geometry": {"type": "Point", "coordinates": [-93.5, 45]}, "properties": {"prop0": "value0"} }';
update_comment_meta($comment_id_to_test,'singlegeom',$single_feature,false);

print "Running WP_Query with geo_meta argument\n";
$q = new WP_comment_Query;
$comments = $q->query( array(
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

print "Names of comment found were:\n";
foreach($comments as $comment){
	print "\t* " . $comment->comment_content . "\n";
}

// Test delete
echo "Deleting test metadata\n";
delete_comment_meta($comment_id_to_test,'singlegeom');
