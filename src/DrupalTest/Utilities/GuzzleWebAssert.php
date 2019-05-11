<?php

namespace AKlump\DrupalTest\Utilities;

use AKlump\DrupalTest\BrowserTestCase;

class GuzzleWebAssert implements AssertInterface {

  protected $testCase;

  public function __construct(BrowserTestCase $test_case) {
    $this->testCase = $test_case;
    $this->response = $this->testCase->getResponse();
  }

  /**
   * {@inheritdoc}
   */
  public function statusCodeEquals($code) {
    $this->testCase->assertEquals($code, $this->response->getStatusCode());
  }

  /**
   * {@inheritdoc}
   */
  public function statusCodeNotEquals($code) {
    $this->testCase->assertNotEquals($code, $this->response->getStatusCode());
  }

}
