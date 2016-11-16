Changes
-------

### 0.2.2
 * Added built-in support for the [WordPress Geodata standard](https://codex.wordpress.org/Geodata)
 * Added filter and action to handle arbitrary lat/lng pair metavalues

### 0.2.1
 * Handle multi-feature GeoJSON correctly in MySQL 5.7 (convert to GEOMETRYCOLLECTION)
 * Use ON DUPLICATE KEY UPDATE to combine added and updated postmeta handlers.

### 0.2.0: Penny Priddy
 * Upgrading no longer truncates and rebuilds the meta tables. 
 * Fix for joins so user meta should work again (umeta_id vs meta_id key name issue).
 * A beautiful dashboard! 
 * Plugin activation hooks so that deactivating/activating without upgrading will recreate database tables
 * Translation ready!
 * Portuguese translation!
 * Code documentation!
 * Changed geometry type so that all geometries are stored as multipoint to work across MySQL versions

### 0.1.1
 * Only x.x.0 releases will get code names
 * orderby should now work
 * Much cleaner joins
 * Minor fix for when upgrades occurs

### 0.1.0: Perfect Tommy
 * Will now work as a library or a plugin. 
 * Additional functions for getting data back into GeoJSON format.
 * Working well enough to submit to the plugin repo.
 * Support for single geometry functions in meta_queries.

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


