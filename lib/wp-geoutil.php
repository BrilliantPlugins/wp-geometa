<?php
/**
 * This class has geo utils that users and WP_Geo* classes might need
 *
 * @package WP_GeoMeta
 */

require_once( __DIR__ . '/geoPHP/geoPHP.inc' );

/**
 * Some spatial utilities that are used by both WP_GeoQuery and WP_GeoMeta
 * and which may be available to developers at some point.
 *
 * For now, their use is not recommended since things are still in flux.
 */
class WP_GeoUtil {
	/**
	 * A GeoJSON reader (GeoPHP classes);.
	 *
	 * @var $geojson
	 */
	protected $geojson;

	/**
	 * A WKT writer (GeoPHP classes);.
	 *
	 * @var $geowkt
	 */
	protected $geowkt;

	/**
	 * EPSG:4326 is the web mercator project, such as is used by Google Maps
	 *
	 * @see https://en.wikipedia.org/wiki/World_Geodetic_System !
	 *
	 * @var $srid
	 */
	protected $srid = 4326;

	/**
	 * This is a list of all known spatial functions in MySQL 5.4 to 5.7.6
	 * We will test for capabilities by checking if the function exists instead of
	 * checking function names.
	 *
	 * @var $all_funcs
	 */
	var $all_funcs = array(
		'Contains',
	'Crosses',
	'Disjoint',
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
	'ST_Difference',
	'ST_Disjoint',
	'ST_Equals',
		'ST_Intersects',
	'ST_Overlaps',
	'ST_SymDifference',
	'ST_Touches',
	'ST_Union',
		'ST_Within',
	'Touches',
	'Within',
	'Distance',
	'GeometryCollection',
		'ST_Distance',
	'ST_Distance_Sphere',
	'ST_Intersection',
	'IsClosed',
	'IsEmpty',
		'IsSimple',
	'ST_IsClosed',
	'ST_IsEmpty',
	'ST_IsSimple',
	'ST_IsValid',
		'AsBinary',
	'AsText',
	'AsWKB',
	'AsWKT',
	'ConvexHull',
		'Dimension',
	'Envelope',
	'GeometryType',
	'SRID',
	'ST_AsBinary',
		'ST_AsGeoJSON',
	'ST_AsText',
	'ST_AsWKB',
	'ST_AsWKT',
	'ST_ConvexHull',
		'ST_Envelope',
	'ST_GeoHash',
	'ST_GeometryType',
	'ST_Length',
	'ST_SRID',
		'ST_Validate',
	'ST_GeomFromGeoJSON',
	'Point',
	'LineFromWKB',
	'LineStringFromWKB',
		'ST_LineFromWKB',
	'ST_LineStringFromWKB',
	'LineFromText',
	'LineStringFromText',
	'ST_LineFromText',
		'ST_LineStringFromText',
	'MultiLineString',
	'Polygon',
	'MLineFromWKB',
	'MultiLineStringFromWKB',
		'MLineFromText',
	'MultiLineStringFromText',
	'MPointFromWKB',
	'MultiPointFromWKB',
	'MPointFromText',
		'MultiPointFromText',
	'GeomCollFromWKB',
	'GeometryCollectionFromWKB',
	'GeometryFromWKB',
	'GeomFromWKB',
		'MPolyFromWKB',
	'MultiPolygonFromWKB',
	'ST_GeomCollFromWKB',
	'ST_GeometryCollectionFromWKB',
	'ST_GeometryFromWKB',
		'GeomCollFromText',
	'GeometryCollectionFromText',
	'GeometryFromText',
	'GeomFromText',
	'MPolyFromText',
		'MultiPolygonFromText',
	'ST_GeomCollFromText',
	'ST_GeometryCollectionFromText',
	'ST_GeometryFromText',
	'PointFromWKB',
		'ST_PointFromWKB',
	'PointFromText',
	'ST_PointFromText',
	'LineString',
	'MultiPoint',
		'PolyFromWKB',
	'PolygonFromWKB',
	'ST_GeomFromWKB',
	'ST_PolyFromWKB',
	'ST_PolygonFromWKB',
		'PolyFromText',
	'PolygonFromText',
	'ST_GeomFromText',
	'ST_PolyFromText',
	'ST_PolygonFromText',
		'MultiPolygon',
	'ST_LatFromGeoHash',
	'ST_LongFromGeoHash',
	'ST_PointFromGeoHash',
	'EndPoint',
		'GLength',
	'NumPoints',
	'PointN',
	'ST_EndPoint',
	'ST_NumPoints',
		'ST_PointN',
	'ST_StartPoint',
	'StartPoint',
	'NumGeometries',
	'NumInteriorRings',
		'ST_Centroid',
	'ST_ExteriorRing',
	'ST_NumGeometries',
	'ST_NumInteriorRings',
	'ST_InteriorRingN',
		'ST_GeometryN',
	'GeometryN',
	'InteriorRingN',
	'ST_Y',
	'X',
		'Y',
	'ST_X',
	'ST_Buffer_Strategy',
	'Area',
	'Centroid',
		'ExteriorRing',
	'ST_Area',
	'ST_Simplify',
	'Buffer',
	'ST_Buffer',
	);

	/**
	 * All the functions we detect as available in MySQL
	 *
	 * @var $found_funcs
	 */
	var $found_funcs = array();


