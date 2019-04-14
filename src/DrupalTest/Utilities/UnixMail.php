<?php

namespace AKlump\DrupalTest\Utilities;

use AKlump\LoftLib\Bash\Bash;
use PhpMimeMailParser\Parser;

/**
 * Retrieves mail using Bash's "mail" command.
 *
 * @see From the CLI type `man mail`.
 */
class UnixMail implements EmailHandlerInterface {

  /**
   * {@inheritdoc}
   *
   * @link https://unix.stackexchange.com/questions/239380/use-mail-to-read-email-from-command-line
   */
  public function getNewMail(): array {
    try {
      $output = Bash::exec('echo p|mail');
    }
    catch (\Exception $exception) {
      return [];
    }

    list(, $mime_mail) = explode('Message 1:', $output) + [NULL, NULL];
    $mime_mail = explode("\n\n", $mime_mail);
    array_pop($mime_mail);
    $mime_mail = trim(implode("\n\n", $mime_mail));
    if (empty($mime_mail)) {
      return [];
    }

    $parser = new Parser();
    $parser->setText($mime_mail);

    return [$parser];
  }

}
