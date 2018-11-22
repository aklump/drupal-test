<?php

namespace AKlump\DrupalTest;

use AKlump\PHPUnit\EasyMockTestBase as Base;

/**
 * Abstract test class to easily test objects with auto mocking.
 */
abstract class EasyMockTestBase extends Base {

  /**
   * {@inheritdoc}
   */
  public function getService($service_name) {
    return \Drupal::getContainer()->get(ltrim($service_name, '@'));
  }

}
