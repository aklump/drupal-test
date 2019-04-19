<?php

namespace AKlump\DrupalTest;

use aik099\PHPUnit\BrowserTestCase as ParentBrowserTestCase;
use AKlump\DrupalTest\Utilities\Generators;
use AKlump\DrupalTest\Utilities\WebAssert;

/**
 * A base class for Browser Tests.
 */
abstract class BrowserTestCase extends ParentBrowserTestCase {

  /**
   * Holds data from the generator, keyed by method name.
   *
   * Use this to access earlier generated data, later in the test.
   *
   * @var \stdClass
   */
  protected static $stored;

  /**
   * Holds a static instance of this.
   *
   * @var \AKlump\DrupalTest\Utilities\Generators
   */
  protected static $generate;

  /**
   * This is read in from the environment variable TEST_BASE_URL.
   *
   * @var string
   */
  protected static $baseUrl = NULL;

  /**
   * Holds an instance with the current session.
   *
   * @var \AKlump\DrupalTest\Utilities\WebAssert
   */
  protected $webAssert;

  /**
   * The loaded content from loadPageByUrl.
   *
   * @var string
   */
  protected $content;

  /**
   * {@inheritdoc}
   */
  public function getBrowser() {
    $browser = parent::getBrowser();
    $browser->setBaseUrl(static::$baseUrl);

    return $browser;
  }

  /**
   * Import the base URL from the environment vars.
   *
   * This should be called in ::setUpBeforeClass.
   */
  protected static function handleBaseUrl() {
    if (!($url = getenv('TEST_BASE_URL')) && !($url = getenv('SIMPLETEST_BASE_URL'))) {
      static::markTestSkipped("Missing environment variable: TEST_BASE_URL or SIMPLETEST_BASE_URL");
    }
    static::$baseUrl = rtrim($url, '/');
  }

  /**
   * Resolve relative URLs based on the base url.
   *
   * @param string $url
   *   The relative URL to resolve, absolute URLs will pass through unchanged.
   * @param bool $remove_authentication_credentials
   *   If the base url contains http auth credentials and you want those
   *   removed from the resolved URL, set this to true.
   *
   * @return mixed|string
   *   An absolute URL.
   */
  protected function resolveUrl($url, $remove_authentication_credentials = FALSE) {
    if (strpos($url, 'http') !== 0) {
      if (substr($url, 0, 1) !== '/') {
        throw new \RuntimeException("relative \$url must begin with a forward slash.");
      }
      $url = rtrim(static::$baseUrl, '/') . "$url";
      $parts = parse_url($url);
      if ($remove_authentication_credentials) {
        $auth = [];
        if (!empty($parts['user'])) {
          $auth[] = $parts['user'];
        }
        if (!empty($parts['pass'])) {
          $auth[] = $parts['pass'];
        }
        if ($auth) {
          $find = $parts['scheme'] . '://' . implode(':', $auth) . '@';
          $replace = $parts['scheme'] . '://';
          $url = str_replace($find, $replace, $url);
        }
      }
    }

    return $url;
  }

  /**
   * Assert and return an array of DOM nodes for manipulation by a test.
   *
   * If the selector is not found, the test will fail.
   *
   * @param array $css_selectors
   *   An array of css selectors that are expected to be on the page.  Each
   *   selector must locate exactly one DOM node.  If you need the first
   *   element by class and you should use ::els and find by index.
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
   * Get an element by CSS selector.
   *
   * @param string $css_selector
   *   If this points to more than one DOM node an exception will be thrown
   *   unless you set $limit_to_one to false.
   * @param bool $limit_to_one
   *   Set this to FALSE if it's okay that $css_selector points to more than
   *   one node in the DOM.  In such a case the exception will not be thrown
   *   and the first node in the result set will be returned.  See also
   *   ::els().
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The node located by $css_selector.
   *
   * @throws \RuntimeException
   *   If $css_selector locates more than one element and $limit_to_one ===
   *   true.
   *
   * @see ::els()
   */
  public function el($css_selector, $limit_to_one = TRUE) {
    $el = $this->getSession()->getPage()->findAll('css', $css_selector);
    if ($limit_to_one && count($el) > 1) {
      throw new \RuntimeException("Expecting a single element for ::el($css_selector); for multiple elements use ::els() or set \$limit_to_one to false.");
    }

    return reset($el);
  }

