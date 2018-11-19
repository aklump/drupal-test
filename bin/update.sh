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

# Update an entire directory.
#
# $1 - The relative path of the directory to update.
#
# Returns 0 if moved.
function update_dir() {
    relative_path=$1

    [[ -d "$app/$relative_path/" ]] || (mkdir -p "$app/$relative_path/" || return 1)
    rsync -a --delete "$relative_path/" "$app/$relative_path/" || return 1
}

# Move new files over.
update_dir bin
update_dir docs
update_dir src/DrupalTest
cp bootstrap_drupal_test.php $app/
cp README.md $app/
cp LICENSE $app/

# Update composer.
cd $app
composer update --lock

echo && echo "Updated to the latest version."