	/**
	 * The instance variable
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
	 * The constructor. Also called by WP_GeoQuery and WP_GeoMeta
	 * since they don't have their own and inherit from WP_GeoUtil.
	 */
	protected function __construct() {
		$this->setup_filters();
		$this->geojson = new GeoJSON();
		$this->geowkt = new WKT();

		$this->srid = apply_filters( 'wp_geoquery_srid',$this->srid );
	}

	/**
	 * An empty function in case something else inherits from WP_GeoUtil
	 * and doesn't provide its own setup_filters function
	 */
	function setup_filters() {
		// Go ahead. Override this.
	}

	/**
	 * Merge one or more pieces of geojson together. Each item could be a FeatureCollection
	 * or an individual feature.
	 *
	 * All pieces will be combined to make a single FeatureCollection
	 *
	 * If only one piece is sent, then it will be converted into a FeatureCollection if
	 * it isn't already.
	 *
	 * @note This function takes as many geojson or geojson fragments as you want to pass in.
	 *
	 * @return A FeatureCollection GeoJSON array
	 */
	public static function merge_geojson() {
		$fragments = func_get_args();

		$ret = array(
			'type' => 'FeatureCollection',
			'features' => array(),
		);

		foreach ( $fragments as $fragment ) {
			if ( is_object( $fragment ) ) {
				$fragment = (array) $fragment;
			} else if ( is_string( $fragment ) ) {
				$fragment = json_decode( $fragment,true );
			}

			if ( ! array_key_exists( 'type',$fragment ) ) {
				return false;
			}

			if ( 'FeatureCollection' === $fragment['type'] && is_array( $fragment['features'] ) ) {
				$ret['features'] += $fragment['features'];
			} else if ( 'Feature' === $fragment['type'] ) {
				$ret['features'][] = $fragment;
			}
		}

		if ( empty( $ret['features'] ) ) {
			return false;
		}

		return wp_json_encode( $ret );
	}

	/**
	 * We're going to support single GeoJSON features and FeatureCollections in either string, object or array format.
	 *
	 * @param mixed $metaval The meta value to try to convert to WKT.
	 */
	function metaval_to_geom( $metaval = '' ) {
		// Let other plugins support non GeoJSON geometry.
		$maybe_geom = apply_filters( 'wpgq_metaval_to_geom', $metaval );
		if ( $this->is_geom( $maybe_geom ) ) {
			return $maybe_geom;
		}

		// Exit early if we're a non-GeoJSON string.
		if ( is_string( $metaval ) ) {
		   	if ( strpos( $metaval,'{' ) === false || strpos( $metaval,'Feature' ) === false || strpos( $metaval,'geometry' ) === false ) {
				return false;
			} else {
				$metaval = json_decode( $metaval,true );
			}
		}

		// If it's an object, cast it to an array for consistancy.
		if ( is_object( $metaval ) ) {
			$metaval = (array) $metaval;
		}

		$metaval = $this->merge_geojson( $metaval );

		if ( false === $metaval ) {
			return;
		}

		// Convert GeoJSON to WKT.
		try {
			$geom = $this->geojson->read( (string) $metaval );
			if ( is_null( $geom ) ) {
				return false;
			}
		} catch (Exception $e) {
			return false;
		}

		$wkt = new wkt();
		try {
			return $this->geowkt->write( $geom );
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Check if a value is in WKT, which is our DB-ready format.
	 *
	 * @param string $maybe_geom Something which we want to check if it's WKT or not.
	 *
	 * @return bool
	 */
	function is_geom( $maybe_geom ) {
		try {
			$what = $this->geowkt->read( (string) $maybe_geom );
			if ( null !== $what ) {
				return true;
			} else {
				return false;
			}
		} catch (Exception $e) {
			return false;
		}
	}


	/**
	 * Fetch the found capabilities from the database
	 *
	 * If no capabilites are found, then generate them by running
	 * queries with each SQL function and seeing what the error
	 * message says
	 *
	 * @param bool $retest Should we re-check and re-store our capabilities.
	 */
	function get_capabilities( $retest = false ) {
		global $wpdb;

		if ( ! empty( $this->found_funcs ) && ! $retest ) {
			return $this->found_funcs;
		}

		if ( ! $retest ) {
			$this->found_funcs = get_option( 'geometa_capabilities',array() );
			if ( ! empty( $this->found_funcs ) ) {
				return $this->found_funcs;
			}
		}

		$suppress = $wpdb->suppress_errors( true );
		$errors = $wpdb->show_errors( false );

		foreach ( $this->all_funcs as $func ) {
			$q = "SELECT $func() AS worked";
			$wpdb->query( $q ); // @codingStandardsIgnoreLine

			if ( strpos( $wpdb->last_error,'Incorrect parameter count' ) !== false || strpos( $wpdb->last_error,'You have an error in your SQL syntax' ) !== false ) {
				$this->found_funcs[] = $func;
			}
		}

		// Re-set the error settings.
		$wpdb->suppress_errors( $suppress );
		$wpdb->show_errors( $errors );

		$this->found_funcs = array_map( 'strtolower',$this->found_funcs );

		update_option( 'geometa_capabilities',$this->found_funcs, false );
		return $this->found_funcs;
	}
}
