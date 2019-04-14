<?php

namespace AKlump\DrupalTest;

use AKlump\DrupalTest\Utilities\DestructiveTrait;
use AKlump\DrupalTest\Utilities\EmailHandlerInterface;
use AKlump\DrupalTest\Utilities\WebAssertTrait;
use GuzzleHttp\Client;

/**
 * A class to interact with the browser for forms and navigation.
 */
abstract class EndToEndTestCase extends BrowserTestCase {

  const WAIT_TIMEOUT = 10;

  use DestructiveTrait;

  public static $browsers = array(
    array(
      'driver' => 'selenium2',
      'host' => 'localhost',
      'port' => 4444,
      'browserName' => 'chrome',
      'sessionStrategy' => 'shared',
    ),
  );

  /**
   * Holds the cookie jar used by requests.
   *
   * @var \GuzzleHttp\Cookie\CookieJar
   */
  protected static $cookieJar;

  /**
   * Holds an email handler interface for email testing.
   *
   * @var \AKlump\DrupalTest\Utilities\EmailHandlerInterface
   */
  protected $emailHandler;

  /**
   * Holds the response of the last remote call.
   *
   * @var null
   */
  protected $response = NULL;

  /**
   * The loaded page from loadPageByUrl.
   *
   * @var \Behat\Mink\Element\NodeElement
   */
  protected $page;

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass() {
    static::handleBaseUrl();
    if (!static::isBrowserOnline()) {
      self::markTestSkipped('Selenium server is offline.');
    }
  }

  /**
   * Verify if the Selenium browser server is online or not.
   *
   * @return bool
   *   True if online, false otherwise.
   */
  public static function isBrowserOnline() {
    $client = new Client();
    try {
      $response = $client->get('http://127.0.0.1:4444/wd/hub/static/resource/hub.html');
    }
    catch (\Exception $exception) {
      return FALSE;
    }

    return $response->getStatusCode() === 200;
  }

  /**
   * Wait for an elapsed time before continuing test.
   *
   * @param int $seconds
   *   The number of seconds to wait.
   */
  public function wait($seconds) {
    $start = time();
    $this->waitFor(function () use ($start, $seconds) {
      return time() - $start > $seconds;
    });
  }

  /**
   * Wait for a condition to return true.
   *
   * @param callable $test
   *   A function to call repeatedly until it returns true, or we hit the
   *   timeout.  Exceptions will not be caught.
   * @param string $description
   *   A description of what you're waiting for.
   * @param int|null $timeout
   *   Set this to 0 for no timeout.  Leave NULL for the default timeout.  Any
   *   other number is the number of seconds to timeout.
   *
   * @return bool
   *   True if success, false if timeout was reached.
   *
   * @link http://docs.behat.org/en/v2.5/cookbook/using_spin_functions.html
   */
  public function waitFor(callable $test, $description = NULL, $timeout = NULL) {
    if (is_null($timeout)) {
      $timeout = self::WAIT_TIMEOUT;
    }
    if (is_null($description)) {
      $description = 'callback to return true';
    }
    $start = time();
    while (TRUE) {
      if ($test()) {
        return TRUE;
      }
      sleep(1);
      if ($timeout && (time() - $start > $timeout)) {
        $this->fail('Timed out while waiting for: ' . $description);

        return FALSE;
      }
    }
  }

  /**
   * Set the Email Handler for retrieving email.
   *
   * @param \AKlump\DrupalTest\Utilities\EmailHandlerInterface $handler
   *   The handler instance.
   *
   * @return \AKlump\DrupalTest\EndToEndTestCase
   *   Self for chaining.
   */
  public function setEmailHandler(EmailHandlerInterface $handler) {
    $this->emailHandler = $handler;

    return $this;
  }

