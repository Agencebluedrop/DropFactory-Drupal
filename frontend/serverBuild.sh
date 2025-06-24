#!/bin/bash

# This script will build the project locally

php bin/console importmap:install
php bin/console tailwind:build --minify
php bin/console asset-map:compile
php bin/console cache:clear

# Notify the user
echo "Build completed successfully."