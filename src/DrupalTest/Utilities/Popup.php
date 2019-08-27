<?php

namespace AKlump\DrupalTest\Utilities;

/**
 * A container for a popup message for interactive tests.
 */
final class Popup implements DisplayObjectInterface {

  private $data = [
    'body' => '',
    'columns' => 1,
    'icon' => '',
    'subtitle' => '',
    'title' => '',
  ];

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

    return $popup->body($body);
  }

  public function setTwoCol() {
    $this->data['columns'] = 2;

    return $this;
  }

  /**
   * Get the inner HTML for .popup__container.
   *
   * @return string
   *   The HTML markup.
   */
  public function getContainerInnerHtml() {
    $inner_html = [];
    $class = '';

    // Make a smart layout for some configurations.
    if ($this->data['title'] && !$this->data['subtitle'] && strlen($this->data['body']) < 1000) {
      $class = ' layout-two-col';
    }
    $inner_html[] = '<div class="popup__inner' . $class . '">';
    if ($this->data['title']) {
      $inner_html[] = '<h1 class="popup__title">' . $this->data['title'] . '</h1>';
    }
    if ($this->data['subtitle']) {
      $inner_html[] = '<h1 class="popup__subtitle">' . $this->data['subtitle'] . '</h1>';
    }
    $inner_html[] = '<div class="popup__body">' . $this->data['body'] . '</div></div>';
    if ($this->data['icon']) {
      $inner_html[] = '<div class="popup__icon">' . $this->data['icon'] . '</div>';
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
  public function icon($icon) {
    $this->data['icon'] = $icon;

    return $this;
  }

  /**
   * @deprecated Use icon() instead.
   */
  public function setIcon($icon) {
    return $this->icon($icon);
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
  public function subtitle($subtitle) {
    $this->data['subtitle'] = $subtitle;

    return $this;
  }

  /**
   * @deprecated Use subtitle() instead.
   */
  public function setSubtitle($subtitle) {
    return $this->subtitle($subtitle);
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
  public function title($title) {
    $this->data['title'] = $title;

    return $this;
  }

  /**
   * @deprecated Use title() instead.
   */
  public function setTitle($title) {
    return $this->title($title);
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
  public function body($body) {
    $this->data['body'] = str_replace("\n", '<br/>', trim($body));

    return $this;
  }

  /**
   * @deprecated Use body() instead.
   */
  public function setBody($body) {
    return $this->body($body);
  }

  /**
   * Set a people icon.
   *
   * @return \AKlump\DrupalTest\Utilities\Popup
   */
  public function setPeopleIcon() {
    return $this->setIcon('<svg width="350" height="204" viewBox="0 0 350 204" xmlns="http://www.w3.org/2000/svg"><title>people</title><g fill="none" fill-rule="nonzero"><g transform="translate(97)"><ellipse fill="#FCB341" cx="78" cy="38.5" rx="39" ry="38.5"/><path d="M36.67 75.608C17.947 92.31 0 125.028 0 189h78V87.059c-10.613 0-20.277-4.219-27.265-11.107-3.883-3.788-10.01-3.96-14.065-.344z" fill="#45466D"/><path d="M105.093 76.025C98.104 82.903 88.613 87.202 78 87.202V189h78c0-64.054-18.033-96.726-36.843-113.405-4.055-3.611-10.181-3.44-14.064.43z" fill="#383754"/></g><g transform="translate(193 15)"><ellipse fill="#FCB341" cx="78" cy="38.5" rx="39" ry="38.5"/><path d="M36.67 75.608C17.947 92.31 0 125.028 0 189h78V87.059c-10.613 0-20.277-4.219-27.265-11.107-3.883-3.788-10.01-3.96-14.065-.344z" fill="#45466D"/><path d="M105.093 76.025C98.104 82.903 88.613 87.202 78 87.202V189h78c0-64.054-18.033-96.726-36.843-113.405-4.055-3.611-10.181-3.44-14.064.43z" fill="#383754"/></g><g transform="translate(0 15)"><circle fill="#FCB341" cx="77.5" cy="38.5" r="38.5"/><path d="M36.2 75.608C17.717 92.31 0 125.028 0 189h77V87.059a37.991 37.991 0 0 1-26.916-11.107c-3.833-3.788-9.88-3.96-13.884-.344z" fill="#45466D"/><path d="M103.746 76.025c-6.9 6.878-16.27 11.177-26.746 11.177V189h77c0-64.054-17.802-96.726-36.37-113.405-4.004-3.611-10.051-3.44-13.884.43z" fill="#383754"/></g></g></svg>');
  }

  /**
   * Set a pinpoint icon.
   *
   * @return \AKlump\DrupalTest\Utilities\Popup
   */
  public function setPinpointIcon() {
    return $this->setIcon('<svg width="135" height="214" viewBox="0 0 135 214" xmlns="http://www.w3.org/2000/svg"><title>pinpoint</title><g fill="none" fill-rule="nonzero"><path d="M135 67.5C135 30.4 104.9 0 67.5 0 30.4 0 0 30.1 0 67.5c0 20.3 9.1 38.6 23.6 51l43.6 94.9 43.6-94.9c15.1-12.1 24.2-30.3 24.2-51z" fill="#FCB341"/><path d="M11.5 68.6c0 30.9 25.1 56.3 56.3 56.3V12.3c-30.9 0-56.3 25.3-56.3 56.3z" fill="#45466D"/><path d="M67.4 12.3v112.6c30.9 0 56.3-25.1 56.3-56.3 0-31-25.3-56.3-56.3-56.3z" fill="#383754"/></g></svg>');
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return $this->data['title'];
  }

  /**
   * {@inheritdoc}
   */
  public function getBody(): string {
    return $this->data['body'];
  }

  /**
   * {@inheritdoc}
   */
  public function getOffset(): array {
    return [0, 0];
  }

  /**
   * {@inheritdoc}
   */
  public function getPosition($default = ''): string {
    return $default;
  }

  /**
   * {@inheritdoc}
   */
  public function getCssSelector($default = ''): string {
    return $default;
  }

  /**
   * {@inheritdoc}
   */
  public function dispatch(string $event_name): DisplayObjectInterface {
    return $this;
  }

}
