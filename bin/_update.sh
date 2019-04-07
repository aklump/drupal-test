#!/usr/bin/env bash

#
# @file
# Do the bulk of updating.  This is in a separate file so that changes can be made and they will be incorporated immediately.  Otherwise update.sh would have to be run twice when changes occurred.
#

# Move new files over.
exact_match_dir docs
exact_match_dir src/DrupalTest
cp drupal_test_bootstrap.php $app/
cp drupal_test.yml $app/
cp README.md $app/
cp LICENSE $app/

if [[ ! -f "$app/drupal_test_config.yml" ]]; then
  cp $app/composer.json $app/composer--original.json
  cp drupal_test_config.yml
fi

# Update composer.
if [[ -f "$app/composer--original.json" ]]; then
  echo "You must delete composer--original.json to continue" && exit 1
fi

cp composer.json $app/

