<?php

namespace AKlump\DrupalTest;

use aik099\PHPUnit\BrowserTestCase;
use AKlump\DrupalTest\Utilities\DestructiveTrait;
use AKlump\DrupalTest\Utilities\Generators;
use AKlump\DrupalTest\Utilities\WebAssertProxy;
use AKlump\DrupalTest\Utilities\WebAssertTrait;

/**
 * A class to interact with the browser for forms and navigation.
 */
abstract class EndToEndTestBase extends BrowserTestCase implements HttpTestInterface {

  use HttpTestBaseTrait;
  use DestructiveTrait;

  public static $browsers = array(
    array(
      'driver' => 'selenium2',
      'host' => 'localhost',
      'port' => 4444,
      'browserName' => 'chrome',
      'baseUrl' => 'http://develop.globalonenessproject.loft',
      'sessionStrategy' => 'shared',
    ),
  );

  /**
   * Holds autodiscovered schema filepaths.
   *
   * @var array
   */
  protected static $jsonSchema = [];

  /**
   * Holds the cookie jar used by requests.
   *
   * @var \GuzzleHttp\Cookie\CookieJar
   */
  protected static $cookieJar;

  protected static $generate;

  /**
   * Holds data from the generator, keyed by method name.
   *
   * Use this to access earlier generated data, later in the test.
   *
   * @var \stdClass
   */
  protected static $stored;

  /**
   * An extra message to use for failed WebAssert assertions.
   *
   * @var string
   */
  public $webAssertMessage;

  /**
   * Holds an instance with the current session.
   *
   * @var \AKlump\DrupalTest\Utilities\WebAssertProxy
   */
  protected $webAssert;

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

  private $generator;

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass() {
    static::handleBaseUrl();
  }


  /**
   * {@inheritdoc}
   */
  public function el($css_selector) {
    return $this->getSession()->getPage()->find('css', $css_selector);
  }

  /**
   * Find all DOM (el)ements by css selector on the current page.
   *
   * Use this when you want more than the first matched node.
   *
   * @return array
   *   All located node elements.
   */
  public function els($css_selector) {
    return $this->getSession()->getPage()->findAll('css', $css_selector);
  }

  /**
   * Generate and store a value.
   *
   * @param string $method
   *   Any method on \AKlump\DrupalTest\Utilities\Generators.
   *
   * @return mixed
   *   The generated value, is also added as $this->stored->{$method}.
   */
  public function generate($method) {
    static $generator;
    if (empty($generator)) {
      $generator = new Generators([
        'baseUrl' => static::$baseUrl,
      ]);
    }
    $value = $generator->{$method}();
    $this->store($method, $value);

    return $value;
  }

  /**
   * Store/overwrite a value for later retrieval.
   *
   * @param string $key
   *   The key to use in $this->stored.
   * @param mixed $value
   *   THe value to store.
   *
   * @return $this
   *
   * @see ::generate()
   */
  public function store($key, $value) {
    if (is_null(static::$stored)) {
      static::$stored = new \stdClass();
    }
    static::$stored->{$key} = $value;

    return $this;
  }

  /**
   * Return a stored value.
   *
   * @param string $key
   *   The key of the stored value.
   * @param null $default
   *   An optional default value if not already stored.
   *
   * @return mixed
   *   The stored or default value.
   */
  public function getStored($key, $default = NULL) {
    if (!isset(static::$stored->{$key})) {
      return $default;
    }

    return static::$stored->{$key};
  }

  /**
   * {@inheritdoc}
   */
  public function getBrowser() {
    $browser = parent::getBrowser();
    $browser->setBaseUrl(static::$baseUrl);

    return $browser;
  }

