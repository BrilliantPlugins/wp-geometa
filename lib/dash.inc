<?php

defined('ABSPATH') or die('No direct access');

?>
<div class="wp-geometa-dash">
	<div class="wpgm-header header noborder">
		<img src="<?php print plugin_dir_url( __FILE__ ); ?>/../../assets/icon.png" title="WP GeoMeta Logo"/><h2>WP GeoMeta</h2>
	</div>

	<div class="wpgm-status noborder">
		<div class="status-table">
			<?php
				print $this->make_update_block();
				print $this->make_db_version_block();
				print $this->make_table_list_block();
				print $this->make_indexes_block();
			?>
		</div>
	</div>

	<div class="wpgm-data"> 
		<h3>Your Spatial Data</h3>
		<div id="wpgmmap">
		</div>
		<div class="posttypegeotable">
			<h4>Found Spatial Metadata Types</h4>
			<table><tr><th>Type</th><th>Meta Key</th><th>Quantity</th><th>View Sample Data (500 records max)</th></tr>
				<?php 
					$meta_stats = $this->get_geometa_stats();
					foreach ( $meta_stats as $meta_stat ) {
						print '<tr>';
						print '<td>' . $meta_stat['name'] . '</td>';
						print '<td>' . $meta_stat['meta_key'] . '</td>';
						print '<td>' . $meta_stat['quantity'] . '</td>';
						print '<td data-color="' . $meta_stat['color'] . '" data-type="' . $meta_stat['type']. '" data-meta_key="' . $meta_stat['meta_key'] . '"><button class="wpgmsampledata">View Data</button><div class="colorswatch" style="background-color:' . $meta_stat['color'] . '"</td>';
						print '</tr>';
					}
				?>
			</table>
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


	<div><h3>Here Be Dragons</h3>
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
