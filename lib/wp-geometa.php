<?php
/*
 * This class handles saving and fetching geo metadata
 *
 *
 *
TODO: write wrappers for get_post_meta which lets users
st_buffer and such when fetching geo data

Note: ST_Buffer etc. going to work well with EPSG:4326. Maybe we should have get_geo_post_meta
use ST_Transform(ST_Buffer(ST_Transform(%geometry%,/some UTM code/),/distance/),4326)

get_geo_post_meta should return GeoJSON
*/

class WP_GeoMeta extends WP_GeoUtil {
	// What kind of meta are we handling?
	public $meta_types = array('comment','post','term','user'); // Missing site and term meta

	// What kind of meta actions are we handling?
	public $meta_actions = array('added','updated','deleted'); // We can ignore get, since we would just return the GeoJSON anyways

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
			"ALTER TABLE {$wpdb->usermeta}_geo ADD SPATIAL INDEX(meta_value);",
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

		CREATE TABLE {$wpdb->usermeta}_geo (
		umeta_id bigint(20) unsigned NOT NULL auto_increment,
		user_id bigint(20) unsigned NOT NULL default '0',
		meta_key varchar(255) default NULL,
		meta_value GEOMETRYCOLLECTION NOT NULL,
		PRIMARY KEY  (umeta_id),
		KEY user_id (user_id),
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
		$drops[] = "DROP TABLE {$wpdb->usermeta}_geo";
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
	}

	/**
	 * Handle all the variations of add/update/delete post/user/comment
	 */
	function __call($name,$arguments) {
		$a = 1 + 1;
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
}
