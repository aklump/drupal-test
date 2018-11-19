<?php

namespace AKlump\DrupalTest;

use Drupal\Driver\DrupalDriver;

/**
 * Base class for kernel tests.
 *
 * Kernel tests have access to the Drupal database and is fully bootstrapped.
 * Extend this class when a unit test is too mockey or relies on too many
 * outside things, e.g. constants and global functions.
 *
 * @see Jig pattern gop/unit_test for implementation details.
 */
abstract class KernelTestBase extends EasyMockTestBase {

  /**
   * Tracks if the Kernel tests have bootstrapped Drupal.
   *
   * @var bool
   */
  protected static $isBootstrapped = FALSE;

  /**
   * Bootstrap Drupal for our Kernel testing.
   *
   * @throws \Drupal\Driver\Exception\BootstrapException
   */
  public static function setUpBeforeClass() {
    if (!static::$isBootstrapped) {
      $driver = new DrupalDriver(WEB_ROOT, 'http://develop.globalonenessproject.loft');
      $driver->setCoreFromVersion();
      $driver->bootstrap();
      static::$isBootstrapped = TRUE;
    }
  }

  /**
   * Assert HTML markup contains a class.
   *
   * This is helpful for testing theme renders.
   *
   * @param string $class
   *   The name of the class to check.
   * @param string $html
   *   Some HTML markup.
   */
  public static function assertHtmlHasCssClass($class, $html) {
    // TODO This could be tightened up.
    static::assertRegExp('/[ "]' . preg_quote($class) . '[ "]/', $html);
  }

}
