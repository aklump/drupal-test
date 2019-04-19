<?php

namespace AKlump\DrupalTest\Utilities;

/**
 * Skip tests with @destructive on production urls.
 *
 * Trait DestructiveTrait will cause tests or classes marked with @destructive
 * to be skipped when running using a TEST_BASE_URL that is not listed in
 * phpunit.xml::allowDestructiveTests.
 *
 * @package AKlump\DrupalTest\Utilities
 */
trait DestructiveTrait {

  /**
   * The configured urls that are allowed to run @destructive tests.
   *
   * @var array
   */
  static protected $allowDestructiveTestsUrls;

  /**
   * {@inheritdoc}
   */
  public function assertPreConditions() {
    $annotations = $this->getAnnotations();
    $class = isset($annotations['class']['destructive']);
    $method = isset($annotations['method']['destructive']);

    if ($class || $method) {
      if (empty(static::$allowDestructiveTestsUrls)) {
        global $__PHPUNIT_CONFIGURATION_FILE;
        $config = simplexml_load_file($__PHPUNIT_CONFIGURATION_FILE);
        static::$allowDestructiveTestsUrls = (array) $config->allowDestructiveTests->url;
      }
      if (!in_array(static::$baseUrl, static::$allowDestructiveTestsUrls)) {
        $classname = get_class($this);
        $noun = $class ? $classname : '::' . $this->getName() . ' in ' . $classname;
        $this->markTestSkipped("Skipping @destructive $noun while testing against " . static::$baseUrl . '. To allow this test you must add ' . static::$baseUrl . ' to phpunit.xml::allowDestructiveTests and re-run.');
      }
    }
    parent::assertPreConditions();
  }

}
