<?php
/**
 * This class handles query interception and
 * modification in order to handle geo queries
 *
 * @package WP_GeoMeta
 */

require_once( __DIR__ . '/wp-geoutil.php' );

/**
 * WP_GeoQuery adds spatial query functionality to WP_Meta_Query, allowing
 * for spatial queries from within WP_Query, WP_User_Query, WP_Comment_Query and get_terms
 * when used in conjunction with WP_GeoMeta.
 */
class WP_GeoQuery extends WP_GeoUtil {

	/**
	 * We need to track query string replacements across
	 * two callbacks so we can make the spatial query stuff work
	 *
	 * @var $placeholders;
	 */
	public $placeholders = array();

	/**
	 * The singleton holder
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
	 * Set up the filters that will listen to meta being added and removed
	 */
	function setup_filters() {
		global $wpdb;
		add_action( 'get_meta_sql', array( $this, 'get_meta_sql' ),10,6 );
	}

	/**
	 * WP_Metq_Query will default to using '=' if it doesn't recognize the compare operator
	 * So we're going to look for any cases where the meta compare operator is in our list of
	 * known spatial comparisons, then build a compare string with =, which we'll look for
	 * and replace with our actual spatial query
	 *
	 * @param array  $clauses The query JOIN and WHERE clauses.
	 * @param array  $queries The array of meta queries.
	 * @param string $type The type of meta we're dealing with.
	 * @param string $primary_table The main table for the meta.
	 * @param string $primary_id_column The primary key for the main table.
	 * @param object $context The main query object.
	 * @param int    $depth How deep have we recursed.
	 */
	function get_meta_sql( $clauses, $queries, $type, $primary_table, $primary_id_column, $context, $depth = 0 ) {
		global $wpdb;

		$metatable = _get_meta_table( $type );

		if ( ! $table ) {
			return false;
		}

		$geotable = $metatable . '_geo';

		if ( $depth > 0 ) {
			$metatable = 'mt' . $depth;
		}

		$id_column = 'user' === $meta_type ? 'umeta_id' : 'meta_id';

		$conditions = array();
		foreach ( $queries as $k => $meta_query ) {
			if ( ! is_array( $meta_query ) ) {
				continue;
			}

			// Not a first-order clause. Recurse!
			if ( ! array_key_exists( 'key',$meta_query ) && ! array_key_exists( 'value',$meta_query ) ) {
				$clauses = $this->get_meta_sql( $clauses,$meta_query,$type,$primary_table,$primary_id_column,$context, $depth + 1 );
			}

			if ( ! in_array( strtolower( $meta_query['compare'] ),$this->get_capabilities() ) ) {
				continue;
			}

			$geometry = $this->metaval_to_geom( $meta_query['value'] );

			if ( empty( $geometry ) ) {
				continue;
			}

			$search_string = "( $metatable.id_column = %s AND CAST($metatable.meta_value AS CHAR) = %s )";
			$search_string = $wpdb->prepare( $search_string, array( $meta_query['key'], $meta_query['value'] ) ); // @codingStandardsIgnoreLine

			$replace_string = "( $metatable.$id_column IN ( SELECT fk_meta_id FROM {$geotable} WHERE (meta_key=%s AND {$meta_query['compare']}($geotable.meta_value,GeomFromText(%s,%d))) ) )";
			$replace_string = $wpdb->prepare( $replace_string,array( $meta_query['key'], $geometry, $this->srid ) ); // @codingStandardsIgnoreLine

			$clauses['where'] = str_replace( $search_string, $replace_string, $clauses['where'] );
		}

		return $clauses;
	}
}
