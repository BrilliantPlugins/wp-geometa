<?php

/**
 * WP-GeoQuery enables Geo metadata for WordPress
 *
 * Plugin Name: WP-GeoQuery
 * Author: Michael Moore
 * Author URI: http://cimbura.com
 * Version: 0.0.1
 */
require_once(__DIR__ . '/geoPHP/geoPHP.inc');

class WP_GeoQuery {

	public $meta_types = array('comment','post','user'); // Missing site and term meta
	public $meta_actions = array('added','updated','deleted'); // We can ignore get, since we would just return the GeoJSON anyways

	// A GeoJSON and WKT reader/write (GeoPHP classes);
	public $geojson;
	public $geowkt; 

	private $srid = 4326;

	private static $_instance = null;

	public $placeholders = array();

	/**
	 * Get the singleton instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	function __construct(){
		$this->setup_filters();
		$this->geojson = new GeoJSON();
		$this->geowkt = new WKT();
	}

	/**
	 * Run SQL to create geo tables
	 */
	public function create_geo_table() {
		global $wpdb;

		// TODO: Missing Usermeta

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$charset_collate = $wpdb->get_charset_collate();
		$max_index_length = 191;

		// TODO: Check if indexes exist and don't re-add them
		$indexes = array(
			"ALTER TABLE {$wpdb->postmeta}_geo ADD SPATIAL INDEX(meta_value);",
			"ALTER TABLE {$wpdb->commentmeta}_geo ADD SPATIAL INDEX(meta_value);",
			"ALTER TABLE {$wpdb->termmeta}_geo ADD SPATIAL INDEX(meta_value);"
		);

		// Only MyISAM supports spatial indexes, at least in MySQL older versions
		// Spatial indexes can only contain non-null columns
		$geotables = "CREATE TABLE {$wpdb->postmeta}_geo (
		meta_id bigint(20) unsigned NOT NULL auto_increment,
		post_id bigint(20) unsigned NOT NULL default '0',
		fk_meta_id bigint(20) unsigned NOT NULL default '0',
		meta_key varchar(255) default NULL,
		meta_value GEOMETRYCOLLECTION NOT NULL,
		PRIMARY KEY  (meta_id),
		KEY post_id (post_id),
		KEY fk_meta_id (fk_meta_id),
		KEY meta_key (meta_key($max_index_length))
		) ENGINE=MyISAM $charset_collate;

		CREATE TABLE {$wpdb->commentmeta}_geo (
		meta_id bigint(20) unsigned NOT NULL auto_increment,
		comment_id bigint(20) unsigned NOT NULL default '0',
		fk_meta_id bigint(20) unsigned NOT NULL default '0',
		meta_key varchar(255) default NULL,
		meta_value GEOMETRYCOLLECTION NOT NULL,
		PRIMARY KEY  (meta_id),
		KEY comment_id (comment_id),
		KEY fk_meta_id (fk_meta_id),
		KEY meta_key (meta_key($max_index_length))
		) ENGINE=MyISAM $charset_collate;

		CREATE TABLE {$wpdb->termmeta}_geo (
		meta_id bigint(20) unsigned NOT NULL auto_increment,
		term_id bigint(20) unsigned NOT NULL default '0',
		fk_meta_id bigint(20) unsigned NOT NULL default '0',
		meta_key varchar(255) default NULL,
		meta_value GEOMETRYCOLLECTION NOT NULL,
		PRIMARY KEY  (meta_id),
		KEY term_id (term_id),
		KEY fk_meta_id (fk_meta_id),
		KEY meta_key (meta_key($max_index_length))
		) ENGINE=MyISAM $charset_collate;
		";

		// TODO: dbDelta has a problem with SPATIAL INDEX
		dbDelta( $geotables );

