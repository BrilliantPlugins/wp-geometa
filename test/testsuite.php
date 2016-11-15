#!/usr/bin/env php
<?php
// Define ourselves!

require_once( dirname( __FILE__ ) . '/tests/__load.php' );

require( WP_GEOMETA_TESTDIR . '/tests/TablesCreatedTest.php' );
require( WP_GEOMETA_TESTDIR . '/tests/LoadDataTest.php' );
require( WP_GEOMETA_TESTDIR . '/tests/LoadLatLng.php' );
require( WP_GEOMETA_TESTDIR . '/tests/DeleteLatLngMeta.php' );
require( WP_GEOMETA_TESTDIR . '/tests/HandleMultiMetaClause.php' );
require( WP_GEOMETA_TESTDIR . '/tests/QueryOrderByTest.php' ); 
require( WP_GEOMETA_TESTDIR . '/tests/QueryOrderByDistance.php' ); 
require( WP_GEOMETA_TESTDIR . '/tests/QueryTwoGeomBoolTest.php' );
require( WP_GEOMETA_TESTDIR . '/tests/QueryOneGeomBoolTest.php' );
require( WP_GEOMETA_TESTDIR . '/tests/QueryOneGeomValTest.php' );
require( WP_GEOMETA_TESTDIR . '/tests/QueryOneGeomInValTest.php' );
require( WP_GEOMETA_TESTDIR . '/tests/UnloadDataTest.php' );
