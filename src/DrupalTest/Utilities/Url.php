<?php

namespace AKlump\DrupalTest\Utilities;

/**
 * A class to mimick Drupal 8's url.
 */
class Url {

  public function __construct($url) {
    $this->url = $url;
  }

  public function toString(): string {
    return $this->url;
  }

}
