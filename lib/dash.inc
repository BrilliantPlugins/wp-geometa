<?php
global $wpdb;
?>
<style type="text/css">
.wp-geometa-dash {
	margin-top: 10px;
	background-color: white;
	margin-right: 10px;
	padding: 10px;
}

.wp-geometa-dash > div {
	width: 45%;
	max-width: 500px;
	min-width: 250px;
	display: inline-block;
	border: 1px solid #ccc;
	border-radius: 2px;
	margin-bottom: 20px;
	margin-right: 20px;
	padding: 10px;
	color: #555;
	vertical-align: top;
	max-height: 300px; 
	overflow: auto;
}

.wp-geometa-dash > div h3 {
	margin-top: 0;
	color: #333;
}
</style>
<div class="wp-geometa-dash">
<h2>WP GeoMeta</h2>
<div><h3>About</h3>
<p>
You are running WP GeoMeta version <strong><?php echo WP_GEOMETA_VERSION; ?></strong>.
</p>
<p>
WP GeoMeta is a spatial foundation for WordPress. It lets you Store and search spatial metadata like you do any other metadata, but using MySQL spatial indexes.
</p>
<p>
Since it uses MySQL's native spatial capabilities, which features are available to you depend on your database. 
</p>
</div>

<div>
<h3>Supported Functions</h3>
<p>To see a table showing which spatial functions are supported in each version of MySQL or MariaDB please visit the <a href="https://mariadb.com/kb/en/mariadb/mysqlmariadb-spatial-support-matrix/" target="_blank">Spatial Support Matrix</a> page.</p>
<p>Your install of MySQL supports the following spatial functions:</p>
<ul>
<?php
	$capabilities = WP_GeoUtil::get_capabilities( false, false );
	foreach($capabilities as $capability){
		print '<li>' . $capability . '</li>';
	}
?>
</ul>
</div>

<div><h3>Spatial System Info</h3>
<p>
<?php

	$version_info = $wpdb->get_var( 'SELECT VERSION()' ); // @codingStandardsIgnoreLine
	print 'Your database is <strong>' . $version_info . '</strong>.';
?>
</p>

<h4>Spatial Meta Tables</h4>
<table>
<tr><th>Table Name</th><th>Spatial Indexes Enabled</th><th>Number of Records</th></tr>
<?php
	$geometa = WP_GeoMeta::get_instance();
	foreach( $geometa->meta_types as $meta_type ) {
		$geotable = _get_meta_table( $meta_type ) . '_geo';
		if ( $geotable !== $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', array( $geotable ) ) ) ) {
			continue;
		}
		$create = $wpdb->get_var( 'SHOW CREATE TABLE `' . $geotable . '`', 1 );
		$has_spatial_index = ( false !== strpos( $create, 'SPATIAL KEY `meta_val_spatial_idx` (`meta_value`)' ) ? 'TRUE' : 'FALSE' ); 

		$num_records = $wpdb->get_var( 'SELECT COUNT(*) FROM `' . $geotable . '`' );

		print '<tr><td>' . $geotable . '</td><td>' . $has_spatial_index . '</td><td>' . $num_records . '</td></tr>';
	}
?>
</table>
</div>
<div><h3>Tools</h3>
<p>
All WP GeoMeta data is stored in its own tables. Your original data is untouched. Removing WP GeoMeta tables will break any spatial queries you may be using.
<br>
	<button>Remove WP GeoMeta Tables</button>
</p>

<p>WP GeoMeta tables are created on plugin activation or upgrade, but you can manually create them here. WP GeoMeta uses dbDelta, so running this multiple times will have no ill effects.
<br>
	<button>Create WP GeoMeta Tables</button>
</p>
<p>If you want to clear out the existing spatial data, but not remove the tables, this is your button.
<br>
	<button>Truncate WP GeoMeta Tables</button>
</p>
<p>
WP GeoMeta detects and loads GeoJSON metadata being stored in any of the existing metadata tables. Click this button to make WP GeoMeta search the (non-geo) metadata tables for any GeoJSON records that weren't there before, which were missed for some reason or if you have truncated the table.
<br>
	<button>Populate WP GeoMeta Tables</button>
</p>
</div>


<div>* Link to WherePress.com</div>
<div>* List of post types with spatial data?</div>
<div>* Truncate / rebuild database (with progress bar?)</div>
</div>
