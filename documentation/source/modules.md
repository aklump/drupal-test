# How to Setup Testing of Modules and Themes

> In order for unit testing to work with a module or a theme, you must [configure autoloading](@autoload).

## Quick Start

1. If your module already has a _composer.json_ file then [add it](@autoload) to _drupal_test_config.yml_.
1. Otherwise determine and configure the best means of autoloading by reading [this](@autoload:module).
1. Add one or more tests to the module (according the file structure convention) extending one of the abstract classes provided by this module, e.g. `\AKlump\DrupalTest\UnitTestBase`, `

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

        class InTheLoftThemeKernelTest extends KernelTestBase {
        
          protected $schema = [
          
            // By setting this to false, we indicate we are not testing a class.
            'classToBeTested' => FALSE,
          ];
          
        ...
