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
	DECLARE firstcoords VARCHAR(100);
	DECLARE curcoords VARCHAR(100);

	SET step = 360 / segments; -- our loop increment value
	SET degrees = 0; -- our starting point for our loop
	SET polygonstring = 'POLYGON(('; -- Our output

			polyloop: LOOP

			SET curcoords = wp_point_bearing_distance_coord_pair( p, degrees, radius, eradius);

			SET firstcoords = IFNULL( firstcoords, curcoords );

			-- Add our new found lat/lon to our polygon string
			SET polygonstring = concat(polygonstring, curcoords, ', ');

			-- Increment our degrees for the next loop
			SET degrees = degrees + step;

			IF (degrees < 360) THEN
				ITERATE polyloop;
			ELSE
				LEAVE polyloop;
			END IF;

			END LOOP polyloop;


	-- close the polygon with the original points and closing parens
	SET polygonstring = concat(polygonstring,firstcoords, '))');

	-- Turn it into geometry and return it
	RETURN GeomFromText(polygonstring);

END$$
DELIMITER ;

