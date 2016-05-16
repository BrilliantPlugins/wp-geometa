<?php

require_once(__DIR__ . '/../../../wp-load.php');
require_once('./wp-geoquery.php');

$a = 1 + 1;
$q = new WP_Query( array(
	'geo_query' => array(
		array(
			'key' => 'single',
			'compare' => 'ST_INTERSECTS',
			'value' => '{"type":"Feature","geometry":{"type":"Point","coordinates":[-93.5,45]}}',
		)
	)
));

print $q->last_query . "\n";

while($q->have_posts() ) {
	$q->the_post();
	echo the_title();
}

