# Email Testing

Using the `\AKlump\DrupalTest\EndToEndTestCase` tests, you can:

* Assert that an email gets sent by your application.
* Make assertions about the email contents.
* Write test scenarios that bridge events such as when a user must click an email link to continue, e.g., password reset, or email confirmation registrations.

## Requirements

1. This requires the mailparse PHP extension; read about the requirements [here](https://github.com/php-mime-mail-parser/php-mime-mail-parser#requirements).

## Test Implementation

1. Configure the email handler in your test class's `setUp`.  At this time there is only one provided handler, `AKlump\DrupalTest\Utilities\UnixMail`, but you may write your own implementing `\AKlump\DrupalTest\Utilities\EmailHandlerInterface`.
        
        use AKlump\DrupalTest\Utilities\UnixMail;
        ...
        public function setUp() {
          $this->setEmailHandler(new UnixMail());
        }

1. Do something in a test like this example, which waits for a password reset email and then visits the contained URL.

        public function testWelcomeEmailContainsPasswordResetUrl() {
          $email = $this->waitForEmail(60);
          $email = reset($email);
      
          $body = $email->getMessageBody('text');
          $this->assertSame(1, preg_match('/(http:\/\/.+\/user\/reset.+)\n/', $body, $matches));
      
          $reset_pass_url = $matches[1];
          $el = $this->loadPageByUrl($reset_pass_url)
            ->getDomElements([
              '.t-pass_submit',
            ]);
        }

## Asserting Emails

`waitForEmail` will return an array of `PhpMimeMailParser\Parser`instances, which makes it easy to assert against parts of each email.  To learn more about that class [click here](https://github.com/php-mime-mail-parser/php-mime-mail-parser).

{% include('_EmailInstances.md') %}
