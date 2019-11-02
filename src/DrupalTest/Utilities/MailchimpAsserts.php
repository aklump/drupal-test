<?php

namespace AKlump\DrupalTest\Utilities;

use Adbar\Dot;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

/**
 * A class to make assertions against the mailchimp API.
 *
 * @see \AKlump\DrupalTest\Utilities\MailchimpAssertTrait
 */
class MailchimpAsserts {

  /**
   * Stores the remote data from the Mailchimp API response.
   *
   * @var array
   */
  protected $member;

  /**
   * Stores the calling test case instance.
   *
   * @var \PHPUnit\Framework\TestCase
   */
  protected $testCase;

  /**
   * Stores the email of the contact to be asserted against.
   *
   * @var string
   */
  protected $email;

  /**
   * MailchimpAssert constructor.
   *
   * @param PHPUnit\Framework\TestCase $test_case
   *   The test case instance running your tests.
   * @param string $email
   *   The email address for the record to use.
   * @param string $list_id
   *   Include this only if you want to limit to a given list.
   */
  public function __construct(TestCase $test_case, $email, $list_id = NULL) {
    $this->testCase = $test_case;
    $this->email = $email;
    $this->listId = $list_id;
  }

  /**
   * Get all merge fields on the record.
   *
   * @return array
   *   The merge fields for a record.
   */
  public function getMergeFields(): array {
    $this->handleRemoteCall();

    return $this->member['merge_fields'];
  }

  /**
   * Assert that a merge field is the same as.
   *
   * @return $this
   */
  public function mergeFieldSame($expected, $merge_field) {
    $this->handleRemoteCall();
    $dot = new Dot($this->member['merge_fields'] ?? []);
    $actual = $dot->get($merge_field);
    $this->testCase->assertSame($expected, $actual);

    return $this;
  }

  /**
   * Assert that a merge field value equals.
   *
   * @return $this
   */
  public function mergeFieldEquals($expected, $merge_field) {
    $this->handleRemoteCall();
    $dot = new Dot($this->member['merge_fields']);
    $actual = $dot->get($merge_field);
    $this->testCase->assertEquals($expected, $actual);

    return $this;
  }

  /**
   * Assert if the email address is subscribed.
   *
   * @return $this
   */
  public function isSubscribed() {
    $this->handleRemoteCall();
    $status = $this->member['status'] ?? 'subscribed';
    $this->testCase->assertSame('subscribed', $status);

    return $this;
  }

  /**
   * Assert if the email address is not subscribed.
   *
   * @return $this
   */
  public function isNotSubscribed() {
    $this->handleRemoteCall();
    $status = $this->member['status'] ?? 'archived';
    $this->testCase->assertSame('archived', $status);

    return $this;
  }

  public function hasInterestGroup($group_id) {
    $this->handleRemoteCall();
    $this->testCase->assertArrayHasKey($group_id, $this->member['interests']);

    return $this;
  }

  public function hasNotInterestGroup($group_id) {
    $this->handleRemoteCall();
    $this->testCase->assertTrue(empty($this->member['interests'][$group_id]));

    return $this;
  }

  private function handleRemoteCall() {
    if (empty($this->member)) {
      $client = new Client();
      list(, $data_center) = explode('-', $this->testCase::MAILCHIMP_API_KEY);
      $endpoint = str_replace('<dc>', $data_center, 'https://<dc>.api.mailchimp.com/3.0');
      $endpoint .= '/search-members';
      $query = ['query' => $this->email];
      if ($this->listId) {
        $query['list_id'] = $this->listId;
      }
      $response = $client->request('GET', $endpoint,
        [
          'query' => $query,
          'headers' => [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
            'Authorization' => 'apikey ' . $this->testCase::MAILCHIMP_API_KEY,
          ],
        ]);
      $body = json_decode($response->getBody(), TRUE);
      $members = $body['exact_matches']['members'];
      $this->member = $members ? reset($members) : [];
    }

    return $this->member;
  }

}