		foreach($indexes as $index){
			$wpdb->query($index);
		}
	}

	/**
	 * Un-create the geo tables
	 */
	public function uninstall() {
		global $wpdb;
		$drops[] = "DROP TABLE {$wpdb->postmeta}_geo";
		$drops[] = "DROP TABLE {$wpdb->commentmeta}_geo";
		$drops[] = "DROP TABLE {$wpdb->termmeta}_geo";
		foreach($drops as $drop){
			$wpdb->query($drop);
		}
	}

	/**
	 * Set up the filters that will listen to meta being added and removed
	 */
	function setup_filters() {
		foreach($this->meta_types as $type){
			foreach($this->meta_actions as $action){
				//         do_action( "added_{$meta_type}_meta",   $meta_id, $object_id, $meta_key, $_meta_value );
				//         do_action( "updated_{$meta_type}_meta", $meta_id, $object_id, $meta_key, $_meta_value );
				//         do_action( "delete_{$meta_type}_meta",  $meta_ids, $object_id, $meta_key, $_meta_value );
				add_action( "{$action}_{$type}_meta", array($this,"{$action}_{$type}_meta"),10,4); 
			}
		}

		add_action( 'pre_get_posts', array($this,'pre_get_posts'));
		add_action( 'get_meta_sql', array($this,'get_meta_sql'),10,6);
	}

	/**
	 * Handle all the variations of add/update/delete post/user/comment
	 */
	function __call($name,$arguments) {
		if(preg_match('/^(' . implode('|',$this->meta_actions) . ')_(' . implode('|',$this->meta_types) . ')_meta$/',$name,$matches)){
			$action = $matches[1];
			$type = $matches[2];

			if($action == 'deleted'){
				$geometry = false;
			} else {
				$geometry = $this->metaval_to_geom($arguments[3]);
				$arguments[3] = $geometry;
			}

			if($geometry || $action == 'deleted'){
				array_unshift($arguments,$type);
				return call_user_func_array(array($this,"{$action}_meta"),$arguments);
			}
		}
	}

	/**
	 * We're going to support single GeoJSON features and FeatureCollections in either string, object or array format
	 */
	function metaval_to_geom($metaval = ''){
		// Let other plugins support non GeoJSON geometry
		$maybe_geom = apply_filters('wpgq_metaval_to_geom', $metaval);
		if($maybe_geom !== $metaval && $this->is_geom($maybe_geom)){
			return $maybe_geom;
		}

		if(is_array($metaval) || is_object($metaval)){
			if(
				!(array_key_exists('type',(array)$metaval) && array_key_exists('geometry',(array)$metaval)) &&
				!(array_key_exists('FeatureCollection',(array)$metaval) && array_key_exists('features',(array)$metaval))
			) {
				return false;
			}

			$metaval = json_encode($metaval);		
		}

		if(!is_string($metaval)){
			return false;
		}

		if(strpos($metaval,'{') === FALSE || strpos($metaval,'Feature') === FALSE || strpos($metaval,'geometry') === FALSE){
			return false;
		}

		$metaval = $this->merge_geojson($metaval);

		// Convert GeoJSON to WKT
		try {
			$geom = $this->geojson->read($metaval);
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
			$this->geowkt->read($maybe_geom);
			return true;
		} Catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Callback for adding meta
	 */
	function added_meta($target,$meta_id,$object_id,$meta_key,$meta_value){
		global $wpdb;
		$q = "INSERT INTO {$wpdb->prefix}{$target}meta_geo ({$target}_id,fk_meta_id,meta_key,meta_value) VALUES ";
		$q .= " (%d,%d,%s,ST_GeomFromText(%s," . $this->srid . "))";

		$sql = $wpdb->prepare($q,array($object_id,$meta_id,$meta_key,$meta_value));

		return $wpdb->query($sql);
	}

	/**
	 * Callback for updating meta
	 */
	function updated_meta($target,$meta_id,$object_id,$meta_key,$meta_value){
		global $wpdb;
		$q = "UPDATE {$wpdb->prefix}{$target}meta_geo SET meta_value=ST_GeomFromText(%s," . $this->srid . ") WHERE fk_meta_id=(%d)";

		$sql = $wpdb->prepare($q,array($meta_value,$meta_id));

		return $wpdb->query($sql);
	}

	/**
	 * Callback for deleting meta
	 */
	function deleted_meta($target,$meta_ids,$object_id,$meta_key,$meta_value){
		global $wpdb;

		$meta_ids = (array) $meta_ids;

		$meta_ids = array_filter($meta_ids,'is_numeric');

		if(empty($meta_ids)) {
			return;
		}

		$sql = "DELETE FROM {$wpdb->prefix}{$target}meta_geo WHERE fk_meta_id IN (" . implode(',',$meta_ids) . ")";

		return $wpdb->query($sql);
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
	 * @return A FeatureCollection GeoJSON array
	 */
	static function merge_geojson($geojson){
		$fragments = func_get_args();
		array_unshift($geojson,$fragments);

		$ret = array(
			'type' => 'FeatureCollection',
			'features' => array()
		);

		foreach($fragments as $fragment){
			if(!is_string($fragment)){
				$fragment = json_encode($fragment);
			}

			$fragment = json_decode($fragment,TRUE);

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
	 * Turn our geo_query queries into meta_query objects
	 * Also, cache the subquery in this object so we can 
	 */
	function pre_get_posts($query){
		if(!is_array($query->query['geo_query'])){
			return;
		}

		if(!is_array($query->meta_query)){
			$query->meta_query = array();
		}

		$newMeta = array();

		$meta_query = $query->get('meta_query');

		/**
		 * For each geo_query we'll construct a meta_query which 
		 * will join the meta_geo table to the meta table 
		 */
		foreach($query->query['geo_query'] as $geo_query){
			$geometry = $this->metaval_to_geom($geo_query['value']);

			if($geometry === false){
				continue;
			}

			$uniqid = uniqid('geoquery-');
			$subquery = "(SELECT CAST(meta.meta_value AS CHAR) FROM wp_postmeta_geo geo , wp_postmeta meta WHERE {$geo_query['compare']}(geo.meta_value,ST_GeomFromText('{$geometry}'," . $this->srid . ")) AND geo.fk_meta_id=meta.meta_id AND '$uniqid'='$uniqid')";

			$this->query_cache[$uniqid] = $subquery;

			$meta_query[] = array(array(
				'geo_query_uuid' => $uniqid, // We're going to add this in so we can re-find our query in get_meta_sql so we can remove the quotes that WP_Meta_Query adds
				'key' => $geo_query['key'],
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

register_activation_hook(__FILE__, 'activate_wp_brilliant_geo');
function activate_wp_brilliant_geo(){
	$wpgeo = WP_GeoQuery::get_instance();
	$wpgeo->create_geo_table();
}

$wpgeo = WP_GeoQuery::get_instance();
