<?php

// Tests adding, updating, querying and deleting meta from terms
// https://developer.wordpress.org/reference/functions/get_terms/

$term_id_to_test = 3;

require_once(__DIR__ . '/../../../../wp-load.php');
require_once('./../wp-geometa.php');


// Test adding data
print "Adding geometry metadata to term $term_id_to_test\n";
$single_feature = '{ "type": "Feature", "geometry": {"type": "Point", "coordinates": [102.0, 0.5]}, "properties": {"prop0": "value0"} }';
add_term_meta($term_id_to_test,'singlegeom',$single_feature);


print "Updating geometry metadata in term $term_id_to_test\n";
$single_feature = '{ "type": "Feature", "geometry": {"type": "Point", "coordinates": [-93.5, 45]}, "properties": {"prop0": "value0"} }';
update_term_meta($term_id_to_test,'singlegeom',$single_feature,false);

/*
 * We need to pass any additional default argument to get_terms since
 * it detects if it should run in legacy mode (which we're not supporting)
 * or in new mode by checking if any of the new default arg keys exist. 
 *
 * Here we used hide_empty => false, but we could use anything defined as parameters
 * in the $args array here: https://developer.wordpress.org/reference/functions/get_terms/
 */
$terms = get_terms( array(
	'hide_empty' => false,
	'meta_query' => array(
		array(
			'key' => 'singlegeom',
			'compare' => 'ST_INTERSECTS',
			'value' => '{"type":"Feature","geometry":{"type":"Point","coordinates":[-93.5,45]}}',
		)
	)
));

print "Names of terms found were:\n";
foreach($terms as $term){
	print "\t* " . $term->name . "\n";
}

// Test delete
echo "Deleting test metadata\n";
delete_term_meta($term_id_to_test,'singlegeom');
