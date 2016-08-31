=== WP-GeoMeta ===
Contributors: stuporglue, cimburacom
Donate link: https://cimbura.com/contact-us/make-a-payment/
Tags: GIS, spatial, mysql, mariadb, geography, mapping, meta, metadata
Requires at least: 4.4.0
Tested up to: 4.6.0
Stable tag: trunk
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Store and search spatial metadata like you do any other metadata, but while taking advantage of MySQL native spatial types and indexes.

== Description == 

A spatial foundation for WordPress. Store and search spatial metadata like
you do any other metadata, but using MySQL spatial indexes.

WP GeoMeta lets you take advantage MySQL's spatial data types and spatial 
indexes when storing and searching spatial metadata. 

It detects when [GeoJSON](http://geojson.org/) metadata is being stored, and transparently 
stores a copy in a spatial meta table. 

WP GeoMeta also adds support for spatial search operators. When a spatial
search operator is used, WP GeoMeta will make sure that the spatial table
is used, taking advantage of indexes and spatial relations.

WP GeoMeta isn't just a plugin, it's also a library which other plugins can
take advantage of. It's meant to be a spatial platform that other GIS and
mapping plugins can build on to allow spatial interoperability between
plugins.

== Installation ==

Install this plugin in the usual WordPress way, then go to your WordPress
dashboard to Tools::WP GeoMeta to see the status of your spatial data and
to use the included tools.

1. Upload the plugin files to the `/wp-content/plugins/wp-geometa` directory,
	or install the plugin through the WordPress plugin screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to Tools::WP Geometa

== Frequently Asked Questions ==

No one has actually asked any questions yet!

= Where can I get help with GIS and WordPress? = 

For community support try [WherePress.com](http://WherePress.com/), or [The Spatial Community](https://julien24.typeform.com/to/kGPqYr).

For commercial support you can contact the plugin developer at [Cimbura.com](https://cimbura.com/contact-us/project-request-form/)

For fast and short questions you can [contact me](https://twitter.com/stuporglue) on twitter

== Screenshots ==

1. WP GeoMeta showing a sample of spatial metadata
2. A list of all spatial functions supported by your version of MySQL
3. WP GeoMeta system status page
4. Built-in regression tests

== Changelog ==






