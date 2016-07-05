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


-- wp_buffer_point_mi('POINT(45 -93)',20,8) should be roughly the same as
-- SELECT ST_ASTEXT(ST_BUFFER(ST_GeomFromText('POINT(45 -93)',4326),0.289)); in PostGIS and
-- which results in 
-- "POLYGON((45.289 -93,45.2834469460365 -93.0563811030627,45.2670011848958 -93.1105955119535,45.2402947179554 -93.1605597973427,45.2043538597629 -93.2043538597629,45.1605597973427 -93.2402947179554,45.1105955119535 -93.2670011848958,45.0563811030627 -93.2834469460365,45 -93.289,44.9436188969373 -93.2834469460365,44.8894044880465 -93.2670011848958,44.8394402026573 -93.2402947179554,44.7956461402371 -93.2043538597629,44.7597052820446 -93.1605597973427,44.7329988151042 -93.1105955119535,44.7165530539635 -93.0563811030627,44.711 -93,44.7165530539635 -92.9436188969373,44.7329988151042 -92.8894044880465,44.7597052820446 -92.8394402026573,44.7956461402371 -92.7956461402371,44.8394402026573 -92.7597052820446,44.8894044880465 -92.7329988151042,44.9436188969373 -92.7165530539635,45 -92.711,45.0563811030627 -92.7165530539635,45.1105955119535 -92.7329988151042,45.1605597973427 -92.7597052820446,45.2043538597629 -92.7956461402371,45.2402947179554 -92.8394402026573,45.2670011848958 -92.8894044880465,45.2834469460365 -92.9436188969373,45.289 -93))"
