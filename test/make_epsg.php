<?php


// Data from: http://earth-info.nga.mil/GandG/coordsys/grids/universal_grid_system.html
$json_string = file_get_contents(__DIR__ . '/utm_zones.geojson');
$json = json_decode($json_string,true);

// Conversion function from: http://www.surfaces.co.il/from-latlon-to-utm-zone-in-spatialite/
foreach($json['features'] as $f => $feature){
	// Get centroid
	$x = array();
	$y = array();
	foreach($feature['geometry']['coordinates'][0] as $coord){
		$x[] = $coord[0];
		$y[] = $coord[1];
	}

	$centerx = array_sum($x) / count($x);
	$centery = array_sum($y) / count($y);

	if($centery > 0){
		if($x == 180){
			$json['features'][$f]['properties']['EPSG'] = 32600;
		} else {
			$json['features'][$f]['properties']['EPSG'] = (int)(($centerx + 186)/6) + 32600;
		}
	} else {
		if($x == 180) {
			$json['features'][$f]['properties']['EPSG'] = 32760;
		} else {
			$json['features'][$f]['properties']['EPSG'] = (int)(($centerx + 186)/6) + 32700;
		}
	}
}

file_put_contents('utm_zones_with_info.geojson',json_encode($json));
