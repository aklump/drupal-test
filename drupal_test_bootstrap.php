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

//
// Detect the version of Drupal being tested.
//
$drupal_version = NULL;

$drupal7 = WEB_ROOT . '/includes/bootstrap.inc';
$drupal8 = WEB_ROOT . '/core/lib/Drupal.php';

if (file_exists($drupal7)) {
  require_once $drupal7;
  if (!defined('VERSION')) {
    throw new \RuntimeException("This appears to be Drupal 7, but VERSION is not defined.");
  }
  $drupal_version = VERSION;
}
elseif (file_exists($drupal8)) {
  require_once $drupal8;
  if (!defined('\Drupal::VERSION')) {
    throw new \RuntimeException("This appears to be Drupal 8, but \Drupal::VERSION is not defined.");
  }
  $drupal_version = \Drupal::VERSION;
}

// Setup autoloading of basic Drupal classes.
if (version_compare($drupal_version, 8, '>=')) {
  $loader->addPsr4('', WEB_ROOT . '/core/lib');
  $loader->addPsr4('Drupal\\Tests\\', WEB_ROOT . '/core/tests/Drupal/Tests');
}
