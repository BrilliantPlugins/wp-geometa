<?php
/**
 * This class handles creating spatial tables saving geo metadata
 *
 * @package wp-geometa
 * @link https://github.com/cimburadotcom/WP-GeoMeta
 * @author Michael Moore / michael_m@cimbura.com / https://profiles.wordpress.org/stuporglue/
 * @copyright Cimbura.com, 2016
 * @license GNU GPL v2
 */

defined( 'ABSPATH' ) or die( 'No direct access' );

/**
 * This class uses GeoUtil
 */
require_once( dirname( __FILE__ ) . '/wp-geoutil.php' );

/**
 * WP_GeoMeta is responsible for detecting when the user
 * saves GeoJSON and adding a spatial version to the meta_geo
 * tables
 */
class WP_GeoMeta {
	/**
	 * Seems like if we call dbDelta twice in rapid succession then we end up
	 * with a MySQL error, at least on MySQL 5.5. Other versions untested.
	 *
	 * This gets set to true after calling create_geo_tables the first time
	 * which prevents it from running again.
	 *
	 * @var $create_geo_tables_called
	 */
	private $create_geo_tables_called = false;

	/**
	 * What kind of meta are we handling?
	 *
	 * @var $meta_types
	 *
	 * @note Still missing sitemeta
	 *
	 * We could use pre_get_posts, pre_get_comments, pre_get_terms, pre_get_users
	 */
	public $meta_types = array( 'comment','post','term','user' );

	/**
	 * What kind of meta actions are we handling?
	 *
	 * @var $meta_actions
	 *
	 * @note We can ignore get, since we would just return the GeoJSON anyways
	 */
	public $meta_actions = array( 'added','updated','deleted' );

	/**
	 * Singleton variable
	 *
	 * @var $_instance
	 */
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
	 * Set up our filters
	 */
	protected function __construct() {
		define( 'WP_GEOMETA_HOME', dirname( dirname( __FILE__ ) ) );

		foreach ( $this->meta_types as $type ) {
			foreach ( $this->meta_actions as $action ) {
				add_action( "{$action}_{$type}_meta", array( $this, "{$action}_{$type}_meta" ),10,4 );
			}
		}
	}

	/**
	 * Run SQL to create geo tables
	 *
	 * @param bool $force Should we force re-creation.
	 */
	public function create_geo_tables( $force = false ) {
		if ( $this->create_geo_tables_called && ! $force ) {
			return;
		} else {
			$this->create_geo_tables_called = true;
		}

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$charset_collate = $wpdb->get_charset_collate();
		$max_index_length = 191;

		$drop_indexes = array();
		$add_indexes = array();

		foreach ( $this->meta_types as $type ) {
			$drop_indexes[] = 'DROP INDEX meta_val_spatial_idx ON ' . _get_meta_table( $type ) . '_geo';
			$add_indexes[] = 'CREATE SPATIAL INDEX meta_val_spatial_idx ON ' . _get_meta_table( $type ) . '_geo (meta_value);';
		}

		/*
			Only MyISAM supports spatial indexes, at least in MySQL older versions.
			Spatial indexes can only contain non-null columns.
		 */
		$geotables = 'CREATE TABLE ' . _get_meta_table( 'post' ) . "_geo (
		meta_id bigint(20) unsigned NOT NULL auto_increment,
		post_id bigint(20) unsigned NOT NULL default '0',
		fk_meta_id bigint(20) unsigned NOT NULL default '0',
		meta_key varchar(255) default NULL,
		meta_value geometrycollection NOT NULL,
		PRIMARY KEY  (meta_id),
		KEY post_id (post_id),
		KEY fk_meta_id (fk_meta_id),
		KEY meta_key (meta_key($max_index_length))
		) ENGINE=MyISAM $charset_collate;

		CREATE TABLE " . _get_meta_table( 'comment' ) . "_geo (
		meta_id bigint(20) unsigned NOT NULL auto_increment,
		comment_id bigint(20) unsigned NOT NULL default '0',
		fk_meta_id bigint(20) unsigned NOT NULL default '0',
		meta_key varchar(255) default NULL,
		meta_value geometrycollection NOT NULL,
		PRIMARY KEY  (meta_id),
		KEY comment_id (comment_id),
		KEY fk_meta_id (fk_meta_id),
		KEY meta_key (meta_key($max_index_length))
		) ENGINE=MyISAM $charset_collate;

		CREATE TABLE " . _get_meta_table( 'term' ) . "_geo (
		meta_id bigint(20) unsigned NOT NULL auto_increment,
		term_id bigint(20) unsigned NOT NULL default '0',
		fk_meta_id bigint(20) unsigned NOT NULL default '0',
		meta_key varchar(255) default NULL,
		meta_value geometrycollection NOT NULL,
		PRIMARY KEY  (meta_id),
		KEY term_id (term_id),
		KEY fk_meta_id (fk_meta_id),
		KEY meta_key (meta_key($max_index_length))
		) ENGINE=MyISAM $charset_collate;

