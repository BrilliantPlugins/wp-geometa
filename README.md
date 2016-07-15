WP GeoMeta
===========
A spatial foundation for WordPress. Store and search spatial metadata like
you do any other metadata, but using MySQL spatial indexes.

WP GeoMeta lets you take advantage MySQL's spatial data types and spatial 
indexes when storing and searching spatial metadata. 

It detects when GeoJSON metadata is being stored, and transparently 
stores a copy in a spatial meta table. 

WP GeoMeta also adds support for spatial search operators. When a spatial
search operator is used, WP GeoMeta will make sure that the spatial table
is used, taking advantage of indexes and spatial relations.

WP GeoMeta isn't just a plugin, it's also a library which other plugins can
take advantage of. It's meant to be a spatial platform that other GIS and
mapping plugins can build on. 

Usage
-----

### Writing and Reading Data
Store GeoJSON strings as metadata like you would for any other metadata. 

Add geometry to a post:

    $single_feature = '{ "type": "Feature", "geometry": {"type": "Point", "coordinates": [102.0, 0.5]}, "properties": {"prop0": "value0"} }';
    add_post_meta(15,'singlegeom',$single_feature,false);

Update the post geometry: 	

    $single_feature = '{ "type": "Feature", "geometry": {"type": "Point", "coordinates": [-93.5, 45]}, "properties": {"prop0": "value0"} }';
    update_post_meta(15,'singlegeom',$single_feature,false);

Read GeoJSON back from the post;

    $single_feature = get_post_meta(15, 'singlegeom'); 
	print $singlegeom;
	// '{ "type": "Feature", "geometry": {"type": "Point", "coordinates": [-93.5, 45]}, "properties": {"prop0": "value0"} }';

### Querying

Querying is done through the WP_Query meta_query argument. Set _compare_ to a [supported spatial operator](https://wordpress.org/plugins/wp-spatial-capabilities-check/) for your system. The _value_ should be GeoJSON representing the second geometry to compare with. 

Query posts spatially and print their titles:

    $q = new WP_Query( array(
    	'meta_query' => array(
    		array(
    			'key' => 'singlegeom',
    			'compare' => 'ST_INTERSECTS',
    			'value' => '{"type":"Feature","geometry":{"type":"Point","coordinates":[-93.5,45]}}',
    		)
    	)
    ));
    
    while($q->have_posts() ) {
    	$q->the_post();
    	print "\t* " . get_the_title() . "\n";
    }


Server Requirements
-------------------

### WordPress
This plugin supports storing spatial metadata for posts, users, comments and
terms. Searching term metadata arrived in WordPress 4.4, but other
functionality should still work in older versions of WordPress.

### MySQL
MySQL 5.6.1 or higher is strongly recommended. 

WP_GeoMeta will probably work on MySQL 5.4, but spatial support was pretty weak 
before version 5.6.1. 

Before MySQL 5.6.1 spatial functions worked against the mininum bounding rectangle 
instead of the actual geometry.

MySQL 5.7 brough spatial indexes to InnoDB tables. Before that only MyISAM tables
supported spatial indexes. Anything else required a full table scan. 

If you are using MySQL 5.7, good for you, and consider converting your geo tables
to InnoDB! (and let me know how it goes).

### PHP
The goal is PHP 5.2 support, just like WordPress's minimum version
Please report any PHP errors you come across and we'll fix them up.

Frequently Asked Questions
--------------------------

### What spatial comparisons are supported?

Any spatial operation that takes two geometries and returns a boolean is
supported, if your version of MySQL supports it. For example, INTERSECT,
CONTAINS, ST_WITHIN, etc.

To see what your install of MySQL supports, install 
[WP Spatial Capabilities Check](https://wordpress.org/plugins/wp-spatial-capabilities-check/). 
We recommend MySQL 5.6.1 or higher since it included many important updates to 
spatial operators.


Next Todos
----------
 * Support single geometry compairson operators.
 * Support spatial orderby
 * Live examples

Future Enhancements
-------------------
 * Where do errors go / who sees them? Eg. inside added_meta callback
 * Buffering is a very common operation, but it doesn't work well in EPSG:4326. 
 * Add filter to let users/devs explicitly define meta keys to filter on w/constant to enable the filter
 * Lat/Lng migration tool or plugin that detects coord pairs
 * Add callbacks/hooks so that other plugins with custom tables (eg. Gravity Forms) could
store geo data in a geo way.

Changes
-------

### 0.1.0: Perfect Tommy
 * Will now work as a library or a plugin. 
 * Additional functions for getting data back into GeoJSON format.
 * Working well enough to submit to the plugin repo.

### 0.0.2: New Jersey
 * Improved meta query capabilities. Now support sub queries, and uses standard meta-query syntax
 * Whitelist of known spatial functions in meta_query args. Allowed args set by detecting MySQL capabilities.
 * We now delete the spatial index on activation so that we don't end up with duplicate spatial keys
 * Populate geo tables on activation with any existing geojson values
 * Submitted ticket to dbDelta SPATIAL INDEX support: https://core.trac.wordpress.org/ticket/36948
 * Conform to WP coding standards
 * Explicitly set visibility on properties and methods

### 0.0.1: Emilio Lizardo
 * Initial Release

Quotes
-----
 * "The ACF of Geo Queries" -- Nick
 * "No matter where you go, there you are"
