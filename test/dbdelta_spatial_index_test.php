<?php

require_once(__DIR__ . '/../../../../wp-load.php');
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

// Only MyISAM supports spatial indexes, at least in MySQL older versions
// Spatial indexes can only contain non-null columns
$geotables = "CREATE TABLE {$wpdb->prefix}index_test (
non_spatial bigint(20) unsigned NOT NULL,
meta_value GEOMETRYCOLLECTION NOT NULL,
KEY non_spatial (non_spatial),
SPATIAL KEY meta_value (meta_value)
) ENGINE=MyISAM;";

print "\n\n" . $geotables . "\n\n";

dbDelta( $geotables );

print "\n";
