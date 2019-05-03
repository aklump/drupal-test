<?php

namespace AKlump\DrupalTest\Utilities;

/**
 * A container for a popup message for interactive tests.
 */
final class Popup {

  private $body;

  private $title;

  private $subtitle;

  private $icon;

  /**
   * Factory method to create a new instance.
   *
   * @param string $body
   *   The body markup of the popup.
   *
   * @return \AKlump\DrupalTest\Utilities\Popup
   *   A new popup.
   */
  public static function create($body) {
    $popup = new self();

    return $popup->setBody($body);
  }

  /**
   * Get the inner HTML for .popup__container.
   *
   * @return string
   *   The HTML markup.
   */
  public function getContainerInnerHtml() {
    $inner_html = [];
    $inner_html[] = '<div class="popup__inner">';
    if ($this->title) {
      $inner_html[] = '<h1 class="popup__title">' . $this->title . '</h1>';
    }
    if ($this->subtitle) {
      $inner_html[] = '<h1 class="popup__subtitle">' . $this->subtitle . '</h1>';
    }
    $inner_html[] = '<div class="popup__body">' . $this->body . '</div></div>';
    if ($this->icon) {
      $inner_html[] = '<div class="popup__icon">' . $this->icon . '</div>';
    }

    return implode('', $inner_html);
  }

  /**
   * Set the SVG contents for an icon.
   *
   * @param string $icon
   *   SVG code for an icon.  Ideal size is 100x100px.
   *
   * @return \AKlump\DrupalTest\Utilities\Popup
   *   Self for chaining.
   */
  public function setIcon($icon) {
    $this->icon = $icon;

    return $this;
  }

  /**
   * Set the value of Subtitle.
   *
   * @param string $subtitle
   *   A subtitle for the popup.
   *
   * @return \AKlump\DrupalTest\Utilities\Popup
   *   Self for chaining.
   */
  public function setSubtitle($subtitle) {
    $this->subtitle = $subtitle;

    return $this;
  }

  /**
   * Set the value of Title.
   *
   * @param string $title
   *   A popup title.
   *
   * @return \AKlump\DrupalTest\Utilities\Popup
   *   Self for chaining.
   */
  public function setTitle($title) {
    $this->title = $title;

    return $this;
  }

  /**
   * Set the value of Body.
   *
   * @param string $body
   *   The markup for the body. \n is replaced with <br/>.
   *
   * @return \AKlump\DrupalTest\Utilities\Popup
   *   Self for chaining.
   */
  public function setBody($body) {
    $this->body = str_replace("\n", '<br/>', trim($body));

    return $this;
  }

}
