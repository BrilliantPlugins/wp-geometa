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
		add_filter( 'get_terms_args', array($this,'get_terms_args'),10,2);
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
		} else {
			return;
		}

		$query->query_vars['meta_query'] = $this->make_meta_query_from_geo_query( $query->query_vars['geo_meta'], $meta_queries, $metatable, $meta_pkey );
	}

	function get_terms_args($args, $taxonomies){
		global $wpdb;

		if(!is_array($args['geo_meta'])){
			return $args;
		}

		if(is_array($args['meta_query'])){
			$meta_queries = $args['meta_query'];
		} else {
			$meta_queries = array();
		}

		$metatable = $wpdb->termmeta;
		$meta_pkey = 'meta_id';

		$args['meta_query'] = $this->make_meta_query_from_geo_query($args['geo_meta'],$meta_queries,$metatable,$meta_pkey);
		return $args;
	}

	/**
	 * Take a geo_meta arg and turn it into a meta_query arg
	 *
	 * @param $geo_queries The geo_meta array
	 * @param $meta_queries The existing array of meta queries (may be an empty array)
	 * @param $metatable The tablename we're joining with
	 * @param $meta_pkey The meta table's primary key because wp_usermeta is special
	 *
	 * @return A modified meta_queries array which should be re inserted into the original query 
	 */
	function make_meta_query_from_geo_query($geo_queries,$meta_queries,$metatable,$meta_pkey){
		$geotable = $metatable . '_geo';

		/**
		 * For each geo_meta we'll construct a meta_query which 
		 * will join the meta_geo table to the meta table 
		 */
		foreach($geo_queries as $geo_meta){
			$geometry = $this->metaval_to_geom($geo_meta['value']);

			if($geometry === false){
				continue;
			}

			$uniqid = uniqid('geoquery-');

			// TODO: Make this a prepare
			// TODO: Whitelist compare
			$subquery = "(
				SELECT CAST(meta.meta_value AS CHAR) 
				FROM $geotable geo , $metatable meta 
				WHERE {$geo_meta['compare']}(geo.meta_value,ST_GeomFromText('{$geometry}'," . $this->srid . ")) 
				AND geo.fk_meta_id=meta.$meta_pkey AND '$uniqid'='$uniqid'
				)";

			$this->query_cache[$uniqid] = $subquery;

			$meta_queries[] = array(array(
				'key' => $geo_meta['key'],
				'compare' => 'in',
				'value' => array($subquery) // Wrap in an array so it doesn't get implode("','",explode(' '))'ed
			));
		}

		return $meta_queries;
	}

	/**
	 * WP_Metq_Query helpfully addslashes and single-quotes our subquery, so we're going to take it back now
	 */
	// TODO: Can we *only* build the sub-query here? 
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
