<?php

namespace AKlump\DrupalTest\Drupal8;

use AKlump\PHPUnit\EasyMockTrait;
use Drupal\Tests\UnitTestCase as DrupalUnitTestCase;

/**
 * Base class for unit tests.
 *
 * Unit tests do not have access to the database, are faster and have not been
 * Drupal bootstrapped.
 */
abstract class UnitTestCase extends DrupalUnitTestCase {

  use EasyMockTrait;

}
