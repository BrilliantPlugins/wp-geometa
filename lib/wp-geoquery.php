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
		add_action( 'pre_get_posts', array($this,'pre_get_posts'));
		add_action( 'get_meta_sql', array($this,'get_meta_sql'),10,6);
	}

	/**
	 * Turn our geo_meta queries into meta_query objects
	 * Also, cache the subquery in this object so we can 
	 */
	function pre_get_posts($query){
		if(!is_array($query->query['geo_meta'])){
			return;
		}

		if(!is_array($query->meta_query)){
			$query->meta_query = array();
		}

		$newMeta = array();

		$meta_query = $query->get('meta_query');

		/**
		 * For each geo_meta we'll construct a meta_query which 
		 * will join the meta_geo table to the meta table 
		 */
		foreach($query->query['geo_meta'] as $geo_meta){
			$geometry = $this->metaval_to_geom($geo_meta['value']);

			if($geometry === false){
				continue;
			}

			$uniqid = uniqid('geoquery-');
			$subquery = "(SELECT CAST(meta.meta_value AS CHAR) FROM wp_postmeta_geo geo , wp_postmeta meta WHERE {$geo_meta['compare']}(geo.meta_value,ST_GeomFromText('{$geometry}'," . $this->srid . ")) AND geo.fk_meta_id=meta.meta_id AND '$uniqid'='$uniqid')";

			$this->query_cache[$uniqid] = $subquery;

			$meta_query[] = array(array(
				'key' => $geo_meta['key'],
				'compare' => 'in',
				'value' => array($subquery) // Wrap in an array so it doesn't get implode("','",explode(' '))'ed
			));
		}

		$query->set('meta_query', $meta_query);
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
