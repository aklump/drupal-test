<?php

namespace AKlump\DrupalTest;

use AKlump\DrupalTest\Utilities\DestructiveTrait;
use AKlump\DrupalTest\Utilities\EmailHandlerInterface;
use AKlump\DrupalTest\Utilities\WebAssertTrait;
use GuzzleHttp\Client;

/**
 * A class to interact with the browser for forms and navigation.
 */
abstract class EndToEndTestCase extends BrowserTestCase {

  const WAIT_TIMEOUT = 10;

  use DestructiveTrait;

  public static $browsers = array(
    array(
      'driver' => 'selenium2',
      'host' => 'localhost',
      'port' => 4444,
      'browserName' => 'chrome',
      'sessionStrategy' => 'shared',
    ),
  );

  /**
   * Holds the cookie jar used by requests.
   *
   * @var \GuzzleHttp\Cookie\CookieJar
   */
  protected static $cookieJar;

  /**
   * Holds an email handler interface for email testing.
   *
   * @var \AKlump\DrupalTest\Utilities\EmailHandlerInterface
   */
  protected $emailHandler;

  /**
   * Holds the response of the last remote call.
   *
   * @var null
   */
  protected $response = NULL;

  /**
   * The loaded page from loadPageByUrl.
   *
   * @var \Behat\Mink\Element\NodeElement
   */
  protected $page;

  /**
   * True when the test is being observed.
   *
   * @var bool
   */
  private $observerIsObserving = FALSE;

  /**
   * The configured title of the continue button as shown to the observer.
   *
   * @var string
   * @see ::getObserverButtonTitle
   */
  private $observerButton = '';

  /**
   * An array of classes to be added to the observer button.
   *
   * This can be used to affect it's appearance.
   *
   * @var array
   */
  private $observerButtonClasses = [];

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass() {
    static::handleBaseUrl();
    if (!static::isBrowserOnline()) {
      self::markTestSkipped('Selenium server is offline.');
    }
  }

  /**
   * Verify if the Selenium browser server is online or not.
   *
   * @return bool
   *   True if online, false otherwise.
   */
  public static function isBrowserOnline() {
    $client = new Client();
    try {
      $response = $client->get('http://127.0.0.1:4444/wd/hub/static/resource/hub.html');
    }
    catch (\Exception $exception) {
      return FALSE;
    }

    return $response->getStatusCode() === 200;
  }

  /**
   * Wait for an elapsed time before continuing test.
   *
   * @param int $seconds
   *   The number of seconds to wait.
   */
  public function wait($seconds) {
    $start = time();
    $this->waitFor(function () use ($start, $seconds) {
      return time() - $start > $seconds;
    });
  }

  /**
   * Wait for a condition to return true.
   *
   * @param callable $test
   *   A function to call repeatedly until it returns true, or we hit the
   *   timeout.  Exceptions will not be caught.
   * @param string $description
   *   A description of what you're waiting for.
   * @param int|null $timeout
   *   Set this to 0 for no timeout.  Leave NULL for the default timeout.  Any
   *   other number is the number of seconds to timeout.
   *
   * @return bool
   *   True if success, false if timeout was reached.
   *
   * @link http://docs.behat.org/en/v2.5/cookbook/using_spin_functions.html
   */
  public function waitFor(callable $test, $description = NULL, $timeout = NULL) {
    if (is_null($timeout)) {
      $timeout = self::WAIT_TIMEOUT;
    }
    if (is_null($description)) {
      $description = 'callback to return true';
    }
    $start = time();
    while (TRUE) {
      if ($test()) {
        return TRUE;
      }
      sleep(1);
      if ($timeout && (time() - $start > $timeout)) {
        $this->fail('Timed out while waiting for: ' . $description);

        return FALSE;
      }
    }
  }

  /**
   * Set the Email Handler for retrieving email.
   *
   * @param \AKlump\DrupalTest\Utilities\EmailHandlerInterface $handler
   *   The handler instance.
   *
   * @return \AKlump\DrupalTest\EndToEndTestCase
   *   Self for chaining.
   */
  public function setEmailHandler(EmailHandlerInterface $handler) {
    $this->emailHandler = $handler;

    return $this;
  }

