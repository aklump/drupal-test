<?php

namespace AKlump\DrupalTest\Drupal7;

use AKlump\PHPUnit\EasyMockTestBase;

/**
 * Base class for unit tests.
 *
 * Unit tests do not have access to the database, are faster and have not been
 * Drupal bootstrapped.
 */
abstract class UnitTestCase extends EasyMockTestBase {

  /**
   * {@inheritdoc}
   */
  public function getService($service_name) {
    return \Drupal::getContainer()->get(ltrim($service_name, '@'));
  }

}
