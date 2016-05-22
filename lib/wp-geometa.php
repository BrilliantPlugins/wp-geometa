<?php
/*

TODO: write wrappers for get_post_meta which lets users
st_buffer and such when fetching geo data

Note: ST_Buffer etc. going to work well with EPSG:4326. Maybe we should have get_geo_post_meta
use ST_Transform(ST_Buffer(ST_Transform(%geometry%,/some UTM code/),/distance/),4326)

get_geo_post_meta should return GeoJSON

*/

class WP_GeoMeta {
	// A GeoJSON and WKT reader/write (GeoPHP classes);
	public $geojson;
	public $geowkt; 



	public $meta_types = array('comment','post','user'); // Missing site and term meta
	public $meta_actions = array('added','updated','deleted'); // We can ignore get, since we would just return the GeoJSON anyways

	private $srid = 4326;

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
			"ALTER TABLE {$wpdb->termmeta}_geo ADD SPATIAL INDEX(meta_value);",
			"ALTER TABLE {$wpdb->prefix}wpgq_utm ADD SPATIAL INDEX(geom);",
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

		CREATE TABLE {$wpdb->prefix}wpgq_utm (
		id bigint(20) unsigned NOT NULL auto_increment,
		epsg bigint(20) unsigned NOT NULL default '0',
		geom GEOMETRYCOLLECTION NOT NULL,
		PRIMARY KEY  (id)
		) ENGINE MyISAM $charset_collate;
		";

		// TODO: dbDelta has a problem with SPATIAL INDEX
		dbDelta( $geotables );

		foreach($indexes as $index){
			$wpdb->query($index);
		}

		// Load UTM Zones
		$wpdb->query("TRUNCATE {$wpdb->prefix}wpgq_utm");
		$this->load_utm_data();
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

		// Ok, we've got an array, sniff it to see if it smells like GoeJSON
		//if(is_array($metaval)){
		//	if(
		//		!array_key_exists('type',$metaval) || 
		//		($metaval['type'] != 'FeatureCollection' && $metaval['type'] != 'Feature') ||
		//		(!is_array($metaval['geometry']) && !is_array($metaval['features']))
		//	){
		//		return false;
		//	}
		//}

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
	 * @param as many geojson or geojson fragments as you want
	 *
	 * @return A FeatureCollection GeoJSON array
	 */
	static function merge_geojson(){
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
}
