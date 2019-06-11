<?php

namespace AKlump\DrupalTest\Drupal7;

use AKlump\PHPUnit\EasyMockTrait;
use PHPUnit\Framework\TestCase;

/**
 * Base class for unit tests.
 *
 * Unit tests do not have access to the database, are faster and have not been
 * Drupal bootstrapped.
 */
abstract class UnitTestCase extends TestCase {

  use EasyMockTrait;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->easyMockSetUp();
  }

  /**
   * {@inheritdoc}
   */
  public function getService($service_name) {
    return \Drupal::getContainer()->get(ltrim($service_name, '@'));
  }

}
