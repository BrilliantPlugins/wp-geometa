<?php

require_once(__DIR__ . '/../../../../wp-load.php');
require_once('./../wp-geometa.php');

$q = new WP_Query( array(
	'geo_meta' => array(
		array(
			'key' => 'single',
			'compare' => 'ST_INTERSECTS',
			'value' => '{"type":"Feature","geometry":{"type":"Point","coordinates":[-93.5,45]}}',
		)
	)
));

print "\n\n" . $q->request . "\n\n";

while($q->have_posts() ) {
	$q->the_post();
	echo the_title();
}

print "\n";