  /**
   * Wait for email(s) to be received.
   *
   * This will mark a test failed if the timeout is reached before the
   * $expected_count of emails are received, or more emails are received than
   * $expected_count.
   *
   * @param int $expected_count
   *   The number of emails expected.  Defaults to 1.  Set this to a higher
   *   number to wait for that many emails to be received in the handler's
   *   inbox.
   * @param int|null $timeout
   *   Set this to 0 for no timeout.  Leave NULL for the default timeout.  Any
   *   other number is the number of seconds to timeout.
   *
   * @return array
   *   Each element is an instance of \PhpMimeMailParser\Parser.
   */
  public function waitForEmail($expected_count = 1, $timeout = NULL) {
    if (empty($this->emailHandler)) {
      throw new \RuntimeException("You must first call ::setEmailHandler() to use this method.");
    }
    $emails = [];
    $to = $this->emailHandler->getInboxAddress();
    $this->waitFor(function () use (&$emails, $expected_count) {
      $emails = array_merge($emails, $this->emailHandler->readMail());

      return count($emails) >= $expected_count;
    }, "$expected_count email(s) received by $to.", $timeout);

    if ($expected_count !== ($actual = count($emails))) {
      $this->fail("Expected email count of $expected_count has been exceeded; $actual emails actually received.");
    }

    return $emails;
  }

  /**
   * Wait for an element to appear on the page.
   *
   * This is helpful when waiting for ajax loading.  You can use this to wait
   * for an element to have a class by simply adding the class to
   * $css_selector.
   *
   * @param string $css_selector
   *   A CSS selector of an element the page.
   * @param int|null $timeout
   *   Set this to 0 for no timeout.  Leave NULL for the default timeout.  Any
   *   other number is the number of seconds to timeout.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The element you waited for.
   */
  public function waitForElement($css_selector, $timeout = NULL) {
    $this->waitFor(function () use ($css_selector) {
      try {
        $this->assert()->elementExists('css', $css_selector);
      }
      catch (\Exception $exception) {
        return FALSE;
      }

      return TRUE;
    }, "$css_selector is present on the page", $timeout);

    return $this->el($css_selector);
  }

  /**
   * Wait for an element to become visible on the page.
   *
   * @param string $css_selector
   *   A CSS selector of an element the page.
   * @param int|null $timeout
   *   Set this to 0 for no timeout.  Leave NULL for the default timeout.  Any
   *   other number is the number of seconds to timeout.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The element you waited for.
   */
  public function waitForElementVisible($css_selector, $timeout = NULL) {
    $el = NULL;
    $this->waitFor(function () use ($css_selector, $el) {
      $el = $this->el($css_selector);

      return !empty($el) && $el->isVisible();
    }, "$css_selector is visible on the page", $timeout);

    return $el;
  }

  /**
   * Wait for an element to become hidden on the page.
   *
   * @param string $css_selector
   *   A CSS selector of an element the page.
   * @param int|null $timeout
   *   Set this to 0 for no timeout.  Leave NULL for the default timeout.  Any
   *   other number is the number of seconds to timeout.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The element you waited for.
   */
  public function waitForElementNotVisible($css_selector, $timeout = NULL) {
    $el = NULL;
    $this->waitFor(function () use ($css_selector, $el) {
      $el = $this->el($css_selector);

      return !empty($el) && !$el->isVisible();
    }, "$css_selector is hidden on the page", $timeout);

    return $el;
  }

  /**
   * Wait for text to appear on a page.
   *
   * @param string $substring
   *   The text to wait for.
   * @param null $timeout
   *   Optional timeout in seconds.
   */
  public function waitForPageContains($substring, $timeout = NULL) {
    $this->waitFor(function () use ($substring) {
      try {
        $this->assert()->pageTextContains($substring);
      }
      catch (\Exception $exception) {
        return FALSE;
      }

      return TRUE;
    }, "\"$substring\" is present on the page", $timeout);
  }

