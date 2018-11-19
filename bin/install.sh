#!/usr/bin/env bash

#
# Install and clean up unnecessary files.
#

[[ -d .web_package ]] && rm -r .web_package/
[[ -d documentation ]] && rm -r documentation/
[[ -f .gitignore ]] && rm .gitignore

composer update --lock
