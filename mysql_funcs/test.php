<?php

$lat1 = deg2rad( 45 );
$lon1 = deg2rad( -93 );

$d = 1000 / 6371000;
$tc = deg2rad( 90 );

$lat = asin(sin($lat1)*cos($d)+cos($lat1)*sin($d)*cos($tc));
$lon = $lon1 + atan2(sin($tc)*sin($d)*cos($lat1),cos($d)-sin($lat1)*sin($lat));

$lat = rad2deg( $lat );
$lon = rad2deg( $lon );

print "Got: lat=$lat, lon=$lon\n";
print "Want: lat=44.99999929577758,  lon=-92.98731718284918 \n";
