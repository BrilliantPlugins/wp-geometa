<?php

require_once(__DIR__ . '/../../../wp-load.php');
require_once('./wp-geoquery.php');

// Test table creation
activate_wp_brilliant_geo();

// $wpgeo = WP_GeoQuery::get_instance();
// $wpgeo->uninstall();

// Test adding
// add_post_meta(48,'test','myvalue',true);
$single_feature = '{ "type": "Feature",
	        "geometry": {"type": "Point", "coordinates": [102.0, 0.5]},
			        "properties": {"prop0": "value0"}
					        }';
add_post_meta(48,'single',$single_feature,false);

$single_feature = '{ "type": "Feature",
	        "geometry": {"type": "Point", "coordinates": [-93.5, 45]},
			        "properties": {"prop0": "value0"}
					        }';
update_post_meta(48,'single',$single_feature,false);

// Test update
// update_post_meta(48,'test','altvalue','myvalue');

// Test delete
// delete_post_meta(48, 'test');
// delete_post_meta(48,'single');
