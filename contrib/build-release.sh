#!/bin/bash

# builds the software for release - should be run using composer.
# this is quick and dirty - hopefully we'll switch to use github actions at some point.

composer install --no-dev --optimize-autoloader

# make folder if not exists
mkdir build

# remove existing files if exists
rm build/geocore-ce.zip build/fusion.zip build/marquee.zip build/tempo.zip

# add license to base folder and all contents of src to base folder, minus a few things
zip build/geocore-ce.zip LICENSE
cd src/

# add files in src as the base folder.. but:
# exclude (some are added back partially further down):
#   - config.php
#   - templates_c
#   - user_images
#   - _geocache
#   - geo_templates
#   - addons/exporter/exports
#   - .DS_Store files (Mac OS file)
zip ../build/geocore-ce.zip -r * -x config.php "templates_c/*" "user_images/*" "_geocache/*" "geo_templates/*" \
    "addons/exporter/exports/*" "*.DS_Store"

# add the starting files needed for _geocache
zip ../build/geocore-ce.zip _geocache/index.php _geocache/.htaccess

# Add empty folders for user_images, templates_c
zip ../build/geocore-ce.zip user_images templates_c

# Add the almost empty folder for the exporter addon with the README.md included
zip ../build/geocore-ce.zip addons/exporter/exports/README.md

# Add the default template and min.php in geo_templates (Note: we exclude the extra template sets for now)
zip ../build/geocore-ce.zip geo_templates/min.php -r geo_templates/default/* -x "*.DS_Store"

# Make a download specificaly for the extra template sets - done so they can be uploaded using the manager if desired
cd geo_templates
zip ../../build/fusion.zip -r fusion -x "*.DS_Store"
zip ../../build/marquee.zip -r marquee -x "*.DS_Store"
zip ../../build/tempo.zip -r tempo -x "*.DS_Store"

echo
echo --- Build complete!  Check the zips in the build/ folder ---
echo
