<?php

namespace AKlump\DrupalTest;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * A PHPUnit Helper to assert Drupal 7 Command arrays.
 *
 * @code
 *   $test = new CommandAssertion($response);
 *   $test->shouldHaveCommand('insert')
 *     ->with('method', 'append')
 *     ->with('selector', '.stories__ajax-content')
 *     ->times(1);
 * @endcode
 */
class CommandAssertion {

  /**
   * All the assertions for the test class.
   *
   * This will be processed in ::tearDown.
   *
   * @var array
   */
  protected static $assertions = [];

  /**
   * The response to assert against.
   *
   * @var \GuzzleHttp\Psr7\Response
   */
  public $response;

  /**
   * The commands to look for.
   *
   * @var string
   */
  public $command = '';

  /**
   * The nubmer of times it should appear.
   *
   * @var null|int
   */
  public $number = NULL;

  /**
   * Key/value to match for command properties.
   *
   * Commands must have these key/value to be considering a match.
   *
   * @var array
   */
  public $properties = [];

  /**
   * Key/var type to match for command property values are of type.
   *
   * @var array
   */
  public $propertyTypes = [];

  /**
   * CommandAssertion constructor.
   *
   * @param \GuzzleHttp\Psr7\Response $response
   *   The response under test.
   */
  public function __construct(Response $response) {
    $this->response = $response;
    self::$assertions[] = $this;
  }

  /**
   * Process all registered assertions.
   *
   * This should be called from \PHPUnit\Framework\TestCase::TearDown.
   *
   * @param \PHPUnit\Framework\TestCase $test_case
   *   The PHPUnit Test Runner.
   */
  public static function handleAssertions(TestCase $test_case) {
    foreach (self::$assertions as $test) {

      $commands = json_decode($test->response->getBody());

      // Count the number of commands of expected type.
      foreach ($commands as $command) {

        // Filter first by command.
        $commands = array_filter($commands, function ($item) use ($test) {
          return $item->command === $test->command;
        });

        // Now filter by properties.
        $with = '';
        if ($test->properties) {
          $with = '.' . \GuzzleHttp\json_encode($test->properties);
          $commands = array_filter($commands, function ($item) use ($test) {
            $matching = array_intersect_key((array) $item, $test->properties);

            return $matching == $test->properties;
          });
        }

        // Now filter by property types.
        if ($test->propertyTypes) {
          $with = ', types: ' . \GuzzleHttp\json_encode($test->propertyTypes);
          $commands = array_filter($commands, function ($item) use ($test) {
            foreach ($test->propertyTypes as $property => $type) {
              if (!isset($item->{$property}) || gettype($item->{$property}) !== $type) {
                return FALSE;
              }
            }

            return TRUE;
          });
        }

        $command = 'Command ' . $test->command . $with;

        if (is_null($test->number)) {
          $test_case->assertNotEmpty($commands, $command . '" should appear at least once.');
        }
        else {
          $test_case->assertCount($test->number, $commands, $command . '" should appear exactly ' . $test->number . ' time(s).');
        }
      }
    }
  }

  /**
   * Specify the command to assert for.
   *
   * @param string $command
   *   The command to match.
   *
   * @return $this
   */
  public function shouldHaveCommand($command) {
    $this->command = $command;

    return $this;
  }

  /**
   * Add a command property/value qualifier to match with.
   *
   * @param mixed $value
   *   The value of the key must be this to match.
   * @param string $key
   *   The command must contain this key to match.
   *
   * @return $this
   */
  public function with($value, $key) {
    $this->properties[$key] = $value;

    return $this;
  }

  /**
   * Add constraint that a property must be of type.
   *
   * @param string $type
   *   The internal type it must match.
   * @param string $key
   *   The property.
   *
   * @return $this
   */
  public function withInternalType($type, $key) {
    $this->propertyTypes[$key] = $type;

    return $this;
  }

  /**
   * Indicate the command must appear exactly 1 times.
   */
  public function once() {
    $this->number = 1;
  }

  /**
   * Indicate the command must appear exactly 2 times.
   */
  public function twice() {
    $this->number = 2;
  }

  /**
   * Indicate that a command must appear a specific number of times.
   *
   * @param int $number
   *   The number of times command must appear.
   */
  public function times($number) {
    $this->number = intval($number);
  }

}
