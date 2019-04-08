<?php

namespace AKlump\DrupalTest;

/**
 * A class to hold configuration info.
 */
class EasyMock {

  /**
   * A flag to indicate a full mocked object.
   *
   * This is the default.
   *
   * @var int
   */
  const FULL = 0;

  /**
   * A Flag to indicate a partial mock should be created.
   *
   * Used for element values as arrays on the classArgumentsMap config.
   *
   * @var int
   */
  const PARTIAL = 1;

  /**
   * A flag to indicate the argument value is not a classname but a value.
   *
   * Use this when a constructor argument is not a class instance.
   *
   * @var int
   */
  const VALUE = 2;

}
