-- Given a point, bearing and distance, generate a line


DELIMITER $$
DROP FUNCTION IF EXISTS wp_point_bearing_distance_to_line$$
DROP FUNCTION IF EXISTS wp_point_bearing_distance_to_line_m$$
DROP FUNCTION IF EXISTS wp_point_bearing_distance_to_line_mi$$
DROP FUNCTION IF EXISTS wp_point_bearing_distance_coord_pair$$



CREATE FUNCTION wp_point_bearing_distance_to_line_m(p POINT, bearing FLOAT, distance FLOAT) RETURNS LINESTRING

NO SQL DETERMINISTIC

COMMENT 'Given a starting point, bearing, and distance in meters, create a linestring'

BEGIN
	RETURN wp_point_bearing_distance_to_line(p,bearing,distance,6371000);
END$$


CREATE FUNCTION wp_point_bearing_distance_to_line_mi(p POINT, bearing FLOAT, distance FLOAT) RETURNS LINESTRING

NO SQL DETERMINISTIC

COMMENT 'Given a starting point, bearing, and distance in miles, create a linestring'

BEGIN
	RETURN wp_point_bearing_distance_to_line(p,bearing,distance,3959);
END$$



CREATE FUNCTION wp_point_bearing_distance_to_line(p POINT, bearing FLOAT, distance FLOAT, eradius INTEGER) RETURNS LINESTRING

NO SQL DETERMINISTIC

COMMENT 'Given a starting point, bearing distance and radius of the earth, create a linestring'

BEGIN

	DECLARE secondpoint TEXT;
	SET secondpoint = CONCAT('POINT(', wp_point_bearing_distance_coord_pair(p,bearing,distance,eradius), ')');
	RETURN LineString(p, GeomFromText(secondpoint) );
END$$


CREATE FUNCTION wp_point_bearing_distance_coord_pair(p POINT, bearing FLOAT, distance FLOAT, eradius INTEGER) RETURNS VARCHAR(50)

NO SQL DETERMINISTIC

COMMENT 'Given a starting point, bearing distance and radius of the earth, calculate the coordinate pair of the other end of a linestring'

BEGIN

	DECLARE d FLOAT;
	DECLARE lat FLOAT;
	DECLARE lon FLOAT;
	DECLARE lat1 FLOAT;
	DECLARE lon1 FLOAT;
	DECLARE tc FLOAT;

	SET d = distance / eradius;
	SET lat1 = RADIANS( Y(p) );
	SET lon1 = RADIANS( X(p) );
	SET tc = RADIANS( bearing );

	-- http://williams.best.vwh.net/avform.htm#LL
	-- http://www.movable-type.co.uk/scripts/latlong.html
	-- d is distance / earth radius
	-- tc is slope in radians

	SET lat = ASIN( SIN( lat1 ) * COS( d ) + COS( lat1 ) * SIN( d ) * COS( tc ) );
	SET lon = lon1 + ATAN2( SIN( tc ) * SIN( d ) * COS( lat1 ), COS( d ) - SIN( lat1 ) * SIN( lat ) );

	SET lat = DEGREES(lat);
	SET lon = DEGREES(lon);

	RETURN CONCAT(lon, ' ', lat);

END$$
DELIMITER ;
