<?php

namespace AKlump\DrupalTest\Utilities;

/**
 * Interface AssertInterface.
 *
 * @package AKlump\DrupalTest\Utilities
 */
interface AssertInterface {

  /**
   * Checks that current response code equals to provided one.
   *
   * @param int $code
   *   The expected HTTP status code.
   */
  public function statusCodeEquals($code);

  /**
   * Checks that current response code does not equal the provided one.
   *
   * @param int $code
   *   The HTTP status code to be sure it is not.
   */
  public function statusCodeNotEquals($code);

}
