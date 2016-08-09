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
			<table><tr><th>Type</th><th>Meta Key</th><th>Number of Records</th><th>View Sample Data (500 records max)</th></tr>
				<?php 
					$meta_stats = $this->get_geometa_stats();
					foreach ( $meta_stats as $meta_stat ) {
						print '<tr>';
						print '<td>' . $meta_stat['name'] . '</td>';
						print '<td>' . $meta_stat['meta_key'] . '</td>';
						print '<td>' . $meta_stat['quantity'] . '</td>';
						print '<td data-subtype="' . ( isset( $meta_stat['sub_type'] ) ? $meta_stat[ 'sub_type' ] : '' ) . '" data-color="' . $meta_stat['color'] . '" data-type="' . $meta_stat['type']. '" data-meta_key="' . $meta_stat['meta_key'] . '"><button class="wpgmsampledata">View Data</button><div class="colorswatch" style="background-color:' . $meta_stat['color'] . '"</td>';
						print '</tr>';
					}
				?>
			</table>
		</div>
	</div>

	<div class="wpgm-funcs">
		<h3>Available Spatial Functions</h3>
		<p>These functions are available in your version of MySQL.</p>
		<p>For a list of all spatial functions MySQL and MariaDB support, along with which database versions support which functions, please visit the <a href="https://mariadb.com/kb/en/mariadb/mysqlmariadb-spatial-support-matrix/" target="_blank">MySQL/MariaDB Spatial Support Matrix</a> page.</p>
		<table class="funclist"><tr><th>Function Group</th><th>Functions</th></tr>
		<?php
			$our_funcs = $this->get_functions_by_type();

			foreach ( $our_funcs as $functype => $funcinfo ) {
				print '<tr><td><h4>' . $funcinfo['label'] . '</h4><p>' . $funcinfo['desc'] . '</p></td><td><div class="funcnamelist">' . implode( '<span class="wordclear"> </span>', $funcinfo['funcs'] )  . '</div></td></tr>';
			}
		?>
		</table>
	</div>

	<div><h3>List of installs</h3>

		<p>
			WP-GeoMeta can be installed as a plugin (like this one) or used as a library by 
other plugins. If you have any plugins installed that use WP-GeoMeta as a library, they will
be listed below along with which version of WP-GeoMeta they were bundled with. 
		</p>
		<p>
WP-GeoMeta always uses the most up to date version installed, even if a plugin bundles an older version.
		</p>
		
		<table class="wpgminstalllist">
		<tr><th>Plugin Name</th><th>WP-GeoMeta Version</th></tr>
		<?php
			$installs = $this->get_list_of_installs();
			foreach ( $installs as $install ) {
				print '<tr><td>' . $install[ 'plugin_name' ] . '</td><td>' . $install[ 'wpgm_version' ] . '</td><tr>';
			}
		?>
		</table>
	</div>

	<div><h3>List of resources</h3>

	</div>

	<h3 class="dragons"><span id="danger-spinner" class="spinner"></span>The Danger Zone</h3>
	<div class="dragons">

		<p>
<strong>Important</strong>: The following section has funtionality that may destroy your spatial data. Your original metadata is never touched, but deleting data may impact plugins or custom code that expect it to be present.
		</p>

		<table>

		<tr><td><button data-action="run-tests" class="wpgm-danger-action">Run Tests</button></td><td>Run the built-in regression tests</td></tr>

		<tr><td><button data-action="remove-tables" class="wpgm-danger-action">Remove WP GeoMeta Tables</button></td><td>
		All WP GeoMeta data is stored in its own tables. Your original data is
		untouched. Removing WP GeoMeta tables will break any spatial queries you
		may be using.</td></tr> 

		<tr><td><button data-action="create-tables" class="wpgm-danger-action">Create WP GeoMeta Tables</button></td><td>
		WP GeoMeta tables are created on plugin activation or upgrade, but
		you can manually create them here. WP GeoMeta uses dbDelta, so running
		this multiple times will have no bad effects.</td></tr>

		<tr><td><button data-action="truncate-tables" class="wpgm-danger-action">Truncate WP GeoMeta Tables</button></td><td>
		Clears existing spatial data, but doesn't remove the tables.</td></tr>
		
		<tr><td><button data-action="populate-tables" class="wpgm-danger-action">Populate WP GeoMeta Tables</button></td><td>
		Detect any spatial data (GeoJSON) in the non-spatial meta tables which is not stored
		in WP-GeoMeta and load it. This may take a while!</td></tr>

		</table>

		<div id="wpgm-danger-results"></div>

	</div>
</div>
