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
  public static function getContainer() {
    return \Drupal::getContainer();
  }

}
