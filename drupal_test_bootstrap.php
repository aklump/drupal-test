<?php

/**
 * @file
 * Bootstrap our Unit and Kernel tests.
 */

$loader = require __DIR__ . '/vendor/autoload.php';

/**
 * The webroot directory.
 *
 * @var WEB_ROOT
 */
define('WEB_ROOT', realpath(__DIR__ . '/../web'));

if (!is_dir(WEB_ROOT)) {
  throw new \RuntimeException("Invalid web root: " . WEB_ROOT);
}
