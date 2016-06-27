-- Buffer a point in 4326 by n meters or miles, resulting in a polygon with n segments

DELIMITER $$
DROP FUNCTION IF EXISTS wp_buffer_point_m$$
DROP FUNCTION IF EXISTS wp_buffer_point_mi$$
DROP FUNCTION IF EXISTS wp_buffer_point_real$$


CREATE FUNCTION wp_buffer_point_m(p POINT, radius FLOAT, segments INT) RETURNS POLYGON
NO SQL DETERMINISTIC
BEGIN
	return wp_buffer_point_real(p,radius,segments,6371000); -- Earth's radius in meters
END$$



CREATE FUNCTION wp_buffer_point_mi(p POINT, radius FLOAT, segments INT) RETURNS POLYGON
NO SQL DETERMINISTIC
BEGIN
	return wp_buffer_point_real(p,radius,segments,3959); -- Earth's radius in miles
END$$


CREATE FUNCTION wp_buffer_point_real(p POINT, radius FLOAT, segments INT, eradius INTEGER) RETURNS POLYGON

NO SQL DETERMINISTIC

COMMENT 'Create a polygon from a point and distance in meters'

BEGIN
	DECLARE step FLOAT; 		-- Our step value for the loop
	DECLARE degrees FLOAT; 		-- How many degrees are we around the circle
	DECLARE polygonstring TEXT;
	DECLARE d FLOAT;
	DECLARE dlon FLOAT;
	DECLARE lat FLOAT;
	DECLARE lon FLOAT;
	DECLARE lat1 FLOAT;
	DECLARE lon1 FLOAT;
	DECLARE firstlat FLOAT;
	DECLARE firstlon FLOAT;
	DECLARE tc FLOAT;

	SET step = 360 / segments; -- our loop increment value
	SET degrees = 0; -- our starting point for our loop
	SET polygonstring = 'POLYGON(('; -- Our output

			SET d = radius / eradius;
			SET lat1 = X(p);
			SET lon1 = Y(p);

			SET firstlat = NULL;
			SET firstlon = NULL;

			polyloop: LOOP

			-- http://williams.best.vwh.net/avform.htm#LL
			-- d is distance in radians
			-- tc is slope in radians
			SET tc = RADIANS( degrees );

			SET lat = ASIN( SIN( lat1 ) * COS( d ) + COS( lat1 ) * SIN( d ) * COS( tc ) );
			SET dlon = ATAN2( SIN( tc ) * SIN( d ) * COS( lat1 ), COS( d ) - SIN( lat1 ) * SIN( lat ) );
			SET lon = MOD( lon1 - dlon + PI(), 2 * PI() ) - PI();

			SET lat = DEGREES(lat);
			SET lon = DEGREES(lon);

			-- If this is the first point, keep the lat/lon so we can close the polygon
			SET firstlat = IFNULL(firstlat, lat);
			SET firstlon = IFNULL(firstlon, lon);

			-- Add our new found lat/lon to our polygon string
			SET polygonstring = concat(polygonstring, lat, ' ', lon, ', ');

			-- Increment our degrees for the next loop
			SET degrees = degrees + step;

			IF (degrees < 360) THEN
				ITERATE polyloop;
			ELSE
				LEAVE polyloop;
			END IF;

END LOOP polyloop;


-- close the polygon with the original points and closing parens
SET polygonstring = concat(polygonstring,firstlat, ' ', firstlon, '))');

	-- Turn it into geometry and return it
	RETURN GeomFromText(polygonstring);

END$$
DELIMITER ;
