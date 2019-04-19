<?php

namespace AKlump\DrupalTest\Utilities;

/**
 * Skip tests with @interactive when TEST_INTERACTIVE is not set.
 *
 * Trait InteractiveTrait will cause tests or classes marked with @interactive
 * to be skipped when running without TEST_INTERACTIVE=1.
 *
 * @package AKlump\DrupalTest\Utilities
 */
trait InteractiveTrait {

  /**
   * {@inheritdoc}
   */
  public function assertPreConditions() {
    $annotations = $this->getAnnotations();
    $class = isset($annotations['class']['interactive']);
    $method = isset($annotations['method']['interactive']);

    if ($class || $method) {
      if (!getenv('TEST_INTERACTIVE')) {
        $classname = get_class($this);
        $noun = $class ? $classname : '::' . $this->getName() . ' in ' . $classname;
        $this->markTestSkipped("Skipping @interactive $noun because missing TEST_INTERACTIVE environment variable. To allow this test you must set TEST_INTERACTIVE=1 and re-run.");
      }
    }
    parent::assertPreConditions();
  }

}
