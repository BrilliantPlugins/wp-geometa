<?php

/*
 * Stuff we probably don't need after all
 *
 */

	/**
	 * Load UTM zones so we can do good buffering
	 */
	function get_utm_code($geometry){
		global $wpdb;
		$q = "SELECT epsg FROM " . $wpdb->prefix . "_utm_epsg_lookup WHERE the_geom ST_CENTROID(ST_ENVELOPE(%s))";
	}

	function load_utm_data(){
		global $wpdb;

		// Data from: http://earth-info.nga.mil/GandG/coordsys/grids/universal_grid_system.html
		$json_string = file_get_contents(__DIR__ . '/utm_zones.geojson');
		$json = json_decode($json_string,true);

		$q = "INSERT INTO {$wpdb->prefix}wpgq_utm (epsg,geom) VALUES (%s,ST_GeomFromText(%s," . $this->srid . "))";

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

			$epsg = '';

			if($centery > 0){
				if($x == 180){
					$epsg = 32600;
				} else {
					$epsg = (int)(($centerx + 186)/6) + 32600;
				}
			} else {
				if($x == 180) {
					$epsg = 32760;
				} else {
					$epsg = (int)(($centerx + 186)/6) + 32700;
				}
			}

			$geom = $this->metaval_to_geom($feature);
			$sql = $wpdb->prepare($q,array($epsg,$geom));
			$wpdb->query($sql);
		}
	}
