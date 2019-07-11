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
   * Return a string of random words.
   *
   * @param int $wordcount
   *   The number of words.
   *
   * @return string
   *   A string of words.
   */
  public function words($wordcount = 5) {
    $words = [];
    while (count($words) < $wordcount) {
      $words[] = $this->name(rand(3, 9));
    }

    return strtolower(implode(' ', $words));
  }

  /**
   * Generate a sentence.
   *
   * @param int $wordcount
   *   The number of words in the sentence.
   *
   * @return string
   *   A sentence string, with capital first letter, ending in '.'.
   */
  public function sentence($wordcount = 5) {
    return ucfirst($this->words($wordcount)) . '.';
  }

  /**
   * Generate a title-cased title of N words.
   *
   * @param int $wordcount
   *   The number of words in title.
   *
   * @return string
   *   A title-cased string.
   */
  public function title($wordcount = 5) {
    return ucwords($this->words($wordcount));
  }

  /**
   * @return string
   *   A random url.
   */
  public function url() {
    return 'https://www.' . $this->words(1) . '.com';
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
   * Generate a phone number.
   *
   * @return string
   *   The phone e.g. 555-555-5555
   */
  public function phone() {
    return rand(100, 999) . '-' . rand(100, 999) . '-' . rand(1000, 9999);
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
    if (!pathinfo($this->config['baseUrl'], PATHINFO_EXTENSION)) {
      $parsed['host'] = "website.com";
    }

    return $user . '@' . $parsed['host'];
  }

  /**
   * Generate a random integer from a range.
   *
   * @param int $min
   *   The minimum value.
   * @param int $max
   *   The maximum value.
   */
  public function integer($min, $max) {
    return rand($min, $max);
  }

}
