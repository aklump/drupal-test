---
id: testing-email
---
# Email Testing

Using the `\AKlump\DrupalTest\EndToEndTestCase` tests, you can:

* Assert that email(s) get sent by your application.
* Make assertions against email contents.
* Write test scenarios that bridge across email-related actions, such as when a user must click an email link to continue, e.g., password reset, or email confirmation registrations.

## Requirements

1. Using these methods, requires the mailparse PHP extension; [learn more](https://github.com/php-mime-mail-parser/php-mime-mail-parser#requirements).

## Test Implementation

1. In `setUpBeforeClass` indicate the email handler to use.  At this time there is only one provided handler, `AKlump\DrupalTest\Utilities\UnixMail`, but you may write your own by implementing `\AKlump\DrupalTest\Utilities\EmailHandlerInterface`.
        
        use AKlump\DrupalTest\Utilities\UnixMail;
        ...
        public static function setUpBeforeClass() {
          static::setEmailHandler(new UnixMail());
        }

1. Do something in a test like this example, which waits for a password reset email and then visits the contained URL.

        public function testWelcomeEmailContainsPasswordResetUrl() {
          $email = $this->waitForEmail();
          
          // ::waitForEmail always returns an array, we just want the first email.
          $email = reset($email);
      
          $body = $email->getMessageBody('text');
          $this->assertSame(1, preg_match('/(http:\/\/.+\/user\/reset.+)\n/', $body, $matches));
      
          $reset_pass_url = $matches[1];
          $this->loadPageByUrl($reset_pass_url);
        }

## Asserting Emails

`waitForEmail` will return an array of `PhpMimeMailParser\Parser`instances, which makes it easy to assert against parts of each email.  To learn more about that class [click here](https://github.com/php-mime-mail-parser/php-mime-mail-parser).

{% include('_Parser.md') %}

## On the Drupal Side of Things

1. Install the [reroute email module](https://www.drupal.org/project/reroute_email).
1. Route all email so that your `EmailHandlerInterface` can retrieve it.
1. You can determine the email address used by your handler with `\AKlump\DrupalTest\Utilities\EmailHandlerInterface::getInboxAddress`.  For example, you could do this temporarily and then read the console output:

          public function setUp() {
            $this->setEmailHandler(new UnixMail());
            echo $this->emailHandler->getInboxAddress(); die;
          }


## Sending Test Emails with Bash

    echo "MESSAGE" | mail -s "SUBJECT" "USER@HOST"
