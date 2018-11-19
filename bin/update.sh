#!/usr/bin/env bash

#
# Update to the latest version.
#

source="${BASH_SOURCE[0]}"
while [ -h "$source" ]; do # resolve $source until the file is no longer a symlink
  dir="$( cd -P "$( dirname "$source" )" && pwd )"
  source="$(readlink "$source")"
  [[ $source != /* ]] && source="$dir/$source" # if $source was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
root="$( cd -P "$( dirname "$source" )" && pwd )"
tempdir=$(mktemp -d 2>/dev/null || mktemp -d -t 'temp')
app=$PWD

# Detect if we're in the right place.
if [[ "$(basename $app)" != "tests" ]] || [[ ! -f "$app/composer.json" ]]; then
  echo && echo "You must run the update from inside the \"tests\" folder" && exit 1
fi

# Clone the latest version.
cd $tempdir
git clone https://github.com/aklump/drupal-test.git tests
cd tests

# Move new files over.
rsync -a --delete bin/ $app/bin/
rsync -a --delete docs/ $app/docs/
rsync -a --delete src/DrupalTest/ $app/src/DrupalTest/
cp bootstrap_tests.php $app/
cp README.md $app/
cp LICENSE $app/

# Update composer.
cd $app
composer update --lock

echo && echo "Updated to the latest version."
