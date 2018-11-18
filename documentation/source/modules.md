# Module and Theme Testing

## Quick Start

1. Create _composer.json_ in the module ensuring it will autoload the module's classes that will be tested.  This file may have nothing to do with Drupal, but it is used by the test runner's unit testing strategy.
1. Add the path to the test runner's _composer.json_, and [you must call](https://github.com/wikimedia/composer-merge-plugin#updating-sub-levels-composerjson-files) `composer update --lock` on the test runner for dependencies to be installed for the test runner.
1. Add one or more tests to the module.

## Test File Structure

Each module or theme provides tests relative to it's own root directory.  Following this convention allows the test runner to auto-discover these tests.  e.g.,

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

        class Gop5ThemeKernelTest extends KernelTestBase {
        
          protected $schema = [
          
            // By setting this to false, we indicate we are not testing a class.
            'classToBeTested' => FALSE,
          ];
          
        ...
