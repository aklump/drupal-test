<?php

/**
 * @file
 * A hook file to generate cheatsheets partials.
 *
 * Be aware that if you change a class and composer has been optimized, you may
 *   have to run composer dump for the change to appear.
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
$trait_reader = new PhpClassMethodReader([
  'show_args' => TRUE,
]);
$helpers_reader = new PhpClassMethodReader([
  'show_args' => TRUE,
]);

$helpers_reader->addClassToScan('\AKlump\DrupalTest\Utilities\Balloon');
$helpers_reader->addClassToScan('\AKlump\DrupalTest\Utilities\Url');

/**
 * Create the Traits group.
 */
$trait_reader->addClassToScan('\AKlump\DrupalTest\Utilities\CKEditorTrait');
$trait_reader->addClassToScan('\AKlump\DrupalTest\Utilities\InteractiveTrait', [
  PhpClassMethodReader::INCLUDE,
  ['/.*/'],
]);
$trait_reader->addClassToScan('\AKlump\DrupalTest\Utilities\TimeTrait');




$reader->excludeFromAll([
  '/^(assertPreConditions|setUpBeforeClass|__construct)$/',
]);

/**
 * Create the ClientTestCase group.
 */
$reader->addClassToScan('\AKlump\DrupalTest\BrowserTestCase', [
  PhpClassMethodReader::EXCLUDE,
  ['/^(getBrowser|setUp)$/'],
], 'ClientTestCase');
$reader->addClassToScan('\aik099\PHPUnit\BrowserTestCase', [
  PhpClassMethodReader::INCLUDE,
  ['/^(getSession)$/'],
], 'ClientTestCase');
$reader->addClassToScan('\AKlump\DrupalTest\ClientTestCase', [
  PhpClassMethodReader::EXCLUDE,
  [
    '/^setUp/',
    '/^(resolveSchemaFilename|tearDown|getSharedRequestHeaders|setUp)$/',
  ],
]);

/**
 * Create the EndToEndTestCase group.
 */
$reader->addClassToScan('\AKlump\DrupalTest\BrowserTestCase', [
  PhpClassMethodReader::EXCLUDE,
  ['/^(getBrowser|setUp)$/'],
], 'EndToEndTestCase');
$reader->addClassToScan('\aik099\PHPUnit\BrowserTestCase', [
  PhpClassMethodReader::INCLUDE,
  ['/^(getSession)$/'],
], 'EndToEndTestCase');
$reader->addClassToScan('\AKlump\DrupalTest\EndToEndTestCase', [
  PhpClassMethodReader::EXCLUDE,
  ['/^(isBrowserOnline|injectCssStyles|.+AssertPreConditions|setUp)$/'],
]);

/**
 * Create the Generators group.
 */
$reader->addClassToScan('\AKlump\DrupalTest\Utilities\Generators', [
  PhpClassMethodReader::EXCLUDE,
  ['/setConfig/'],
]);

/**
 * Create the DocumentElement group.
 */
$reader->addClassToScan('\Behat\Mink\Element\Element', [
  PhpClassMethodReader::EXCLUDE,
  ['/^()$/'],
], 'DocumentElement');
$reader->addClassToScan('\Behat\Mink\Element\TraversableElement', [
  PhpClassMethodReader::EXCLUDE,
  ['/^()$/'],
], 'DocumentElement');
$reader->addClassToScan('\Behat\Mink\Element\DocumentElement', [
  PhpClassMethodReader::EXCLUDE,
  ['/^()$/'],
], 'DocumentElement');

/**
 * Create the NodeElement group.
 */
$reader->addClassToScan('\Behat\Mink\Element\Element', [
  PhpClassMethodReader::EXCLUDE,
  ['/^()$/'],
], 'NodeElement');
$reader->addClassToScan('\Behat\Mink\Element\TraversableElement', [
  PhpClassMethodReader::EXCLUDE,
  ['/^()$/'],
], 'NodeElement');
$reader->addClassToScan('\Behat\Mink\Element\NodeElement', [
  PhpClassMethodReader::EXCLUDE,
  ['/^()$/'],
], 'NodeElement');

/**
 * Create the Parser.
 */
$reader->addClassToScan('PhpMimeMailParser\Parser', [
  PhpClassMethodReader::EXCLUDE,
  [
    '/^addM/',
    '/^set/',
    '/^save/',
    '/^getCharset$/',
    '/^getStream/',
    '/^getResource/',
    '/^__destruct/',
  ],
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

$grouped_methods = array_merge($reader->scan(), $trait_reader->scan(), $helpers_reader->scan());

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