  /**
   * Break the test and wait for observer to click a button.
   *
   * This is different from waitForObserver because it enables observation mode
   * if not already enabled for the course of this one event.  Then returns to
   * the previous state.  It also changes the button title to Debugger.
   */
  public function debugger() {
    $stash = [$this->observerButton, $this->observerIsObserving];
    // https://unicode.org/emoji/charts/full-emoji-list.html#25b6.
    $this->beginObservation('▶', ['is-debug-breakpoint']);
    $this->waitForObserver('body');
    list($this->observerButton, $this->observerIsObserving) = $stash;
  }

  /**
   * Scroll the page to the top.
   */
  public function scrollTop() {
    $this->getSession()->executeScript('window.scrollTo(0,0);');
  }

  /**
   * Indicate an observation stopping point in your test.
   *
   *   When observation mode is disabled this method does nothing.  However,
   *   when enabled, a UI button will be injected into the DOM after
   *   $css_selector, the test will pause indefinitely until this button is
   *   clicked, at which time the test will continue.
   *
   * @param string $css_selector
   *   The CSS selector of the exiting DOM element to attach our UI button to.
   *
   * @return ObserverTrait
   */
  public function waitForObserver($css_selector) {
    if ($element = $this->requireElement($css_selector)) {
      if ($this->observerIsObserving) {
        $this->injectObserverUiIntoDom($css_selector);

        // When the button is clicked it is removed and the waitFor will continue.
        $this->waitFor(function () {
          return !$this->el('.observe__next');
        }, 'Pause while demo is explained', 0);
      }
    }

    return $this;
  }

  /**
   * Turn on observation mode.
   *
   * @param string $button_title
   *   A title to override the default text.
   * @param array $button_css_classes
   *   Optional classes to add to the button.
   */
  public function beginObservation($button_title = '', array $button_css_classes = []) {
    $this->observerIsObserving = TRUE;
    if ($button_title) {
      $this->observerButton = $button_title;
    }
    $this->observerButtonClasses = $button_css_classes;
  }

  /**
   * Turn off observation mode.
   */
  public function endObservation() {
    $this->observerIsObserving = FALSE;
    $this->observerButton = '';
    $this->observerButtonClasses = [];
  }

  /**
   * Use this to ensure that an element exists or fail the test.
   *
   * @param string $css_selector
   *   The CSS selector for the required element.
   *
   * @return \Behat\Mink\Element\Element|null
   *   The element or null if not found in the DOM.
   */
  public function requireElement($css_selector) {
    if (!($element = $this->el($css_selector))) {
      $this->fail('Missing element .' . $css_selector);
    }

    return $element ? $element : NULL;
  }

  /**
   * Get the title of the observer continue button to use.
   *
   * @return string
   *   The overridden or default title.
   */
  protected function getObserverButtonTitle() {
    return $this->observerButton ? $this->observerButton : '&nbsp;';
  }

