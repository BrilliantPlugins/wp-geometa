<?php

/**
 * This class handles query interception and
 * modification in order to handle geo queries
 */

require_once(__DIR__ . '/wp-geoutil.php');
class WP_GeoQuery extends WP_GeoUtil {

	/**
	 * We need to track query string replacements across
	 * two callbacks so we can make the spatial query stuff work
	 */
	public $placeholders = array();

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
	 * Set up the filters that will listen to meta being added and removed
	 */
	function setup_filters() {
		global $wpdb;
		$version = $wpdb->db_version();
		if(version_compare('5.4.0',$wpdb->db_version(),'>=')){

		} else if(version_compare('5.6.1',$wpdb->db_version(),'>=')){

		} else if(version_compare('5.7',$wpdb->db_version(),'>=')){

		} else {

		}

		add_action( 'get_meta_sql', array($this,'get_meta_sql'),10,6);
	}

	/**
	 * WP_Metq_Query will default to using '=' if it doesn't recognize the compare operator
	 * So we're going to look for any cases where the meta compare operator is in our list of
	 * known spatial comparisons, then build a compare string with =, which we'll look for
	 * and replace with our actual spatial query
	 */
	function get_meta_sql($sql,$queries,$type,$primary_table,$primary_id_column,$context,$depth = 0) {
		global $wpdb;

		$metatable = _get_meta_table( $type );
		$geotable = $metatable . '_geo';

		if($depth > 0){
			$metatable = 'mt' . $depth;
		}

		$meta_key = 'meta_id';
		if($type == 'user') {
			$meta_key = 'umeta_id';
		}
		
		$conditions = array();
		foreach($queries as $k => $meta_query){
			if(!is_array($meta_query)){
				continue;
			}

			// not a first-order clause
			if(!array_key_exists('key',$meta_query) && !array_key_exists('value',$meta_query)){
				$sql = $this->get_meta_sql($sql,$meta_query,$type,$primary_table,$primary_id_column,$context, $depth + 1);
			}

			if(!in_array(strtolower($meta_query['compare']),$this->get_capabilities())){
				continue;
			}

			$geometry = $this->metaval_to_geom($meta_query['value']);

			if(empty($geometry)){
				continue;
			}

			$search_string = "( $metatable.meta_key = %s AND CAST($metatable.meta_value AS CHAR) = %s )";
			$search_string = $wpdb->prepare($search_string,array($meta_query['key'],$meta_query['value']));

			$replace_string = "( $metatable.$meta_key IN ( SELECT fk_meta_id FROM {$geotable} WHERE (meta_key=%s AND {$meta_query['compare']}($geotable.meta_value,GeomFromText(%s,%d))) ) )";
			$replace_string = $wpdb->prepare($replace_string,array($meta_query['key'],$geometry,$this->srid));

			$sql['where'] = str_replace($search_string, $replace_string, $sql['where']);
		}

		return $sql;
	}
}
