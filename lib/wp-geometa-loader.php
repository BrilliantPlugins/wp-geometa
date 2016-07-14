<?php

add_filter('wp_geometa_load',function($instances){
	$instances[] = array(
		'version' => '0.0.2',
		'wp_geometa' => __DIR__ . '/wp-geometa.php'
	);

	return $instances;
});

add_action('plugins_loaded', 

if ( !function_exists( 'wp_geometa_loader' ) ) {
	function wp_geometa_loader() {
		$wp_geometa_instances = apply_filters( 'wp_geometa_load', array() );

		if ( count( $wp_geometa_instances ) === 0 ) {
			error_log( "Some instance of wp_geometa ruined it for everyone" );
			return;
		}

		$version_to_use = $wp_geometa_instances[0];
		foreach( $wp_geometa_instances as $instance ) {
			if ( ! array_key_exists( 'version', $instance ) ) {
				continue;
			}

			if ( version_compare( $version_to_use[ 'version' ], $instance[ 'version' ] ) < 0 ) {
				$version_to_use = $instance;
			}
		}

		require_once( $version_to_use[ 'wp_geometa' ] );
	}
}
