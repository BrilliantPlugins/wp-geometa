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

	public function create_geo_table() {
		global $wpdb;

		$a = 1 + 1;
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

		print $geotables . "\n\n";

		print_r($indexes);

		// TODO: dbDelta has a problem with SPATIAL INDEX
		dbDelta( $geotables );

		foreach($indexes as $index){
			$wpdb->query($index);
		}
	}
}

register_activation_hook(__FILE__, 'activate_wp_brilliant_geo');
function activate_wp_brilliant_geo(){
	$wpgeo = WP_Geo::get_instance();
	$wpgeo->create_geo_table();
}
