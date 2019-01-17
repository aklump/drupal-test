<?php

namespace AKlump\DrupalTest\Utilities;

use aik099\PHPUnit\BrowserTestCase;
use AKlump\DrupalTest\HttpTestInterface;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Session;
use Behat\Mink\WebAssert;

/**
 * A proxy for \Behat\Mink\WebAssert for better PHPUnit integration.
 *
 * Could not extend \Behat\Mink\WebAssert because the assert method is private.
 */
class WebAssertProxy {

  /**
   * WebAssertProxy constructor.
   *
   * @param \AKlump\DrupalTest\HttpTestInterface $testcase
   *   The PHPUnit test case using these asserts.
   * @param \Behat\Mink\Session $session
   *   The Mink session.
   */
  public function __construct(BrowserTestCase $testcase, Session $session) {
    $this->testcase = $testcase;
    $this->session = $session;
  }

  /**
   * A proxy method to run WebAssert methods natively to PhpUnit.
   *
   * @param string $method
   *   The method to call on WebAssert class.
   * @param array $args
   *   The arguments to send to the method.
   *
   * @return \AKlump\DrupalTest\HttpTestInterface
   *   An instance for chaining.
   *
   * @see $this->testcase->assertMessage
   */
  private function assert($method, array $args) {
    try {
      $method = explode('::', $method);
      $method = end($method);

      if (empty($this->assertInstance)) {
        $this->assertInstance = new WebAssert($this->session);
      }

      call_user_func_array([$this->assertInstance, $method], $args);
      $this->testcase->assertThat(TRUE, $this->testcase->isTrue());
    }
    catch (ExpectationException $exception) {
      $message = [];
      if (!empty($this->testcase->webAssertMessage)) {
        $message[] = $this->testcase->webAssertMessage;
      }
      $message[] = $exception->getMessage();
      $this->testcase->fail(implode(PHP_EOL, $message));
    }

    return $this->testcase;
  }

  public function addressEquals() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function addressMatches() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function addressNotEquals() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function checkboxChecked() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function checkboxNotChecked() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function cookieEquals() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function cookieExists() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function elementAttributeContains() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function elementAttributeExists() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function elementAttributeNotContains() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function elementContains() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function elementExists() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function elementNotContains() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function elementNotExists() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function elementsCount() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function elementTextContains() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function elementTextNotContains() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function fieldExists() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function fieldNotExists() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function fieldValueEquals() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function fieldValueNotEquals() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function pageTextContains() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function pageTextMatches() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function pageTextNotContains() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function pageTextNotMatches() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function responseContains() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function responseHeaderContains() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function responseHeaderEquals() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function responseHeaderMatches() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function responseHeaderNotContains() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function responseHeaderNotEquals() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function responseHeaderNotMatches() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function responseMatches() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function responseNotContains() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function responseNotMatches() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function statusCodeEquals() {
    return $this->assert(__METHOD__, func_get_args());
  }

  public function statusCodeNotEquals() {
    return $this->assert(__METHOD__, func_get_args());
  }

}
