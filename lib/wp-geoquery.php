<?php
/**
 * This class handles query interception and
 * modification in order to handle geo queries
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
		add_action( 'get_meta_sql', array( $this, 'get_meta_sql' ), 10, 6 );
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
	public function get_meta_sql( $clauses, $queries, $type, $primary_table, $primary_id_column, $context, &$depth = 0 ) {

		$metatable = _get_meta_table( $type );

		if ( ! $metatable ) {
			return false;
		}

		$geotable = $metatable . '_geo';

		$id_column = 'user' === $type ? 'umeta_id' : 'meta_id';

		foreach ( $queries as $k => $meta_query ) {
			if ( ! is_array( $meta_query ) ) {
				continue;
			}

			if ( $depth > 0 ) {
				$metatable = 'mt' . $depth;
			}

			// Not a first-order clause. Recurse!
			if ( ! array_key_exists( 'key',$meta_query ) && ! array_key_exists( 'value',$meta_query ) ) {
				$clauses = $this->get_meta_sql( $clauses,$meta_query,$type,$primary_table,$primary_id_column,$context, $depth );
				continue;
			}

			$depth++;

			// Is our compare a spatial compare? If, so, it has to be in our list of allowed compares.
			if ( in_array( strtolower( $meta_query['compare'] ),WP_GeoUtil::get_capabilities(), true ) ) {
				$worked = $this->handle_two_geom_bool_meta( $clauses,$meta_query,$type,$primary_table,$primary_id_column,$context, $metatable, $geotable, $id_column );
			} else if ( array_key_exists( 'geom_op', $meta_query ) && in_array( strtolower( $meta_query['geom_op'] ),WP_GeoUtil::get_capabilities(), true ) ) {
				// Single arg functions that get cast and compared just need to re-alias the meta table. We can leave the rest of the WHERE clause alone.
				$new_meta_value = "{$meta_query['geom_op']}(meta_value)";
				$worked = $this->make_join_spatial( $clauses,$meta_query,$type,$primary_table,$primary_id_column,$context, $metatable, $geotable, $id_column, $new_meta_value );
			} else {
				continue;
			}

			if ( ! $worked ) {
				continue;
			}

			if ( WP_GEOMETA_DEBUG > 1 ) {
				print 'WPGM Final Where: ----' . esc_attr( $clauses['where'] ) . "---\n";
				print "\n\n-------------\n\n";
			}
		}

		return $clauses;
	}


	/**
	 * Make the join spatial
	 *
	 * Modifies $clauses by reference
	 *
	 * @param array  $clauses The query JOIN and WHERE clauses.
	 * @param array  $meta_query The current meta query we're working with.
	 * @param string $type The type of meta we're dealing with.
	 * @param string $primary_table The main table for the meta.
	 * @param string $primary_id_column The primary key for the main table.
	 * @param object $context The main query object.
	 * @param string $metatable The table to query.
	 * @param string $geotable The geo table to query.
	 * @param string $id_column The id column name.
	 * @param string $new_meta_value A string with the SQL which will result in the new meta_value value.
	 * @return bool for success.
	 */
	private function make_join_spatial( &$clauses, $meta_query, $type, $primary_table, $primary_id_column, $context, $metatable, $geotable, $id_column, $new_meta_value ) {
		/*
         * Replace the original join with a named subquery that hits the spatial table and performs the
		 * requested spatial operations. It's given the same name as the original table so that we don't
		 * have to touch ORDERBY.
		 *
         * eg.
		 * INNER JOIN wp_postmeta ON ( wp_posts.ID = wp_postmeta.post_id )
		 * INNER JOIN wp_postmeta AS mt1 ON ( wp_posts.ID = mt1.post_id )
		 *
		 * Needs to become
		 *
		 * INNER JOIN ( SELECT meta_key, Dimension(meta_value) AS meta_value FROM wp_postmeta_geo ) wp_postmeta ON ( wp_posts.ID = wp_postmeta.post_id)
         * INNER JOIN ( SELECT meta_key, ST_DISTANCE(meta_value, GeomFromText('POINT(0,0)')) AS meta_value FROM wp_postmeta_geo ) mt1 ON ( wp_posts.ID = mt1.post_id )
		 *
		 */

		$realmetatable = _get_meta_table( $type );
		if ( is_numeric( $metatable[ strlen( $metatable ) - 1 ] ) ) {
			$alias = ' AS ' . $metatable;
		} else {
			$alias = '';
		}

		$orig_join = 'JOIN ' . $realmetatable . $alias . ' ON ( ' . $primary_table . '.' . $primary_id_column . ' = ' . $metatable . '.' . $type . '_id )';
		$new_join  = 'JOIN ( SELECT ' . $id_column . ', ' . $type . '_id, meta_key, ' . $new_meta_value . ' AS meta_value FROM ' . $realmetatable . '_geo ) AS ' . $metatable . ' ON ( ' . $primary_table . '.' . $primary_id_column . ' = ' . $metatable . '.' . $type . '_id )';

		if ( WP_GEOMETA_DEBUG > 1 ) {
			print "\n";
			print 'Orig Join: ' . esc_attr( $clauses['join'] ) . "\n";
			print 'Search Join: ' . esc_attr( $orig_join ) . "\n";
			print 'Replace Join: ' . esc_attr( $new_join ) . "\n";
			print "\n";
		}

		$clauses['join'] = str_replace( $orig_join, $new_join, $clauses['join'] );

		return true;
	}

	/**
	 * Handle query modifications for spatial operations that take two geometry args.
	 *
	 * Modifies $clauses by reference
	 *
	 * @param array  $clauses The query JOIN and WHERE clauses.
	 * @param array  $meta_query The current meta query we're working with.
	 * @param string $type The type of meta we're dealing with.
	 * @param string $primary_table The main table for the meta.
	 * @param string $primary_id_column The primary key for the main table.
	 * @param object $context The main query object.
	 * @param string $metatable The table to query.
	 * @param string $geotable The geo table to query.
	 * @param string $id_column The id column name.
	 * @return bool for success.
	 */
	private function handle_two_geom_bool_meta( &$clauses, $meta_query, $type, $primary_table, $primary_id_column, $context, $metatable, $geotable, $id_column ) {
		global $wpdb;

		$meta_type = $context->meta_query->get_cast_for_type( $meta_query['type'] );

		// If we have a geometry for our value, then we're doing a two-geometry function that returns a boolean.
		$geometry = WP_GeoUtil::metaval_to_geom( $meta_query['value'] );
		if ( ! empty( $geometry ) ) {
			$new_meta_value = "{$meta_query['compare']}( meta_value,GeomFromText( %s, %d ) )";
			$new_meta_value = $wpdb->prepare( $new_meta_value, array( $geometry, WP_GeoUtil::get_srid() ) ); // @codingStandardsIgnoreLine

			$std_query = "( $metatable.meta_key = %s AND CAST($metatable.meta_value AS $meta_type) = %s )";
			$std_query = $wpdb->prepare( $std_query, array( $meta_query['key'], $meta_query['value'] ) ); // @codingStandardsIgnoreLine
		} else {
			// If we don't have a value, then our subquery gets written without parenthesis wraps.
			// IDK why.
			$new_meta_value = "{$meta_query['compare']}( meta_value )";

			$std_query = "  $metatable.meta_key = %s";
			$std_query = $wpdb->prepare( $std_query, array( $meta_query['key'] ) ); // @codingStandardsIgnoreLine
		}

		// Our geom_query will be against our aliased meta table so we just need to check for boolean true.
		$geom_query = "( $metatable.meta_key = %s AND $metatable.meta_value )";
		$geom_query = $wpdb->prepare( $geom_query, array( $meta_query['key'] ) ); // @codingStandardsIgnoreLine

		$this->make_join_spatial( $clauses,$meta_query,$type,$primary_table,$primary_id_column,$context, $metatable, $geotable, $id_column, $new_meta_value );

		if ( WP_GEOMETA_DEBUG > 1 ) {
			print 'WPGM Original Where: ---' . esc_attr( str_replace( ' ','*',"{$clauses['where']}" ) ) . "---\n";
			print 'WPGM Search Patterns: ---' . esc_attr( str_replace( ' ','*',$std_query ) ) . "---\n";
			print 'WPGM Replacement Pattern: ----' . esc_attr( $geom_query ) . "---\n";
		}
		$clauses['where'] = str_replace( $std_query, $geom_query, $clauses['where'] );

		return true;
	}
}
