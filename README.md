WP_GeoQuery
===========

This is pre-alpha and shouldn't be used for anything yet. 

The goal of this plugin is to create very WordPressy spatial support, allowing 
WordPress users to do spatial stuff in a way which fits in with the rest of
the WordPress environment.

If done well, this would become a standard that other spatial plugin developers could
work against.

We are creating and releasing this plugin in the hopes of increasing the use of
spatial in the WordPress world. 

The intention is to provide a WordPress plugin which will allow WordPress objects 
(Posts, Comments and Users) to have spatial meta attached, and to provide 
support for spatial query operations within WP_Query. 


Game Plan / Current Status
--------------------------

*Upated: May 21, 2016*

Two classes: WP_GeoQuery and WP_GeoMeta

* WP_GeoMeta will just worry about getting and setting meta values as GeoJSON (or GeoJSON fragments)
* WP_GeoQuery will worry about intercepting WP_Query requests and making them into spatial queries


### WP_GeoMeta

WP_GeoMeta creates parallel geo meta tables for all supported object types with a 
spatial column type for the meta_value column. These tables are named 
wp_postmeta_geo, etc.

WP_GeoMeta handles (added|updated|deleted)_(post|comment|term||user)_meta actions and
detects when GeoJSON is being set or updated in postmeta and stores a parallel
geometrey value in the geo meta table. 

By default data will be stored in EPSG:4326 since WordPress is web software and the data will 
probably be used to produce web maps. 4326 is also the most common format for GeoJSON and possibly
the only offical EPSG for GeoJSON 2.0.

The EPSG will be able to be overridden with a filter.

We won't add a filter for get_(post|comment|term|user)_meta, so that the user will get
whatever they put into the meta table in the first place, including the non-spatial
properties of the GeoJSON.

### WP_GeoQuery

WP_GeoQuery will use actions and filters to detect if WP_Query has a 'geo_query' 
argument which it would then construct into an appropriate meta_query. 

WP_GeoQuery should support spatial relation functions [documented here](https://dev.mysql.com/doc/refman/5.6/en/spatial-relation-functions-object-shapes.html)


Server Requirements
-------------------

Can we safely require MySQL 5.6? It brought a LOT of new spatial functionality?

MySQL 5.4 did technically have spatial support though. 

Tables are created as MyISAM tables because InnoDB tables don't support
spatial indexes until 5.7.


Rants
-----
Can you believe that MySQL doesn't have ST_TRANSFORM and doesn't use the SRID?


Todo
----
 * Populate geo tables on activation with any existing geojson values
 * Where do errors go / who sees them? Eg. inside added_meta callback