  /**
   * Find all DOM (el)ements by css selector on the current page.
   *
   * Use this when you want more than the first matched node.
   *
   * @param string $css_selector
   *   The CSS selector string.
   *
   * @return \Behat\Mink\Element\NodeElement[]
   *   All located node elements.
   */
  public function els($css_selector) {
    return $this->getSession()->getPage()->findAll('css', $css_selector);
  }

  /**
   * Load a page so you can make assertions on it.
   *
   * This will set $this->content from the response.
   *
   * @param string $url
   *   An absolute or relative URL.
   *
   * @return \AKlump\DrupalTest\BrowserTestCase
   *   Self for chaining.
   */
  public function loadPageByUrl($url) {
    $url = $this->resolveUrl($url);
    $this->getSession()->visit($url);
    $this->content = $this->getSession()->getPage()->getContent();

    return $this;
  }

  /**
   * Return a WebAssert instance with the current session.
   *
   * @param string $fail_message
   *   An optional message to be displayed on failure.
   *
   * @return \AKlump\DrupalTest\Utilities\WebAssert
   *   An instance to use for asserting.
   */
  public function assert($fail_message = '') {
    if (empty($this->webAssert)) {
      $this->webAssert = new WebAssert($this, $this->getSession());
    }
    $this->webAssert->message = $fail_message;

    return $this->webAssert;
  }

  /**
   * Assert a DOM Element exists by CSS selector.
   *
   * @param string $css_selector
   *   The CSS selector.
   * @param string $failure_message
   *   An optional message to be displayed on failure.
   *
   * @return \AKlump\DrupalTest\BrowserTestCase
   *   Self for chaining.
   */
  public function assertElementExists($css_selector, $failure_message = '') {
    $this->assert($failure_message)->elementExists('css', $css_selector);

    return $this;
  }

  /**
   * Assert a DOM Element does not exist by CSS selector.
   *
   * @param string $css_selector
   *   The CSS selector.
   * @param string $failure_message
   *   An optional message to be displayed on failure.
   *
   * @return \AKlump\DrupalTest\BrowserTestCase
   *   Self for chaining.
   */
  public function assertElementNotExists($css_selector, $failure_message = '') {
    $this->assert($failure_message)
      ->elementNotExists('css', $css_selector);

    return $this;
  }

  /**
   * Assert that an element is present and visible on the page.
   *
   * @param string $css_selector
   *   The CSS selector.
   * @param string $failure_message
   *   An optional message to be displayed on failure.
   *
   * @return \AKlump\DrupalTest\BrowserTestCase
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
   * @return \AKlump\DrupalTest\BrowserTestCase
   *   Self for chaining.
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
   * Generate and store a value.
   *
   * @param string $method
   *   Any method on \AKlump\DrupalTest\Utilities\Generators.  If you pass a
   *   string with a colon, e.g. 'title:my_title', the latter will be used as
   *   the storage key.
   * @param ...
   *   Any additional params will be sent to generator function.
   *
   * @return mixed
   *   The generated value, is also added as $this->stored->{$as}.
   */
  public function generate($method) {
    static $generator;
    if (empty($generator)) {
      $generator = new Generators([
        'baseUrl' => static::$baseUrl,
      ]);
    }
    list($method, $as) = explode(':', $method) + [NULL, NULL];
    $value = $generator->{$method}();
    $as = $as ? $as : $method;
    $this->store($as, $value);

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

}
