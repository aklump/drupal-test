<?php

namespace AKlump\DrupalTest\Utilities;

/**
 * Allow assertions against a mailchimp account.
 *
 * To use this trait you must:
 * 1. Add this trait to your PhpUnit TestCase class.
 * 2. Generate a test-only API key in your Mailchimp account; do not use your
 * production API key. To generate a new key log in and visit:
 * https://us3.admin.mailchimp.com/account/api
 * 3. Add the new key as a constnat to your test case class, e.g.,
 *
 *      const MAILCHIMP_API_KEY = '3582...-us3';
 *
 * 4. Implement the assertions in your test case like so:
 *
 * @code
 *     // Merge values for an email address.
 *     $this->mailchimpAssert($email)
 *       ->mergeFieldSame('Daniel', 'FNAME')
 *       ->mergeFieldSame('Boone', 'LNAME');
 *
 *     // Is email subscribed to a given list.
 *     $this->mailchimpAssert($email, self::NEWSLETTER_LIST_ID)
 *       ->isSubscribed();
 * @endcode
 */
trait MailchimpAssertTrait {

  /**
   * Return a new MailchimpAsserts instance.
   *
   * Each instance will trigger a remote API call, if you wish to make more
   * than one assert against a single API call, then you should chain your
   * asserts to the same `mailchimpAssert()` call.
   *
   * @param string $email
   *   The email address to use.
   * @param string $list_id
   *   To limit your asserts to a single list id, include it here.
   *
   * @return \AKlump\DrupalTest\Utilities\MailchimpAsserts
   *   The assert instance.
   */
  public function mailchimpAssert($email, $list_id = NULL) {
    return new MailchimpAsserts($this, $email, $list_id);
  }
}
