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
	 * Keep track of our lat/lng fields
	 *
	 * @var $latlngs
	 */
	private static $latlngs = array();

	/**
	 * Track just the lat/lng names so we can quickly check if we're processing a
	 *
	 * @var $latlngs_index
	 */
	private static $latlngs_index = array();


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

		add_filter( 'wpgm_pre_metaval_to_geom', array( $this, 'handle_latlng_meta' ), 10, 2 );
		add_filter( 'wpgm_populate_geo_tables', array( $this, 'populate_latlng_geo' ) );
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
		UNIQUE KEY fk_meta_id (fk_meta_id),
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
		UNIQUE KEY fk_meta_id (fk_meta_id),
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
		UNIQUE KEY fk_meta_id (fk_meta_id),
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
		UNIQUE KEY fk_meta_id (fk_meta_id),
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
	 * Truncate the geo tables.
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

		if ( ! in_array( $action, $this->meta_actions, true ) || ! in_array( $type, $this->meta_types, true ) ) {
			return;
		}

		if ( 'deleted' === $action ) {
			$geometry = false;
		} else {
			$arguments = apply_filters( 'wpgm_pre_metaval_to_geom', $arguments, $type );
			$geometry = WP_GeoUtil::metaval_to_geom( $arguments[3] );
			$arguments[3] = $geometry;
		}

		if ( 'deleted' === $action ) {
			array_unshift( $arguments,$type );
			return call_user_func_array( array( $this, 'deleted_meta' ), $arguments );
		} else if ( $geometry ) {
			array_unshift( $arguments,$type );
			return call_user_func_array( array( $this, 'upsert_meta' ), $arguments );
		}
	}

	/**
	 * Callback for adding or updating meta
	 *
	 * @param string $meta_type The type of meta we are targeting.
	 * @param int    $meta_id The ID of the non-geo meta object which was just saved.
	 * @param int    $object_id The ID of the object the meta is for.
	 * @param mixed  $meta_key The key for this metadata pair.
	 * @param mixed  $meta_value The value for this metadata pair.
	 *
	 * The function uses INSERT ... ON DUPLICATE KEY UPDATE so it handles meta added and updated cases.
	 */
	private function upsert_meta( $meta_type, $meta_id, $object_id, $meta_key, $meta_value ) {
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
				) ON DUPLICATE KEY UPDATE meta_value=GeomFromText(%s,%d)",
				array(
					$object_id,
					$meta_id,
					$meta_key,
					$meta_value,
					WP_GeoUtil::get_srid(),
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
	 * Repopulate the geometa tables based on the non-geo meta rows that hold GeoJSON.
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
					WHERE 
						( $metatable.meta_value LIKE '{%{%Feature%geometry%}%' -- By using a leading { we can get some small advantage from MySQL indexes
						OR $metatable.meta_value LIKE 'a:%{%Feature%geometry%}%' -- But we also need to handle serialized GeoJSON arrays
						)
					AND {$metatable}_geo.fk_meta_id IS NULL
					AND $metatable.$meta_pkey > $maxid 
					ORDER BY $metatable.$meta_pkey
					LIMIT 100";

				$res = $wpdb->get_results( $q,ARRAY_A ); // @codingStandardsIgnoreLine
				$found_rows = count( $res );

				foreach ( $res as $row ) {
					$geometry = WP_GeoUtil::metaval_to_geom( $row['meta_value'] );
					if ( $geometry ) {
						$this->upsert_meta( $meta_type,$row[ $meta_pkey ],$row[ $meta_type . '_id' ],$row['meta_key'],$geometry );
					}
					$maxid = $row[ $meta_pkey ];
				}
			} while ($found_rows);
		}

		do_action( 'wpgm_populate_geo_tables' );
	}

	/**
	 * Add the names of latitude and longitude fields which will be coerced into a Point GeoJSON representation automatically
	 *
	 * @param string $latitude_name The name of the latitude meta field.
	 * @param string $longitude_name The name of the longitude meta field.
	 * @param string $geojson_name The name of the geojson meta field to put in the meta table.
	 */
	public function add_latlng_field( $latitude_name, $longitude_name, $geojson_name ) {
		$idx = count( WP_GeoMeta::$latlngs );
		WP_GeoMeta::$latlngs[] = array(
			'lat' => $latitude_name,
			'lng' => $longitude_name,
			'geo' => $geojson_name,
		);

		WP_GeoMeta::$latlngs_index[ $latitude_name ] = WP_GeoMeta::$latlngs[ $idx ];
		WP_GeoMeta::$latlngs_index[ $longitude_name ] = WP_GeoMeta::$latlngs[ $idx ];
	}


	/**
	 * Handle lat/lng values from the WP Geodata standard: https://codex.wordpress.org/Geodata
	 *
	 * Any time geo_latitude or geo_longitude are saved to (eg.) wp_postmeta, this will run.
	 * We check if the other piece of the coordinate is present so we can make a coordinate pair
	 * then always modify the args so that we save a single value to the geometa table.
	 *
	 * The key we use in the geometa tables is 'geo_'.
	 *
	 * Since the value has already been saved to the regular postmeta table this won't mess with those values.
	 *
	 * @param array  $meta_args Array with the meta_id that was just saved, the object_id it was for, the meta_key and meta_values used.
	 * $meta_args[0] -- meta_id from insert
	 * $meta_args[1] -- object_id which this applies to 
	 * $meta_args[2] -- meta key
	 * $meta_args[3] -- the meta value
	 *
	 * @param string $object_type Which WP type is it? (comment/user/post/term).
	 */
	public static function handle_latlng_meta( $meta_args, $object_type ) {
		$object_id = $meta_args[1];
		$metakey = $meta_args[2];
		$metaval = $meta_args[3];

		// Quick return if the meta key isn't something we recognize as a lat or lng meta key.
		if ( ! array_key_exists( $metakey, WP_GeoMeta::$latlngs_index ) ) {
			return $meta_args;
		}

		$thepair = WP_GeoMeta::$latlngs_index[ $metakey ];

		$the_other_field = ( $thepair['lat'] === $metakey  ? $thepair['lng'] : $thepair['lat'] );

		$func = 'get_' . $object_type. '_meta';
		$the_other_value = $func( $object_id, $the_other_field, true );

		if ( empty( $the_other_value ) ) {
			return $meta_args;
		}

		if ( $thepair['lat'] === $metakey ) {
			$coordinates = array( $the_other_value, $metaval );
		} else {
			$coordinates = array( $metaval, $the_other_value );
		}

		$geojson = array(
			'type' => 'Feature',
			'geometry' => array(
				'type' => 'Point',
				'coordinates' => $coordinates,
			),
			'properties' => array(),
			);

		$meta_args[2] = $thepair['geo'];
		$meta_args[3] = wp_json_encode( $geojson );
		return $meta_args;
	}

	/**
	 * When WP_GeoMeta::populate_geo_tables() is called, an action will trigger this call.
	 *
	 * It gives us an opportunity to re-populate the meta table if needed.
	 */
	public function populate_latlng_geo() {
		global $wpdb;

		$latitude_fields = array();
		$longitude_fields = array();

		foreach ( WP_GeoMeta::$latlngs as $latlng ) {
			$latitude_fields[] = $latlng['lat'];
			$longitude_fields[] = $latlng['lng'];
		}

		if ( 0 === count( $latitude_fields ) ) {
			return;
		}

		$pmtables_range = range( 1, count( $latitude_fields ) - 1 );
		$pmtables = '`pm' . implode( '`.`meta_value`, pm', $pmtables_range ) . '.meta_value';

		/*
		  SELECT
		  pm.post_id,
		  pm.meta_key AS `lat`,
		  pm.meta_value AS `latval`,
		  COALESCE(pm1.meta_key, pm2.meta_key) AS `lng`,
		  COALESCE(pm1.meta_value, pm2.meta_value) AS `lngval`
          FROM
		  wp_postmeta pm
		  LEFT JOIN wp_postmeta pm1 ON ( pm.meta_key='latitude' AND pm1.meta_key='longitude' AND pm.post_id=pm1.post_id)
		  LEFT JOIN wp_postmeta pm2 ON ( pm.meta_key='thelat' AND pm2.meta_key='thelng' AND pm.post_id=pm2.post_id)

		  WHERE
		  pm.meta_key IN ('latitude', 'thelat')
		 */

		foreach ( $this->meta_types as $type ) {

			$meta_table = _get_meta_table( $type );

			$query = 'SELECT
				`pm`.`' . $type . '_id` AS `obj_id`,
				`pm`.`meta_value` AS `lat`,	
				COALESCE(' . $pmtables . ') AS `lng`
				FROM
				`' . $meta_table  . '` `pm` ';

			foreach ( $longitude_fields as $idx => $lng ) {
				$query .= "LEFT JOIN `$meta_table` `pm$idx` ON ( `pm`.`meta_key`='{$latitude_fields[ $idx ]}' AND `pm$idx`.`meta_key`='{$longitude_fields[ $idx ] }' AND `pm`.`{$type}_id`=`pm$idx`.`{$type}_id` )\n";
			}

			$query .= 'WHERE pm.meta_key IN (\'' . implode( "','", $latitude_fields ) . '\')';

			$res = $wpdb->get_results( $query, 'ARRAY_A' ); // @codingStandardsIgnoreLine

			$func = "updated_{$type}_meta";

			foreach ( $res as $row ) {
				$geojson = array(
					'type' => 'Feature',
					'geometry' => array(
						'type' => 'Point',
						'coordinates' => array( $row['lng'], $row['lat'] ),
					),
					'properties' => array(),
				);

				$this->$func( $func, 'updated', $type, $meta_key, $geojson );
			}
		}
	}
}
