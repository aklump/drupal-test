#!/bin/bash
#
# @file
# Copy distribution files to /dist
#

test -h "$7/dist" && rm "$7/dist"
test -d "$7/dist" || mkdir -p "$7/dist"
rsync -a "$7/docs/" "$7/dist/docs/"
rsync -a "$7/src/" "$7/dist/src/"
cp "$7/composer.json" "$7/dist/"
cp "$7/phpunit.xml" "$7/dist/"
test -e "$7/README.md" && cp "$7/README.md" "$7/dist/"
test -e "$7/CHANGELOG.md" && cp "$7/CHANGELOG.md" "$7/dist/"
