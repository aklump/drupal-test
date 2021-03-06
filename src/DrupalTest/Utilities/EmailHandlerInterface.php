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
   * @return array[\PhpMimeMailParser\Parser]
   *   Each element is an instance of \PhpMimeMailParser\Parser  Returns an
   *   empty array if there is no unread mail.
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

  /**
   * Mark all messages in the inbox as read.
   *
   * Use this, say at the beginning of a test class, to make sure you begin
   * with a clean inbox, so that ::readMail will only return new emails.
   *
   * @return \AKlump\DrupalTest\Utilities\EmailHandlerInterface
   *   Self for chaining.
   */
  public function markAllRead();

}
