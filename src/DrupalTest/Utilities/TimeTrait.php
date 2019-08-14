<?php

namespace AKlump\DrupalTest\Utilities;

/**
 * Trait TimeTrait.
 *
 * ::setValue() does not always work on Drupal date time time inputs, use this
 * trait on any class extending \AKlump\DrupalTest\EndToEndTestCase and
 * setTimeValue() for consistent time input form entry.
 *
 * @package AKlump\DrupalTest\Utilities
 */
trait TimeTrait {

  /**
   * Set the value of a time input field.
   *
   * @param string $css_selector
   *   The CSS selector of the time input field, e.g. input[type=time].
   * @param string|\DateTime $time
   *   The time value as a string "HH:mm", "HH:mm:ss" or "HH:mm:ss.SSS"; or a
   *   date object.
   *
   * @return \AKlump\DrupalTest\Utilities\TimeTrait
   *   Self for chaining.
   */
  public function setTimeValue($css_selector, $time) {
    if (is_string($time)) {
      $hour = '[0-2][0-3]';
      $minute = '[0-5][0-9]';
      $second = '[0-5][0-9]';
      $regex = "/^$hour:$minute:$second.\d{3}|$hour:$minute:$second|$hour:$minute$/";
      if (!preg_match($regex, $time)) {
        throw new \InvalidArgumentException(sprintf('The specified value "%s" does not conform to the required format.  The format is "HH:mm", "HH:mm:ss" or "HH:mm:ss.SSS" where HH is 00-23, mm is 00-59, ss is 00-59, and SSS is 000-999.', $time));
      }
    }
    else {
      $time = $time->format('H:i:s');
    }
    $selector = $this->getJavascriptSelectorCode($css_selector);
    $this->getSession()->executeScript("${selector}.value = '$time'");

    return $this;
  }

}
