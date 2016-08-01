Tests
=====

Welcome to the test directory. Utility scripts and automated tests belong here. 

Scripts
-------

 * phpcs_wordpress_lint.sh -- Runs PHPCS on all of the plugin PHP files. Does *NOT* test the test files.
 * testsuite.php -- Meant to be run from the command line. It will run all of the scripts in the _tests_ sub-directory. 

 testsuite.php and the scripts in the _tests_ sub-directory are all meant to be run on the command-line. (eg. ```php ./testsuite.php```). Some scripts just check that results were returned, while some test for a specific number of results. If you run LoadDataTest.php twice, those tests will fail. 

 For best results, clear the database using ```php tests/UnloadDataTest.php``` before trying to run the test suite. 

 The test results print out emoji. 😎 indicates that the test passed, 😡 indicates that the test failed. 

 If you don't have emoji support you could modify tests/__load.php and print something boring like "PASS" or "FAIL" instead. 
