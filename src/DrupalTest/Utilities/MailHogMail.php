<?php

namespace AKlump\DrupalTest\Utilities;

use GuzzleHttp\Client;
use PhpMimeMailParser\Parser;

/**
 * Retrieves mail captured by Mailhog.
 *
 * To use this, you must set an environment variable `MAILHOG_BASE_URL` with the
 * url to the Mailhog UI, this is the URL where you check the mail.
 *
 * @link https://github.com/mailhog/MailHog
 * @link https://github.com/rpkamp/mailhog-client
 */
final class MailHogMail implements EmailHandlerInterface {

  private $baseUrl = '';

  /**
   * MailHogMail constructor.
   */
  public function __construct() {
    $baseUrl = getenv('MAILHOG_BASE_URL');
    if (empty($baseUrl)) {
      throw new \RuntimeException("Missing or empty environment variable: MAILHOG_BASE_URL");
    }
    $this->baseUrl = $baseUrl;
    $this->client = new Client([
      'base_uri' => $this->baseUrl,
      'timeout' => 2.0,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getInboxAddress() {
    return 'info@' . parse_url($this->baseUrl, PHP_URL_HOST);
  }

  /**
   * {@inheritdoc}
   *
   * @link https://github.com/mailhog/MailHog/blob/master/docs/APIv2/swagger-2.0.yaml
   */
  public function readMail() {
    try {
      $response = $this->client->get('api/v2/messages');
      $json = json_decode($response->getBody(), TRUE);
      if (!isset($json['total']) || $response->getStatusCode() !== 200) {
        throw new UnreadableMailException(sprintf("Cannot read mail from: %s", $this->baseUrl));
      }
      $unread_mail = [];
      if ($json['total']) {
        foreach ($json['items'] as $item) {
          if (!empty($item['Raw']['Data'])) {
            $parser = new Parser();
            $parser->setText($item['Raw']['Data']);
            $unread_mail[] = $parser;
          }
        }
        $this->markAllRead();
      }
    }
    catch (\Exception $exception) {
      throw new MailOfflineException(sprintf("Mailhog does not appear to be online at: %s", $this->baseUrl), 1, $exception);
    }

    return $unread_mail;
  }

  /**
   * {@inheritdoc}
   *
   * @link https://github.com/mailhog/MailHog/blob/master/docs/APIv1.md
   */
  public function markAllRead() {
    try {
      $response = $this->client->delete('api/v1/messages');
      if ($response->getStatusCode() !== 200) {
        throw new \RuntimeException("");
      }
    }
    catch (\Exception $exception) {
      throw new UnreadableMailException(sprintf("Cannot mark messages read at: %s", $this->baseUrl));
    }

    return $this;
  }

}
