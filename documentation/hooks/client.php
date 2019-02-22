<?php

/**
 * @file
 * A hook file to generate cheatsheets partials.
 *
 * Available variables:
 * - $compiler.
 */

use AKlump\LoftDocs\DynamicContent\PhpClassMethodReader;
use AKlump\LoftLib\Code\Markdown;

require_once $argv[1] . '/../../vendor/autoload.php';

// Define the classes to read.
$reader = new PhpClassMethodReader([
  'show_args' => TRUE,
]);
$reader->excludeFromAll([
  '/^(assertPreConditions|setUpBeforeClass|__construct)$/',
]);

/**
 * Create the ClientTestBase group.
 */
$reader->addClassToScan('\AKlump\DrupalTest\BrowserTestCase', [
  PhpClassMethodReader::EXCLUDE,
  ['/^(getBrowser)$/'],
], 'ClientTestBase');
$reader->addClassToScan('\aik099\PHPUnit\BrowserTestCase', [
  PhpClassMethodReader::INCLUDE,
  ['/^(getSession)$/'],
], 'ClientTestBase');
$reader->addClassToScan('\AKlump\DrupalTest\ClientTestBase', [
  PhpClassMethodReader::EXCLUDE,
  [
    '/^setUp/',
    '/^(resolveSchemaFilename|tearDown|getSharedRequestHeaders)$/',
  ],
]);

/**
 * Create the EndToEndTestBase group.
 */
$reader->addClassToScan('\AKlump\DrupalTest\BrowserTestCase', [
  PhpClassMethodReader::EXCLUDE,
  ['/^(getBrowser)$/'],
], 'EndToEndTestBase');
$reader->addClassToScan('\aik099\PHPUnit\BrowserTestCase', [
  PhpClassMethodReader::INCLUDE,
  ['/^(getSession)$/'],
], 'EndToEndTestBase');
$reader->addClassToScan('\AKlump\DrupalTest\EndToEndTestBase', [
  PhpClassMethodReader::EXCLUDE,
  ['/^(isBrowserOnline)$/'],
]);

/**
 * Create the NodeElement group.
 */
$reader->addClassToScan('\Behat\Mink\Element\NodeElement', [
  PhpClassMethodReader::EXCLUDE,
  ['/^()$/'],
]);

/**
 * Create the WebAssert group.
 */
$reader->addClassToScan('\AKlump\DrupalTest\Utilities\MinkWebAssert', [
  PhpClassMethodReader::EXCLUDE,
  ['/^()$/'],
], function () {
  return 'WebAssert';
});
$reader->addClassToScan('\AKlump\DrupalTest\Utilities\WebAssert', [
  PhpClassMethodReader::EXCLUDE,
  ['/^()$/'],
], function () {
  return 'WebAssert';
});


$grouped_methods = $reader->scan();

// Generate the markup.
foreach ($grouped_methods as $group => $methods) {
  $contents = '';
  $methods = array_map(function ($method) use ($group) {
    $key = $group;
    if ($method['parent'] === 'AKlump\DrupalTest\BrowserTestCase') {
      $key .= ' <em>extends BrowserTestCase</em>';
    }

    return [$key => '<strong>' . $method['name'] . '</strong> <em>(' . implode(', ', $method['params']) . ')</em>'];
  }, $methods);
  $contents .= Markdown::table($methods) . PHP_EOL;
  $compiler->addInclude("_{$group}.md", $contents);
}
