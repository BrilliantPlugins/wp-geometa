<?php

defined( 'ABSPATH' ) or die( 'No direct access' );

/**
 * Gather some self metadata so that if WP-GeoMeta is included as a lib in multiple plugins
 * and/or as a plugin itself, we can determine which one to load.
 */
require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
$this_plugin_info = get_plugin_data( __FILE__, false, false );
$wp_geometa_version = $this_plugin_info['Version'];
$wp_geometa_max_version = get_option( 'wp_geometa_version', '0.0.0' );
$wp_geometa_db_version = get_option( 'wp_geometa_db_version', '0.0.0' );

/**
 * -1 means that our version is lower.
 * 0 means they are equal.
 * 1 means our version is higher.
 */
$wp_geometa_version_status = version_compare( $wp_geometa_version, $wp_geometa_max_version );

if ( 1 === $wp_geometa_version_status ) {
	// This will cause our version to get loaded next time.
	update_option( 'wp_geometa_version', $wp_geometa_version );
} 

if ( 0 === $wp_geometa_version_status || '0.0.0' === $wp_geometa_max_version ) {

	// Other instances of WP_GeoMeta shouldn't have loaded these classes
	// unless they're the same version as this instance.
	if ( ! class_exists( 'WP_GeoMeta' ) ) {
		require_once( dirname( __FILE__ ) . '/lib/wp-geoquery.php' );
		require_once( dirname( __FILE__ ) . '/lib/wp-geometa.php' );
		$wpgeo = WP_GeoMeta::get_instance();
		$wpgq = WP_GeoQuery::get_instance();

		define( 'WP_GEOMETA_VERSION', $wp_geometa_version );

		// Since we just got loaded, make sure that the database reflects any
		// changes that the latest version of WP_GeoMeta might have added.
		if ( version_compare( $wp_geometa_version, $wp_geometa_db_version ) > 0 ) {
			$wpgeo->create_geo_tables();

			$wp_geoutil = WP_GeoUtil::get_instance();
			$wp_geoutil->get_capabilities( true );
			update_option( 'wp_geometa_db_version', $wp_geometa_version );
		}
	}
}

/**
 * There's a chance that someone installed a newer version of the plugin,
 * (or a plugin that used the library) which caused the option to get set,
 * then removed that plugin, which would mean that we aren't loading the
 * usual way.
 *
 * Add an action to try to load our classes after the rest of the plugins
 * get a chance to load.
 */
if ( ! function_exists( 'wp_geometa_load_older_version' ) ) {
	/**
	 * Load this instance's libraries.
	 */
	function wp_geometa_load_older_version() {
		if ( ! class_exists( 'WP_GeoMeta' ) ) {

			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			require_once( dirname( __FILE__ ) . '/lib/wp-geoquery.php' );
			require_once( dirname( __FILE__ ) . '/lib/wp-geometa.php' );
			$wpgeo = WP_GeoMeta::get_instance();
			$wpgq = WP_GeoQuery::get_instance();

			$this_plugin_info = get_plugin_data( __FILE__, false, false );
			$wp_geometa_version = $this_plugin_info['Version'];
			$wp_geometa_max_version = get_option( 'wp_geometa_version', '0.0.0' );
			$wp_geometa_db_version = get_option( 'wp_geometa_db_version', '0.0.0' );

			// Since we just got loaded, make sure that the database reflects any
			// changes that the latest version of WP_GeoMeta might have added.
			if ( version_compare( $wp_geometa_version, $wp_geometa_db_version ) > 0 ) {
				$wpgeo->create_geo_tables();

				$wp_geoutil = WP_GeoUtil::get_instance();
				$wp_geoutil->get_capabilities( true );
				update_option( 'wp_geometa_db_version', $wp_geometa_version );
			}

			/*
			 * If we got downgraded, then the first found wp-geometa will have been
			 * loaded. Lowering the version to this instance's version will allow
			 * WP GeoMeta to pick the highest version again on the next run.
			 *
			 * Eg. This is v5 and is the first one that WP finds. v6 is also installed
			 * and v7 was installed. When v7 is no longer found, this (v5) will run since
			 * it was the first one found and will set wp_geometa_version to v5.
			 *
			 * On the next run, it would find that v6 is the higher version and would update
			 * wp_geometa_version. On the run after that v6 would be loaded.
			 */
			update_option( 'wp_geometa_version', $wp_geometa_version );
		}
	}
	add_action( 'plugins_loaded', 'wp_geometa_load_older_version' );
}


/**
 * Handle tasks that only matter if we're on the dashboard.
 *
 * The dashboard allows some actions that should be admin only.
 */
if ( is_admin() ) {
	if ( ! class_exists( 'WP_GeoMeta_Installs' ) ) {
		/**
		 * This class is deliberately simple, because if it ever changes
		 * the changes need to be backwards compatible.
		 *
		 * We're using a singleton instead of a global array to capture
		 * each WP-GeoMeta's version and location.
		 */
		class WP_GeoMeta_Installs {
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
				return ( is_null( self::$_instance ) ? new self : self::$_instance );
			}

			/**
			 * Add an install listing
			 *
			 * @param string $file __FILE__ of wp-geometa.php.
			 * @param string $version the version of wp-geometa.php.
			 */
			public static function add( $file, $version ) {
				$self = self::$_instance = WP_GeoMeta_Installs::get_instance();
				$self->installs[ $file ] = $version;
			}

			/**
			 * Get the list of installs with versions.
			 */
			public static function get_list() {
				$self = WP_GeoMeta_Installs::get_instance();
				return $self->installs;
			}
		}
	}

	// Add ourself to the list of installs.
	WP_GeoMeta_Installs::add( __FILE__, $wp_geometa_version );
}

/**
 * Set up an activation hook for when this is a plugin.
 *
 * Plugins using this as a lib should run $wpgeo->create_geo_tables() themselves
 */
if ( ! function_exists( 'wpgeometa_activation_hook' ) ) {
	/**
	 * Simple callback for the activation hook. Creates the spatial tables.
	 */
	function wpgeometa_activation_hook() {
		require_once( dirname( __FILE__ ) . '/lib/wp-geometa.php' );
		$wpgeo = WP_GeoMeta::get_instance();
		$wpgeo->create_geo_tables();
	}
	register_activation_hook( __FILE__ , 'wpgeometa_activation_hook' );
}

/**
 * Set up handling for standard latlng fields
 */
if ( ! function_exists( 'wpgeometa_setup_latlng_fields' ) ) {
	/**
	 * A simple callback function to register the WordPress Geodata standard latitude and longitude fields
	 *
	 * See also https://codex.wordpress.org/Geodata for more info.
	 */
	function wpgeometa_setup_latlng_fields() {
		WP_GeoMeta::add_latlng_field( 'geo_latitude', 'geo_longitude', 'geo_' );
	}
	add_action( 'init', 'wpgeometa_setup_latlng_fields' );
}