  /**
   * Inject our UI element into the DOM relative to $css_selector.
   *
   * @param string $css_selector
   *   The element to attach our observation UI to.
   */
  protected function injectObserverUiIntoDom($css_selector) {
    $type = substr($css_selector, 0, 1);
    $selector = substr($css_selector, 1);
    $button_title = $this->getObserverButtonTitle();
    switch ($type) {
      case '.':
        $selector = "document.getElementsByClassName('$selector')[0]";
        break;

      case '#':
        $selector = "document.getElementsById('$selector')";
        break;

      default:
        $selector = "document.getElementsByTagName('$css_selector')[0]";
        break;

    }

    $this->injectCssStyles($this->getObserverUiCssStyles());

    $class_name = $this->observerButtonClasses;
    array_unshift($class_name, 'observe__next');
    $class_name = implode(' ', $class_name);


    // Create a "continue" button and then wait for it to be removed from the DOM.
    $js = "(function(){ 
  var el = ${selector};
  var pointer = document.createElement('button');
  pointer.innerHTML = '${button_title}';
  pointer.className = '${class_name}';
  pointer.addEventListener('click', function() {
    this.remove();
  }, false);
  el.after(pointer);
})();";

    $this
      ->getSession()
      ->getDriver()
      ->evaluateScript(trim($js));
  }

  /**
   * Return the CSS styles to inject into the DOM for observers.
   *
   * @return string
   *   The CSS styles, less the <script> tag.  This should address the
   *   following elements:
   *   - .observe__next
   */
  protected function getObserverUiCssStyles() {
    return /** @lang CSS */ <<<CSS
.observe__next {
  margin-left: .5em;
  -moz-box-shadow:inset 0px 39px 0px -24px #fbafe3;
  -webkit-box-shadow:inset 0px 39px 0px -24px #fbafe3;
  box-shadow:inset 0px 39px 0px -24px #fbafe3;
  background-color:#ff5bb0;
  border:1px solid #ee1eb5;
  text-shadow:0px 1px 0px #c70067;
  -moz-border-radius:4px;
  -webkit-border-radius:4px;
  border-radius:4px;
  display:inline-block;
  cursor:pointer;
  color:#fff;
  font-family:Arial;
  font-size:20px;
  padding:6px 15px;
  text-decoration:none;
  z-index: 10000;
}
.observe__next:hover {
  background-color:#ef027d;
}
.observe__next:active {
  position:relative;
  top:1px;
}
.observe__next.is-debug-breakpoint {
  position: fixed;
  top: .25em;
  right: .25em;
  -moz-box-shadow:inset 0px 39px 0px -24px #3dc21b;
  -webkit-box-shadow:inset 0px 39px 0px -24px #3dc21b;
  box-shadow:inset 0px 39px 0px -24px #3dc21b;
  background-color:#44c767;
  border:1px solid #18ab29;
  text-shadow:0px 1px 0px #2f6627;
  z-index: 10000;
}
.observe__next.is-debug-breakpoint:hover {
  background-color:#5cbf2a;
}
CSS;
  }

  /**
   * Inject a <style> tag with $css into the current page.
   *
   * @param string $css
   *   The CSS styles.
   *
   * @throws \Behat\Mink\Exception\DriverException
   * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
   */
  public function injectCssStyles($css) {
    $css = str_replace("\n", '', trim($css));
    $js = <<<EOD
(function(){
  var head = document.getElementsByTagName('head')[0];
  var s = document.createElement('style');
  s.setAttribute('type', 'text/css');
  s.appendChild(document.createTextNode('{$css}'));
  head.appendChild(s);
})();
EOD;
    $this
      ->getSession()
      ->getDriver()
      ->evaluateScript($js);
  }

  /**
   * Get manual assertion CSS.
   *
   * @return string
   *   The CSS to use for manual assertions.
   */
  protected function getManualAssertUICssStyles() {
    return /** @lang CSS */ <<<CSS
.manual-test {
    z-index: 10000;
    position: absolute;
    display: flex;
    justify-content: center;
    align-items: center;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 20, 70, .75)
}

.manual-test a {
    text-decoration: underline;
}

.manual-test > div {
    width: 65%;
    height: auto;
    background: #fff;
    color: #333;
    padding: 2em;
    -webkit-box-shadow: 0px 0px 12px -1px rgba(10,10,10,0.65);
    -moz-box-shadow: 0px 0px 12px -1px rgba(10,10,10,0.65);
    box-shadow: 0px 0px 12px -1px rgba(10,10,10,0.65);
}

.manual-test__steps {
    margin-bottom: 1.5em;
}

.manual-test__assertion {
    font-size: 1.1em;
    margin-bottom: 2.5em;
}

.manual-test__buttons {
    display: flex;
    justify-content: space-around;
}

