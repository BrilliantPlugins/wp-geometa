<?php
/**
 * This class and file are respnsible for the WP GeoMeta WordPress dashboard page. This file doesn't need to be loaded
 * when WP GeoMeta is used as a library.
 *
 * @package WP-GeoMeta
 * @link https://github.com/cimburadotcom/WP-GeoMeta
 * @author Michael Moore / michael_m@cimbura.com / https://profiles.wordpress.org/stuporglue/
 * @copyright Cimbura.com, 2016
 * @license GNU GPL v2
 */

defined( 'ABSPATH' ) or die( 'No direct access' );

/**
 * This class encapsulates all the data gathering, display and interaction for the dashboard.
 */
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
				'label' => __('Spatial Relationship Tests'),
				'desc' => __('Test topological relationships between two geometries.'),
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
				'label' => __('Geometry Properties'),
				'desc' => __('Analyize spatial properties of a single geometry.'),
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
				'label' => __('Geometry Disection'),
				'desc' => __('Investigate the type and sub-parts of a geometry.'),
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
				'label' => __('Generate new Geometry'),
				'desc' => __('Create a new geometry based on existing geometries and spatial operations.'),
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
				'label' => __('Data Format Helpers'),
				'desc' => __('Create or convert geometries from various input and output formats.'),
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
				'label' => __('Miscellaneous Functions'),
				'desc' => __('Other little-used functions.'),
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
				if ( in_array( strtolower( $func ), $our_caps, true ) ) {
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
	 *
	 * @param string $hook The admin page hook we're processing.
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( 'tools_page_wp-geometa' !== $hook ) {
			return;
		}

		wp_enqueue_script( 'leafletjs', 'https://npmcdn.com/leaflet@1.0.0-rc.3/dist/leaflet.js', array(), null );
		wp_enqueue_style( 'leafletcss', 'https://npmcdn.com/leaflet@1.0.0-rc.3/dist/leaflet.css', array(), null );
		wp_enqueue_style( 'wpgeometadash', plugin_dir_url( __FILE__ ) . '/../../assets/wpgeometa.css', array( 'leafletcss' ) );

		wp_register_script( 'wpgeometadashjs', plugin_dir_url( __FILE__ ) . '/../../assets/wpgeometa.js', array( 'leafletjs' ) );
		$translation_array = array(
			'action_confirm_dialog' => __( 'Are you sure you want to %1$s?' )
			);
		wp_localize_script( 'wpgeometadashjs', 'wpgmjs_strings', $translation_array );
		wp_enqueue_script( 'wpgeometadashjs' );

		// Since we're on the right page, gather the data we need.
		$this->set_list_of_geotables();
	}

	/**
	 * Add the dashboard menu listing.
	 */
	public function admin_menu() {
		add_management_page( esc_html__( 'WP GeoMeta', 'wp-geometa' ), esc_html__( 'WP GeoMeta','wp-geometa' ), 'install_plugins', 'wp-geometa', array( $this, 'show_dashboard' ) );
	}

	/**
	 * This is the top-level function for the dashboard display.
	 *
	 * Prints the dashboard HTML.
	 */
	public function show_dashboard() {
		print '<div class="wp-geometa-dash">';
		$this->section_header();

		print '<ul class="wpgmtabctrl">';
		print '<li data-tab="home" class="shown">Home</li>';
		print '<li data-tab="installs">Installs</li>';
		print '<li data-tab="yourmeta">Your WP GeoMeta</li>';
		print '<li data-tab="functions">Your Functions</li>';
		print '<li data-tab="quickstart">Quick Start</li>';
		print '<li data-tab="resources">Resources</li>';
		print '<li data-tab="danger">Danger Zone</li>';
		print '</ul>';

		// Home.
		print '<div class="wpgmtab shown" data-tab="home">';
		$this->section_status_summary();
		print '</div>';

		print '<div class="wpgmtab" data-tab="installs">';
		$this->section_installs();
		print '</div>';

		// Your data and available functions.
		print '<div class="wpgmtab" data-tab="yourmeta">';
		$this->section_data();
		print '</div>';

		print '<div class="wpgmtab" data-tab="functions">';
		$this->section_functions();
		print '</div>';

		// Quickstart and resources.
		print '<div class="wpgmtab" data-tab="quickstart">';
		$this->section_quickstart();
		print '</div>';

		print '<div class="wpgmtab" data-tab="resources">';
		$this->section_resources();
		print '</div>';

		// Danger zone!
		print '<div class="wpgmtab" data-tab="danger">';
		$this->section_dragons();
		print '</div>';

		print '</div>';
	}

	/**
	 * This function sets $this->tables_found and $this->table_types_found. It should be called before displaying the dashboard.
	 */
	public function set_list_of_geotables() {
		global $wpdb;

		if ( isset( $this->tables_found ) ) {
			return $this->tables_found;
		}

		$geometa = WP_GeoMeta::get_instance();
		$this->tables_found = array();
		foreach ( $geometa->meta_types as $meta_type ) {
			$geotable = _get_meta_table( $meta_type ) . '_geo';
			if ( $geotable === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', array( $geotable ) ) ) ) { // @codingStandardsIgnoreLine
				$this->tables_found[] = $geotable;
				$this->table_types_found[] = $meta_type;
			}
		}
	}

	/**
	 * Get the block that checks if all the tables are present.
	 */
	public function block_table_list() {

		$geometa = WP_GeoMeta::get_instance();

		if ( count( $this->tables_found ) === count( $geometa->meta_types ) ) {
			$this->make_status_block( 'good', esc_html__( 'Geo Tables Exist!' ) , esc_html__( 'All geometa tables exist.' ) );
		} else if ( count( $this->tables_found ) > 0 ) {
			$this->make_status_block( 'fair', esc_html__( 'Some Geo Tables Exist' ) , sprintf( esc_html__( 'Some geo tables are missing. If this wasn\'t intentional, there could be a problem. %1%s all exist. %2$s don\'t exist' ), implode( ', ', $this->tables_found ), implode( ', ', array_diff( $geometa->meta_types, $this->tables_found ) ) ) );
		} else {
			$this->make_status_block( 'poor', esc_html__( 'No Geo Tables Exist!' ), esc_html__( 'No geo tables exist. You can try recreating them with the tools at the bottom of this page.' ) );
		}
	}

	/**
	 * Get the block that shows if all tables are indexed.
	 */
	public function block_indexes() {
		global $wpdb;
		$tables_found = array();
		$no_index = array();

		foreach ( $this->tables_found as $geotable ) {
			$create = $wpdb->get_var( 'SHOW CREATE TABLE `' . $geotable . '`', 1 ); // @codingStandardsIgnoreLine
			$has_spatial_index = strpos( $create, 'SPATIAL KEY `meta_val_spatial_idx` (`meta_value`)' );

			if ( false !== $has_spatial_index ) {
				$tables_found[] = $geotable;
			} else {
				$no_index[] = $geotable;
			}
		}

		if ( count( $tables_found ) === count( $this->tables_found ) ) {
			$this->make_status_block( 'good', esc_html__( 'Geo Tables Indexed!' ), sprintf( esc_html__( 'All %1$s geometa tables have spatial indexes' ), count( $tables_found ) ) );
		} else if ( count( $tables_found ) > 0 ) {
			$this->make_status_block( 'fair', esc_html__( 'Some Geo Tables Not Indexed' ), sprintf( esc_html__( 'Some geo tables are not indexed. The following tables could have performance issues: %1$s' ), implode( ', ', $no_index ) ) );
		} else {
			$this->make_status_block( 'poor', esc_html__( 'No Spatial Indexes!' ), esc_html__( 'No spatial indexes found. Spatial queries will be slow.' ) );
		}
	}

	/**
	 * Get the block that shows if we've got a good database version.
	 */
	public function block_db_versions() {
		global $wpdb;

		$our_caps = WP_GeoUtil::get_capabilities();
		$version_info = $wpdb->get_var( 'SELECT VERSION()' ); // @codingStandardsIgnoreLine

		if ( in_array( 'st_intersects', $our_caps, true ) ) {
			$this->make_status_block( 'good', esc_html__( 'Good Database Version!' ), sprintf( esc_html__( 'Your database version (%1$s) supports a wide variety of useful spatial functions.' ), $version_info ) );
		} else if ( in_array( 'geometrycollection', $our_caps, true ) ) {
			$this->make_status_block( 'fair', esc_html__( 'OK Database Version' ), sprintf( esc_html__( 'Your database version (%1$s) has some spatial support, but doesn\'t support key spatial functions. Consider upgrading to MySQL 5.6.1 or higher, or MariaDB 5.3.3 or higher.' ), $version_info ) );
		} else {
			$this->make_status_block( 'poor', esc_html__( 'Bad Database Version!' ), sprintf( esc_html__( 'Your database version (%1$s) doesn\'t appear to have spatial support. You won\'t be able to store or use spatial data.' ), $version_info ) );
		}
	}

	/**
	 * Get the block that shows if our plugin is up to date.
	 */
	public function block_updates() {

		$all_plugins = get_plugin_updates();

		$this_plugin = basename( dirname( dirname( __FILE__ ) ) ) . '/wp-geometa.php';

		/*
		 * Three statuses.
		 * Poor. There are updates and WP_GEOMETA_DASH_VERSION and WP_GEOMETA_VERSION are the same and both are out of date
		 * OK. There are updates, and WP_GEOMETA_DASH_VERSION is out of date, but WP_GEOMETA_VERSION is up to date (some other plugin has an updated version)
		 * Good. There are no updates: WP_GEOMETA_DASH_VERSION is up to date and WP_GEOMETA_VERSION is up to date
		 */

		if ( empty( $all_plugins[ $this_plugin ] ) ) {
			$this->make_status_block( 'good', esc_html__( 'Up To Date!' ), sprintf( esc_html__( 'You are running the most recent version of WP GeoMeta (%1$s).' ), WP_GEOMETA_VERSION ) );
		} else if ( 0 === version_compare( WP_GEOMETA_VERSION, $all_plugins[ $this_plugin ]->Version ) && -1 === version_compare( WP_GEOMETA_DASH_VERSION, $all_plugins[ $this_plugin ]->Version ) ) {
			$this->make_status_block( 'fair', esc_html__( 'Out Of Date.' ), sprintf( esc_html__( 'A plugin you are using is providing the most recent version of the WP GeoMeta library (%1$s), but this plugin is out of date.', WP_GEOMETA_VERSION ) ) );
		} else {
			$this->make_status_block( 'poor', esc_html__( 'Out Of Date!' ), sprintf( esc_html__( 'You are running an outdated version of WP GeoMeta (%1$s). Please upgrade!', WP_GEOMETA_VERSION ) ) );
		}
	}

	/**
	 * Get the block that shows if the GIS data is loaded from the meta table.
	 */
	public function block_data_loaded() {
		global $wpdb;

		$percents_loaded = array();

		$wpgm = WP_GeoMeta::get_instance();

		$total_meta = 0;
		$total_geo = 0;

		foreach ( $wpgm->meta_types as $meta_type ) {
			$metatable = _get_meta_table( $meta_type );
			$geotable = $metatable . '_geo';

			$num_meta = $wpdb->get_var( "SELECT COUNT(*) FROM $metatable WHERE $metatable.meta_value LIKE '%{%Feature%geometry%}%'" ); // @codingStandardsIgnoreLine

			$total_meta += $num_meta;

			if ( 0 === $num_meta ) {
				$percents_loaded[ $meta_type ] = 1;
				continue;
			}

			if ( in_array( $meta_type, $this->table_types_found, true ) ) {
				$num_geo = $wpdb->get_var( "SELECT COUNT(*) FROM $geotable" ); // @codingStandardsIgnoreLine

				$total_geo += $num_geo;
			} else {
				$percents_loaded[ $meta_type ] = 0;
				continue;
			}

			$percents_loaded[ $meta_type ] = $num_geo / $num_meta;
		}

		if ( 0 === $total_meta ) {
			$total_percent = 100;
		} else {
			$total_percent = $total_geo / $total_meta * 100;
		}

		if ( 100 === $total_percent ) {
			$this->make_status_block( 'good', esc_html__( 'All Spatial Data Loaded!' ), sprintf( esc_html__( 'All %1$s spatial records are loaded!' ), $total_meta ) );
		} else if ( $total_percent > 0 ) {
			$this->make_status_block( 'fair', esc_html__( 'Some Spatial Data Loaded' ), sprintf( esc_html__( '%1$s% of spatial records are loaded (%2$s records not loaded). Try using the %3$sPopulate WP GeoMeta Tables%4$s tool below to load them.' ), $total_percent, ( $total_meta - $total_geo ), '<em>', '</em>' ) );
		} else {
			$this->make_status_block( 'poor', esc_html__( 'No Spatial Data Loaded!' ), sprintf( esc_html__( 'Please verify that the spatial tables exist, then use the %1$sPopulate WP GeoMeta Tables%2$s tool below to load the data.' ), '<em>', '</em>' ) );
		}
	}

	/**
	 * Block helper function that actually generates the HTML for a block.
	 *
	 * @param string $status The block status. Must be good, fair or poor.
	 * @param string $title The title to show on the block.
	 * @param string $description The description for the block.
	 */
	public function make_status_block( $status, $title, $description ) {
		print '<tr><td><div class="status-block"><div class="status-circle ' . esc_attr( $status ). '"></div><div class="status-title">' . esc_html( $title ) . '</div></td><td>' . $description  . '</td></tr>'; // @codingStandardsIgnoreLine -- $title and $description are already escaped
		// print '<div class="status-block"><div class="status-circle ' . esc_attr( $status ). '"></div><div class="status-title">' . esc_html( $title ) . '</div><div class="status-text">' . $description  . '</div></div>'; // @codingStandardsIgnoreLine -- $title and $description are already escaped
	}

	/**
	 * Gather info about which object types and meta keys have spatial data.
	 */
	function get_geometa_stats() {
		global $wpdb;

		$found_data = array();
		$found_tables = $this->table_types_found;

		if ( in_array( 'post', $found_tables, true ) ) {
			// Posts.
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

foreach ( $wpdb->get_results( $q, ARRAY_A ) as $geometa ) {  // @codingStandardsIgnoreLine

				$post_type_object = get_post_type_object( $geometa['post_type'] );

				$found_data[] = array(
					'name' => $post_type_object->labels->name . ' (post)',
					'type' => 'post',
					'the_meta_key' => $geometa['meta_key'],
					'quantity' => $geometa['quantity'],
					'sub_type' => $geometa['post_type'],
							);
			}
		}

		if ( in_array( 'user', $found_tables, true ) ) {
			// Users.
			/**
			 * We don't actually *use* $wpdb->usermeta, we just
			 * enable it for users who might need it, so we're going to ignore
			 * the `Usage of users/usermeta tables is highly discouraged in VIP context`
			 * message.
			 */
			// @codingStandardsIgnoreStart
			$q = 'SELECT 
				meta_key, 
				COUNT(umeta_id) AS quantity
				FROM 
				' . $wpdb->usermeta . '_geo geo
				GROUP BY 
				meta_key';

// @codingStandardsIgnoreEnd

foreach ( $wpdb->get_results( $q, ARRAY_A ) as $usermeta ) {  // @codingStandardsIgnoreLine
				$found_data[] = array(
					'name' => 'Users',
					'type' => 'user',
					'the_meta_key' => $usermeta['meta_key'],
					'quantity' => $usermeta['quantity'],
							);
			}
		}

		if ( in_array( 'term', $found_tables, true ) ) {
			// Term Meta.
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

foreach ( $wpdb->get_results( $q, ARRAY_A ) as $termmeta ) { // @codingStandardsIgnoreLine

				$found_data[] = array(
					'name' => $termmeta['name'] . ' (term)',
					'type' => 'user',
					'the_meta_key' => $termmeta['meta_key'],
					'quantity' => $termmeta['quantity'],
							);
			}
		}

		if ( in_array( 'comment', $found_tables, true ) ) {
			// Comment Meta.
			$q = 'SELECT 
				meta_key, 
				COUNT(meta_id) AS quantity
				FROM 
				' . $wpdb->commentmeta . '_geo geo
				GROUP BY 
				meta_key';

foreach ( $wpdb->get_results( $q, ARRAY_A ) as $commentmeta ) { // @codingStandardsIgnoreLine
				$found_data[] = array(
					'name' => 'Comments',
					'type' => 'comment',
					'the_meta_key' => $commentmeta['meta_key'],
					'quantity' => $commentmeta['quantity'],
							);
			}
		}

		foreach ( $found_data as &$data ) {
			$data['color'] = sprintf( '#%06X', mt_rand( 0, 0xFFFFFF ) );
		}

		return $found_data;
	}

	/**
	 * Get 500 random spatial records from a post type.
	 */
	public function ajax_wpgm_get_sample_data() {
		global $wpdb;

		$wpgm = WP_GeoMeta::get_instance();

		$type = $_GET['type']; // @codingStandardsIgnoreLine
		$subtype = ( empty( $_GET['subtype'] ) ? null : $_GET['subtype']);

		if ( ! in_array( $type, $wpgm->meta_types, true ) ) {
			die();
		}

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
		if ( ! empty( $subtype ) ) {
			$q .= ' AND t.post_type = %s ';
		}
		$q .= ' ORDER BY RAND() LIMIT 500';

		$meta_key = $_GET['meta_key']; // @codingStandardsIgnoreLine

		$placeholders = array( $meta_key, $subtype );
		$res = $wpdb->get_results( $wpdb->prepare( $q, $placeholders ), ARRAY_A ); // @codingStandardsIgnoreLine

		if ( empty( $res ) ) {
			wp_send_json( array() );
		}

		$geojson = array();

		if ( ! empty( $_GET['subtype'] ) ) { // @codingStandardsIgnoreLine
			$post_type_object = get_post_type_object( $_GET['subtype'] );  // @codingStandardsIgnoreLine
			$type = $post_type_object->labels->name . ' (post)';
		} else {
			$type = ucfirst( $type );
		}

		foreach ( $res as $record ) {
			$feature_collection = WP_GeoUtil::merge_geojson( $record['meta_value'] );
			$feature_collection = json_decode( $feature_collection, true );
			foreach ( $feature_collection['features'] as &$feature ) {
				$feature['title'] = $type . ' id ' . $record['the_id'];
			}
			$geojson[] = $feature_collection;
		}

		$geojson = WP_GeoUtil::merge_geojson( $geojson );

		$geojson = json_decode( $geojson );

		if ( empty( $geojson ) ) {
			wp_send_json( array() );
		}

		wp_send_json( $geojson );
	}

	/**
	 * Generate a list of WP GeoMeta installs.
	 */
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
		$real_action = $_GET['action_type']; // @codingStandardsIgnoreLine

		switch ( $real_action ) {
			case 'run-tests':
				ob_start();
				require_once( dirname( __FILE__ ) . '/../test/testsuite.php' );
				$res = ob_get_clean();
				$res = trim( $res );
				$lines = explode( "\n", $res );
				array_shift( $lines );
				print esc_html( implode( "\n", $lines ) );
				break;
			case 'remove-tables':
				$wpgm = WP_GeoMeta::get_instance();
				$wpgm->uninstall();
				print esc_html__( 'The WP GeoMeta tables should be gone now.' );
				break;
			case 'create-tables':
				$wpgm = WP_GeoMeta::get_instance();
				$wpgm->create_geo_tables();
				print esc_html__( 'The WP GeoMeta tables should exist now.' );
				break;
			case 'truncate-tables':
				$wpgm = WP_GeoMeta::get_instance();
				$wpgm->truncate_tables();
				print esc_html__( 'The WP GeoMeta tables should be empty now.' );
				break;
				break;
			case 'populate-tables':
				$wpgm = WP_GeoMeta::get_instance();
				$wpgm->populate_geo_tables();
				print esc_html__( 'The WP GeoMeta tables should be populated now.' );
				break;
			default:
				print esc_html__( "I don't understand what I'm supposed to do." );
		}

		exit();
	}

	/**
	 * Print the dashboard header section.
	 */
	public function section_header() {
		$icon = plugin_dir_url( __FILE__ ) . '/../../assets/icon.png';
		print '<div class="wpgm-header header noborder"><h2><img src="' . esc_attr( $icon ). '" title="WP GeoMeta Logo"/>WP GeoMeta</h2></div>';
	}

	public function section_status_summary() {
		print '<div class="wpgm-status noborder"><div class="status-table">';
		print '<table class="summary">';
		$this->block_updates();
		$this->block_table_list();
		$this->block_indexes();
		$this->block_db_versions();
		$this->block_data_loaded();
		print '</table>';
		print '</div></div>';
	}

	/**
	 * Print the section that shows stored spatial object types, their meta keys and how many records of that type there are.
	 */
	public function section_data() {
		print '<div class="wpgm-data"><h3>' . esc_html__( 'Your Spatial Data' ) . '<span id="yourdata-spinner"></span></h3><div id="wpgmmap"></div><div class="posttypegeotable">';
		print '<h4>' . esc_html__( 'Found Spatial Metadata Types' ) . '</h4>';
		print '<table><tr>';
		print '<th>' . esc_html__( 'Type' ) . '</th>';
		print '<th>' . esc_html__( 'Meta Key' ) . '</th>';
		print '<th>' . esc_html__( 'Number of Records' ) . '</th>';
		print '<th>' . esc_html__( 'View Sample Data (500 records max)' ) . '</th></tr>';

		foreach ( $this->get_geometa_stats() as $meta_stat ) {
			print '<tr>';
			print '<td>' . esc_html( $meta_stat['name'] ) . '</td>';
			print '<td>' . esc_html( $meta_stat['the_meta_key'] ). '</td>';
			print '<td>' . esc_html( $meta_stat['quantity'] ). '</td>';
			print '<td data-subtype="' . ( isset( $meta_stat['sub_type'] ) ? esc_html( $meta_stat['sub_type'] ) : '' ) . '" data-color="' . esc_attr( $meta_stat['color'] ) . '" data-type="' . esc_attr( $meta_stat['type'] ) . '" data-meta_key="' . esc_attr( $meta_stat['the_meta_key'] ). '">';
			print '<button class="wpgmsampledata">' . esc_html__( 'View Data' ) . '</button>';
			print '<div class="colorswatch" style="background-color:' . esc_attr( $meta_stat['color'] ) . '"></div></td>';
			print '</tr>';
		}

		print '</table></div></div>';
	}

	/**
	 * Print the section showing some basic quick-start info.
	 */
	public function section_quickstart() {
		print '<div><h3>' . esc_html__('Quick Start') .'</h3>';

		print '<p>' . esc_html__('WP GeoMeta uses the standard WordPress metadata functions. If you are familiar with these functions, you already know how to use WP GeoMeta.') . '</p>';

		print '<p>' . esc_html__('WP GeoMeta watches for GeoJSON metadata values being saved to the database. It saves the GeoJSON like WordPress expects, but also converts the GeoJSON into a spatial format MySQL understands and saves that to special geo-meta tables which can handle spatial data and which have spatial indexes.') . '</p>';

		print '<h4>' . esc_html__('Adding and Updating Data') . '</h4>';
		print '<p>' . esc_html__('To add spatial data, use the usual add_post_meta or update_post_meta functions like you would for any other metadata, but use GeoJSON for the value.') . '</p>';

		print '<pre><code>';
		print '$geojson = \'{' . "\n";
		print '    "type":"Feature",' . "\n";
		print '    "properties":{"_post_title":"Test post 17"},' . "\n";
		print '    "geometry":{"type":"Point","coordinates":[-93.27949941158295,45.063696581607836]}' . "\n";
		print '}\';' . "\n";
		print '$post_id = get_the_ID();' . "\n";
		print '$meta_property_name = \'location\';' . "\n";
		print 'update_post_meta( $post_id, $meta_property_name, $geojson );';
		print '</code></pre>';

		print '<h4>' . esc_html__('Fetching and Using Data') . '</h4>';

		print '<p>' . esc_html__('WP GeoMeta doesn\'t do anything when fetching data. You will get back the same GeoJSON value that you stored previously.') . '</p>';
		print '<p>' . esc_html__('GeoJSON can be used by any of the popular web map software available today.') . '</p>';
		print '<pre><code>';
		print '$post_id = get_the_ID();' . "\n";
		print '$meta_property_name = \'location\';' . "\n";
		print '$get_single = true;' . "\n";
		print '$geojson = get_post_meta( $post_id, $meta_property_name, $get_single );' . "\n";
		print '</code></pre>';

		print '<h4>' . esc_html__('Running Spatial Queries') . '</h4>';

		print '<p>' . esc_html__('The real power of GIS and Spatial data becomes evident when you start doing real spatial searches. WP GeoMeta integrates with WP_Query, get_posts and other functions that use WP_Meta_Query under the hood.') . '</p>';

		print '<pre><code>';
		print '$bounding_box = \'{"type":"Feature","properties":{},\';' . "\n";
		print '$bounding_box .= \'"geometry":{"type":"Polygon","coordinates":[[\';' . "\n";
		print '$bounding_box .= \'[-93.45,45.00],\';' . "\n";
		print '$bounding_box .= \'[-93.45,45.10],\';' . "\n";
		print '$bounding_box .= \'[-93.09,45.10],\';' . "\n";
		print '$bounding_box .= \'[-93.09,45.00],\';' . "\n";
		print '$bounding_box .= \'[-93.45,45.00]\';' . "\n";
		print '$bounding_box .= \']]}}\';' . "\n";
		print "\n";
		print '$rectangle_query = new WP_Query( array(' . "\n";
		print '    "meta_query" =&gt; array(' . "\n";
		print '        array(' . "\n";
		print '            "key" =&gt; "location",' . "\n";
		print '            "compare" =&gt; "INTERSECTS",' . "\n";
		print '            "value" =&gt; $bounding_box' . "\n";
		print '        )' . "\n";
		print '    )' . "\n";
		print '));' . "\n";
		print "\n";
		print 'if ( $rectangle_query->have_posts() ) {' . "\n";
		print '   print ' . esc_html("<ul>") . ';' . "\n";
		print '    while ( $rectangle_query->have_posts() ) {' . "\n";
		print '        $rectangle_query->the_post();' . "\n";
		print '        print ' . esc_html("<li>") . 'get_the_title()' . esc_html("</li>") . "\n";
		print '   }' . "\n";
		print '}';
		print '</code></pre>';

		print '</div>';
	}

	/**
	 * Print the list of supported functions.
	 */
	public function section_functions() {
		print '<div class="wpgm-funcs">';
		print '<h3>' . esc_html__( 'Available Spatial Functions' ) . '</h3>';
		print '<p>' . esc_html__( 'These functions are available in this version of MySQL.' ) . '</p>';
		print '<p>' . sprintf( esc_html__( 'Spatial function support varries widely between versions of MySQL and MariaDB. Visit the %1$sMySQL/MariaDB Spatial Support Matrix%2$s page for a full breakdown.' ), '<a href="https://mariadb.com/kb/en/mariadb/mysqlmariadb-spatial-support-matrix/" target="_blank">', '</a>' ) . '</p>';
		print '<table class="funclist"><tr><th>' . esc_html__( 'Function Group' ) . '</th><th>' . esc_html__( 'Functions' ) . '</th></tr>';
		$our_funcs = $this->get_functions_by_type();

		foreach ( $our_funcs as $functype => $funcinfo ) {
			print '<tr><td><h4>' . esc_html( $funcinfo['label'] ) . '</h4><p>' . esc_html( $funcinfo['desc'] ) . '</p></td><td><div class="funcnamelist">' . implode( '<span class="wordclear"> </span>', array_map( 'esc_html', $funcinfo['funcs'] ) )  . '</div></td></tr>';
		}

		print '</table></div>';
	}

	/**
	 * Print the list of WP GeoMeta installs.
	 */
	public function section_installs() {
		print '<div><h3>' . esc_html__( 'List of Installs' ) . '</h3>';
		print '<p>' . esc_html__('WP GeoMeta can be installed as a plugin or used as a library by 
			other plugins. This list includes all installed versions of WP GeoMeta and which plugin they came with.') . '</p>';
		print '<p>' . esc_html__( 'WP GeoMeta always uses the most up to date version installed, even if a plugin bundles an older version.' ) . '</p>';

		print '<table class="wpgminstalllist"><tr><th>' . esc_html__( 'Plugin Name' ) . '</th><th>' . esc_html__( 'WP GeoMeta Version' ) . '</th></tr>';

		$installs = $this->get_list_of_installs();
		foreach ( $installs as $install ) {
			print '<tr><td>' . esc_html( $install['plugin_name'] ) . '</td><td>' . esc_html( $install['wpgm_version'] ) . '</td><tr>';
		}
		print '</table></div>';
	}

	/**
	 * Print the list of useful resources.
	 */
	public function section_resources() {
		print '<div><h3>' . esc_html__( 'WP GeoMeta Meta and Resources' ) . '</h3>';

		$logo = plugin_dir_url( __FILE__ ) . '/../../assets/cimbura_logo.png';
		print '<p><img src="' . esc_attr( $logo ) . '" class="logo">' . sprintf( esc_html__( 'WP GeoMeta is a work of love from the GIS+WordPress development team at %1$s' ), '<a href="http://cimbura.com" target="_blank">Cimbura.com</a>' );
		print ' ';
		printf( esc_html__( 'We appreciate %1$sbug reports, feature requests%2$s and %3$spull requests (especially with test cases)%4$s.' ), '<a href="https://github.com/cimburadotcom/WP-GeoMeta/issues" target="_blank">', '</a>','<a href="https://github.com/cimburadotcom/WP-GeoMeta/pulls" target="_blank">', '</a>' );
		print ' ';
		printf( esc_html__( 'If you need assistance implementing your GIS solution, please %1$scontact us%2$s with details about what you\'d like to do.' ),  '<a href="https://cimbura.com/contact-us/" target="_blank">', '</a>' );
		print '</p><p>';

		print '<h4>' . esc_html__( 'Our Sites' ) . '</h4>';
		print '<ul>';
		print '<li><a href="https://cimbura.com" target="_blank">Cimbura.com — ' . esc_html__( 'Our home on the web' ) . '</a></li>';
		print '<li><a href="https://github.com/cimburadotcom/WP-GeoMeta" target="_blank">' . esc_html__( 'WP GeoMeta on GitHub' ) . '</a></li>';
		print '<li><a href="http://wherepress.com/" target="_blank">' . esc_html__( 'WherePress.com — Our WordPress/GIS Blog Site' ) . '</a></li>';
		print '</ul>';

		print '<h4>' . esc_html__( 'Documentation' ) . '</h4>';
		print '<ul>';
		print '<li><a href="https://dev.mysql.com/doc/refman/5.7/en/spatial-analysis-functions.html" target="_blank">' . esc_html__( 'MySQL (5.7) Spatial Analysis Functions Documentation' ) . '</a></li>';
		print '<li><a href="https://mariadb.com/kb/en/mariadb/gis-functionality/" target="_blank">' . esc_html__( 'MariaDB Geographic Features Documentation' ) . '</a></li>';
		print '<li><a href="https://mariadb.com/kb/en/mariadb/mysqlmariadb-spatial-support-matrix/" target="_blank">' . esc_html__( 'MySQL/MariaDB Spatial Support Matrix' ) . '</a></li>';
		print '<li>' . sprintf( esc_html__( '%1$sAdd%2$s, %3$sUpdate%4$s and %5$sDelete%6$s post meta' ), '<a href="https://codex.wordpress.org/Function_Reference/add_post_meta" target="_blank">', '</a>', '<a href="https://codex.wordpress.org/Function_Reference/update_post_meta" target="_blank">', '</a>', '<a href="https://codex.wordpress.org/Function_Reference/delete_post_meta" target="_blank">', '</a>' ) . '</li>';
		print '<li>' . sprintf( esc_html__( '%1$sWP_Query%2$s and %3$sWP_Meta_Query%4$s' ),'<a href="https://codex.wordpress.org/Class_Reference/WP_Query#Custom_Field_Parameters" target="_blank">','</a>', '<a href="https://codex.wordpress.org/Class_Reference/WP_Meta_Query" target="_blank">','</a>' ) . '</li>';
		print '<li><a href="http://geojson.org/" target="_blank">' . esc_html__( 'GeoJSON Specification' ) . '</a></li>';
		print '<li><a href="http://leafletjs.com/" target="_blank">' . esc_html__( 'Leaflet.js - Our recommended map software' ) . '</a></li>';
		print '</ul>';

		print '<h4>' . esc_html__( 'GIS Communities' ) . '</h4>';
		print '<ul>';
		print '<li><a href="http://gis.stackexchange.com/" target="_blank">' . esc_html__( 'GIS StackExchange' ) . '</a></li>';
		print '<li><a href="http://www.thespatialcommunity.com/" target="_blank">' . esc_html__( 'The Spatial Community' ) . '</a></li>';
		print '</ul></p></div>';
	}

	/**
	 * Print the danger section.
	 */
	public function section_dragons() {
		print '<div class="dragons">';
		print '<h3 class="dragons">'. esc_html__( 'The Danger Zone' ) . '<span id="danger-spinner"></span></h3>';
		print '<p>';
		print sprintf( esc_html__( '%1$sImportant%2$s: The following section has funtionality that may destroy your spatial data. Your original metadata is never touched, but deleting data may impact plugins or custom code that expect it to be present.' ),'<strong>','</strong>' );
		print'</p><table>';

		// Run tests.
		print '<tr><td><button data-action="run-tests" class="wpgm-danger-action">' . esc_html__( 'Run Regression Tests' ) . '</button></td>';
		print '<td>' . esc_html__( 'Run the built-in regression tests' ) . '</td></tr>';

		// Remove WP GeoMeta Tables.
		print '<tr><td><button data-action="remove-tables" class="wpgm-danger-action">' . esc_html__( 'Remove WP GeoMeta Tables' ) . '</button></td>';
		print '<td>' . esc_html__( 'All WP GeoMeta data is stored in its own tables. Your original data is untouched. Removing WP GeoMeta tables will break any spatial queries you may be using.' ) . '</td></tr>';

		// Create WP GeoMeta Tables.
		print '<tr><td><button data-action="create-tables" class="wpgm-danger-action">' . esc_html__( 'Create WP GeoMeta Tables' ) . '</button></td>';
		print '<td>' . esc_html__( 'WP GeoMeta tables are created on plugin activation or upgrade, but you can manually create them here. WP GeoMeta uses dbDelta, so running this multiple times will have no bad effects.' ) . '</td></tr>';

		// Truncate WP GeoMeta Tables.
		print '<tr><td><button data-action="truncate-tables" class="wpgm-danger-action">' . esc_html__( 'Truncate WP GeoMeta Tables' ) . '</button></td>';
		print '<td>' . esc_html__( 'Clears existing spatial data, but doesn\'t remove the tables.' ) . '</td></tr>';

		// Populate WP GeoMeta Tables.
		print '<tr><td><button data-action="populate-tables" class="wpgm-danger-action">' . esc_html__( 'Populate WP GeoMeta Tables' ) . '</button></td>';
		print '<td>' . esc_html__( 'Detect any spatial data (GeoJSON) in the non-spatial meta tables which is not stored in WP GeoMeta and load it. This may take a while!' ) . '</td></tr>';

		print '</table><div id="wpgm-danger-results"></div></div>';
	}
}
