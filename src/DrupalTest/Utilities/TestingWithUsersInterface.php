<?php


namespace AKlump\DrupalTest\Utilities;

use PHPUnit\Framework\TestCase;

/**
 * An interface to use when testing against a user-based system.
 *
 * Interface TestingWithUsersInterface should be used when you are testing
 * against a system that provides user accounts, e.g. Drupal.  The
 * implementation is left up to the test class, and should probably be done in
 * an abstract base class or trait as specific to your application.
 *
 * @package AKlump\DrupalTest\Utilities
 */
interface TestingWithUsersInterface {

  /**
   * Create a logged in user session.
   *
   * @param string $username
   *   The username.  This is optional as you may have context based on
   *   establishUser, in which case this method will use that context.
   * @param string $password
   *   The password.
   * @param string $destination_url
   *   An optional value to set as query string ?destination=.
   *
   * @return \PHPUnit\Framework\TestCase
   *   Self for chaining.
   */
  public function loginUser(string $username = '', string $password = '', string $destination_url = ''): TestCase;

  /**
   * Destroy any current logged in user session.
   *
   * @return \PHPUnit\Framework\TestCase
   *   Self for chaining.
   */
  public function logoutUser(): TestCase;

  /**
   * Establish a user in the current system.
   *
   * This method should create or make sure a user based on $context, exists in
   * the system under test.  It should not log said user into the system.
   *
   * @param array $context
   *   This is any arbitrary information you may need for establishing the
   *   user.  In drupal this may be an array of roles.
   *
   * @return \PHPUnit\Framework\TestCase
   *   Self for chaining.
   */
  public function establishUser(array $context): TestCase;

}
