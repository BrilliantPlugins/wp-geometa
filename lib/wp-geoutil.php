<?php
/**
 * This class has geo utils that users and WP_Geo* classes might need.
 *
 * @package WP_GeoMeta
 * @link https://github.com/cimburadotcom/WP_GeoMeta
 * @author Michael Moore / michael_m@cimbura.com / https://profiles.wordpress.org/stuporglue/
 * @copyright Cimbura.com, 2016
 * @license GNU GPL v2
 */

/**
 * Include geoPHP for this function
 */
require_once( dirname( __FILE__ ) . '/geoPHP/geoPHP.inc' );

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
	private static $geojson;

	/**
	 * A WKT writer (GeoPHP classes);.
	 *
	 * @var $geowkt
	 */
	private static $geowkt;

	/**
	 * EPSG:4326 is the web mercator project, such as is used by Google Maps
	 *
	 * @see https://en.wikipedia.org/wiki/World_Geodetic_System !
	 *
	 * @var $srid
	 */
	private static $srid;

	/**
	 * This is a list of all known spatial functions in MySQL 5.4.2 to 5.7.6 and MariaDB 5.1 - 10.1.2
	 * We will test for capabilities by checking if the function exists instead of
	 * checking function names.
	 *
	 * @var $all_funcs
	 */
	public static $all_funcs = array(
		'Area',
		'AsBinary',
		'AsText',
		'AsWKB',
		'AsWKT',
		'Boundary',
		'Buffer',
		'Centroid',
		'Contains',
		'ConvexHull',
		'Crosses',
		'Dimension',
		'Disjoint',
		'Distance',
		'EndPoint',
		'Envelope',
		'Equals',
		'ExteriorRing',
		'GeomCollFromText',
		'GeomCollFromWKB',
		'GeometryCollection',
		'GeometryCollectionFromText',
		'GeometryCollectionFromWKB',
		'GeometryFromText',
		'GeometryFromWKB',
		'GeometryN',
		'GeometryType',
		'GeomFromText',
		'GeomFromWKB',
		'GLength',
		'InteriorRingN',
		'Intersects',
		'IsClosed',
		'IsEmpty',
		'IsRing',
		'IsSimple',
		'LineFromText',
		'LineFromWKB',
		'LineString',
		'LineStringFromText',
		'LineStringFromWKB',
		'MBRContains',
		'MBRCoveredBy',
		'MBRDisjoint',
		'MBREqual',
		'MBREquals',
		'MBRIntersects',
		'MBROverlaps',
		'MBRTouches',
		'MBRWithin',
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
		'NumGeometries',
		'NumInteriorRings',
		'NumPoints',
		'Overlaps',
		'Point',
		'PointFromText',
		'PointFromWKB',
		'PointOnSurface',
		'PointN',
		'PolyFromText',
		'PolyFromWKB',
		'Polygon',
		'PolygonFromText',
		'PolygonFromWKB',
		'SRID',
		'ST_Area',
		'ST_AsBinary',
		'ST_AsGeoJSON',
		'ST_AsText',
		'ST_AsWKB',
		'ST_AsWKT',
		'ST_Boundary',
		'ST_Buffer',
		'ST_Buffer_Strategy',
		'ST_Centroid',
		'ST_Contains',
		'ST_ConvexHull',
		'ST_Crosses',
		'ST_Difference',
		'ST_Dimension',
		'ST_Disjoint',
		'ST_Distance',
		'ST_Distance_Sphere',
		'ST_EndPoint',
		'ST_Envelope',
		'ST_Equals',
		'ST_ExteriorRing',
		'ST_GeoHash',
		'ST_GeomCollFromText',
		'ST_GeomCollFromWKB',
		'ST_GeometryCollectionFromText',
		'ST_GeometryCollectionFromWKB',
		'ST_GeometryFromText',
		'ST_GeometryFromWKB',
		'ST_GeometryN',
		'ST_GeometryType',
		'ST_GeomFromGeoJSON',
		'ST_GeomFromText',
		'ST_GeomFromWKB',
		'ST_InteriorRingN',
		'ST_Intersection',
		'ST_Intersects',
		'ST_IsClosed',
		'ST_IsEmpty',
		'ST_IsRing',
		'ST_IsSimple',
		'ST_IsValid',
		'ST_LatFromGeoHash',
		'ST_Length',
		'ST_LineFromText',
		'ST_LineFromWKB',
		'ST_LineStringFromText',
		'ST_LineStringFromWKB',
		'ST_LongFromGeoHash',
		'ST_NumGeometries',
		'ST_NumInteriorRings',
		'ST_NumPoints',
		'ST_Overlaps',
		'ST_PointFromGeoHash',
		'ST_PointFromText',
		'ST_PointFromWKB',
		'ST_PointOnSurface',
		'ST_PointN',
		'ST_PolyFromText',
		'ST_PolyFromWKB',
		'ST_PolygonFromText',
		'ST_PolygonFromWKB',
		'ST_Relate',
		'ST_Simplify',
		'ST_SRID',
		'ST_StartPoint',
		'ST_SymDifference',
		'ST_Touches',
		'ST_Union',
		'ST_Validate',
		'ST_Within',
		'ST_X',
		'ST_Y',
		'StartPoint',
		'Touches',
		'Within',
		'X',
		'Y',
		);

	/**
	 * All the functions we detect as available in MySQL
	 *
	 * @var $found_funcs
	 */
	private static $found_funcs = array();


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
			self::$geojson = new GeoJSON();
			self::$geowkt = new WKT();
			self::$srid = apply_filters( 'wp_geoquery_srid', 4326 );
		}

		return self::$_instance;
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

		// Check if we've been given an array of fragments and act accordingly.
		// If we don't have 'type' in our keys, then there's a good chance we might have an array of geojsons as our first arg.
		if ( 1 === count( $fragments ) && is_array( $fragments[0] ) && ! array_key_exists( 'type', $fragments[0] ) ) {
			$fragments = $fragments[0];
		}

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

			$fragment = array_change_key_case( $fragment );

			if ( ! array_key_exists( 'type',$fragment ) ) {
				continue;
			}

			if ( 0 === strcasecmp( 'featurecollection',$fragment['type'] ) && is_array( $fragment['features'] ) ) {
				$ret['features'] += $fragment['features'];
			} else if ( 0 === strcasecmp( 'feature', $fragment['type'] ) ) {
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
	 *
	 * @return A WKT geometry string.
	 */
	public static function metaval_to_geom( $metaval = '' ) {
		// Let other plugins support non GeoJSON geometry.
		$maybe_geom = apply_filters( 'wpgq_metaval_to_geom', $metaval );
		if ( self::is_geom( $maybe_geom ) ) {
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

		$metaval = self::merge_geojson( $metaval );

		if ( false === $metaval ) {
			return;
		}

		// Convert GeoJSON to WKT.
		try {
			$geom = self::$geojson->read( (string) $metaval );
			if ( is_null( $geom ) ) {
				return false;
			}
		} catch (Exception $e) {
			return false;
		}

		try {
			return self::$geowkt->write( $geom );
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Convert WKT to GeoJSON
	 *
	 * @param geometry $wkt Convert a geometry of some sort to GeoJSON.
	 *
	 * @return A GeoJSON string.
	 */
	public static function geom_to_geojson( $wkt ) {
		$maybe_geojson = apply_filters( 'wpgq_geom_to_geojson', $wkt );
		if ( self::is_geojson( $maybe_geojson ) ) {
			return $maybe_geojson;
		}

		// Don't know what to do non-strings.
		if ( ! is_string( $maybe_geojson ) ) {
			return false;
		}

		// WKT needs to start with one of these things.
		$maybe_geojson = trim( $maybe_geojson );
		if ( stripos( $maybe_geojson, 'POINT' ) !== 0 &&
			stripos( $maybe_geojson, 'LINESTRING' ) !== 0 &&
			stripos( $maybe_geojson, 'POLYGON' ) !== 0 &&
			stripos( $maybe_geojson, 'MULTIPOINT' ) !== 0 &&
			stripos( $maybe_geojson, 'MULTILINESTRING' ) !== 0 &&
			stripos( $maybe_geojson, 'MULTIPOLYGON' ) !== 0
		) {
			return false;
		}

		try {
			$geom = self::$geowkt->read( $maybe_geojson );
			return self::$geojson->write( $geom );
		} catch ( Exception $e ) {
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
	public static function is_geom( $maybe_geom ) {
		try {
			$what = self::$geowkt->read( (string) $maybe_geom );
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
	 * Check if a value is in GeoJSON, which is our code-ready forma.
	 *
	 * @param anything $maybe_geojson Check if a value is GeoJSON or not.
	 */
	public static function is_geojson( $maybe_geojson ) {
		try {
			$what = self::$geojson->read( (string) $maybe_geojson );
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
	public static function get_capabilities( $retest = false ) {
		global $wpdb;

		if ( ! empty( self::$found_funcs ) && ! $retest ) {
			return self::$found_funcs;
		}

		if ( ! $retest ) {
			self::$found_funcs = get_option( 'geometa_capabilities',array() );
			if ( ! empty( self::$found_funcs ) ) {
				return self::$found_funcs;
			}
		}

		$suppress = $wpdb->suppress_errors( true );
		$errors = $wpdb->show_errors( false );

		foreach ( WP_GeoUtil::$all_funcs as $func ) {
			$q = "SELECT $func() AS worked";
			$wpdb->query( $q ); // @codingStandardsIgnoreLine

			if ( strpos( $wpdb->last_error,'Incorrect parameter count' ) !== false || strpos( $wpdb->last_error,'You have an error in your SQL syntax' ) !== false ) {
				self::$found_funcs[] = $func;
			}
		}

		// Re-set the error settings.
		$wpdb->suppress_errors( $suppress );
		$wpdb->show_errors( $errors );

		self::$found_funcs = array_map( 'strtolower',self::$found_funcs );

		update_option( 'geometa_capabilities',self::$found_funcs, false );
		return self::$found_funcs;
	}

	/**
	 * Get the srid
	 */
	public static function get_srid() {
		return self::$srid;
	}
}
WP_GeoUtil::get_instance();
