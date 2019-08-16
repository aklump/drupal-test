<?php

namespace AKlump\DrupalTest\Utilities;

/**
 * Represents a balloon message.
 */
final class Balloon implements DisplayObjectInterface {

  /**
   * Holds the default/overridden data.
   *
   * @var array
   */
  private $data = [
    'delay' => 0,
    'message' => '',
    'offsetX' => 0,
    'offsetY' => 0,
    'position' => '',
    'selector' => '',
  ];

  public function __construct(string $message) {
    $this->data['message'] = $message;

    return $this;
  }

  public static function create($message) {
    return new static($message);
  }

  public function el(string $css_selector) {
    $this->data['selector'] = $css_selector;

    return $this;
  }

  public function delay(int $seconds) {
    $this->callbacks['onBeforeShow'][] = function () use ($seconds) {
      sleep($seconds);
    };

    return $this;
  }

  public function before() {
    $this->data['position'] = 'before';

    return $this;
  }

  public function after() {
    $this->data['position'] = 'after';

    return $this;
  }

  public function offset(int $x, int $y) {
    $this->data['offsetX'] = $x;
    $this->data['offsetY'] = $y;

    return $this;
  }

  public function onBeforeShow(callable $callback) {
    $this->callbacks[__FUNCTION__][] = $callback;

    return $this;
  }

  public function onAfterShow(callable $callback) {
    $this->callbacks[__FUNCTION__][] = $callback;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function dispatch(string $callback_name): DisplayObjectInterface {
    if (!empty($this->callbacks[$callback_name])) {
      foreach ($this->callbacks[$callback_name] as $callback) {
        $callback($this);
      }
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getBody(): string {
    return $this->data['message'];
  }

  /**
   * {@inheritdoc}
   */
  public function getOffset(): array {
    return [$this->data['offsetX'], $this->data['offsetX']];
  }

  /**
   * {@inheritdoc}
   */
  public function getPosition($default = ''): string {
    return empty($this->data['position']) ? $default : $this->data['position'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCssSelector($default = ''): string {
    return empty($this->data['selector']) ? $default : $this->data['selector'];
  }

}
