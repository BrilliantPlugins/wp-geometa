<?php
/**
 * Deletes all test posts
 */
require_once( dirname( __FILE__ ) . '/__load.php' );

print str_pad( "Deleting sample data", WP_GEOMETA_TEST_WIDTH, '.' );

do {
	foreach( $test_posts as $test_post ) {
		wp_delete_post( $test_post->ID, true );
	}
} while ( $test_posts = get_posts( array( 'post_type' => 'geo_test', 'posts_per_page' => 100, 'post_status' => 'any') ) );

$results = $wpdb->get_results( "SELECT post_id FROM {$wpdb->postmeta}_geo WHERE meta_key='wpgeometa_test'" );

if ( count( $results ) ) {
	fail();
} else {
	pass();
}
