<?php

namespace AKlump\DrupalTest\Utilities;

/**
 * Interface GetEmailTrait.
 *
 * @package AKlump\DrupalTest\Utilities
 */
interface EmailHandlerInterface {

  /**
   * Return all new email since last check.
   *
   * @return array
   *   Each element is a raw MIME encoded email message.
   */
  public function getNewMail(): array;

}