		CREATE TABLE " . _get_meta_table( 'user' ) . "_geo (
		umeta_id bigint(20) unsigned NOT NULL auto_increment,
		user_id bigint(20) unsigned NOT NULL default '0',
		fk_meta_id bigint(20) unsigned NOT NULL default '0',
		meta_key varchar(255) default NULL,
		meta_value geometrycollection NOT NULL,
		PRIMARY KEY  (umeta_id),
		KEY user_id (user_id),
		KEY fk_meta_id (fk_meta_id),
		KEY meta_key (meta_key($max_index_length))
		) ENGINE=MyISAM $charset_collate;
		";

		/*
		Pre WP 4.6, dbDelta had a problem with SPATIAL INDEX, so we run those separate.
		https://core.trac.wordpress.org/ticket/36948

		Once WP 4.6 is out we can revisit this.
		 */
		dbDelta( $geotables );

		$suppress = $wpdb->suppress_errors( true );
		$errors = $wpdb->show_errors( false );

		foreach ( $drop_indexes as $index ) {
			$wpdb->query( $index ); // @codingStandardsIgnoreLine
		}

		$wpdb->suppress_errors( $suppress );
		$wpdb->show_errors( $errors );

		foreach ( $add_indexes as $index ) {
			// @codingStandardsIgnoreStart
			$wpdb->query( $index );
			// @codingStandardsIgnoreEnd
		}
	}

	/**
	 * Un-create the geo tables
	 */
	public function uninstall() {
		global $wpdb;

		$suppress = $wpdb->suppress_errors( true );
		$errors = $wpdb->show_errors( false );

		foreach ( $this->meta_types as $type ) {
			$drop = 'DROP TABLE ' . _get_meta_table( $type ) . '_geo';
			$wpdb->query( $drop ); // @codingStandardsIgnoreLine
		}

		$wpdb->suppress_errors( $suppress );
		$wpdb->show_errors( $errors );
	}

	/**
	 * Truncate the geo tables
	 */
	public function truncate_tables() {
		global $wpdb;

		$suppress = $wpdb->suppress_errors( true );
		$errors = $wpdb->show_errors( false );

		foreach ( $this->meta_types as $type ) {
			$drop = 'TRUNCATE TABLE ' . _get_meta_table( $type ) . '_geo';
			$wpdb->query( $drop ); // @codingStandardsIgnoreLine
		}

		$wpdb->suppress_errors( $suppress );
		$wpdb->show_errors( $errors );
	}

	/**
	 * Handle all the variations of add/update/delete post/user/comment
	 *
	 * @param String $name The name of the function we're asking for.
	 * @param Mixed  $arguments All the function arguments.
	 */
	public function __call( $name, $arguments ) {
		$parts = explode( '_', $name );
		if ( count( $parts ) !== 3 ) {
			return;
		}

		$action = $parts[0];
		$type = $parts[1];
		$meta_value = $parts[2];

		if ( ! in_array( $action, $this->meta_actions, true ) || ! in_array( $type, $this->meta_types, true ) ) {
			return;
		}

		if ( 'deleted' === $action ) {
			$geometry = false;
		} else {
			$geometry = WP_GeoUtil::metaval_to_geom( $arguments[3] );
			$arguments[3] = $geometry;
		}

		if ( $geometry || 'deleted' === $action ) {
			array_unshift( $arguments,$type );
			return call_user_func_array( array( $this, "{$action}_meta" ), $arguments );
		}
	}

	/**
	 * Callback for adding meta
	 *
	 * @param string $meta_type The type of meta we are targeting.
	 * @param int    $meta_id The ID of the non-geo meta object which was just saved.
	 * @param int    $object_id The ID of the object the meta is for.
	 * @param mixed  $meta_key The key for this metadata pair.
	 * @param mixed  $meta_value The value for this metadata pair.
	 */
	private function added_meta( $meta_type, $meta_id, $object_id, $meta_key, $meta_value ) {
		global $wpdb;

		if ( ! $meta_type || ! $meta_key || ! is_numeric( $object_id ) ) {
			return false;
		}

		$object_id = absint( $object_id );
		if ( ! $object_id ) {
			return false;
		}

		$table = _get_meta_table( $meta_type );
		if ( ! $table ) {
			return false;
		}

		$table .= '_geo';

		// @codingStandardsIgnoreStart
		$sql = $wpdb->prepare(
				"INSERT INTO $table 
				(
					{$meta_type}_id,
					fk_meta_id,
					meta_key,
					meta_value
				) VALUES (
					%d,
					%d,
					%s,
					GeomFromText(%s,%d)
				)",
				array(
					$object_id,
					$meta_id,
					$meta_key,
					$meta_value, 
					WP_GeoUtil::get_srid(),
				)
			);
		$result = $wpdb->query( 
			$wpdb->prepare(
				"INSERT INTO $table 
				(
					{$meta_type}_id,
					fk_meta_id,
					meta_key,
					meta_value
				) VALUES (
					%d,
					%d,
					%s,
					GeomFromText(%s,%d)
				)",
				array(
					$object_id,
					$meta_id,
					$meta_key,
					$meta_value, 
					WP_GeoUtil::get_srid(),
				)
			)
		);
		// @codingStandardsIgnoreEnd

		if ( ! $result ) {
			return false;
		}

		$mid = (int) $wpdb->insert_id;

		wp_cache_delete( $object_id, $meta_type . '_metageo' );

		return $mid;
	}

	/**
	 * Callback for updating meta
	 *
	 * @param string $meta_type The type of meta we are targeting.
	 * @param int    $meta_id The ID of the non-geo meta object which was just saved.
	 * @param int    $object_id The ID of the object the meta is for.
	 * @param mixed  $meta_key The key for this metadata pair.
	 * @param mixed  $meta_value The value for this metadata pair.
	 */
	private function updated_meta( $meta_type, $meta_id, $object_id, $meta_key, $meta_value ) {
		global $wpdb;

		if ( ! $meta_type || ! $meta_key || ! is_numeric( $object_id ) ) {
			return false;
		}

		$object_id = absint( $object_id );
		if ( ! $object_id ) {
			return false;
		}

		$table = _get_meta_table( $meta_type );
		if ( ! $table ) {
			return false;
		}

		$table .= '_geo';

		$column = sanitize_key( $meta_type . '_id' );
		$id_column = 'user' === $meta_type ? 'umeta_id' : 'meta_id';

		$q = "UPDATE $table SET meta_value=GeomFromText(%s,%d) WHERE fk_meta_id=(%d)";
		$sql = $wpdb->prepare( $q,array( $meta_value, WP_GeoUtil::get_srid(), $meta_id ) ); // @codingStandardsIgnoreLine 
		$count = $wpdb->query( $sql );

		if ( ! $count ) {
			return false;
		}

		wp_cache_delete( $object_id, $meta_type . '_metageo' );

		return true;
	}

	/**
	 * Callback for deleting meta
	 *
	 * @param string $meta_type The type of meta we are targeting.
	 * @param int    $meta_ids The ID of the non-geo meta object which was just saved.
	 * @param int    $object_id The ID of the object the meta is for.
	 * @param mixed  $meta_key The key for this metadata pair.
	 * @param mixed  $meta_value The value for this metadata pair.
	 */
	private function deleted_meta( $meta_type, $meta_ids, $object_id, $meta_key, $meta_value ) {
		global $wpdb;

		if ( ! $meta_type || ! $meta_key || ! is_numeric( $object_id ) && ! $delete_all ) {
			return false;
		}

		$object_id = absint( $object_id );
		if ( ! $object_id && ! $delete_all ) {
			return false;
		}

		$table = _get_meta_table( $meta_type );
		if ( ! $table ) {
			return false;
		}

		$table .= '_geo';

		$type_column = sanitize_key( $meta_type . '_id' );
		$id_column = 'user' === $meta_type ? 'umeta_id' : 'meta_id';

		$meta_ids = array_map( 'intval', $meta_ids );

		$sql = "DELETE FROM $table WHERE fk_meta_id IN (" . implode( ',',$meta_ids ) . ')';

		$count = $wpdb->query( $sql ); // @codingStandardsIgnoreLine

		if ( ! $count ) {
			return false;
		}

		wp_cache_delete( $object_id, $meta_type . '_metageo' );

		return true;
	}

	/**
	 * Repopulate
	 */
	public function populate_geo_tables() {
		global $wpdb;

		foreach ( $this->meta_types as $meta_type ) {
			$metatable = _get_meta_table( $meta_type );
			$geotable = $metatable . '_geo';

			$meta_pkey = 'meta_id';
			if ( 'user' === $meta_type  ) {
				$meta_pkey = 'umeta_id';
			}

			$maxid = -1;
			do {
				$q = "SELECT $metatable.* 
					FROM $metatable 
					LEFT JOIN {$metatable}_geo ON ({$metatable}_geo.fk_meta_id = $metatable.$meta_pkey )
					WHERE $metatable.meta_value LIKE '%{%Feature%geometry%}%' 
					AND {$metatable}_geo.fk_meta_id IS NULL
					AND $metatable.$meta_pkey > $maxid 
					ORDER BY $metatable.$meta_pkey
					LIMIT 100";

				$res = $wpdb->get_results( $q,ARRAY_A ); // @codingStandardsIgnoreLine
				$found_rows = count( $res );

				foreach ( $res as $row ) {
					$geometry = WP_GeoUtil::metaval_to_geom( $row['meta_value'] );
					if ( $geometry ) {
						$this->added_meta( $meta_type,$row[ $meta_pkey ],$row[ $meta_type . '_id' ],$row['meta_key'],$geometry );
					}
					$maxid = $row[ $meta_pkey ];
				}
			} while ($found_rows);
		}
	}
}
