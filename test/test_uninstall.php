<?php

require_once(__DIR__ . '/../../../../wp-load.php');
require_once('./../wp-geometa.php');
$wpgeo = WP_GeoMeta::get_instance();
$wpgeo->uninstall();
