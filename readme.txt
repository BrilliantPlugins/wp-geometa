=== WP-GeoMeta ===
Contributors: stuporglue, luminfire 
Donate link: https://LuminFire.com/contact-us/make-a-payment/
Tags: GIS, geo, spatial, mysql, mariadb, geography, mapping, meta, metadata
Requires at least: 4.4.0
Tested up to: 4.8
Stable tag: 0.3.4
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Store and search spatial metadata like any other metadata, but while taking advantage of MySQL spatial types and indexes.

== Description == 

A spatial foundation for WordPress. Store and search spatial metadata like you do any other metadata, but using MySQL spatial indexes.

WP GeoMeta lets you take advantage MySQL's spatial data types and spatial indexes when storing and searching spatial metadata. 

It detects when [GeoJSON](http://geojson.org/) metadata is being stored, and transparently stores a copy in a spatial meta table. 

WP GeoMeta also adds support for spatial search operators. When a spatial search operator is used, WP GeoMeta will make sure that the spatial table is used, taking advantage of indexes and spatial relations.

WP GeoMeta isn't just a plugin, it's also a library which other plugins can take advantage of. It's meant to be a spatial platform that other GIS and mapping plugins can build on to allow spatial interoperability between plugins.

== Installation ==

Install this plugin in the usual WordPress way, then go to your WordPress dashboard to Tools::WP GeoMeta to see the status of your spatial data and to use the included tools.

1. Upload the plugin files to the `/wp-content/plugins/wp-geometa` directory, or install the plugin through the WordPress plugin screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to Tools::WP Geometa

== Frequently Asked Questions ==

No one has actually asked any questions yet!

= Where can I get help with GIS and WordPress? = 

For community support try [WherePress.com](http://WherePress.com/), or [The Spatial Community](https://julien24.typeform.com/to/kGPqYr).

For commercial support you can contact the plugin developer at [LuminFire.com](https://LuminFire.com/contact-us/project-request-form/)

For fast and short questions you can [contact me](https://twitter.com/stuporglue) on twitter

== Screenshots ==

1. WP GeoMeta showing a sample of spatial metadata
2. A list of all spatial functions supported by your version of MySQL
3. WP GeoMeta system status page
4. Built-in regression tests
5. GeoJSON Import Functionality

== Changelog ==

= 0.3.4 = 
 * Fixed accessing property of null object on dashboard.
 * Added GeoJSON import utility.
 * Use and detect hashes in dashboard URL so page reload can go back to original tab.

= 0.3.3 = 
 * Updated wp-geometa-lib to 0.3.3
 * Updated Readme
 * Updated documentation and copyrights to reflect company name change from Cimbura.com to LuminFire
 * Tested with WP 4.8

= 0.3.2 = 
 * Not released on WP.org
 * ???

= 0.3.1 =
 * Support for custom MySQL functions (User Defined Functions and stored functions).
 * Built-in support for some functions which may be useful for working with Lat/Lng distances and bearings.
 * Fixed issue where duplicate function names would appear in get_capabilities result set.
 * Fixed OSM tiles to not be hardcoded to http://
 * Added button to rebuild known spatial function list cache

= 0.3.0 =
 * Moved core functionality to a [library (wp-geometa-lib)](https://github.com/BrilliantPlugins/wp-geometa-lib) so it can be used in other plugins.
 * Most of the previous changelog is no longer applicable to this plugin, but to wp-geometa-lib
 * Fixed issue that made plugins using WP-GeoMeta as a library to be activated twice.

= 0.2.2 =
 * Added built-in support for the WordPress Geodata standard
 * Added filter and action to handle arbitrary lat/lng pair metavalues
 * Added documentation for hooks and filters
 * Leaflet is now loaded locally instead of from the CDN

= 0.2.1 =
 * Handle multi-feature GeoJSON correctly in MySQL 5.7 (convert to GEOMETRYCOLLECTION)
 * Use ON DUPLICATE KEY UPDATE to combine added and updated postmeta handlers.

= 0.2.0 =
 * Upgrading no longer truncates and rebuilds the meta tables. 
 * Fix for joins so user meta should work again (umeta_id vs meta_id key name issue).
 * A beautiful dashboard! 
 * Plugin activation hooks so that deactivating/activating without upgrading will recreate database tables
 * Translation ready!
 * Portuguese translation!
 * Code documentation!
 * Changed geometry type so that all geometries are stored as multipoint to work across MySQL versions

= 0.1.1 =
 * orderby should now work
 * Much cleaner joins
 * Minor fix for when upgrades occurs

= 0.1.0 =
 * Will now work as a library or a plugin. 
 * Additional functions for getting data back into GeoJSON format.
 * Working well enough to submit to the plugin repo.
 * Support for single geometry functions in meta_queries.

= 0.0.2 =
 * Improved meta query capabilities. Now support sub queries, and uses standard meta-query syntax
 * Whitelist of known spatial functions in meta_query args. Allowed args set by detecting MySQL capabilities.
 * We now delete the spatial index on activation so that we don't end up with duplicate spatial keys
 * Populate geo tables on activation with any existing geojson values
 * Submitted ticket to dbDelta SPATIAL INDEX support: https://core.trac.wordpress.org/ticket/36948
 * Conform to WP coding standards
 * Explicitly set visibility on properties and methods

= 0.0.1 =
 * Initial Release

== Developers ==

WP-GeoMeta is tested and developed in the open over at [GitHub](https://github.com/BrilliantPlugins/wp-geometa). Stable versions are pushed to  the WordPress plugin repository. 

There is more developer documentation in the GitHub repository and I'd love to get pull requests and bug reports over there (but I'll take them via SVN too if that's what you like better).

== Upgrade Notice ==

= 0.3.2 = 
 * Update to wp-geometa-lib 0.3.2.
 * Updated internal filter callback function names to match the filter they're for.
 * Updated branding to reflect company name change.
 * Updated handling if submodules aren't checked out.

= 0.3.1 = 
* This release brings support for stored spatial functions, a new button to
rebuild the list of known spatial functions and support for https map tiles. 

= 0.3.0 =
* Previous upgrades caused "Index Exists" type MySQL errors in certain
circumstances. This shouldn't occur. If it does, please let us know.

= 0.2.2 =
* WP-GeoMeta now supports the WordPress Geodata standard and separate
latitude/longitude fields.  
