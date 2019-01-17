<?php

namespace AKlump\DrupalTest;

use aik099\PHPUnit\BrowserTestCase as ParentBrowserTestCase;
use AKlump\DrupalTest\Utilities\WebAssertProxy;

/**
 * A base class for Browser Tests.
 */
abstract class BrowserTestCase extends ParentBrowserTestCase {

  /**
   * This is read in from the environment variable TEST_BASE_URL.
   *
   * @var string
   */
  protected static $baseUrl = NULL;

  /**
   * The loaded html document from loadPageByUrl.
   *
   * @var string
   */
  protected $html;

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
   */
  protected static function handleBaseUrl() {
    if (!($url = getenv('TEST_BASE_URL'))) {
      static::markTestSkipped('Missing environment variable: TEST_BASE_URL');
    }
    static::$baseUrl = rtrim($url, '/');
  }

  /**
   * Resolve relative URLs based on the base url.
   *
   * @param $path
   * @param bool $remove_authentication_credentials
   *
   * @return mixed|string
   */
  public function resolvePath($path, $remove_authentication_credentials = FALSE) {
    if (strpos($path, 'http') !== 0) {
      if (substr($path, 0, 1) !== '/') {
        throw new \RuntimeException("relative \$path must begin with a forward slash.");
      }
      $path = rtrim(static::$baseUrl, '/') . "$path";
      $parts = parse_url($path);
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
          $path = str_replace($find, $replace, $path);
        }
      }
    }

    return $path;
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
   * {@inheritdoc}
   */
  public function loadPageByUrl($url) {
    $url = $this->resolvePath($url);
    $this->getSession()->visit($url);
    $this->html = $this->getSession()->getPage()->getContent();

    return $this;
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

}
