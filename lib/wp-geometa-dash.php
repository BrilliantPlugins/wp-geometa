<?php

class WP_GeoMeta_Dash {

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
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'wp_ajax_delete_tables', array( $this, 'ajax_delete_tables' ) );
		add_action( 'wp_ajax_create_tables', array( $this, 'ajax_create_tables' ) );
		add_action( 'wp_ajax_truncate_tables', array( $this, 'ajax_truncate_tables' ) );
		add_action( 'wp_ajax_populate_tables', array( $this, 'ajax_populate_tables' ) );
	}

	/**
	 * Add the dashboard menu listing.
	 */
	public function admin_menu() {
		add_management_page( esc_html__( 'WP GeoMeta', 'wp-geometa' ), esc_html__( 'WP GeoMeta','wp-geometa' ), 'install_plugins', 'wp-geometa', array( $this, 'show_dashboard' ) );
	}

	public function show_dashboard() {
		require_once( dirname( __FILE__ ) . '/dash.inc' );
	}

	public function ajax_delete_tables() {

	}

	public function ajax_create_tables() {

	}

	public function ajax_truncate_tables() {

	}

	public function ajax_populate_tables() {

	}
}
