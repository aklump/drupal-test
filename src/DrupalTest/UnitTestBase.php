<?php

namespace AKlump\DrupalTest;

/**
 * Base class for unit tests.
 *
 * Unit tests do not have access to the database, are faster and have not been
 * Drupal bootstrapped.
 *
 * @see Jig pattern gop/unit_test for implementation details.
 */
abstract class UnitTestBase extends EasyMockTestBase {

}
