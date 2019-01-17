<?php

namespace AKlump\DrupalTest\Utilities;

use Password\Generator as PWGenerator;

class Generators {

  protected $config = [];

  public function __construct($config) {
    $this->setConfig($config);
  }

  public function setConfig(array $config) {
    $this->config = $config;

    return $this;
  }

  /**
   * Generate a name with a leading capital.
   *
   * @param int $length
   *   The length of the name
   *
   * @return string
   */
  public function name($length = 5) {
    $generator = new PWGenerator();
    $generator->setMinLength($length);
    $generator->setNumberOfUpperCaseLetters(0);
    $generator->setNumberOfNumbers(0);
    $generator->setNumberOfSymbols(0);
    $this->config['name'] = ucfirst(strtolower($generator->generate()));

    return $this->config['name'];
  }

  /**
   * Generate a username.
   *
   * @param int $length
   *   Optional.
   *
   * @return string
   *   A random username.
   */
  public function username($length = 10) {
    $generator = new PWGenerator();
    $generator->setMinLength($length);
    $generator->setNumberOfUpperCaseLetters(0);
    $generator->setNumberOfNumbers(1);
    $generator->setNumberOfSymbols(0);
    $this->config['username'] = $generator->generate();

    return $this->config['username'];
  }

  /**
   * Generate a password.
   *
   * @param int $length
   *   Optional.
   *
   * @return string
   *   A random password.
   */
  public function password($length = 32) {
    $generator = new PWGenerator();
    $generator->setMinLength($length);
    $generator->setNumberOfUpperCaseLetters(5);
    $generator->setNumberOfNumbers(5);
    $generator->setNumberOfSymbols(5);
    $this->config['password'] = $generator->generate();

    return $this->config['password'];
  }

  /**
   * Generate an email address.
   *
   * If you generate a username first, that will be used as the basis of the
   * email.
   *
   * @return string
   *   A random email address at the baseUrl.
   */
  public function email() {
    if (!empty($this->config['username'])) {
      $user = $this->config['username'];
    }
    else {
      $user = $this->username();
    }
    $parsed = parse_url($this->config['baseUrl']);

    return $user . '@' . $parsed['host'];
  }

}
