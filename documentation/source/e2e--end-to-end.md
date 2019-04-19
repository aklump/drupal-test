---
id: end-to-end
---
# End to End Testing

**End-to-end testing is a Software testing methodology to test an application flow from start to end.** The purpose of end to end testing is to simulate the real user scenario and validate the system under test and its components for integration and data integrity.

These tests use [Mink](http://mink.behat.org/en/latest/index.html) and [Selenium](https://www.seleniumhq.org) to have a fully controllable browser, but **run against a real site in it's active state.**  These should be used when you wish to interact at the browser level with a website in it's current state, just as you would as a human running manual tests.

Create these tests by extending `\AKlump\DrupalTest\EndToEndTestCase`.  They should be saved in your _EndToEnd_ folder.

These tests are the slowest as they offer a full browser environment, so they facilitate the testing of forms, multi-page workflows, and Javascript interaction.  If you just want to check for elements on a single page, use `\AKlump\DrupalTest\ClientTestCase` instead.

Note: These are not the same as [Drupal 8's Browser Tests](https://www.drupal.org/docs/8/phpunit/phpunit-browser-test-tutorial) because: "Browser tests create a complete Drupal installation and a virtual web browser and then use the virtual web browser to walk the Drupal install through a series of tests, just like you would do if you were doing it by hand."  **These tests DO NOT create a complete Drupal installation.**

## Organizing Tests

1. Think of a single test class as one end-to-end scenario.
1. Have lots of shorter test classes.
1. Break each scenario (class) into small units, as test methods.
1. Keep test methods small and named very discriptively.

## Using Mink's `WebAssert`

1. All of the methods of `\Behat\Mink\WebAssert` are available in your test methods through the use of `::assert()`, e.g.

        public function testPageTextOnHomepage() {
          $this->loadPageByUrl('/');
          
          // This is a method from \Behat\Mink\WebAssert that has been made available by \AKlump\DrupalTest\EndToEndTestCase
          $this->assert()->pageTextContains('Welcome to My Website');
        }

1. `::assert()` returns you a wrapper around \Behat\Mink\WebAssert that handles assertion tallies and failure exceptions normally throw by \Behat\Mink\WebAssert in a way native to PhpUnit.
1. There is some duplication with certain shorthand and common assertions, for example take these two examples of the same assertion.  The first one has an opinion that all elements should be searched using a CSS selector, so there's less typing, however the second is more powerful, and in fact necessary if you want to assert using XPath.

        $this->assertElementExists('.t-trigger-account-dropdown');
        $this->assert()->elementExists('css', '.t-trigger-account-dropdown');

## Annotations

1. Give the test class a summary such as _User is able to login..._
1. If the [test modifies the database](@destructive) in any way mark the class `@destructive`.
1. If [the test uses `assertManual`](@interactive), mark the class/method as `@interactive`.
1. Make test method names very descriptive so they can be parsed: _testLoggedInUserCanAccessUserSettingsPage_

        <?php
        
        namespace Drupal\Tests\gop;
        
        /**
         * User is able to login, change password, logout and login with new password.
         *
         * @destructive
         */
        class UserCanChangePasswordEndToEndTest extends EndToEndTestCase {
        
          public function testLoggedInUserCanAccessUserSettingsPage() {
            ...

## Pausing a Test for Inspection

While developing a test, it can be handy to pause a test during execution to study the page or DOM.  Call `::debugger` at the breakpoint in your test.  When the test runner reaches that point you will see a play button appear in the upper right of the screen.  The test runner is waiting for you to click that button.  Click it and the test will continue. `::debugger` should not be present in completed tests since they cause automation to hang.

![Debugger](images/debugger.jpg)

This `::debugger` method is intended for use during development of a test and to be discarded once the test is complete.  Here is an example:
    
      public function testShowingUseOfDebugger() {
        $el = $this->loadPageByUrl('/user/register')
          ->getDomElements([
            '.t-field_account_type',
          ]);
        $this->assertTrue($el['.t-field_account_type']->isVisible());
        $this->debugger();
        ...
      }

## Advanced End to End Testing

* See also [Observation Mode](@observation-mode).
* See also [Interactive End to End Tests](@interactive).

## Installing Selenium

1. You must have a running selenium server to use these tests.

This should be as simple as downloading a file, and starting a Java process on that file.  Follow these steps:

1. Download from [this page](https://www.seleniumhq.org/download/); the link should be at the top.  This needs to happen just once.  Place it in a logical location on your system, maybe your home folder.
1. Create a shortcut script _./bin/selenium.sh_ to launch your Selenium server with contents like this, adjusted for your situation.

        #!/usr/bin/env bash
        java -jar /Users/aklump/selenium/selenium-server-standalone-3.141.59.jar  -host 127.0.0.1 -port 4444

1. Make that script executable `chmod u+x ./bin/selenium.sh`
1. Start your server in a new terminal window:

        cd {path to this lib}
        ./bin/selenium.sh
        
1. Verify it's running by visiting: <http://127.0.0.1:4444/wd/hub/static/resource/hub.html>
