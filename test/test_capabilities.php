<?php

require_once(__DIR__ . '/../../../../wp-load.php');
require_once('./../wp-geometa.php');


$util = WP_GeoUtil::get_instance();

$util->get_capabilities(true);
$util->get_capabilities();
