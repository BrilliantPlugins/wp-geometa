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
		add_action( 'pre_get_posts', array($this,'pre_get'));
		add_action( 'pre_get_users', array($this,'pre_get'));
		add_action( 'pre_get_comments', array($this,'pre_get'));
		add_action( 'get_meta_sql', array($this,'get_meta_sql'),10,6);
	}

	/**
	 * Turn our geo_meta queries into meta_query objects
	 * Also, cache the subquery in this object so we can 
	 */
	function pre_get($query){
		global $wpdb;

		if(!is_array($query->query_vars['geo_meta'])){
			return;
		}

		$newMeta = array();

		if(!($query->meta_query instanceof WP_Meta_Query)){
			$query->meta_query = new WP_Meta_Query();
		}

		$meta_queries = $query->meta_query->queries;
		if(!is_array($meta_queries)){
			$meta_queries = array();
		}


		// Find the right tablename
		$meta_pkey = 'meta_id';
		if($query instanceof WP_User_Query){
			$meta_pkey = 'umeta_id';
			$metatable = $wpdb->usermeta;
		} else if ($query instanceof WP_Query){
			$metatable = $wpdb->postmeta;
		} else if ($query instanceof WP_Comment_Query){
			$metatable = $wpdb->commentmeta;
		} else if (false){
			$metatable = $wpdb->termmeta;
		} else {
			return;
		}

		$geotable = $metatable . '_geo';


		/**
		 * For each geo_meta we'll construct a meta_query which 
		 * will join the meta_geo table to the meta table 
		 */
		foreach($query->query_vars['geo_meta'] as $geo_meta){
			$geometry = $this->metaval_to_geom($geo_meta['value']);

			if($geometry === false){
				continue;
			}

			$uniqid = uniqid('geoquery-');
			$subquery = "(SELECT CAST(meta.meta_value AS CHAR) FROM $geotable geo , $metatable meta WHERE {$geo_meta['compare']}(geo.meta_value,ST_GeomFromText('{$geometry}'," . $this->srid . ")) AND geo.fk_meta_id=meta.$meta_pkey AND '$uniqid'='$uniqid')";

			$this->query_cache[$uniqid] = $subquery;

			$meta_queries[] = array(array(
				'key' => $geo_meta['key'],
				'compare' => 'in',
				'value' => array($subquery) // Wrap in an array so it doesn't get implode("','",explode(' '))'ed
			));
		}

		$query->query_vars['meta_query'] = $meta_queries;
	}

	/**
	 * WP_Metq_Query helpfully addslashes and single-quotes our subquery, so we're going to take it back now
	 */
	function get_meta_sql($sql,$queries,$type,$primary_table,$primary_id_column,$context) {
		// Find our geoquery key again. Gotta love the quadrupal backslashes...
		if(preg_match("|\\\\'(geoquery-[^=]+)\\\\'=\\\\'\\1\\\\'|",$sql['where'],$matches)){
			$val = $this->query_cache[$matches[1]];
			$valslashed = addslashes($val);
			$sql['where'] = str_replace("('$valslashed')",$val,$sql['where']);
		}
		return $sql;
	}
}
