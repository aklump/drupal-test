<?php

namespace AKlump\DrupalTest;


/**
 * A trait providing common methods for the HttpTestInterface.
 */
trait HttpTestBaseTrait {

  /**
   * This is read in from the environment variable TEST_BASE_URL.
   *
   * @var string
   */
  protected static $baseUrl = NULL;

  /**
   * Import the base URL from the environment vars.
   */
  protected static function handleBaseUrl() {
    if (!($url = getenv('TEST_BASE_URL'))) {
      static::markTestSkipped('Missing environment variable: TEST_BASE_URL');
    }
    static::$baseUrl = rtrim($url, '/');
  }

  /**
   * Resolve relative URLs based on the base url.
   *
   * @param $path
   * @param bool $remove_authentication_credentials
   *
   * @return mixed|string
   */
  public function resolvePath($path, $remove_authentication_credentials = FALSE) {
    if (strpos($path, 'http') !== 0) {
      $path = rtrim(static::$baseUrl, '/') . "/$path";
      $parts = parse_url($path);
      if ($remove_authentication_credentials) {
        $auth = [];
        if (!empty($parts['user'])) {
          $auth[] = $parts['user'];
        }
        if (!empty($parts['pass'])) {
          $auth[] = $parts['pass'];
        }
        if ($auth) {
          $find = $parts['scheme'] . '://' . implode(':', $auth) . '@';
          $replace = $parts['scheme'] . '://';
          $path = str_replace($find, $replace, $path);
        }
      }
    }

    return $path;
  }

}
