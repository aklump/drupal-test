<?php


namespace AKlump\DrupalTest\Utilities;

/**
 * Interface DisplayObjectInterface
 *
 * Used to contain observation instructions for a user message.
 *
 * @package AKlump\DrupalTest\Utilities
 */
interface DisplayObjectInterface {

  /**
   * Return the message title.
   *
   * This may not be rendered in all display contexts.
   *
   * @return string
   */
  public function getTitle(): string;

  /**
   * Return the message body.
   *
   * @return string
   */
  public function getBody(): string;

  /**
   * Return the x, y offset relative to the anchor.
   *
   * @return array
   *   - 0 int The X offset.
   *   - 1 int The Y offset.
   */
  public function getOffset(): array;

  /**
   * Return the position of the displayed object.
   *
   * This is relative to the element indicated by the CSS selector.
   *
   * @return string
   *   Usually one of: before or after
   */
  public function getPosition($default = ''): string;

  /**
   * Return the CSS selector.
   *
   * @param string $default
   *   A default value if the selector is empty.
   *
   * @return string
   *   The css class, id or other selector.
   */
  public function getCssSelector($default = ''): string;

  /**
   * Dispatch an event.
   *
   * @param string $event_name
   *   The name of the event to dispatch.
   *
   * @return \AKlump\DrupalTest\Utilities\DisplayObjectInterface
   */
  public function dispatch(string $event_name): DisplayObjectInterface;
}
