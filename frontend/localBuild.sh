#!/bin/bash

# This script will build the project locally

lando php bin/console importmap:install
lando php bin/console tailwind:build
lando php bin/console asset-map:compile
lando php bin/console cache:clear

# Notify the user
echo "Build completed successfully."