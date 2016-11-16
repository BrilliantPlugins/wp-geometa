This document is for developers who wish to use WP-GeoMeta in their own plugin. 

For developers interested in helping out with WP-GeoMeta, see the [HACKING.md](HACKING.md) file.

Developers
==========

Thanks for considering using WP-GeoMeta! 

WP-GeoMeta is meant to be a spatial foundation for WordPress. It provides a solid foundation
for spatial data using MySQL's native spatial support.

WP-GeoMeta marries the power of true spatial queries with the tidy abstraction of 
WP_Query and the other capabilites of WordPress.

WP-GeoMeta was created with developers in mind. If you find it cumbersome, buggy or 
missing features, let us know! 


Hooks: Filters and Actions
--------------------------

 * *Filter*: wpgm_pre_metaval_to_geom

 This filter is called right before WP-GeoMeta tries to convert the incoming meta value 
 to geometry. It is used internally to handle separate latitude and longitude values and
 could be used to support other unusual situations. If you just need to convert a non-geojson
 geometry to WKT, you should use wpgq_metaval_to_geom instead.

 Usage:
 ```
	add_filter( 'wpgm_pre_metaval_to_geom', 'myplugin_handle_pre_metaval', 10, 2 );

	/*
	 * @param array  $meta_args Array with the meta_id that was just saved, the object_id it was for, the meta_key and meta_values used.
	 *  $meta_args[0] -- meta_id from insert.
	 *  $meta_args[1] -- object_id which this applies to.
	 *  $meta_args[2] -- meta key.
	 *  $meta_args[3] -- the meta value.
	 *
	 * @param string $object_type Which WP type is it? (comment/user/post/term).
	 */
	public static function handle_latlng_meta( $meta_args, $object_type ) {
		// Return early if it's not the key we're looking for.
		if ( 'special meta key' !== $meta_args[2] ) {
			return $meta_args;
		}

		// Do some stuff, then return $meta_args.
		return $meta_args;
	}
 ```

 * *Filter*: wpgm_pre_delete_geometa

 This filter is called after a meta value has been deleted from the regular meta table, right 
 before WP-GeoMeta deletes the corresponding value from the geometa table. Deletions are 
 done based on the regular meta table's meta ID. This filter is used internally to delete
 the geo meta value when a latitude or longitude meta value is deleted from the non-geo 
 meta tables. 

 Usage:
 ```
	add_filter( 'wpgm_pre_delete_geometa', 'special_delete_scenario', 10, 5 );

	/*
	 * @param array  $meta_ids The Meta IDs that will be deleted.
	 * @param string $type The type of object whose meta is being deleted.
	 * @param int    $object_id The ID of the object whose meta is being deleted.
	 * @param string $meta_key The name of the meta key which is being deleted.
	 * @param string $meta_value The value which is being deleted.
	 */
	public function special_delete_scenario( $meta_ids, $type, $object_id, $meta_key, $meta_value ) {

		if ( 'special meta key' !== $meta_key ) {
			return $meta_ids;
		}

		// Do some stuff, then return $meta_ids;
		return $meta_ids;
	}
 ```

 * *Filter*: wp_geoquery_srid

 This filter is called during plugins_loaded. It sets the [SRID](https://en.wikipedia.org/wiki/Spatial_reference_system) 
 that will be used when storing values in the database. The default value is 4326 
 (for EPSG:4326), which is the standard for GeoJSON. 

 MySQL doesn't support ST_Transform, and will complain if two geometries being compared
 have different SRIDs. As such, this option is dangerous and should be left alone unless 
 you know what you're doing.

 * *Filter*: wpgq_metaval_to_geom

 This filter is called within WP_GeoUtil::metaval_to_geom. It offers an opportunity to 
 support non-GeoJSON geometry types. 

 Functions implementing this filter should either return the incoming $metaval untouched
 or return WKT (best) or GeoJSON (will work).

 Usage:
 ```
	add_filter( 'wpgq_metaval_to_geom', 'kml_to_geometry' );

	/**
	  * @param string $metaval The metavalue that we're about to store.
	  */
	function kml_to_geometry( $metaval ) {

		if ( !is_kml( $metaval ) ) {
			return $metaval;
		}

		$wkt = kml_to_wkt( $metaval );

		return $wkt;
	}
 ```

 * *Filter*: wpgq_geom_to_geojson

 This filter is called when converting a geometry from the database into GeoJSON
 so it can be displayed on a map (or whatever). 

 This could be used to do transformations or other alterations to geometries before
 displaying them. 

 Usage:
 ```
	add_filter( 'wpgq_geom_to_geojson', 'myplugin_geom_to_geojson' );

	/**
	  * @param string $wkt The well known text representation of the geometry
	  from the database.
	  */
	function myplugin_geom_to_geojson( $wkt ) {
		$geojson = myfunc_wkt_to_geojson( $wkt );

		// Do something to the geojson
		return $geojson;
	}
 ```

 * *Action*: wpgm_populate_geo_tables

 This action is called at the end of WP_GeoMeta->populate_geo_tables() to give you
 an opportunity to populate the geo metatables with any non-GeoJSON types of geometry
 you are supporting. It is used internally to support populating the geo metatables
 with any latitude/longitude pairs added through WP_GeoMeta::add_latlng_field. 

 Usage:
 ```
	add_filter( 'wpgm_populate_geo_tables', 'myplugin_populate_kml' );

	function myplugin_populate_kml() {
		global $wpdb;

		$wpgeometa = WP_GeoMeta::get_instance();

		$query = "SELECT post_id, meta_id, meta_key, meta_value 
			FROM wp_postmeta 
			WHERE meta_value LIKE '<?xml%/kml/2.2%'";

		$res = $query->get_results( $query, ARRAY_A );
		foreach( $res as $row ) {
			// We don't have to convert KML to WKT because the filters we have set up will get called.
			$wpgeometa->updated_post_meta( $row[ 'meta_id' ], $row[ 'post_id' ], $row[ 'meta_key' ], $row[ 'meta_value' ] );
		}
	}
 ```


Why WP-GeoMeta?
---------------

### Integration with Other Plugins

You might not need spatial queries yourself, but by using WP-GeoMeta you allow other developers to 
query your data more easily. 

For example, if you were creating a restaurant locations plugin, and someone else had a neighborhood
boundary plugin, the website developer could query which neighborhood a restaurant is in, or which
restaurants are within a given neighborhood. 


### Why not separate lat and long fields?

Storing lat and long in separate fields means that you have to implement your own 
[complicated queries](http://stackoverflow.com/questions/20795835/wordpress-and-haversine-formula)
if you want to search by distance. 

You'll only be able to store points, and you won't have indexing available. 

### OK, fine, but I really need separate fields

Using separate Latitude and Longitude fields is slightly more complex, but is 
supported by WP-GeoMeta. You will need to register your new latitude/longitude
meta keys so that WP-GeoMeta knows about them. You can do this any time after
plugins_loaded. 

```
add_action('plugins_loaded', function() {
	// WP_GeoMeta::add_latlng_field( <latitude field name>, <longitude field name>, <spatial meta_key name> );
	WP_GeoMeta::add_latlng_field( 'myplugin_lat', 'myplugin_lng', 'myplugin_geo' );
});
```

A few caveats with handling latitude and longitude:

 1. The spatial meta key will only be present in the wp_postmeta_geo table ( or
 other applicable geo metatable ). Any spatial queries will need to use the 
 spatial meta key you register. 
 2. There's a chance of conflicts. If your latitude or longitude field is named
 the same as another plugin's latitude or longitude field the resulting behavior 
 is undefined and unsupported. 

*Note*: The [WordPress Geodata meta keys](https://codex.wordpress.org/Geodata) are 
supported out of the box. 

How to Use WP-GeoMeta
--------------------- 

1. Download [the latest version](https://github.com/cimburadotcom/WP-GeoMeta/releases) of WP-GeoMeta to 
a sub-directory inside your plugin — ```myplugin/wp-geometa```

2. Within your plugin require *wp-geometa.php* — ```require_once( dirname( __FILE__ ) . 'wp-geometa/wp-geometa.php' )```

3. Add an activation hook to your plugin to create the spatial tables

```
    function my_activation_hook() {
        $wpgeo = WP_GeoMeta::get_instance();
        $wpgeo->create_geo_tables();
    }
    register_activation_hook( __FILE__ , 'my_activation_hook' );
```


4. Use the usual postmeta functions within your plugin (update_post_meta, update_user_meta, etc.) 
   using GeoJSON as the values. 

5. See the [README.md](README.md) document for instructions on how to query your data. 


Important Notes
---------------

* When WP-GeoMeta is installed as a plugin, it presents a dashboard page with information about the user's
spatial storage status. When it is used as a library the dashboard is not shown.

* For more complex spatial operations you can always use ```$wpdb->query()``` with custom SQL.

* MySQL 5.6.1 brought **HUGE** improvements to its spatial capabilities. You should use ```WP_GeoUtil::get_capabilities()``` 
to see if the function you're about to use is available.

* Some MySQL spatial functions only work on the Bounding Box of the shape and not the actual geometry. For details about
when and why this is a problem, see [this 2013 blog post from Percona](https://www.percona.com/blog/2013/10/21/using-the-new-mysql-spatial-functions-5-6-for-geo-enabled-applications/).

