# End to End Testing

> End-to-end testing is a Software testing methodology to test an application flow from start to end. The purpose of end to end testing is to simulate the real user scenario and validate the system under test and its components for integration and data integrity.

Create these tests by extending `\AKlump\DrupalTest\EndToEndTestBase`.  They should be saved in your _Client_ folders.

These tests are the slowest as they offer a full browser environment, so they facilitate the testing of forms, multi-page workflows, and Javascript interaction.  If you just want to check for elements on a single page, use `\AKlump\DrupalTest\ClientTestBase` instead.

These tests use [Mink](http://mink.behat.org/en/latest/index.html) and [Selenium](https://www.seleniumhq.org) to have a fully controllable browser, but **run against a real site in it's active state.**  These should be used when you wish to interact at the Browser level with a website in it's current state, just as you would as a human running manual tests.

> These are not the same as [Drupal 8's Browser Tests](https://www.drupal.org/docs/8/phpunit/phpunit-browser-test-tutorial) because: "Browser tests create a complete Drupal installation and a virtual web browser and then use the virtual web browser to walk the Drupal install through a series of tests, just like you would do if you were doing it by hand."
>
> **These tests DO NOT create a complete Drupal installation.**


## Installation

1. You must have a running selenium server to use these tests.

### Installing Selenium Standalone Server

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

## Organizing Tests

1. Think of a single test class as one end-to-end scenario.
1. Have lots of shorter test classes.
1. Break each scenario (class) into small units, as test methods.
1. Keep test methods small and named very discriptively.

## Annotations

1. Give the test class a summary such as _User is able to login..._
1. If the test modifies the database in any way mark the class `@destructive`.
1. Make test method names very descriptive so they can be parsed: _testLoggedInUserCanAccessUserSettingsPage_

       
    <?php
    
    namespace Drupal\Tests\gop;
    
    /**
     * User is able to login, change password, logout and login with new password.
     *
     * @destructive
     */
    class UserCanChangePasswordEndToEndTest extends EndToEndTestBase {
    
      public function testLoggedInUserCanAccessUserSettingsPage() {
        ...

#

## Using Mink's `WebAssert`

1. All of the methods of `\Behat\Mink\WebAssert` are available in your test methods through the use of `::assert()`, e.g.

        public function testPageTextOnHomepage() {
          $this->loadPageByUrl('/');
          
          // This is a method from \Behat\Mink\WebAssert that has been made available by \AKlump\DrupalTest\EndToEndTestBase
          $this->assert()->pageTextContains('Welcome to My Website');
        }

1. `::assert()` returns you a wrapper around \Behat\Mink\WebAssert that handles assertion tallies and failure exceptions normally throw by \Behat\Mink\WebAssert in a way native to PhpUnit.
1. There is some duplication with certain shorthand and common assertions, for example take these two examples of the same assertion.  The first one has an opinion that all elements should be searched using a CSS selector, so there's less typing, however the second is more powerful, and in fact necessary if you want to assert using XPath.

        $this->assertElementExists('.t-trigger-account-dropdown');
        $this->assert()->elementExists('css', '.t-trigger-account-dropdown');

## An Example End To End Test

1. Notice we load the page using relative links.

        $this->loadPageByUrl('/');

1. Then we get the elements by CSS selectors, which we'll need to work the test.  Notice the latter two begin with `.t-`.  To keep tests as flexible as possible I prefer to add _test only_ classes directly to elements, using these over any other kind of identification (class, id, XPath, etc).  These test classes will only be rendered during testing.  `::getDomElements` requires there is only one DOM node with each given class.

        $el = $this->getDomElements([
          '.search-widget',
          '.page-header__search a',
          '.t-text-search__input',
          '.t-text-search__submit',
        ]);

1. If all elements are located in the DOM then you will be able to work with them as in the example; if not the test will fail.  Each element is a [Node Element](http://mink.behat.org/en/latest/guides/traversing-pages.html), with all the corresponding methods.

        $el['.search-widget']->isVisible()

1. Notice the messages passed to the assertions.  These should be an affirmative statement describing the correct result.  You should include these as assert arguments, rather than as code comments in order to make your tests more readable.

        $this->assertFalse(
          $el['.search-widget']->isVisible(),
          "The search modal is hidden by default."
        );
1. The use of `waitFor` is only necessary if you have to wait for some JS to execute, or to wait for an AJAX load.
1. Lastly notice the use of `::readPage`, it must be called because the session has changed pages, and we want to make assertions on the newest page.

### The Entire Test

    <?php
    
    namespace Drupal\Tests\my_project;
    
    use AKlump\DrupalTest\EndToEndTestBase;
    
    /**
     * Tests the Story resource endpoint.
     */
    class SearchEndToEndTest extends EndToEndTestBase {
    
      public function testSearchLinkInHeaderSearchesByTextAndReturnsResults() {
        $this->loadPageByUrl('/');
    
        $el = $this->getDomElements([
          '.search-widget',
          '.page-header__search a',
          '.t-text-search__input',
          '.t-text-search__submit',
        ]);
    
        $this->assertFalse(
          $el['.search-widget']->isVisible(),
          "The search modal is hidden by default."
        );
    
        $el['.page-header__search a']->click();
    
        $this->assertTrue(
          $el['.search-widget']->isVisible(),
          "The search modal is revealed after clicking link."
        );
    
        $this->assertFalse(
          $el['.t-text-search__submit']->isVisible(),
          "The text search submit button is hidden by default."
        );
        $el['.t-text-search__input']->setValue('tree');
    
        $this->waitFor(function () use ($el) {
          return $el['.t-text-search__submit']->isVisible();
        },
          'Submit button is made visible after entering a search phrase.'
        );
        $el['.t-text-search__submit']->click();
    
        $this->readPage()
          ->assertPageContains('Planting a Tree of Peace', 'Search term yielded expected results.')
          ->assertPageContains('The Axis and the Sycamore');
      }
    
    }
