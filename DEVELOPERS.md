Developers
==========
Send me your bug reports, suggestions and pull requests!

Please feel free to use this library in **your** plugin! Standardization makes
life easier for everyone. If this plugin isn't meeting your needs, lets talk!

If you've got ideas about how spatial should work in WordPress, let's talk!

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

A list of [spatial functions is available here](https://mariadb.com/kb/en/mariadb/mysqlmariadb-spatial-support-matrix/). 

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

Related Projects
----------------
* https://github.com/cimburadotcom/WP-GeoJSON-Loader
* https://github.com/cimburadotcom/MySQL_Stored_Geo_Functions
* https://github.com/cimburadotcom/wp-spatial-capabilities-check
