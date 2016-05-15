<?php

/**
 * WP-Geo enables Geo data for WordPress
 *
 * Plugin Name: WP-Geo
 * Author: Michael Moore
 * Author URI: http://cimbura.com
 * Version: 0.0.1
 */



class WP_Geo {

	var $meta_types = array('comment','post','user'); // Missing site and term meta
	var $meta_actions = array('add','update','delete'); // We can ignore get, since we would just return the GeoJSON anyways

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
	}

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
		meta_key varchar(255) default NULL,
		meta_value GEOMETRYCOLLECTION NOT NULL,
		PRIMARY KEY  (meta_id),
		KEY post_id (post_id),
		KEY meta_key (meta_key($max_index_length))
		) ENGINE=MyISAM $charset_collate;

		CREATE TABLE {$wpdb->commentmeta}_geo (
		meta_id bigint(20) unsigned NOT NULL auto_increment,
		comment_id bigint(20) unsigned NOT NULL default '0',
		meta_key varchar(255) default NULL,
		meta_value GEOMETRYCOLLECTION NOT NULL,
		PRIMARY KEY  (meta_id),
		KEY comment_id (comment_id),
		KEY meta_key (meta_key($max_index_length))
		) ENGINE=MyISAM $charset_collate;

		CREATE TABLE {$wpdb->termmeta}_geo (
		meta_id bigint(20) unsigned NOT NULL auto_increment,
		term_id bigint(20) unsigned NOT NULL default '0',
		meta_key varchar(255) default NULL,
		meta_value GEOMETRYCOLLECTION NOT NULL,
		PRIMARY KEY  (meta_id),
		KEY term_id (term_id),
		KEY meta_key (meta_key($max_index_length))
		) ENGINE=MyISAM $charset_collate;
		";

		/*
		* Not sure we want/need to support sitemeta at the moment
				if(!empty($wpdb->sitemeta)){
					$geotables .= "
					CREATE TABLE {$wpdb->sitemeta}_geo (
					meta_id bigint(20) NOT NULL auto_increment,
					site_id bigint(20) NOT NULL default '0',
					meta_key varchar(255) default NULL,
					meta_value GEOMETRYCOLLECTION NOT NULL,
					PRIMARY KEY  (meta_id), 
					KEY meta_key (meta_key($max_index_length)),
					KEY site_id (site_id)
					) ENGINE=MyISAM $charset_collate; 
					";

					$indexes[] = "ALTER TABLE {$wpdb->sitemeta}_geo ADD SPATIAL INDEX(meta_value);";
				}
		*/

		// TODO: dbDelta has a problem with SPATIAL INDEX
		dbDelta( $geotables );

		foreach($indexes as $index){
			$wpdb->query($index);
		}
	}

	function setup_filters() {
		foreach($this->meta_types as $type){
			foreach($this->meta_actions as $action){
				//         do_action( "add_{$meta_type}_meta",    $object_id, $meta_key, $_meta_value );
				//         do_action( "update_{$meta_type}_meta", $meta_id, $object_id, $meta_key, $_meta_value );
				//         do_action( "delete_{$meta_type}_meta", $meta_ids, $object_id, $meta_key, $_meta_value );
				add_action( "{$action}_{$type}_meta", array($this,"{$action}_{$type}_meta"),10,4); // We always ask for four, but will get three in the add case
			}
		}
	}

	function __call($name,$arguments) {
		if(preg_match('/^(' . implode('|',$this->meta_actions) . ')_(' . implode('|',$this->meta_types) . ')_meta$/',$name,$matches)){
			$action = $matches[1];
			$type = $matches[2];

			if($action == 'add'){
				$geometry = $this->geojson_to_wkt($arguments[2]);
				if($geometry){
					$arguments[2] = $geometry;
				}
			} else if ($action == 'update'){
				$geometry = $this->geojson_to_wkt($arguments[3]);
				if($geometry){
					$arguments[3] = $geometry;
				}
			} else if($action == 'delete'){
				$geometry == false;
			}

			if($geometry){
				array_unshift($arguments,$type);
				call_user_func_array(array($this,"{$action}_meta"),$arguments);
			}
		}
	}

	function geojson_to_wkt($geojson_maybe = ''){

	}

	function add_meta($target,$object_id,$meta_key,$meta_value){

	}

	function update_meta($target,$meta_id,$object_id,$meta_key,$meta_value){

	}

	function delete_meta($target,$meta_ids,$object_id,$meta_key,$meta_value){

	}
}

register_activation_hook(__FILE__, 'activate_wp_brilliant_geo');
function activate_wp_brilliant_geo(){
	$wpgeo = WP_Geo::get_instance();
	$wpgeo->create_geo_table();
}

$wpgeo = WP_Geo::get_instance();
