# How to Setup Testing of Modules and Themes

The term _extension_ will be used to refer to themes and modules interchangeably.

## Unit Test Quick Start

1. Ensure your _extension_ has _composer.json_ in it's root directory.
1. Configure [Drupal Test autoloading](@autoload) for your extension.
1. Namespace all tests with `Drupal\Tests\{extension_name}\{suite type}`
1. Place all tests relative to your extension's root directory in _tests/src/{suite type}/_, e.g. _my_module/tests/src/Unit/MyModuleUnitTest.php_
1. Suffix all test classes with _{suite type}Test.php_; not just _Test.php_.
1. Extend `\AKlump\DrupalTest\*\UnitTestCase` for your Unit test classes.

## Test File Structure

Each module or theme provides tests and schema files relative to it's own directory (`.`).  Following this convention allows the test runner to auto-discover these tests.  e.g.,

    .
    └── tests
        ├── jsonschema
        │   └── story_resource.json
        └── src
            ├── Client
            │   ├── Service
            │   │   └── EarthriseServiceClientTest.php
            ├── Kernel
            │   ├── Service
            │   │   ├── BreakpointServiceKernelTest.php
            │   └── TransformKernelTest.php
            ├── TestBase.php
            └── Unit
                ├── Service
                │   └── EarthriseServiceUnitTest.php
                └── TransformUnitTest.php

## Must Test Classes Test a Single Class?

Unit and Kernel tests do not have to test a single class, for example if you are writing a test to cover theme functions.  In order to make this happen you have to do the following in your test class:

        class InTheLoftThemeKernelTest extends KernelTestCase {
        
          protected $schema = [
          
            // By setting this to false, we indicate we are not testing a class.
            'classToBeTested' => FALSE,
          ];
          
        ...
