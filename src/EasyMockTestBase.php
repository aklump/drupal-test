<?php

namespace AKlump\DrupalTest;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * Abstract test class to easily test objects with auto mocking.
 */
abstract class EasyMockTestBase extends \PHPUnit_Framework_TestCase {

  /**
   * A flag to indicate a full mocked object.
   *
   * This is the default.
   *
   * @var int
   */
  const FULL_MOCK = 0;

  /**
   * A Flag to indicate a partial mock should be created.
   *
   * Used for element values as arrays on the classArgumentsMap config.
   *
   * @var int
   */
  const PARTIAL_MOCK = 1;

  use MockeryPHPUnitIntegration;

  /**
   * An instance of the class being tested.
   *
   * @var mixed
   */
  protected $obj;

  /**
   * All mocked objects from $argsMap.
   *
   * @var object
   */
  protected $args;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    if (empty($this->schema)) {
      throw new \RuntimeException("Missing protected property test definition: \$schema in " . static::class . '.');
    }

    $this->schema += [
      'classArgumentsMap' => [],
      'mockObjectsMap' => [],
    ];

    $is_unit_test = $this instanceof UnitTestBase;

    // Allow our tests classes to not use this convention by setting the class
    // to false.  This prevents mocking and instantiation.
    if ($this->schema['classToBeTested'] !== FALSE) {

      $provided_args = !empty($this->args) ? (array) $this->args : [];
      $args = [];
      foreach ($this->schema['classArgumentsMap'] as $property => $class) {

        if (!is_array($class)) {
          $class = [$class, self::FULL_MOCK];
        }
        list($class, $mock_type) = $class;

        $is_service = strpos($class, '@') === 0;
        if ($is_service && $is_unit_test) {
          throw new \RuntimeException("The service container is not available in Unit tests; try a Kernal test instead.");
        }

        // Only set the argument if it's empty.  This allows for seeding in the
        // caller's ::setUp method.
        if (!isset($provided_args[$property])) {
          if ($is_service) {
            // Load a service from the container if the map begins with '@'.
            $args[$property] = \Drupal::getContainer()
              ->get(ltrim($class, '@'));
          }
          else {

            // Otherwise create a mock object.
            $args[$property] = Mockery::mock($class);
            if ($mock_type === self::PARTIAL_MOCK) {
              $args[$property]->makePartial();
            }
          }
        }
        else {
          $args[$property] = $provided_args[$property];
        }
      }
      $this->args = (object) $args;
    }

    foreach ($this->schema['mockObjectsMap'] as $property => $class) {
      if (!isset($this->{$property})) {
        if (!is_array($class)) {
          $class = [$class, self::FULL_MOCK];
        }
        list($class, $mock_type) = $class;
        $this->{$property} = Mockery::mock($class);
        if ($mock_type === self::PARTIAL_MOCK) {
          $this->{$property}->makePartial();
        }
      }
    }
    if ($this->schema['classToBeTested'] !== FALSE) {
      $this->createObj();
    }
  }

  /**
   * Create a new instance of $this->obj using $this->args.
   *
   * @return $this
   *   Self for chaining.
   *
   * @throws \ReflectionException
   */
  protected function createObj() {
    $objectReflection = new \ReflectionClass($this->schema['classToBeTested']);
    $this->obj = $objectReflection->newInstanceArgs(array_values(get_object_vars($this->args)));

    return $this;
  }

  /**
   * Create a runtime partial test double as $this->obj.
   *
   * @return $this
   *   Self for chaining.
   *
   * @link http://docs.mockery.io/en/latest/reference/creating_test_doubles.html#runtime-partial-test-doubles
   */
  protected function createMockedObj() {
    $this->obj = Mockery::mock(get_class($this->obj), array_values(get_object_vars($this->args)))
      ->shouldAllowMockingProtectedMethods()
      ->makePartial();

    return $this;
  }

  /**
   * Assert constructor sets internal properties.
   *
   * @throws \ReflectionException
   */
  public function assertConstructorSetsInternalProperties() {
    foreach (array_keys(get_object_vars($this->args)) as $property) {
      $reflection = new \ReflectionClass(get_class($this->obj));
      $value = $reflection->getProperty($property);
      $value->setAccessible(TRUE);
      $this->assertSame($this->args->{$property}, $value->getValue($this->obj));
    }
  }

}
