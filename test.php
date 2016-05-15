<?php

require_once(__DIR__ . '/../../../wp-load.php');
require_once('./wp-geo.php');

// Test table creation
// activate_wp_brilliant_geo();

// Test adding
add_post_meta(48,'test','myvalue',true);

// Test update
update_post_meta(48,'test','altvalue','myvalue');

// Test delete
delete_post_meta(48, 'test');
