<?php

namespace AKlump\DrupalTest\Utilities;

use AKlump\LoftLib\Bash\Bash;
use PhpMimeMailParser\Parser;

/**
 * Retrieves mail using Bash's "mail" command.
 *
 * @see From the CLI type `man mail`.
 */
final class UnixMail implements EmailHandlerInterface {

  /**
   * Caches the inbox address.
   *
   * @var string
   */
  private static $address;

  /**
   * {@inheritdoc}
   */
  public function getInboxAddress() {
    if (empty(self::$address)) {
      try {
        self::$address = Bash::exec("echo $(whoami)@$(hostname)");
      }
      catch (\Exception $exception) {
        return '';
      }
    }

    return self::$address;
  }

  /**
   * {@inheritdoc}
   *
   * @link https://unix.stackexchange.com/questions/239380/use-mail-to-read-email-from-command-line
   */
  public function readMail() {
    $unread_mail = [];
    try {
      // First read the header from mail using -H, which will be parsed for message ids.
      $header = Bash::exec('echo|mail -H');
    }
    catch (\Exception $exception) {
      if ($exception->getCode() === 1) {
        // We land here if there is no unread mail.
        return $unread_mail;
      }
      throw new MailOfflineException("Failed to connect to email client.", 1, $exception);
    }

    // Parse out mail ids.
    preg_match_all('/^[>\s][UN]\s+(\d+)/m', $header, $unread_mail_index);
    $unread_mail_index = $unread_mail_index[1];
    foreach ($unread_mail_index as $mail_id) {
      try {
        $raw_message = Bash::exec("echo p{$mail_id}|mail -N");
      }
      catch (\Exception $exception) {
        throw new UnreadableMailException("Cannot read email id: {$mail_id}.", 1, $exception);
      }

      // Strip off the last N messages saved, which is the output from mail.
      $raw_message = explode("\n\n", $raw_message);
      array_pop($raw_message);
      $raw_message = trim(implode("\n\n", $raw_message));

      if (!empty($raw_message)) {
        $parser = new Parser();
        $parser->setText($raw_message);
        $unread_mail[] = $parser;
      }
    }

    return $unread_mail;
  }

}
