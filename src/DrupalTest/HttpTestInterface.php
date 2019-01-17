<?php

namespace AKlump\DrupalTest;

/**
 * Interface HttpTestInterface for all test cases that test against endpoints.
 *
 * @package AKlump\DrupalTest
 */
interface HttpTestInterface {

  /**
   * Load a DOM from an URL.
   *
   * This must be called in any test method using any assertDom* methods.
   * This loads the following:
   *   - $this->dom
   *   - $this->html
   *   - $this->response.
   *
   * @param string $url
   *   The URL to load into the DOM.
   *
   * @return \AKlump\DrupalTest\ClientTestBase
   *   Self for chaining.
   */
  public function loadPageByUrl($url);

  /**
   * Assert a loaded page contains a string.
   *
   * @param string $expected
   *   The string to search for.
   * @param string $failure_message
   *   An optional message to be displayed on failure.
   *
   * @return $this
   */
  public function assertPageContains($expected, $failure_message = '');

  /**
   * Find a DOM (el)ement by css selector on the current page.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The first located node element.
   */
  public function el($css_selector);

}
