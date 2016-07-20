<?php
/**
 * This class handles query interception and
 * modification in order to handle geo queries
 *
 * @package WP_GeoMeta
 * @link https://github.com/cimburadotcom/WP_GeoMeta
 * @author Michael Moore / michael_m@cimbura.com / https://profiles.wordpress.org/stuporglue/
 * @copyright Cimbura.com, 2016
 * @license GNU GPL v2
 */

/**
 * This class extends GeoUtil
 */
require_once( dirname( __FILE__ ) . '/wp-geoutil.php' );

/**
 * WP_GeoQuery adds spatial query functionality to WP_Meta_Query, allowing
 * for spatial queries from within WP_Query, WP_User_Query, WP_Comment_Query and get_terms
 * when used in conjunction with WP_GeoMeta.
 */
class WP_GeoQuery {

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
	 * Set up our filters
	 */
	protected function __construct() {
		$this->setup_filters();
	}

	/**
	 * Set up the filters that will listen to meta being added and removed
	 */
	protected function setup_filters() {
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
	public function get_meta_sql( $clauses, $queries, $type, $primary_table, $primary_id_column, $context, $depth = 0 ) {
		global $wpdb;

		$metatable = _get_meta_table( $type );

		if ( ! $metatable ) {
			return false;
		}

		$geotable = $metatable . '_geo';

		if ( $depth > 0 ) {
			$metatable = 'mt' . $depth;
		}

		$id_column = 'user' === $type ? 'umeta_id' : 'meta_id';

		$conditions = array();
		foreach ( $queries as $k => $meta_query ) {
			if ( ! is_array( $meta_query ) ) {
				continue;
			}

			// Not a first-order clause. Recurse!
			if ( ! array_key_exists( 'key',$meta_query ) && ! array_key_exists( 'value',$meta_query ) ) {
				$clauses = $this->get_meta_sql( $clauses,$meta_query,$type,$primary_table,$primary_id_column,$context, $depth + 1 );
			}

			$meta_type = $context->meta_query->get_cast_for_type( $meta_query['type'] );

			// Is our compare a spatial compare? If, so, it has to be in our list of allowed compares
			if ( in_array( strtolower( $meta_query['compare'] ),WP_GeoUtil::get_capabilities(), true ) ) {

				// If we have a geometry for our value, then we're doing a two-geometry function that returns a boolean.
				$geometry = WP_GeoUtil::metaval_to_geom( $meta_query['value'] );
				if ( !empty( $geometry ) ) {

					$std_query = "( $metatable.meta_key = %s AND CAST($metatable.meta_value AS $meta_type) = %s )";
					$std_query = $wpdb->prepare( $std_query, array( $meta_query['key'], $meta_query['value'] ) ); // @codingStandardsIgnoreLine

					$geom_query = "( $metatable.$id_column IN ( SELECT fk_meta_id FROM {$geotable} WHERE (meta_key=%s AND {$meta_query['compare']}($geotable.meta_value,GeomFromText(%s,%d))) ) )";
					$geom_query = $wpdb->prepare( $geom_query, array( $meta_query['key'], $geometry, WP_GeoUtil::get_srid() ) ); // @codingStandardsIgnoreLine

				} else {

					// If we don't have a value, then our subquery gets written without parenthesis wraps
					// IDK why.

					$std_query = "  $metatable.meta_key = %s";
					$std_query = $wpdb->prepare( $std_query, array( $meta_query['key'] ) ); // @codingStandardsIgnoreLine

					// Otherwise we're doing a one geometry operation that returns a boolean.
					$geom_query = "( $metatable.$id_column IN ( SELECT fk_meta_id FROM {$geotable} WHERE (meta_key=%s AND {$meta_query['compare']}($geotable.meta_value)) ) )";
					$geom_query = $wpdb->prepare( $geom_query, array( $meta_query['key'] ) ); // @codingStandardsIgnoreLine
				}

			} else if ( array_key_exists( 'geom_op', $meta_query ) && in_array( strtolower( $meta_query['geom_op'] ),WP_GeoUtil::get_capabilities(), true ) ) {

				// We must be doing a one geom op that returns a value.
				// Verify that we're requesting a valid compare type.
				if ( in_array( $meta_query[ 'compare' ], array(
					'=', '!=', '>', '>=', '<', '<=',
					'LIKE', 'NOT LIKE',
					'IN', 'NOT IN',
					'BETWEEN', 'NOT BETWEEN',
					'REGEXP', 'NOT REGEXP', 'RLIKE'
				) ) ) {

				$std_query = "( $metatable.meta_key = %s AND CAST($metatable.meta_value AS $meta_type) {$meta_query[ 'compare' ]}";
				$std_query = $wpdb->prepare( $std_query, array( $meta_query['key'] ) ); // @codingStandardsIgnoreLine
				$std_escaped = preg_quote( $std_query );
				$std_regex = "|$std_escaped(.*)\)|";

				if ( preg_match( $std_regex, $clauses[ 'where' ], $matches ) ) {

					$std_query .= $matches[1] . ')';

					// TODO: Handle IN/NOT INT/BETWEET/NOT BETWEEN...

					$geom_query = "( $metatable.$id_column IN ( SELECT fk_meta_id FROM {$geotable} WHERE (meta_key=%s AND CAST({$meta_query['geom_op']}($geotable.meta_value) AS $meta_type) {$meta_query[ 'compare' ]} ";
					$geom_query = $wpdb->prepare( $geom_query, array( $meta_query['key'] ) ); // @codingStandardsIgnoreLine
					$geom_query .= $matches[1] . ')))';
				} else {
					error_log( "WP_GeoQuery: I expected to find a compare in `{$clauses['where']}`" );
					continue;
				}
				} else {
					continue;
				}
			} else {
				continue;
			}

			// $a = 1 + 1;

			// print "\n\n\n";

			// print str_replace(' ','*',"----{$clauses['where']}----\n\n");
			// print str_replace(' ','*',"----{$std_query}-----\n\n");
			// print "----{$geom_query}-----\n\nn";
			$clauses['where'] = str_replace( $std_query, $geom_query, $clauses['where'] );
			// print "----{$clauses['where']}----\n";

			// print "\n\n\n-------------\n";
			// $a = 1 + 1;
		}

		return $clauses;
	}
}
