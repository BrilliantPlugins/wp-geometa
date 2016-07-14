<?php

// Tests adding, updating, querying and deleting meta from posts
// https://codex.wordpress.org/Class_Reference/WP_Query

$post_id_to_test = 48;

require_once(__DIR__ . '/../../../../wp-load.php');
require_once('./../wp-geometa.php');

// Test adding data
print "Adding geometry metadata to post $post_id_to_test\n";
$single_feature = '
  { "type": "FeatureCollection",
    "features": [
      { "type": "Feature",
        "geometry": {"type": "Point", "coordinates": [102.0, 0.5]},
        "properties": {"prop0": "value0"}
        },
      { "type": "Feature",
        "geometry": {
          "type": "LineString",
          "coordinates": [
            [102.0, 0.0], [103.0, 1.0], [104.0, 0.0], [105.0, 1.0]
            ]
          },
        "properties": {
          "prop0": "value0",
          "prop1": 0.0
          }
        },
      { "type": "Feature",
         "geometry": {
           "type": "Polygon",
           "coordinates": [
             [ [100.0, 0.0], [101.0, 0.0], [101.0, 1.0],
               [100.0, 1.0], [100.0, 0.0] ]
             ]
         },
         "properties": {
           "prop0": "value0",
           "prop1": {"this": "that"}
           }
         }
       ]
     }
';

add_post_meta($post_id_to_test,'multigeom',$single_feature,false);
