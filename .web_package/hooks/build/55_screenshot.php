<?php

/**
 * @file
 * Fix the path the the screenshot for the root README.
 */

$file = $argv[7] . '/README.md';
$contents = file_get_contents($file);
$contents = str_replace('![drupal-test](images/screenshot.jpg)', '![drupal-test](docs/images/screenshot.jpg)', $contents);
file_put_contents($file, $contents);
