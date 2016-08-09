<?php
defined( 'ABSPATH' ) or die( 'No direct access' );

class WP_GeoMeta_Dash {

	/**
	 * Singleton variable
	 *
	 * @var $_instance
	 */
	private static $_instance = null;

	/**
	 * Get the singleton instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
	 * Get the functions by type
	 */
	public function get_functions_by_type() {

		$cap_cats = array(
			'geom_relationship' => array(
				'label' => 'Spatial Relationship Tests',
				'desc' => 'Test topological relationships between two geometries. Some functions may work on a shape\'s bounding box rather than the shape itself.',
				'funcs' => array(
					'Contains',
					'Crosses',
					'Disjoint',
					'Distance',
					'Equals',
					'Intersects',
					'MBRContains',
					'MBRCoveredBy',
					'MBRDisjoint',
					'MBREqual',
					'MBREquals',
					'MBRIntersects',
					'MBROverlaps',
					'MBRTouches',
					'MBRWithin',
					'Overlaps',
					'ST_Contains',
					'ST_Crosses',
					'ST_Dimension',
					'ST_Disjoint',
					'ST_Distance',
					'ST_Equals',
					'ST_Intersects',
					'ST_Overlaps',
					'ST_Touches',
					'ST_Within',
					'Touches',
					'Within',
				),
			),

			'properties' => array(
				'label' => 'Geometry Properties',
				'desc' => 'These functions analyize spatial properties of a single geometry.',
				'funcs' => array(
					'Area',
					'GLength',
					'IsClosed',
					'IsEmpty',
					'IsRing',
					'IsSimple',
					'SRID',
					'ST_Area',
					'ST_GeometryType',
					'ST_IsClosed',
					'ST_IsEmpty',
					'ST_IsRing',
					'ST_IsSimple',
					'ST_IsValid',
					'ST_Length',
					'ST_SRID',
					'ST_Validate',
				),
			),

			'investigation' => array(
				'label' => 'Geometry Disection',
				'desc' => 'Investigate the type and sub-parts of a geometry.',
				'funcs' => array(
					'Dimension',
					'EndPoint',
					'ExteriorRing',
					'GeometryN',
					'GeometryType',
					'InteriorRingN',
					'NumGeometries',
					'NumInteriorRings',
					'NumPoints',
					'PointN',
					'ST_EndPoint',
					'ST_ExteriorRing',
					'ST_GeometryN',
					'ST_InteriorRingN',
					'ST_NumGeometries',
					'ST_NumInteriorRings',
					'ST_NumPoints',
					'ST_PointN',
					'ST_StartPoint',
					'ST_X',
					'ST_Y',
					'StartPoint',
					'X',
					'Y',
				),
			),

			'make_new_geom' => array(
				'label' => 'Generate new Geometry',
				'desc' => 'Create a new geometry based on existing geometries and spatial operations.',
				'funcs' => array(
					'Boundary',
					'Buffer',
					'Centroid',
					'ConvexHull',
					'Envelope',
					'ST_Boundary',
					'ST_Buffer',
					'ST_Centroid',
					'ST_ConvexHull',
					'ST_Difference',
					'ST_Envelope',
					'ST_Intersection',
					'ST_Simplify',
					'ST_SymDifference',
					'ST_Union',
				),
			),

			'change_format' => array(
				'label' => 'Data Format Helpers',
				'desc' => 'Create or convert geometries from various input and output formats.',
				'funcs' => array(
					'GeomCollFromText',
					'GeomCollFromWKB',
					'GeometryCollection',
					'GeometryCollectionFromText',
					'GeometryCollectionFromWKB',
					'GeometryFromText',
					'GeometryFromWKB',
					'GeomFromText',
					'GeomFromWKB',
					'LineFromText',
					'LineFromWKB',
					'LineString',
					'LineStringFromText',
					'LineStringFromWKB',
					'MLineFromText',
					'MLineFromWKB',
					'MPointFromText',
					'MPointFromWKB',
					'MPolyFromText',
					'MPolyFromWKB',
					'MultiLineString',
					'MultiLineStringFromText',
					'MultiLineStringFromWKB',
					'MultiPoint',
					'MultiPointFromText',
					'MultiPointFromWKB',
					'MultiPolygon',
					'MultiPolygonFromText',
					'MultiPolygonFromWKB',
					'Point',
					'PointFromText',
					'PointFromWKB',
					'PolyFromText',
					'PolyFromWKB',
					'Polygon',
					'PolygonFromText',
					'PolygonFromWKB',
					'ST_GeomCollFromText',
					'ST_GeomCollFromWKB',
					'ST_GeometryCollectionFromText',
					'ST_GeometryCollectionFromWKB',
					'ST_GeometryFromText',
					'ST_GeometryFromWKB',
					'ST_GeomFromGeoJSON',
					'ST_GeomFromText',
					'ST_GeomFromWKB',
					'ST_LineFromText',
					'ST_LineFromWKB',
					'ST_LineStringFromText',
					'ST_LineStringFromWKB',
					'ST_PointFromGeoHash',
					'ST_PointFromText',
					'ST_PointFromWKB',
					'ST_PolyFromText',
					'ST_PolyFromWKB',
					'ST_PolygonFromText',
					'ST_PolygonFromWKB',
					'AsBinary',
					'AsText',
					'AsWKB',
					'AsWKT',
					'ST_AsBinary',
					'ST_AsGeoJSON',
					'ST_AsText',
					'ST_AsWKB',
					'ST_AsWKT',
				),
			),
			'other' => array(
				'label' => 'Miscellaneous Functions',
				'desc' => 'Other little-used functions.',
				'funcs' => array(
					'PointOnSurface',
					'ST_Buffer_Strategy',
					'ST_Distance_Sphere',
					'ST_GeoHash',
					'ST_LatFromGeoHash',
					'ST_LongFromGeoHash',
					'ST_PointOnSurface',
					'ST_Relate',
				),
			),
		);

		$our_caps = WP_GeoUtil::get_capabilities();

		$our_cap_cats = array();

		foreach ( $cap_cats as $category => $funcinfo ) {

			sort( $funcinfo['funcs'] );
			foreach ( $funcinfo['funcs'] as $func ) {
				if ( in_array( strtolower( $func ), $our_caps ) ) {
					$our_cap_cats[ $category ]['funcs'][] = $func;
				}
			}

			if ( ! empty( $our_cap_cats[ $category ] ) ) {
				$our_cap_cats[ $category ]['label'] = $funcinfo['label'];
				$our_cap_cats[ $category ]['desc'] = $funcinfo['desc'];
			}
		}

		return $our_cap_cats;
	}

