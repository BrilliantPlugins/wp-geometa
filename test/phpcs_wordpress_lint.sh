#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

phpcs --standard=WordPress $DIR/../wp-geometa.php
phpcs --standard=WordPress $DIR/../lib/wp-geoutil.php
phpcs --standard=WordPress $DIR/../lib/wp-geometa.php
phpcs --standard=WordPress $DIR/../lib/wp-geoquery.php

echo "Done. If this is the only output, all files passed."
