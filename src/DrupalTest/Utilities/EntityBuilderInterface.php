<?php

namespace AKlump\DrupalTest\Utilities;

use AKlump\DrupalTest\EndToEndTestCase;

interface EntityBuilderInterface {

  /**
   * Use to indicate a field should be ignored when entering data.  Should be
   * returned by any fill__* method.
   *
   * @var bool
   */
  const SKIP_FORM_ENTRY = 1;
  const FORM_ENTRY_COMPLETE = 2;

  public function save(EndToEndTestCase $test_case): int;

  public static function create(array $data): EntityBuilderInterface;

}