	/**
	 * Set up our filters
	 */
	protected function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'wp_ajax_delete_tables', array( $this, 'ajax_delete_tables' ) );
		add_action( 'wp_ajax_create_tables', array( $this, 'ajax_create_tables' ) );
		add_action( 'wp_ajax_truncate_tables', array( $this, 'ajax_truncate_tables' ) );
		add_action( 'wp_ajax_populate_tables', array( $this, 'ajax_populate_tables' ) );
		add_action( 'wp_ajax_wpgm_get_sample_data', array( $this, 'ajax_wpgm_get_sample_data' ) );
		add_action( 'wp_ajax_wpgm_dangerzone', array( $this, 'ajax_wpgm_dangerzone' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Enqueue our scripts and css for the dashboard
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( 'tools_page_wp-geometa' !== $hook ) {
			return;
		}

		wp_enqueue_script( 'leafletjs', 'https://npmcdn.com/leaflet@1.0.0-rc.3/dist/leaflet.js', array(), null );
		wp_enqueue_style( 'leafletcss', 'https://npmcdn.com/leaflet@1.0.0-rc.3/dist/leaflet.css', array(), null );
		wp_enqueue_style( 'wpgeometadash', plugin_dir_url( __FILE__ ) . '/../../assets/wpgeometa.css', array( 'leafletcss' ) );
		wp_enqueue_script( 'wpgeometadashjs', plugin_dir_url( __FILE__ ) . '/../../assets/wpgeometa.js', array( 'leafletjs' ) );
	}

	/**
	 * Add the dashboard menu listing.
	 */
	public function admin_menu() {
		add_management_page( esc_html__( 'WP GeoMeta', 'wp-geometa' ), esc_html__( 'WP GeoMeta','wp-geometa' ), 'install_plugins', 'wp-geometa', array( $this, 'show_dashboard' ) );
	}

	public function show_dashboard() {
		print '<div class="wp-geometa-dash">';
		$this->section_header();
		$this->section_data();
		$this->section_quickstart();
		$this->section_functions();
		$this->section_installs();
		$this->section_resources();
		$this->section_dragons();
		print '</div>';
	}

	public function set_list_of_geotables() {
		global $wpdb;

		if ( isset( $this->tables_found ) ) {
			return $this->tables_found;
		}

		$geometa = WP_GeoMeta::get_instance();
		$this->tables_found = array();
		foreach ( $geometa->meta_types as $meta_type ) {
			$geotable = _get_meta_table( $meta_type ) . '_geo';
			if ( $geotable === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', array( $geotable ) ) ) ) {
				$this->tables_found[] = $geotable;
				$this->table_types_found[] = $meta_type;
			}
		}
	}

	public function block_table_list() {

		$this->set_list_of_geotables();
		$geometa = WP_GeoMeta::get_instance();

		if ( count( $this->tables_found ) === count( $geometa->meta_types ) ) {
			return $this->make_status_block( 'good', 'Geo Tables Exist', 'All ' . count( $this->tables_found ) . ' geometa tables exist. (' . implode( ', ', $this->tables_found ) . ')' );
		} else if ( count( $this->tables_found ) > 0 ) {
			return $this->make_status_block( 'fair', 'Some Geo Tables Exist', 'Some geo tables are missing. If this wasn\'t intentional, there could be a problem. ' . implode( ', ', $this->tables_found ) . ' all exist. ' . implode( ', ', array_diff( $geometa->meta_types, $this->tables_found ) ) . ' don\'t exist' );
		} else {
			return $this->make_status_block( 'poor', 'No Geo Tables Exist', 'No geo tables exist. You can try recreating them with the tools at the bottom of this page.' );
		}
	}

	public function block_indexes() {
		global $wpdb;
		$tables_found = array();
		;
		$this->set_list_of_geotables();
		$no_index = array();

		foreach ( $this->tables_found as $geotable ) {
			$create = $wpdb->get_var( 'SHOW CREATE TABLE `' . $geotable . '`', 1 );
			$has_spatial_index = strpos( $create, 'SPATIAL KEY `meta_val_spatial_idx` (`meta_value`)' );

			if ( false !== $has_spatial_index ) {
				$tables_found[] = $geotable;
			} else {
				$no_index[] = $geotable;
			}
		}

		if ( count( $tables_found ) === count( $this->tables_found ) ) {
			return $this->make_status_block( 'good', 'All existing geo tables indexed', 'All ' . count( $tables_found ) . ' geometa tables have spatial indexes' );
		} else if ( count( $tables_found ) > 0 ) {
			return $this->make_status_block( 'fair', 'Some existing geo tables not indexed', 'Some geo tables are not indexed. The following tables could have performance issues: ' . implode( ', ', $no_index ) );
		} else {
			return $this->make_status_block( 'poor', 'No spatial indexes', 'No spatial indexes found. Spatial queries will be slow.' );
		}
	}

	public function block_db_versions() {
		global $wpdb;

		$our_caps = WP_GeoUtil::get_capabilities();
		$version_info = $wpdb->get_var( 'SELECT VERSION()' ); // @codingStandardsIgnoreLine

		if ( in_array( 'st_intersects', $our_caps ) ) {
			return $this->make_status_block( 'good', 'Good Database!', 'Your database version (' . $version_info . ') supports a wide variety of useful spatial functions.' );
		} else if ( in_array( 'geometrycollection', $our_caps ) ) {
			return $this->make_status_block( 'fair', 'OK Database', 'Your database version (' . $version_info . ') has some spatial support, but doesn\'t support key spatial functions. Consider upgrading to MySQL 5.6.1 or higher, or MariaDB 5.3.3 or higher.' );
		} else {
			return $this->make_status_block( 'poor', 'Bad Database', 'Your database version (' . $version_info . ') doesn\'t appear to have spatial support. You won\'t be able to store or use spatial data.' );
		}
	}

	public function block_updates() {

		$all_plugins = get_plugin_updates();

		$this_plugin = basename( dirname( dirname( __FILE__ ) ) ) . '/wp-geometa.php';

		/*
         * Three statuses.
		 * Bad. There are updates and WP_GEOMETA_DASH_VERSION and WP_GEOMETA_VERSION are the same and both are out of date
		 * OK. There are updates, and WP_GEOMETA_DASH_VERSION is out of date, but WP_GEOMETA_VERSION is up to date (some other plugin has an updated version)
		 * Good. There are no updates: WP_GEOMETA_DASH_VERSION is up to date and WP_GEOMETA_VERSION is up to date
		 */

		if ( empty( $all_plugins[ $this_plugin ] ) ) {
			return $this->make_status_block( 'good', 'Up to date!', 'You are running the most recent version of WP-GeoMeta (' . WP_GEOMETA_VERSION . ')' );
		} else if ( 0 === version_compare( WP_GEOMETA_VERSION, $all_plugins[ $this_plugin ]->Version ) && -1 === version_compare( WP_GEOMETA_DASH_VERSION, $all_plugins[ $this_plugin ]->Version ) ) {
			return $this->make_status_block( 'fair', 'Out of date.', 'A plugin you are using is providing the most recent version of the WP-GeoMeta library (' . WP_GEOMETA_VERSION . '), but your plugin is out of date.' );
		} else {
			return $this->make_status_block( 'poor', 'Out of date!', 'You are running an outdated version of WP-GeoMeta (' . WP_GEOMETA_VERSION . '). Please upgrade!' );
		}
	}

	public function block_data_loaded() {
		return $this->make_status_block( 'fair', 'not yet implemented','asdfasdfasdfasdf' );
	}

	public function make_status_block( $status, $title, $description ) {

		$block = '<div class="status-block"><div class="status-circle ' . $status . '"></div><div class="status-title">' . $title . '</div><div class="status-text">' . $description . '</div></div>';
		return $block;
	}

	function get_geometa_stats() {
		global $wpdb;

		$found_data = array();
		$this->set_list_of_geotables();
		$found_tables = $this->table_types_found;

		if ( in_array( 'post', $found_tables ) ) {
			// Posts
			$q = 'SELECT 
				p.post_type,
				geo.meta_key,
				COUNT(p.ID) AS quantity 
				FROM
				' . $wpdb->postmeta . '_geo geo,
				' . $wpdb->posts . ' p
				WHERE
				1=1
				AND geo.post_id=p.ID
				GROUP BY 
				p.post_type,
				geo.meta_key
				ORDER BY p.post_type, geo.meta_key';

			foreach ( $wpdb->get_results( $q, ARRAY_A ) as $geometa ) {

				$post_type_object = get_post_type_object( $geometa['post_type'] );

				$found_data[] = array(
					'name' => $post_type_object->labels->name . ' (post)',
					'type' => 'post',
					'meta_key' => $geometa['meta_key'],
					'quantity' => $geometa['quantity'],
					'sub_type' => $geometa['post_type'],
							);
			}
		}

		if ( in_array( 'user', $found_tables ) ) {
			// Users
			$q = 'SELECT 
				meta_key, 
				COUNT(umeta_id) AS quantity
				FROM 
				' . $wpdb->usermeta . '_geo geo
				GROUP BY 
				meta_key';

			foreach ( $wpdb->get_results( $q, ARRAY_A ) as $usermeta ) {
				$found_data[] = array(
					'name' => 'Users',
					'type' => 'user',
					'meta_key' => $usermeta['meta_key'],
					'quantity' => $usermeta['quantity'],
							);
			}
		}

		if ( in_array( 'term', $found_tables ) ) {
			// Term Meta
			$q = 'SELECT 
				t.name,
				geo.meta_key,
				COUNT(t.term_id) AS quantity
				FROM
				' . $wpdb->termmeta . '_geo geo,
				' . $wpdb->terms . ' t
				WHERE
				1=1
				AND geo.term_id=t.term_id
				GROUP BY 
				t.name,
				geo.meta_key
				ORDER BY t.name, geo.meta_key';

			foreach ( $wpdb->get_results( $q, ARRAY_A ) as $termmeta ) {

				$found_data[] = array(
					'name' => $termmeta['name'] . ' (term)',
					'type' => 'user',
					'meta_key' => $termmeta['meta_key'],
					'quantity' => $termmeta['quantity'],
							);
			}
		}

		if ( in_array( 'comment', $found_tables ) ) {
			// Comment Meta
			$q = 'SELECT 
				meta_key, 
				COUNT(meta_id) AS quantity
				FROM 
				' . $wpdb->commentmeta . '_geo geo
				GROUP BY 
				meta_key';

			foreach ( $wpdb->get_results( $q, ARRAY_A ) as $commentmeta ) {
				$found_data[] = array(
					'name' => 'Comments',
					'type' => 'comment',
					'meta_key' => $commentmeta['meta_key'],
					'quantity' => $commentmeta['quantity'],
							);
			}
		}

		foreach ( $found_data as &$data ) {
			$data['color'] = sprintf( '#%06X', mt_rand( 0, 0xFFFFFF ) );
		}

		return $found_data;
	}

	public function ajax_wpgm_get_sample_data() {
		global $wpdb;

		$wpgm = WP_GeoMeta::get_instance();

		if ( ! in_array( $_GET['type'], $wpgm->meta_types ) ) {
			die();
		}

		$type = $_GET['type'];
		$types = $type . 's';
		$metatype = $type . 'meta';
		$id_column = 'user' === $type ? 'umeta_id' : 'meta_id';

		switch ( $type ) {
			case 'post':
				$table_id = 'ID';
				break;
			case 'comment':
				$table_id = 'comment_ID';
				break;
			case 'term':
				$table_id = 'term_id';
				break;
			case 'user':
				$table_id = 'ID';
				break;
		}

		$q = 'SELECT 
			t.' . $table_id . ' AS the_id,
			m.meta_value 
			FROM 	
			' . $wpdb->$metatype . ' m,
			' . $wpdb->$metatype . '_geo geo,
			' . $wpdb->$types . ' t
			WHERE
			m.meta_key=%s
			AND geo.fk_meta_id=m.' . $id_column . '
			AND t.' . $table_id . '=geo.' . $type . '_id ';
		if ( ! empty( $_GET['subtype'] ) ) {
			$q .= ' AND t.post_type = %s ';
		}
		$q .= ' ORDER BY RAND()
			LIMIT 500';

		$sql = $wpdb->prepare( $q, array( $_GET['meta_key'], $_GET['subtype'], 'asdf' ) );

		$res = $wpdb->get_results( $sql, ARRAY_A );

		if ( empty( $res ) ) {
			wp_send_json( array() );
		}

		$geojson = array();

		if ( ! empty( $_GET['subtype'] ) ) {
			$post_type_object = get_post_type_object( $_GET['subtype'] );
			$type = $post_type_object->labels->name . ' (post)';
		} else {
			$type = ucfirst( $type );
		}

		foreach ( $res as $record ) {
			$featureCollection = WP_GeoUtil::merge_geojson( $record['meta_value'] );
			$featureCollection = json_decode( $featureCollection, true );
			foreach ( $featureCollection['features'] as &$feature ) {
				$feature['title'] = $type . ' id ' . $record['the_id'];
			}
			$geojson[] = $featureCollection;
		}

		$geojson = WP_GeoUtil::merge_geojson( $geojson );

		$geojson = json_decode( $geojson );

		if ( empty( $geojson ) ) {
			wp_send_json( array() );
		}

		wp_send_json( $geojson );
	}

	function get_list_of_installs() {
		// TODO: Support WPMU_PLUGIN_DIR too.
		$all_plugins = get_plugins();

		/*
		 * Loop through plugins, find the one our wp-geometa is in and get its metadata.
		 */
		$installs_info = array();
		$installs = WP_GeoMeta_Installs::get_list();
		foreach ( $installs as $file => $version ) {
			$relative_path = str_replace( WP_PLUGIN_DIR, '', $file );
			$plugin_dir = explode( DIRECTORY_SEPARATOR, trim( $relative_path, DIRECTORY_SEPARATOR ) );

			$plugin_base_dir = $plugin_dir[0];

			foreach ( $all_plugins as $plugin_name => $plugin_details ) {
				if ( strpos( $plugin_name, $plugin_dir[0] . '/' ) === 0 ) {
					$installs_info[ $plugin_details['Name'] ] = array(
						'plugin_name' => $plugin_details['Name'],
						'wpgm_version' => $version,
						'file' => $file,
					);
				}
			}
		}

		ksort( $installs_info );
		return $installs_info;
	}

	/**
	 * Handle the danger zone actions
	 */
	public function ajax_wpgm_dangerzone() {
		$real_action = $_GET['action_type'];

		switch ( $real_action ) {
			case 'run-tests':
				ob_start();
				require_once( dirname( __FILE__ ) . '/../test/testsuite.php' );
				$res = ob_get_clean();
				$res = trim( $res );
				$lines = explode( "\n", $res );
				array_shift( $lines );
				print implode( "\n", $lines );
				break;
			case 'remove-tables':
				$wpgm = WP_GeoMeta::get_instance();
				$wpgm->uninstall();
				print 'The WP-GeoMeta tables should be gone now.';
				break;
			case 'create-tables':
				$wpgm = WP_GeoMeta::get_instance();
				$wpgm->create_geo_tables();
				print 'The WP-GeoMeta tables should exist now.';
				break;
			case 'truncate-tables':
				$wpgm = WP_GeoMeta::get_instance();
				$wpgm->truncate_tables();
				print 'The WP-GeoMeta tables should be empty now.';
				break;
				break;
			case 'populate-tables':
				$wpgm = WP_GeoMeta::get_instance();
				$wpgm->populate_geo_tables();
				print 'The WP-GeoMeta tables should be populated now.';
				break;
			default:
				print "I don't understand what I'm supposed to do.";
		}

		exit();
	}

	public function section_header() {
		$icon = plugin_dir_url( __FILE__ ) . '/../../assets/icon.png';
		print '<div class="wpgm-header header noborder"><img src="' . $icon . '" title="WP GeoMeta Logo"/><h2>WP GeoMeta</h2></div><div class="wpgm-status noborder"><div class="status-table">';
		print $this->block_updates();
		print $this->block_table_list();
		print $this->block_indexes();
		print $this->block_db_versions();
		print $this->block_data_loaded();
		print '</div></div>';
	}

	public function section_data() {
		print '<div class="wpgm-data"><h3>Your Spatial Data</h3><div id="wpgmmap"></div><div class="posttypegeotable">';
		print '<h4>Found Spatial Metadata Types</h4>';
		print '<table><tr><th>Type</th><th>Meta Key</th><th>Number of Records</th><th>View Sample Data (500 records max)</th></tr>';

		foreach ( $this->get_geometa_stats() as $meta_stat ) {
			print '<tr>';
			print '<td>' . $meta_stat['name'] . '</td>';
			print '<td>' . $meta_stat['meta_key'] . '</td>';
			print '<td>' . $meta_stat['quantity'] . '</td>';
			print '<td data-subtype="' . ( isset( $meta_stat['sub_type'] ) ? $meta_stat['sub_type'] : '' ) . '" data-color="' . $meta_stat['color'] . '" data-type="' . $meta_stat['type']. '" data-meta_key="' . $meta_stat['meta_key'] . '"><button class="wpgmsampledata">View Data</button><div class="colorswatch" style="background-color:' . $meta_stat['color'] . '"</td>';
			print '</tr>';
		}

		print '</table></div></div>';
	}

	public function section_functions() {
		print '<div class="wpgm-funcs">';
		print '<h3>Available Spatial Functions</h3>';
		print '<p>These functions are available in your version of MySQL.</p>';
		print '<p>For a list of all spatial functions MySQL and MariaDB support, along with which database versions support which functions, please visit the <a href="https://mariadb.com/kb/en/mariadb/mysqlmariadb-spatial-support-matrix/" target="_blank">MySQL/MariaDB Spatial Support Matrix</a> page.</p>';
		print '<table class="funclist"><tr><th>Function Group</th><th>Functions</th></tr>';
		$our_funcs = $this->get_functions_by_type();

		foreach ( $our_funcs as $functype => $funcinfo ) {
			print '<tr><td><h4>' . $funcinfo['label'] . '</h4><p>' . $funcinfo['desc'] . '</p></td><td><div class="funcnamelist">' . implode( '<span class="wordclear"> </span>', $funcinfo['funcs'] )  . '</div></td></tr>';
		}

		print '</table></div>';
	}

	public function section_installs() {
		print '<div><h3>List of Installs</h3>';
		print '<p>WP-GeoMeta can be installed as a plugin (like this one) or used as a library by 
			other plugins. If you have any plugins installed that use WP-GeoMeta as a library, they will
			be listed below along with which version of WP-GeoMeta they were bundled with. 
			</p>';
		print '<p>WP-GeoMeta always uses the most up to date version installed, even if a plugin bundles an older version.</p>';

		print '<table class="wpgminstalllist"><tr><th>Plugin Name</th><th>WP-GeoMeta Version</th></tr>';

		$installs = $this->get_list_of_installs();
		foreach ( $installs as $install ) {
			print '<tr><td>' . $install['plugin_name'] . '</td><td>' . $install['wpgm_version'] . '</td><tr>';
		}
		print '</table></div>';
	}

	public function section_resources() {
		print '<div><h3>WP GeoMeta Meta and Resources</h3>
					<p>
					WP GeoMeta is a work of love from the GIS+WordPress development team at <a href="http://cimbura.com" target="_blank">Cimbura.com</a>. 
					We appreciate <a href="https://github.com/cimburadotcom/WP-GeoMeta/issues" target="_blank">bug reports, feature requests</a> and <a href="https://github.com/cimburadotcom/WP-GeoMeta/pulls" target="_blank">pull requests (especially with test cases)</a>.
If you need assistance implementing your GIS solution, please <a href="https://cimbura.com/contact-us/" target="_blank">contact us</a> with details about what you\'d like to do.
					</p>

					<p>
					<h4>Our Sites</h4>
						<ul>
							<li><a href="https://github.com/cimburadotcom/WP-GeoMeta" target="_blank">WP GeoMeta on GitHub</a></li>
							<li><a href="http://wherepress.com/" target="_blank">WherePress.com — Our WordPress/GIS Blog Site</a></li>
						</ul>
			
					<h4>Documentation</h4>
						<ul>
							<li><a href="https://dev.mysql.com/doc/refman/5.7/en/spatial-analysis-functions.html" target="_blank">MySQL (5.7) Spatial Analysis Functions Documentation</a></li>
							<li><a href="https://mariadb.com/kb/en/mariadb/gis-functionality/" target="_blank">MariaDB Geographic Features Documentation</a></li>
							<li><a href="https://mariadb.com/kb/en/mariadb/mysqlmariadb-spatial-support-matrix/" target="_blank">MySQL/MariaDB Spatial Support Matrix</a></li>
							<li><a href="https://codex.wordpress.org/Function_Reference/add_post_meta" target="_blank">Add</a>, <a href="https://codex.wordpress.org/Function_Reference/update_post_meta" target="_blank">Update</a> and <a href="https://codex.wordpress.org/Function_Reference/delete_post_meta" target="_blank">Delete</a> post meta</li>
							<li><a href="https://codex.wordpress.org/Class_Reference/WP_Query#Custom_Field_Parameters" target="_blank">WP_Query</a> and <a href="https://codex.wordpress.org/Class_Reference/WP_Meta_Query" target="_blank">WP_Meta_Query<a></li>
							<li><a href="http://geojson.org/" target="_blank">GeoJSON Specification</a></li>
							<li><a href="http://leafletjs.com/" target="_blank">Leaflet.js - Our recommended map software</a></li>
						</ul>


					<h4>GIS Communities</h4>
						<ul>
							<li><a href="http://gis.stackexchange.com/" target="_blank">GIS StackExchange</a></li>
							<li><a href="http://www.thespatialcommunity.com/" target="_blank">The Spatial Community</a></li>
						</ul>
					</p>
				</div>';
	}

	public function section_dragons() {
				print '<h3 class="dragons">The Danger Zone<span id="danger-spinner"></span></h3>
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
								Clears existing spatial data, but doesn\'t remove the tables.</td></tr>

						<tr><td><button data-action="populate-tables" class="wpgm-danger-action">Populate WP GeoMeta Tables</button></td><td>
								Detect any spatial data (GeoJSON) in the non-spatial meta tables which is not stored
								in WP-GeoMeta and load it. This may take a while!</td></tr>
					</table>
					<div id="wpgm-danger-results"></div>
				</div>';
	}
}
