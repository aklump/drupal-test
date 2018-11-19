# Drupal Test

![drupal_test](docs/images/screenshot.jpg)

## Summary

This is a testing solution for using PhpUnit with Drupal 7 websites.  It provides Unit, Kernel, and Client abstract test classes to use in writing your tests, a single test runner, testing for modules and themes, as well as a set of guidelines and processes for better testing of Drupal 7 modules and websites. A single test runn

**Visit <https://aklump.github.io/drupal_test> for full documentation.**

## Quick Start

Download this project and rename the _dist_ folder _tests_.  Move it one level above your Drupal 7 webroot.  You may discard the rest of the project files.  From inside the new _tests_ directory run `composer install`.

Follow instructions in the documentation to write and run tests.  Load _docs/index.html_ in a browser.

## Requirements

* Composer
* PhpUnit

## Contributing

If you find this project useful... please consider [making a donation](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4E5KZHDQCEUV8&item_name=Gratitude%20for%20aklump%2Fdrupal_test).

## Installation

Beginning in a directory above the web root:

    $ git clone https://github.com/aklump/drupal_test.git
    $ mv drupal_test/dist tests
    $ rm -r drupal_test
    $ cd tests
    $ composer install
    
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
