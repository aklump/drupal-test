# Module and Theme Testing

> In order for unit testing to work with a module or a theme, you must add the module or theme's _composer.json_ file to _test/composers.json_ and run `composer install --lock` from the _test_ directory.

## Quick Start

1. When you see `tests/composer.json`, we are referring the file included in the _dist_ folder of this project, which is the root directory containing the test runner _phpunit.xml_.
1. Create _composer.json_ in the module's folder ensuring it will autoload the module's classes that will be tested.  If not testing classes, but functions, make sure the module's _composer.json_ file loads all files that contain the functions to be tested, usually using `requre-dev`.  This file may or may not have anything to do with Drupal (depending on the module's implementation), but it is required by the test runner's unit testing strategy.
1. Add the path to the test runner's _composer.json_ to _tests/composer.json_, and [you must call](https://github.com/wikimedia/composer-merge-plugin#updating-sub-levels-composerjson-files) `composer update --lock` on the test runner for dependencies to be installed for the test runner.
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

## Add the Module's _composer.json_

For a module to be unit testable it must have a _composer.json_ file, which autoloads it's classes, the path of which **must be added to this project's _composer.json_ file**, in the section `extra.merge-plugin.require`.  This is how the unit tests are able to autoload classes without bootstrapping Drupal, e.g.,

        "extra": {
            "merge-plugin": {
                "require": [
                    "../web/sites/all/modules/custom/gop3_core/composer.json"
                ]
            }
        }

## Must Test Classes Test a Single Class?

Unit and Kernel tests do not have to test a single class, for example if you are writing a test to cover theme functions.  In order to make this happen you have to do the following in your test class:

        class InTheLoftThemeKernelTest extends KernelTestBase {
        
          protected $schema = [
          
            // By setting this to false, we indicate we are not testing a class.
            'classToBeTested' => FALSE,
          ];
          
        ...
