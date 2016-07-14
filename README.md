WP_GeoMeta
===========
A WordPressy spatial foundation for WordPress.

Usage
-----

See the [test files](./test/) for more examples. If you're going to run the tests change 
the test object IDs to something in your database.


Add geometry to a post:

    $single_feature = '{ "type": "Feature", "geometry": {"type": "Point", "coordinates": [102.0, 0.5]}, "properties": {"prop0": "value0"} }';
    add_post_meta(15,'singlegeom',$single_feature,false);

Update the post geometry: 	

    $single_feature = '{ "type": "Feature", "geometry": {"type": "Point", "coordinates": [-93.5, 45]}, "properties": {"prop0": "value0"} }';
    update_post_meta(15,'singlegeom',$single_feature,false);


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
Probably WordPress 4.4 since we are using the meta_query parameters of get_terms 
which was introduced in that version.

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


The Problems
------------
### GIS + WordPress = ???
GIS is awesome (and popular!). WordPress is awesome (and popular!). Unfortunately
they don't interact very much. If you drew a ven diagram, the two circles would
just barely be touching. 

And most of that touching is just embedded maps from 3rd party services.

GIS and WordPress should become better friends.

### GIS + Developers = ???
GIS isn't hard, but it's different. WordPress developers don't need or want to 
learn yet another thing for just one project. WordPress admins have even less 
desire to dig into the murky details of GIS.

WordPress isn't hard, but it's different. GIS developers don't need or want to
learn yet another thing for just one project. GIS admins have even less desire 
to dig into the murky details of WordPress.


The Goal
--------
The goal of this plugin is to create WordPressy spatial support. It should provide
a way to store and access spatial data in a way that feels familiar to a typical
WordPress developer. 

Being WordPressy is key because WordPress developers and users are acustomed to
working in a certain way, and dealing with plugins that work within that model.

By being WordPressy WP_GeoMeta has a better shot at being a generic foundation for 
spatial support in WordPress. In this vein, WP_GeoMeta should be available both as 
a standalone plugin, and as a library that other plugins can embed to ensure that 

Geo support is present. 

Beign WordPressy also means that this plugin should provide hooks to allow other
developers to add functionality or extend WP_GeoMeta in various ways.

The Big Plan
--------------

WP_GeoMeta should be light and tight. It should focus on core spatial functionality
and leave the rest for plugin developers.

### High Level
Two classes: WP_GeoQuery and WP_GeoMeta

* WP_GeoMeta will just worry about getting and setting meta values as GeoJSON (or GeoJSON fragments)
* WP_GeoQuery will worry about intercepting WP_Query requests and making them into spatial queries

When a user runs add_post_meta (etc.) and passes in a GeoJSON string or array
WP_GeoMeta will store the geometry in a spatial column. 

WP_GeoQuery will provide a way for users to query posts (etc.) in a similar
way to how they use WP_Query's post_meta arguments.

It may also provide some convenience wrapper functions to handle required
spatial operations in a WordPressy way.

### A Bit Deeper 

WP_GeoMeta will build on both MySQL's spatial support and the WordPress meta
data system.

On plugin activation WP_GeoMeta will create a parallel spatial <del>universe</del> set of
meta tables. Where only wp_postmeta existed you will also find wp_postmeta_geo. 

WP_GeoMeta will use the actions (added|updated|delete)_(comment|post|term|user)_meta to
do the right thing AFTER add_post_meta (etc.) have done their jobs. 

    $single_feature = '{ 
					"type": "Feature", 
					"geometry": {
						"type": "Point", 
						"coordinates": [-93.5, 45]
					}, 
					"properties": {
						"prop0": "value0"
					} 
	}';
    update_post_meta(48,'my_shape',$single_feature);

Since GeoJSON is the one true format for spatial data on the web, all getting and
setting of spatial data will be done in this format - until someone adds a plugin 
to support other formats!

FeatureClass objects and individual Feature objects will both be accepted. String
object and array representations of GeoJSON will be accepted.

WP_GeoMeta will store data in EPSG:4326 by default, which is (a) the default format
for GeoJSON and (b) the most common format for web maps.

WP_GeoMeta won't act on any of the *get_{$meta_type}_meta* filters because we want the 
orignal input data to be returned to the user with the GeoJSON properties it had at the
beginning. 

Since *pre_get_posts* is intimidating and since querying with spatial queries is a 
bit funky, WP_GeoQuery will handle that part for the user. 

WP_GeoQuery adds support to *meta_query* for known spatial comparison operations.
It expects that the value will be a GeoJSON string (either Feature or FeatureCollection). 

A partial list of [spatial comparisons is available here](https://dev.mysql.com/doc/refman/5.6/en/spatial-relation-functions-object-shapes.html). 

Support in other versions of MySQL 

As with WP_GeoMeta, arguments will be passed to the *meta_query* argument as GeoJSON. 

    $q = new WP_Query( array(
    	'meta_query' => array(
    		array(
    			'key' => 'my_shape',
    			'compare' => 'ST_INTERSECTS',
    			'value' => '{"type":"Feature","geometry":{"type":"Point","coordinates":[-93.5,45]}}',
    		)
    	)
    ));

In pre_get_posts WP_GeoQuery will take those arguments, prepare a properly formatted subquery and append it 
back into the post_meta parameter where it.

An additional class, WP_GeoUtil, currently exists in the test code. It currently
contains some common functions that both WP_GeoQuery and WP_GeoMeta use. It may
also include other utility functions.

Rants
-----
Can you believe that MySQL doesn't have ST_TRANSFORM and doesn't use the SRID?


Next Todos
----------
 * Support single geometry compairson operators.
 * Support spatial orderby
 * Replace geoPHP with something small and focused. All we're using it for is GeoJSON to WKT conversion.
 * Live examples

Future Enhancements
-------------------
 * Where do errors go / who sees them? Eg. inside added_meta callback
 * Buffering is a very common operation, but it doesn't work well in EPSG:4326. 
 * Add filter to let users/devs explicitly define meta keys to filter on w/constant to enable the filter
 * Lat/Lng migration tool or plugin that detects coord pairs
Can we use a reverse haversine or something to determine an approximate number 
of degrees to buffer if given a center point and a distance?
 * Add callbacks/hooks so that other plugins with custom tables (eg. Gravity Forms) could
store geo data in a geo way.
 * Add support for https://github.com/krandalf75/MySQL-Spatial-UDF/blob/master/README.md

Changes
-------

### 0.1.0: Perfect Tommy
 * Will now work as a library or a plugin. 
 * Additional functions for getting data back into GeoJSON format.
 * Working well enough to use in production.


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
