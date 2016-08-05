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

	.wp-geometa-dash .wpgm-header img{
		display: inline-block; 
		vertical-align: middle; 
		padding-right: 20px;
	}

	.wp-geometa-dash .wpgm-header h2 {
		font-size: 75px; 
		font-family: Garamond, Baskerville, “Baskerville Old Face”, “Hoefler Text”, “Times New Roman”, serif;
		font-family: Rockwell, “Courier Bold”, Courier, Georgia, Times, “Times New Roman”, serif;
		display: inline-block; 
		vertical-align: middle; 
		margin-top: 0;
		margin-bottom: 0;
	}

	.wp-geometa-dash > div {
		border: 1px solid #ccc;
		border-radius: 2px;
		margin-bottom: 20px;
		margin-right: 20px;
		padding: 10px;
		color: #555;
	}

	.wp-geometa-dash > div h3 {
		margin-top: 0;
		color: #333;
	}

/**
 * System status shapes
 */

	.status-circle {
		height: 50px;
		width: 50px;
		display: inline-block;
		border-radius: 50px;
		color: #000000;
		border-width: 8px;
		border-style: solid;
	}

	.status-circle:before {
		display: block;
		position: absolute;
		font-size: 50px;
		line-height: 38px; 
		width: 50px;
	}

	.status-circle.good {
		border-color: #499b44; /* grey green */
		background-color: #29df20; /* bright green */
	}

	.status-circle.good:before {
		content: '✔';
	}

	.status-circle.fair {
		border-color: #98752c; /* grey orange */
		background-color: #f6b01b; /* bright orange */
	}

	.status-circle.fair:before {
		content: '❋';
	}

	.status-circle.poor {
		border-color: #9b4544; /* grey red */
		background-color: #fe2624; /* bright red*/
	}

	.status-circle.poor:before {
		content: '✗';
	}

	.status-title {
		text-align: center;
		font-weight: bold;
		font-size: 18px;
	}

	.status-table {
		display: table;
		width: 100%;
	}

	.status-table .status-block {
		display: table-cell;
		text-align: center;
	}

/**
 * Function list table
 */
.funclist {
    border: 1px solid #000;
    border-collapse: collapse;
	max-width: 100%;
}
.funclist th {
    text-align: left;
}
.funclist tr, .funclist td, .funclist th {
    border: 1px solid #000;
    border-collapse: collapse;
    padding: 5px;
	vertical-align: top;
}

.funcnamelist {
	-moz-column-count: 9;
	-webkit-column-count:9;
	column-count:9;

	-moz-column-gap: 10px;
	-webkit-column-gap:10px;
	column-gap:10px;

	-moz-column-rule: 1px solid #ccc;
	-webkit-column-rule:1px solid #ccc;
	column-rule:1px solid #ccc;

	-moz-column-width: 200px;
	-webkit-column-width:200px;
	column-width:200px;
}

.wordclear {
display: block;
clear: both;
}



</style>
<div class="wp-geometa-dash">
	<div class="wpgm-header">
		<img src="<?php print plugin_dir_url( __FILE__ ); ?>/../../assets/icon.png" title="WP GeoMeta Logo"/><h2>WP GeoMeta</h2>
	</div>

	<div class="wpgm-status">
		<div class="status-table">

			<?php
				print $this->make_update_block();
				print $this->make_db_version_block();
				print $this->make_table_list_block();
			?>

			<div class="status-block">
				<div class="status-circle poor"></div>
				<div class="status-title">Tables Exist</div>
				<div class="status-text"></div>
			</div>

			<div class="status-block">
				<div class="status-circle good"></div>
				<div class="status-title">Indexes Enabled</div>
				<div class="status-text"></div>
			</div>
		</div>
	</div>

	<div class="wpgm-funcs">
		<h3>Available Spatial Functions</h3>
		<p>These functions are available in your version of MySQL.</p>
		<p>For a list of all spatial functions MySQL and MariaDB support, along with which database versions support which functions, please visit the <a href="https://mariadb.com/kb/en/mariadb/mysqlmariadb-spatial-support-matrix/" target="_blank">Spatial Support Matrix</a> page.</p>
		<table class="funclist"><tr><th>Function Group</th><th>Functions</th></tr>
		<?php
			$our_funcs = $this->get_functions_by_type();

			foreach ( $our_funcs as $functype => $funcinfo ) {
				print '<tr><td><h4>' . $funcinfo['label'] . '</h4><p>' . $funcinfo['desc'] . '</p></td><td><div class="funcnamelist">' . implode( '<span class="wordclear"> </span>', $funcinfo['funcs'] )  . '</div></td></tr>';
			}
		?>
		</table>
	</div>


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
		All WP GeoMeta data is stored in its own tables. Your original data is
		untouched. Removing WP GeoMeta tables will break any spatial queries you
		may be using.  <br> <button>Remove WP GeoMeta Tables</button> </p>

		<p>WP GeoMeta tables are created on plugin activation or upgrade, but
		you can manually create them here. WP GeoMeta uses dbDelta, so running
		this multiple times will have no ill effects.  <br> <button>Create WP
			GeoMeta Tables</button> </p> 

		<p>If you want to clear out the
		existing spatial data, but not remove the tables, this is your button.
		<br> <button>Truncate WP GeoMeta Tables</button> </p> 

		<p> WP GeoMeta
		detects and loads GeoJSON metadata being stored in any of the existing
		metadata tables. Click this button to make WP GeoMeta search the
		(non-geo) metadata tables for any GeoJSON records that weren't there
		before, which were missed for some reason or if you have truncated the
		table.  <br> <button>Populate WP GeoMeta Tables</button> </p> 

	</div>

</div>
