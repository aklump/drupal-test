#!/usr/bin/env bash

#
# Install and remove unnecessary files.
#

[[ -d .git ]] && rm -rf .git/
[[ -d .web_package ]] && rm -r .web_package/
[[ -d documentation ]] && rm -r documentation/
[[ -f .gitignore ]] && rm .gitignore

composer update --lock