  /**
   * Assert and return an array of DOM nodes for manipulation by a test.
   *
   * If the selector is not found, the test will fail.
   *
   * @param array $css_selectors
   *   An array of css selectors that are expected to be on the page.  Each
   *   selector must locate exactly one DOM node.  If you need the first
   *   element by class and it's okay there are more than one, you can use:
   *   ::el instead.
   *
   * @return array
   *   Keyed by the $css_selectors.
   *   Values are instances of \Behat\Mink\Element\NodeElement.
   *
   * @see ::el
   */
  public function getDomElements(array $css_selectors) {
    $page = $this->getSession()->getPage();
    if (!$page) {
      throw new \RuntimeException("::getElements() must come after starting a Mink session.");
    }

    return array_combine($css_selectors, array_map(function ($selector) use ($page) {
      $el = $page->findAll('css', $selector);
      $this->assertNotEmpty($el, "Cannot locate $selector in the DOM.");
      $this->assertCount(1, $el, "\"$selector\" must only return a single node.");

      return reset($el);
    }, $css_selectors));
  }

  /**
   * {@inheritdoc}
   */
  public function loadPageByUrl($url) {
    $url = $this->resolvePath($url);
    $this->getSession()->visit($url);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function assertPageContains($expected, $failure_message = '') {
    return $this->assert($failure_message)->pageTextContains($expected);
  }

  /**
   * {@inheritdoc}
   */
  public function assertPageNotContains($expected, $failure_message = '') {
    return $this->assert($failure_message)->pageTextNotContains($expected);
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
      $timeout = 5;
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
        return $this->assert()->pageTextContains($substring);
      }
      catch (\Exception $exception) {
        return FALSE;
      }

      return TRUE;
    }, "\"$substring\" is present on the page", $timeout);
  }

  /**
   * Assert that an element is present and visible on the page.
   *
   * @param string $css_selector
   *   The CSS selector.
   * @param string $failure_message
   *   An optional message to be displayed on failure.
   *
   * @return \AKlump\DrupalTest\EndToEndTestBase
   *   Self for chaining.
   */
  public function assertElementVisible($css_selector, $failure_message = '') {
    if (empty($failure_message)) {
      $failure_message = 'Expecting "' . $css_selector . '" to be visible.';
    }
    static::assertThat($this->getSession()
      ->getPage()
      ->find('css', $css_selector)
      ->isVisible(), static::isTrue(), $failure_message);

    return $this;
  }

  /**
   * Assert that an element is present and not visible on the page.
   *
   * @param string $css_selector
   *   The CSS selector.
   * @param string $failure_message
   *   An optional message to be displayed on failure.
   *
   * @return \AKlump\DrupalTest\EndToEndTestBase
   */
  public function assertElementNotVisible($css_selector, $failure_message = '') {
    if (empty($failure_message)) {
      $failure_message = 'Expecting "' . $css_selector . '" to be hidden.';
    }
    static::assertThat($this->getSession()
      ->getPage()
      ->find('css', $css_selector)
      ->isVisible(), static::isFalse(), $failure_message);

    return $this;
  }

  /**
   * Assert a DOM Element exists by CSS selector.
   *
   * @param string $css_selector
   *   The CSS selector.
   * @param string $failure_message
   *   An optional message to be displayed on failure.
   *
   * @return \AKlump\DrupalTest\EndToEndTestBase
   *   Self for chaining.
   */
  public function assertElementExists($css_selector, $failure_message = '') {
    return $this->assert($failure_message)->elementExists('css', $css_selector);
  }

  /**
   * Assert a DOM Element does not exist by CSS selector.
   *
   * @param string $css_selector
   *   The CSS selector.
   * @param string $failure_message
   *   An optional message to be displayed on failure.
   *
   * @return \AKlump\DrupalTest\EndToEndTestBase
   *   Self for chaining.
   */
  public function assertElementNotExists($css_selector, $failure_message = '') {
    return $this->assert($failure_message)
      ->elementNotExists('css', $css_selector);
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
   * Return a WebAssert instance with the current session.
   *
   * @param string $fail_message
   *   An optional message to be displayed on failure.
   *
   * @return \AKlump\DrupalTest\Utilities\WebAssertProxy
   *   An instance to use for asserting.
   */
  public function assert($fail_message = '') {
    $this->webAssertMessage = $fail_message;
    if (empty($this->webAssert)) {
      $this->webAssert = new WebAssertProxy($this, $this->getSession());
    }

    return $this->webAssert;
  }

  /**
   * Scroll the page to the top.
   */
  protected function scrollTop() {
    $this->getSession()->executeScript('window.scrollTo(0,0);');
  }

}
