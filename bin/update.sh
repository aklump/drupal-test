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
base=$PWD


cd $tempdir
git clone https://github.com/aklump/drupal-test.git tests && (cd tests && ./bin/install.sh) || echo "Installation error, nothing installed."

rsync -av src/ $PWD/src/
rsync -av docs/ $PWD/docs/
cp bootstrap_tests.php $PWD/
cp README.md $PWD/
cp LICENSE $PWD/
