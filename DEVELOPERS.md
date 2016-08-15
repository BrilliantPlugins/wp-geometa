This document is for developers who wish to use WP-GeoMeta in their own plugin. 

For developers interested in helping out with WP-GeoMeta, see the (HACKING.md) file.

Developers
==========

Thanks for considering using WP-GeoMeta! 

WP-GeoMeta is meant to be a spatial foundation for WordPress. It provides a solid foundation
for spatial data using MySQL's native spatial support.

WP-GeoMeta marries the power of true spatial queries with the tidy abstraction of 
WP_Query and the other capabilites of WordPress.

WP-GeoMeta was created with developers in mind. If you find it cumbersome, buggy or 
missing features, let us know! 


Why WP-GeoMeta?
---------------

### Why not separate lat and long fields?

Storing lat and long in separate fields means that you have to implement your own 
(complicated queries)[http://stackoverflow.com/questions/20795835/wordpress-and-haversine-formula] 
if you want to search by distance. 

You'll only be able to store points, and you won't have indexing available. 


### Integration with Other Plugins

You might not need spatial queries yourself, but by using WP-GeoMeta you allow other developers to 
query your data more easily. 

For example, if you were creating a restaurant locations plugin, and someone else had a neighborhood
boundary plugin, the website developer could query which neighborhood a restaurant is in, or which
restaurants are within a given neighborhood. 


How to Use WP-GeoMeta
--------------------- 

1. Download (the latest version)[https://github.com/cimburadotcom/WP-GeoMeta/releases] of WP-GeoMeta to 
a sub-directory inside your plugin — `myplugin/wp-geometa`

2. Within your plugin require *wp-geometa.php* — `require_once( dirname( __FILE__ ) . 'wp-geometa/wp-geometa.php' )`

3. Add an activation hook to your plugin to create the spatial tables

    function my_activation_hook() {
        $wpgeo = WP_GeoMeta::get_instance();
        $wpgeo->create_geo_tables();
    }
    register_activation_hook( __FILE__ , 'wpgeometa_activation_hook' );

4. Use the usual postmeta functions within your plugin (update_post_meta, update_user_meta, etc.) 
   using GeoJSON as the values. 

5. See the [README.md] document for instructions on how to query your data. 


Important Notes
---------------

* When WP-GeoMeta is installed as a plugin, it presents a dashboard page with information about the user's
spatial storage status. When it is used as a library the dashboard is not shown.

* For more complex spatial operations you can always use `$wpdb->query()` with custom SQL.

* MySQL 5.6.1 brought **HUGE** improvements to its spatial capabilities. You should use `WP_GeoUtil::get_capabilities()` 
to see if the function you're about to use is available.

* Some MySQL spatial functions only work on the Bounding Box of the shape and not the actual geometry. For details about
when and why this is a problem, see (this 2013 blog post from Percona)[https://www.percona.com/blog/2013/10/21/using-the-new-mysql-spatial-functions-5-6-for-geo-enabled-applications/].

