<?php

// Tests adding, updating, querying and deleting meta from posts
// https://codex.wordpress.org/Class_Reference/WP_Query

$post_id_to_test = 48;

require_once(__DIR__ . '/../../../../wp-load.php');
require_once('./../wp-geometa.php');

// Test table creation
print "Creating tables for WP_GeoMeta\n";
activate_wp_geometa();


