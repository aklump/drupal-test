<?php

namespace AKlump\DrupalTest\Drupal7;

use AKlump\DrupalTest\EasyMockTestBase;

/**
 * Base class for unit tests.
 *
 * Unit tests do not have access to the database, are faster and have not been
 * Drupal bootstrapped.
 */
abstract class UnitTestCase extends EasyMockTestBase {

}
