<?php

namespace AKlump\DrupalTest\Utilities;

use aik099\PHPUnit\BrowserTestCase;
use Behat\Mink\Session;

/**
 * A proxy for \Behat\Mink\WebAssert for better PHPUnit integration.
 *
 * Could not extend \Behat\Mink\WebAssert because the assert method is private.
 */
class WebAssert extends MinkWebAssert {

  /**
   * A value here will override the assert message.
   *
   * Each time ::assert() is called this is set to ''.
   *
   * @var string
   */
  public $message = '';

  /**
   * WebAssertProxy constructor.
   *
   * @param \aik099\PHPUnit\BrowserTestCase $testcase
   *   The PHPUnit test case using these asserts.
   * @param \Behat\Mink\Session $session
   *   The Mink session.
   */
  public function __construct(BrowserTestCase $testcase, Session $session) {
    $this->testcase = $testcase;
    $this->session = $session;
  }

  /**
   * Asserts a condition.
   *
   * @param bool $condition
   * @param string $message Failure message
   *
   * @return \aik099\PHPUnit\BrowserTestCase
   *   A instance of the test case for chaining.
   */
  protected function assert($condition, $message) {
    if ($condition) {
      $this->testcase->assertThat(TRUE, $this->testcase->isTrue());

    }
    else {
      $this->testcase->fail(empty($this->message) ? $message : $this->message);
    }
    $this->message = '';

    return $this->testcase;
  }

}
