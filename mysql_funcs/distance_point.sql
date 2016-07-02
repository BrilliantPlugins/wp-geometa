-- Determine the didstance between two points in 4326, in meters or miles

DELIMITER $$

DROP FUNCTION IF EXISTS wp_distance_point_m$$
DROP FUNCTION IF EXISTS wp_distance_point_mi$$
DROP FUNCTION IF EXISTS wp_distance_point_real$$

CREATE FUNCTION wp_distance_point_m(p1 POINT, p2 POINT) RETURNS FLOAT
NO SQL DETERMINISTIC
BEGIN
	RETURN wp_distance_point_real(p1, p2, 6371000); -- Earth's radius in meters
END$$

CREATE FUNCTION wp_distance_point_mi(p1 POINT, p2 POINT) RETURNS FLOAT 
NO SQL DETERMINISTIC
BEGIN
	RETURN wp_distance_point_real(p1, p2, 3959); -- Earth's radius in miles
END$$

CREATE FUNCTION wp_distance_point_real(p1 POINT, p2 POINT, radius FLOAT) RETURNS FLOAT
NO SQL DETERMINISTIC
BEGIN

	DECLARE lat1 FLOAT;
	DECLARE lon1 FLOAT;
	DECLARE lat2 FLOAT;
	DECLARE lon2 FLOAT;
	
	SET lat1 = RADIANS( Y( p1 ) );
	SET lon1 = RADIANS( X( p1 ) );

	SET lat2 = RADIANS( Y( p2 ) );
	SET lon2 = RADIANS( X( p2 ) );

	-- http://williams.best.vwh.net/avform.htm#Dist
	return radius * 2 * ASIN( SQRT(  POW( ( SIN( ( lat1 - lat2 ) / 2 ) ), 2 ) + COS( lat1 ) * COS( lat2 ) * POW( ( SIN( ( lon1 - lon2 ) / 2 ) ), 2) ) );
END$$

DELIMITER ;
