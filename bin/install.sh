#!/usr/bin/env bash

[[ -d .web_package ]] && rm -r .web_package/
[[ -d .documentation ]] && rm -r .documentation/
[[ -f .gitignore ]] && rm .gitignore

composer update --lock
