# Drupal Test

![drupal-test](images/screenshot.jpg)

## Summary

This is a testing solution for using PhpUnit with Drupal 7 websites.  It provides Unit, Kernel, and Client abstract test classes to use in writing your tests, a single test runner, testing for modules and themes, support for JsonSchema validation, as well as a set of guidelines and processes for better testing of Drupal 7 modules and websites.  All of this using a single test runner that can be divided by test suite or filtered by test class using normal PhpUnit options.

**Visit <https://aklump.github.io/drupal-test> for full documentation.**

## Quick Start

Download this project and rename the _dist_ folder _tests_.  Move it one level above your Drupal 7 webroot.  You may discard the rest of the project files.  From inside the new _tests_ directory run `composer install`.

Follow instructions in the documentation to write and run tests.  Load _docs/index.html_ in a browser.

## Requirements

* Composer
* PhpUnit

## Contributing

If you find this project useful... please consider [making a donation](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4E5KZHDQCEUV8&item_name=Gratitude%20for%20aklump%2Fdrupal-test).

## Installation

From inside the directory above the web root run this one-liner:

    [ ! -d tests ] && git clone https://github.com/aklump/drupal-test.git tests && (cd tests && ./bin/install.sh) || echo "Installation error, nothing installed."
   
   
### About the files in this project

The following files are considered core and should never be modified.

    bootstrap_tests.php
    LICENSE
    README.md

Additionally, do not add files to the following folders as replaced on every update.  It is safe to add classes to _src_ so long as avoid _src/DrupalTest_.
           
    bin
    docs
    src/DrupalTest

## Update to the latest version

From inside the _tests_ directory, run:

    ./bin/update.sh
    
This will copy over the core files from the latest repository, but leave the non-core files alone, namely _phpunit.xml_ and _composer.json_, which you most-likely will have modified.

## Configuration

See documentation for more information about configuration.

1. Open _tests/composer.json_ and add module and theme _composer.json_ filepaths.
1. From _tests_ run `composer update --lock`.
1. Open _tests/phpunit.xml_ and add any JSON schema directories.

## Usage

### Run All Tests

    $ cd tests
    $ phpunit -c phpunit.xml

### Run All Unit Tests

    $ cd tests
    $ phpunit -c phpunit.xml --testsuite Unit
    

### Run All Kernel Tests

    $ cd tests
    $ phpunit -c phpunit.xml --testsuite Kernel
    

### Run All Client Tests

    $ cd tests
    $ phpunit -c phpunit.xml --testsuite Client
    
Refer to the documentation for more info.
