<?php

namespace AKlump\DrupalTest\Utilities;

/**
 * Interface GetEmailTrait.
 *
 * @package AKlump\DrupalTest\Utilities
 */
interface EmailHandlerInterface {

  /**
   * Return all new/unread email.
   *
   * Each call to ::readMail() should mark any retrieved mail as read
   * and thus prevent those same emails from being returned in subsequent calls
   * to ::readMail().  In other words no two calls to this method should ever
   * return the same email.
   *
   * @return array
   *   Each element is a raw MIME encoded email message.  Returns an empty
   *   array if there is no unread mail.
   *
   * @throws \AKlump\DrupalTest\Utilities\MailOfflineException
   * @throws \AKlump\DrupalTest\Utilities\UnreadableMailException
   */
  public function readMail();

  /**
   * Return the full email address that will be checked for email.
   *
   * @return string
   *   The email address to send email to so that it can be read by ::readMail.
   */
  public function getInboxAddress();

}
