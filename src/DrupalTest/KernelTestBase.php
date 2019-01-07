<?php

namespace AKlump\DrupalTest;

use Drupal\Driver\DrupalDriver;

/**
 * Base class for kernel tests.
 *
 * Kernel tests have access to the Drupal database and is fully bootstrapped.
 * Extend this class when a unit test is too mockey or relies on too many
 * outside things, e.g. constants and global functions.
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
    if (!($url = getenv('TEST_BASE_URL'))) {
      throw new \RuntimeException("Missing environment variable: TEST_BASE_URL");
    }
    if (!static::$isBootstrapped) {
      $driver = new DrupalDriver(WEB_ROOT, $url);
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

  /**
   * Return a random, published node by bundle type.
   *
   * @param string $bundle_type
   *   The node type.
   *
   * @return \stdClass|false
   *   The loaded node or false if none found.
   */
  public function getRandomPublishedEntityByBundle($bundle_type) {
    $query = db_select('node', 'n')
      ->fields('n', array('nid'))
      ->condition('status', 1)
      ->condition('type', $bundle_type)
      ->orderRandom()
      ->range(0, 1);
    $nid = $query->execute()->fetchField();

    return node_load($nid);
  }

}