.manual-test__buttons > button {
    -moz-border-radius:4px;
    -webkit-border-radius:4px;
    border-radius:4px;
    display:inline-block;
    cursor:pointer;
    color:#fff;
    font-family:Arial;
    font-size:20px;
    padding:6px 15px;
    text-decoration:none;
}
.manual-test__pass {
    -moz-box-shadow:inset 0px 39px 0px -24px #3dc21b;
    -webkit-box-shadow:inset 0px 39px 0px -24px #3dc21b;
    box-shadow:inset 0px 39px 0px -24px #3dc21b;
    background-color:#44c767;
    border:1px solid #18ab29;
    color:#fff;
    text-shadow:0px 1px 0px #2f6627;
}
.manual-test__pass:hover {
    background-color:#5cbf2a;
}
.manual-test__fail {
    -moz-box-shadow:inset 0px 39px 0px -24px #f5978e;
    -webkit-box-shadow:inset 0px 39px 0px -24px #f5978e;
    box-shadow:inset 0px 39px 0px -24px #f5978e;
    background-color:#f24537;
    border:1px solid #d02718;
    color:#fff;
    text-shadow:0px 1px 0px #810e05;
}
.manual-test__fail:hover {
    background-color:#c62d1f;
}
CSS;
  }

  /**
   * Make a manual assertion.
   *
   * A manual assertion is a modal that will appear and ask the observer to
   * click pass or fail based on certain criteria.  You may ask the observer to
   * take steps by using $prerequisite_steps, or you may just ask them to
   * evaluate $assertion.  The test will hang until the user makes a choice.
   * The test is then marked as either pass/fail based on the response.
   *
   * @param string $assertion
   *   The manual assertion sentence.  Markdown is allowed.
   * @param array $prerequisite_steps
   *   An array of steps to be taken before evaluating the assertion.  Markdown
   *   is allowed.
   *
   * @throws \Behat\Mink\Exception\DriverException
   * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
   */
  public function assertManual($assertion, array $prerequisite_steps = []) {
    $manualTestMarkup = [];
    $markdown = new \Parsedown();
    if ($prerequisite_steps) {
      $prerequisite_steps = array_map([$markdown, 'text'], $prerequisite_steps);
      $manualTestMarkup[] = '<ol class="manual-test__steps"><li>' . implode('</li><li>', $prerequisite_steps) . '</ol>';
    }
    $manualTestMarkup[] = '<div class="manual-test__assertion">' . $markdown->text($assertion) . '</div>';
    $manualTestMarkup = implode('', $manualTestMarkup);

    $this->injectCssStyles($this->getManualAssertUICssStyles());

    $js = "(function(){ 
  var manualTest = document.createElement('div');
  manualTest.className = 'manual-test';
  
  var inner = document.createElement('div');
  manualTest.appendChild(inner);
  
  var assertion = document.createElement('div');
  assertion.innerHTML = '{$manualTestMarkup}';
  inner.appendChild(assertion);
  
  var buttons = document.createElement('div');
  buttons.className = 'manual-test__buttons';
  inner.appendChild(buttons);
  
  var failButton = document.createElement('button');
  failButton.innerHTML = 'Fail';
  failButton.className = 'manual-test__fail';
  failButton.addEventListener('click', function() {
    passButton.remove();
  }, false);
  buttons.appendChild(failButton);
  
  var passButton = document.createElement('button');
  passButton.innerHTML = 'Pass';
  passButton.className = 'manual-test__pass';
  passButton.addEventListener('click', function() {
    failButton.remove();
  }, false);
  buttons.appendChild(passButton);
  
  document.getElementsByTagName('body')[0].appendChild(manualTest);
})();";

    $this
      ->getSession()
      ->getDriver()
      ->evaluateScript(trim($js));

    // When the user clicks a button it will be removed from the DOM by JS, here
    // we wait for that and recognize which button is removed as the answer to
    // our assertion.
    $result = NULL;
    $this->waitFor(function () use (&$result) {
      if (!$this->el('.manual-test__pass')) {
        $result = FALSE;
      }
      if (!$this->el('.manual-test__fail')) {
        $result = TRUE;
      }

      // Once a choice has been made, then remove the modal overlay.
      if ($result !== NULL) {
        $js = "(function(){
  var test = document.getElementsByClassName('manual-test');
  document.getElementsByClassName('manual-test')[0].remove();
})();";

        $this
          ->getSession()
          ->getDriver()
          ->evaluateScript(trim($js));
      }

      return is_null($result) ? FALSE : TRUE;

    }, 'Wait for manual assertion.', 0);

    $this->assertTrue($result, $assertion);
  }

}
