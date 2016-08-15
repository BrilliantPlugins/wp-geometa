This document is for developers who wish to help out with WP-GeoMeta.

For developers interested in using WP-GeoMeta in their own project, see the [DEVELOPERS.md](DEVELOPERS.md) file.

Hackers
=======

Send me your bug reports, suggestions and pull requests.

Please feel free to use this library in **your** plugin. Standardization makes
life easier for everyone. If this plugin isn't meeting your needs, or you have other 
great ideas, lets talk!

The Big Plan
--------------

WP-GeoMeta should be light and tight. It should focus on core spatial functionality
and leave the rest to plugin developers.

### The Basics

There are two main classes: WP_GeoQuery and WP_GeoMeta

* ```WP_GeoMeta``` — Handles adding, updating and deleting spatial values.
* ```WP_GeoQuery``` — Handles spatial meta queries.

When a user runs add_post_meta (etc.) and passes in a GeoJSON string or GeoJSON compatible
array, WP_GeoMeta will store the geometry in a spatial column. 

WP_GeoQuery sets up a handler for the ```get_meta_sql``` action to spatial queries and orderby operations.

An additional class ```WP_GeoUtil``` handles data checking and conversion.

### A Bit Deeper 

WP_GeoMeta builds on both MySQL's spatial support and the WordPress meta data system.

On plugin activation WP-GeoMeta will create a parallel spatial set of meta tables. Where 
only wp_postmeta existed you will now also find wp_postmeta_geo. 

WP-GeoMeta uses the actions (added|updated|delete)_(comment|post|term|user)_meta to
do the right thing AFTER ```add_post_meta``` (etc.) have done their jobs. 

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
setting of spatial data is done in this format. Someone could add a plugin 
to support other formats though!

FeatureClass objects and individual Feature objects will both be accepted. String
object and array representations of GeoJSON are be accepted.

WP-GeoMeta stores data in EPSG:4326 by default, which is (a) the default format
for GeoJSON and (b) the most common format for web maps.

WP-GeoMeta does't act on any of the ```get_{$meta_type}_meta``` filters because we want the 
orignal input data to be returned to the user with the GeoJSON properties it had at the
beginning. 

WP_GeoQuery adds support to the ```meta_query``` argument (in WP_Query, get_posts, WP_User_Query, get_users, WP_Comment_Query and get_comments) for known spatial comparison operations.

See the (README.md) for examples of how to use WP_GeoQuery.

Related Projects
----------------
* https://github.com/cimburadotcom/WP-GeoJSON-Loader
* https://github.com/cimburadotcom/MySQL_Stored_Geo_Functions
* https://github.com/cimburadotcom/wp-spatial-capabilities-check
