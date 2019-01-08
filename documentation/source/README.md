# Drupal Test

![drupal-test](images/screenshot.jpg)

## Summary

This is a complete testing solution for using PhpUnit with Drupal 7 websites.  It provides Unit, Kernel, and Client abstract test classes to use in writing your tests, a single test runner, testing for modules and themes, support for JsonSchema validation, as well as a set of guidelines and processes for better testing of Drupal 7 modules and websites.  All of this using a single test runner that can be divided by test suite or filtered by test class using normal PhpUnit options.

Finally, [manual functional tests](https://github.com/aklump/manual-test) are supported as well.  

**Visit <https://aklump.github.io/drupal-test> for full documentation.**

## Quick Start

After installation (see below), follow instructions in the documentation (_docs/index.html_) to write and run tests.

## Requirements

* Composer
* PHPUnit

## Contributing

If you find this project useful... please consider [making a donation](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4E5KZHDQCEUV8&item_name=Gratitude%20for%20aklump%2Fdrupal-test).

## Installation

From inside the directory above the web root run this one-liner:

    [ ! -d tests ] && git clone https://github.com/aklump/drupal-test.git tests && (cd tests && ./bin/install.sh) || echo "Installation error, nothing installed."
   
### About the files in this project

The following files are considered core and should never be modified.

    drupal_test.yml
    drupal_test_bootstrap.php
    LICENSE
    README.md

Additionally, do not add files to the following folders, which are replaced on every update.  It is safe to add classes to _src_ so long as you avoid _src/DrupalTest_.
           
    docs
    src/DrupalTest
    
Do not modify any of the files in _bin_, which are provided by this module.  You may add your own files to _bin_, if you wish.        

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
