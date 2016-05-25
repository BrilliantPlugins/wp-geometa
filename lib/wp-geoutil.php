<?php

/**
 * This class has geo utils that users and WP_Geo* classes might need
 */

require_once(__DIR__ . '/geoPHP/geoPHP.inc');
class WP_GeoUtil {
	// A GeoJSON and WKT reader/write (GeoPHP classes);
	protected $geojson;
	protected $geowkt; 

	// EPSG:4326 is the web mercator project, such as is used by Google Maps
	// @see https://en.wikipedia.org/wiki/World_Geodetic_System
	protected $srid = 4326;

	var $known_spatial_functions = array(
		'two_geoms_return_bool' => array(
			'MBRCoveredBy',
			'Contains',
			'Crosses',
			'Disjoint',
			'Equals',
			'Intersects',
			'MBRContains',
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
			'ST_Touches',
			'ST_Within',
			'Touches',
			'Within',
			'ST_SymDifference',
			'ST_Union',
			'NeverWork',
		)
	);

	var $supported_spatial_functions = array();

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



	protected function __construct(){
		$this->setup_filters();
		$this->geojson = new GeoJSON();
		$this->geowkt = new WKT();

		$this->srid = apply_filters('wp_geoquery_srid',$this->srid);
	}

	function setup_filters() {
		// go ahead. Override this
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
	 * @param as many geojson or geojson fragments as you want
	 *
	 * @return A FeatureCollection GeoJSON array
	 */
	public static function merge_geojson(){
		$fragments = func_get_args();

		$ret = array(
			'type' => 'FeatureCollection',
			'features' => array()
		);

		foreach($fragments as $fragment){
			if(is_object($fragment)){
				$fragment = (array)$fragment;
			} else if(is_string($fragment)){
				$fragment = json_decode($fragment,TRUE);
			}

			if(!array_key_exists('type',$fragment)){
				return false;
			}

			if($fragment['type'] == 'FeatureCollection' && is_array($fragment['features'])){
				$ret['features'] += $fragment['features'];
			} else if($fragment['type'] == 'Feature') {
				$ret['features'][] = $fragment;
			}
		}

		if(empty($ret['features'])){
			return false;
		}

		return json_encode($ret);
	}

	/**
	 * We're going to support single GeoJSON features and FeatureCollections in either string, object or array format
	 */
	function metaval_to_geom($metaval = ''){
		// Let other plugins support non GeoJSON geometry
		$maybe_geom = apply_filters('wpgq_metaval_to_geom', $metaval);
		if($this->is_geom($maybe_geom)){
			return $maybe_geom;
		}

		// Exit early if we're a non-GeoJSON string
		if(is_string($metaval)){
		   	if(strpos($metaval,'{') === FALSE || strpos($metaval,'Feature') === FALSE || strpos($metaval,'geometry') === FALSE){
				return false;
			} else {
				$metaval = json_decode($metaval,true);
			}
		}

		// If it's an object, cast it to an array for consistancy
		if(is_object($metaval)){
			$metaval = (array)$metaval;
		}

		$metaval = $this->merge_geojson($metaval);

		if($metaval === false){
			return;
		}

		// Convert GeoJSON to WKT
		try {
			$geom = $this->geojson->read((string)$metaval);
			if(is_null($geom)){
				return false;
			}
		} catch (Exception $e){
			return false;
		}

		$wkt = new wkt();
		try {
			return $this->geowkt->write($geom);
		} catch (Exception $e){
			return false;
		}
	}

	/**
	 * Check if a value is in WKT, which is our DB-ready format
	 *
	 * @return bool
	 */
	function is_geom($maybe_geom){
		try {
			$what = $this->geowkt->read((string)$maybe_geom);
			if($what !== null){
				return true;
			} else {
				return false;
			}
		} Catch (Exception $e) {
			return false;
		}
	}


	function get_capabilities() {
		global $wpdb;

		$geom1 = 'POINT (25 15)';
		$geom2 = 'POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))';

		$suppress = $wpdb->suppress_errors(true);
		$errors = $wpdb->show_errors(false);

		foreach($this->known_spatial_functions['two_geoms_return_bool'] as $func){
			$q = "SELECT $func(GeomFromText(%s,%d),GeomFromText(%s,%d)) AS worked";
			$sql = $wpdb->prepare($q,array($geom1,$this->srid,$geom2,$this->srid));
			if($wpdb->query($sql) !== false){
				$this->supported_spatial_functions['two_geoms_return_bool'][] = $func;	
			}
		}

		// Re set the error settings
		$wpdb->suppress_errors($suppress);
		$wpdb->show_errors($errors);

		print_r($this->supported_spatial_functions['two_geoms_return_bool']);
	}
}
