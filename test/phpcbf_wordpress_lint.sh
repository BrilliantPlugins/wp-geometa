#!/bin/bash

# This script runs phpcbf on the PHP files in the project to apply the auto-corrections that phpcbf can do.
# For safety you should commit before running this, just in case it breaks something.

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

phpcbf --standard=WordPress $DIR/../wp-geometa.php
phpcbf --standard=WordPress $DIR/../lib/wp-geoutil.php
phpcbf --standard=WordPress $DIR/../lib/wp-geometa.php
phpcbf --standard=WordPress $DIR/../lib/wp-geometa-dash.php
phpcbf --standard=WordPress $DIR/../lib/wp-geoquery.php
