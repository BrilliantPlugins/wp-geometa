#!/bin/bash

# This script will run phpcs on all the php files in the project to report any violations of the WordPress coding standard.

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

phpcs --standard=WordPress $DIR/../wp-geometa.php
phpcs --standard=WordPress $DIR/../lib/wp-geoutil.php
phpcs --standard=WordPress $DIR/../lib/wp-geometa.php
phpcs --standard=WordPress $DIR/../lib/wp-geometa-dash.php
phpcs --standard=WordPress $DIR/../lib/wp-geoquery.php

echo "Done. If this is the only output, all files passed."