  /**
   * Wait for email(s) to be received.
   *
   * This will mark a test failed if the timeout is reached before the
   * $expected_count of emails are received, or more emails are received than
   * $expected_count.
   *
   * @param int $expected_count
   *   The number of emails expected.  Defaults to 1.  Set this to a higher
   *   number to wait for that many emails to be received in the handler's
   *   inbox.
   * @param int|null $timeout
   *   Set this to 0 for no timeout.  Leave NULL for the default timeout.  Any
   *   other number is the number of seconds to timeout.
   *
   * @return array
   *   Each element is an instance of \PhpMimeMailParser\Parser.
   */
  public function waitForEmail($expected_count = 1, $timeout = NULL) {
    if (empty($this->emailHandler)) {
      throw new \RuntimeException("You must first call ::setEmailHandler() to use this method.");
    }
    $emails = [];
    $to = $this->emailHandler->getInboxAddress();
    $this->waitFor(function () use (&$emails, $expected_count) {
      $emails = array_merge($emails, $this->emailHandler->readMail());

      return count($emails) >= $expected_count;
    }, "$expected_count email(s) received by $to.", $timeout);

    if ($expected_count !== ($actual = count($emails))) {
      $this->fail("Expected email count of $expected_count has been exceeded; $actual emails actually received.");
    }

    return $emails;
  }

  /**
   * Wait for an element to appear on the page.
   *
   * This is helpful when waiting for ajax loading.  You can use this to wait
   * for an element to have a class by simply adding the class to
   * $css_selector.
   *
   * @param string $css_selector
   *   A CSS selector of an element the page.
   * @param int|null $timeout
   *   Set this to 0 for no timeout.  Leave NULL for the default timeout.  Any
   *   other number is the number of seconds to timeout.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The element you waited for.
   */
  public function waitForElement($css_selector, $timeout = NULL) {
    $this->waitFor(function () use ($css_selector) {
      try {
        $this->assert()->elementExists('css', $css_selector);
      }
      catch (\Exception $exception) {
        return FALSE;
      }

      return TRUE;
    }, "$css_selector is present on the page", $timeout);

    return $this->el($css_selector);
  }

  /**
   * Wait for an element to become visible on the page.
   *
   * @param string $css_selector
   *   A CSS selector of an element the page.
   * @param int|null $timeout
   *   Set this to 0 for no timeout.  Leave NULL for the default timeout.  Any
   *   other number is the number of seconds to timeout.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The element you waited for.
   */
  public function waitForElementVisible($css_selector, $timeout = NULL) {
    $el = NULL;
    $this->waitFor(function () use ($css_selector, $el) {
      $el = $this->el($css_selector);

      return !empty($el) && $el->isVisible();
    }, "$css_selector is visible on the page", $timeout);

    return $el;
  }

  /**
   * Wait for an element to become hidden on the page.
   *
   * @param string $css_selector
   *   A CSS selector of an element the page.
   * @param int|null $timeout
   *   Set this to 0 for no timeout.  Leave NULL for the default timeout.  Any
   *   other number is the number of seconds to timeout.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The element you waited for.
   */
  public function waitForElementNotVisible($css_selector, $timeout = NULL) {
    $el = NULL;
    $this->waitFor(function () use ($css_selector, $el) {
      $el = $this->el($css_selector);

      return !empty($el) && !$el->isVisible();
    }, "$css_selector is hidden on the page", $timeout);

    return $el;
  }

  /**
   * Wait for text to appear on a page.
   *
   * @param string $substring
   *   The text to wait for.
   * @param null $timeout
   *   Optional timeout in seconds.
   */
  public function waitForPageContains($substring, $timeout = NULL) {
    $this->waitFor(function () use ($substring) {
      try {
        $this->assert()->pageTextContains($substring);
      }
      catch (\Exception $exception) {
        return FALSE;
      }

      return TRUE;
    }, "\"$substring\" is present on the page", $timeout);
  }

  /**
   * Break the test and hang.
   *
   * Use this to hang a test so you can inspect a page.  Should only be used
   * during the writing of tests.
   */
  public function debugger() {
    $this->waitFor(function () {
      return FALSE;
    }, '', 0);
  }

  /**
   * Scroll the page to the top.
   */
  protected function scrollTop() {
    $this->getSession()->executeScript('window.scrollTo(0,0);');
  }

}
