<?php
/**
 * Tests if 4 _geo tables exist in the database after activating the plugin.
 */

require_once( dirname( __FILE__ ) . '/__load.php' );

print str_pad( "Checking if the tables were created", WP_GEOMETA_TEST_WIDTH, '.' );

global $wpdb;

$geo_tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}_%_geo'",ARRAY_N);

if(count($geo_tables) !== 4){
	print "😞\n";
}else {
	print "😎\n";
}
