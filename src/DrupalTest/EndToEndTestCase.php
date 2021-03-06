<?php

namespace AKlump\DrupalTest;

use aik099\PHPUnit\Session\ISessionStrategyFactory;
use AKlump\DrupalTest\Utilities\Balloon;
use AKlump\DrupalTest\Utilities\DestructiveTrait;
use AKlump\DrupalTest\Utilities\DisplayObjectInterface;
use AKlump\DrupalTest\Utilities\EmailHandlerInterface;
use AKlump\DrupalTest\Utilities\InteractiveTrait;
use AKlump\DrupalTest\Utilities\Popup;
use AKlump\DrupalTest\Utilities\WebAssertTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\SetCookie;

/**
 * A class to interact with the browser for forms and navigation.
 */
abstract class EndToEndTestCase extends BrowserTestCase {

  const WAIT_TIMEOUT = 10;

  use DestructiveTrait {
    DestructiveTrait::assertPreConditions as destructiveAssertPreConditions;
  }
  use InteractiveTrait {
    InteractiveTrait::assertPreConditions as interactiveAssertPreConditions;
  }

  public static $browsers = array(
    array(
      'driver' => 'selenium2',
      'host' => 'localhost',
      'port' => 4444,
      'browserName' => 'chrome',
      'sessionStrategy' => ISessionStrategyFactory::TYPE_SHARED,
    ),
  );

  /**
   * Holds the cookie jar used by requests.
   *
   * @var \GuzzleHttp\Cookie\CookieJar
   */
  protected static $cookieJar;

  /**
   * Holds a string of CSS To be injected into pages during testing.
   *
   * @var string
   */
  private static $injectedCss = '';

  /**
   * Holds an email handler interface for email testing.
   *
   * @var \AKlump\DrupalTest\Utilities\EmailHandlerInterface
   */
  private static $emailHandler;

  /**
   * True when the test is being observed.
   *
   * @var bool
   */
  private static $observerIsObserving = FALSE;

  /**
   * The configured title of the continue button as shown to the observer.
   *
   * @var string
   */
  private static $observerButton = '▶';

  /**
   * An array of classes to be added to the observer button.
   *
   * This can be used to affect it's appearance.
   *
   * @var array
   */
  private static $observerButtonClasses = [];

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
   * User-injected CSS to be included during observation.
   *
   * @var string
   */
  private $observationCss = '';

  /**
   * {@inheritdoc}
   */
  public function assertPreConditions() {
    $this->destructiveAssertPreConditions();
    $this->interactiveAssertPreConditions();
  }

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
   * This method automatically calls clearAllNew() and should be called from
   * ::setUpBeforeClass() on classes that will test for email.
   *
   * @param \AKlump\DrupalTest\Utilities\EmailHandlerInterface $handler
   *   The handler instance.
   */
  public static function setEmailHandler(EmailHandlerInterface $handler) {
    self::$emailHandler = $handler->markAllRead();
  }

  /**
   * Return the current email handler.
   *
   * @return \AKlump\DrupalTest\Utilities\EmailHandlerInterface
   *   The current handler.
   */
  public static function getEmailHandler() {
    if (!self::$emailHandler) {
      throw new \RuntimeException("You must call ::setEmailHandler() first.");
    }

    return self::$emailHandler;
  }

  /**
   * Wait for email(s) to be received.
   *
   * This will mark a test failed if the timeout is reached before the
   * $expected_count of emails are received, or more emails are received than
   * $expected_count.
   *
   * When running in observation mode, the emails will appear as popups.
   *
   * Illustration credit: Vecteezy!
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
    $emails = [];
    $to = $this->getEmailHandler()->getInboxAddress();
    $this->waitFor(function () use (&$emails, $expected_count) {
      $new = $this->getEmailHandler()->readMail();
      if (self::$observerIsObserving) {
        foreach ($new as $email) {
          $body = $email->getMessageBody('text');
          $this->waitForObserver(Popup::create($body)
            ->title('Received by: ' . $email->getHeader('to'))
            ->subtitle('Subject: ' . $email->getHeader('subject'))
            // https://www.vecteezy.com/vector-art/166803-contact-me-icon-vector-pack
            ->icon('<svg width="110" height="119" viewBox="0 0 110 119" xmlns="http://www.w3.org/2000/svg"><title>envelope</title><g fill="none" fill-rule="evenodd"><path d="M99.98 119H9.965C4.465 119 0 114.536 0 109.037V50.785C0 46.485 3.485 43 7.787 43h94.426A7.784 7.784 0 0 1 110 50.785v58.252c-.054 5.499-4.52 9.963-10.02 9.963z" fill="#383754" fill-rule="nonzero"/><path d="M3.761 40.068L47.914 2.62c4.088-3.493 10.139-3.493 14.227 0l44.152 37.448a10.67 10.67 0 0 1 3.707 8.08L55 91 0 48.148c0-3.167 1.363-6.114 3.761-8.08z" fill="#FCB341" fill-rule="nonzero"/><path d="M2 115.107l46.644-38.811c3.71-3.061 9.056-3.061 12.712 0L108 115.106s-2.51 3.882-7.91 3.882H9.91s-5.237.437-7.91-3.881z" fill="#45466D" fill-rule="nonzero"/><text fill="#FFF" font-family="Helvetica" font-size="40"><tspan x="34" y="58">@</tspan></text></g></svg>')
          );
        }
      }
      $emails = array_merge($emails, $new);

      return count($emails) >= $expected_count;
    }, "$expected_count email(s) received by $to.", $timeout);

    if ($expected_count !== ($actual = count($emails))) {
      $this->fail("Expected email count of $expected_count has been exceeded; $actual emails actually received.");
    }

    return $emails;
  }

  /**
   * {@inheritdoc}
   */
  public function loadPageByUrl($url) {
    parent::loadPageByUrl($url);
    self::doCssInjection();

    return $this;
  }

  /**
   * Do the actual CSS injection.
   *
   * You may need to do this after a page loaded by clicking a link.  This
   * simply takes whatever CSS has been defined and injects it into the DOM.
   *
   * @throws \Behat\Mink\Exception\DriverException
   * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
   */
  public function doCssInjection() {
    if (self::$injectedCss) {
      $this->injectCssStyles(self::$injectedCss);
    }
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
    $this->waitFor(function () use ($css_selector, &$el) {
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
    // https://unicode.org/emoji/charts/full-emoji-list.html#25b6.
    $this->injectObserverUiIntoDom('body', 'after', '▶', '', ['is-debug-breakpoint']);

    // When button is clicked it is removed and the waitFor will continue.
    $this->waitFor(function () {
      return !$this->el('.observe__next');
    }, 'Pause for debugging', 0);

    return $this;
  }

  /**
   * Scroll the page to the top.
   */
  public function scrollTop() {
    $this->getSession()->executeScript('window.scrollTo(0,0);');
  }

  /**
   * Scroll page so that an element's top is at window top.
   *
   * @param string $css_selector
   *   The element selector.
   * @param int $offset
   *   An additional number of pixels from the top.
   */
  public function scrollToElementTop($css_selector, $offset = 20) {
    // TODO Look at injectObserverUiIntoDom.
    $selection = $this->getJavascriptSelectorCode($css_selector);
    $js = <<<JS
var el = ${selection};
if (el !== undefined) {
  var bound = el.getBoundingClientRect();
  var y = window.pageYOffset;
  var margin = bound.y < 0 ? -1 * $offset : $offset;
  window.scrollTo(0, y + bound.y + margin);
}
JS;

    $this->getSession()->executeScript($js);
  }

  /**
   * Indicate an observation stopping point in your test.
   *
   * When observation mode is disabled this method has no effect.  However,
   * when enabled, a UI button will be injected into the DOM after
   * $css_selector, the test will pause indefinitely until this button is
   * clicked, at which time the test will continue.
   *
   * @param \AKlump\DrupalTest\Utilities\DisplayObjectInterface|string $display_object
   *   If you just want to attach a play button to an element, you may send the
   *   $css_selector as a single argument. An instance of a display object.
   *   For legacy support arguments are still accepted following this footprint
   *   $css_selector, $balloon_message,
   *   $position, $before, $after), but this is deprecated and will be removed
   *   in future versions.
   *
   * @return \AKlump\DrupalTest\EndToEndTestCase
   *   Self for chaining.
   *
   * @throws \Behat\Mink\Exception\DriverException
   * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
   */
  public function waitForObserver($display_object = NULL) {
    if ($display_object instanceof Popup) {
      return $this->waitForObserverPopup($display_object);
    }

    $this->assertTrue(TRUE);

    $default_position = 'after';

    // Legacy support.
    if (!$display_object instanceof DisplayObjectInterface) {
      list($css_selector, $balloon_message, $position, $before, $after) = func_get_args() + [
        '',
        '',
        $default_position,
        '',
        NULL,
      ];
      $display_object = Balloon::create($balloon_message)
        ->el(strval($css_selector))
        ->{$position}()
        ->delay(intval($before));
      if (is_callable($after)) {
        $display_object->onAfterShow($after);
      }
    }

    $css_selector = $display_object->getCssSelector('.observe__anchor');
    $this->injectObserverAnchors();
    if ($element = $this->requireElement($css_selector)) {
      if (self::$observerIsObserving) {
        $display_object->dispatch('onBeforeShow');
        $this->injectObserverUiIntoDom($css_selector, $display_object->getPosition($default_position), self::$observerButton, $display_object->getBody(), self::$observerButtonClasses);
        //        $this->scrollToElementTop('.observe__message');
        $display_object->dispatch('onAfterShow');

        // When button is clicked it is removed and the waitFor will continue.
        $this->waitFor(function () {
          return !$this->el('.observe__next');
        }, 'Pause while demo is explained', 0);
      }
    }

    return $this;
  }

  /**
   * This is called automatically, so you needed add it generally.
   *
   * @return $this
   * @throws \Behat\Mink\Exception\DriverException
   * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
   */
  public function waitForFinished() {
    if (self::$observerIsObserving) {
      $this->injectObserverUiIntoDom('body', 'after', '🛑', '', ['is-final-screen']);

      // When button is clicked it is removed and the waitFor will continue.
      $this->waitFor(function () {
        return !$this->el('.observe__next');
      }, 'Pause after demo has ended.', 0);
    }

    return $this;
  }

  /**
   * Display a popup and wait for user to close it.
   *
   * @param \AKlump\DrupalTest\Utilities\Popup $popup
   *   The contents of the popup.
   *
   * @return \AKlump\DrupalTest\EndToEndTestCase
   *   Self for chaining.
   *
   * @throws \Behat\Mink\Exception\DriverException
   * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
   *
   * @deprecated Use waitForObserver with Popup argument instead.
   */
  private function waitForObserverPopup(Popup $popup) {
    $this->assertTrue(TRUE);
    if (!self::$observerIsObserving) {
      return $this;
    }
    $this->injectCssStyles($this->getPopupCssStyles());
    $container_markup = str_replace('"', '\"', $popup->getContainerInnerHtml());
    // Some non-printable chars cause problems with the JS popup, so we remove them here.
    // @link https://stackoverflow.com/questions/1176904/php-how-to-remove-all-non-printable-characters-in-a-string
    // @link https://alvinalexander.com/php/how-to-remove-non-printable-characters-in-string-regex
    $container_markup = preg_replace('/[\x00-\x1F\x80-\xFF]/u', '', $container_markup);
    $js = <<<JS
(function(){ 
  var popup = document.createElement('div');
  popup.className = 'popup';
  
  var container = document.createElement('div');
  container.className = 'popup__container';
  container.innerHTML = "${container_markup}";
  popup.appendChild(container);
  
  var popupClose = document.createElement('div');
  popupClose.innerHTML = '&times;';
  popupClose.className = 'popup__close';
  popupClose.addEventListener('click', function() {
    popup.remove();
  }, false);
  container.appendChild(popupClose);
  
  document.getElementsByTagName('body')[0].appendChild(popup);
})();
JS;
    $this
      ->getSession()
      ->executeScript(trim($js));

    $result = NULL;
    $this->waitFor(function () use (&$result) {
      return !$this->el('.popup__close');
    }, 'Wait for popup to close.', 0);

    return $this;
  }

  /**
   * Turn on observation mode.
   *
   * @param string $button_title
   *   A title to override the default text.
   * @param array $button_css_classes
   *   Optional classes to add to the button.
   *
   * @return \PHPUnit\Framework\TestCase.
   *   Self for chaining.
   */
  public function beginObservation($button_title = '', array $button_css_classes = []) {
    self::$observerIsObserving = TRUE;
    if ($button_title) {
      self::$observerButton = $button_title;
    }
    if ($button_css_classes) {
      self::$observerButtonClasses = $button_css_classes;
    }

    return $this;
  }

  /**
   * Turn off observation mode.
   *
   * @return \AKlump\LoftTesting\PhpUnit\TestCase
   *   Self for chaining.
   */
  public function endObservation() {
    self::$observerIsObserving = FALSE;

    return $this;
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
      $this->fail('Missing element ' . $css_selector);
    }

    return $element ? $element : NULL;
  }

  /**
   * Given a CSS selector return the JS snippet to get that item.
   *
   * @param string $css_selector
   *   The CSS selector, e.g. '.arrow'.
   *
   * @return string
   *   The JS snippet to use.
   */
  public function getJavascriptSelectorCode($css_selector) {
    $selector = substr($css_selector, 1);
    $type = substr($css_selector, 0, 1);
    if ($type === '.') {
      return "document.getElementsByClassName('$selector')[0]";
    }
    elseif ($type === '#') {
      return "document.getElementById('$selector')";
    }
    elseif ($type === '[') {
      return "document.querySelectorAll('${type}${selector}')[0]";
    }

    return "document.getElementsByTagName('$css_selector')[0]";

  }

  /**
   * Inject observer anchors into DOM.
   */
  protected function injectObserverAnchors() {
    $anchor_selector = $this->getJavascriptSelectorCode('.observe__anchor');
    $js = <<<JS
(function(){ 
  document.body.classList.add("is-being-observed")
  var anchor = ${anchor_selector};
  if (anchor === undefined) {
    var middle = document.createElement('div');
    middle.className = 'observe__center';
    middle.innerHTML = '<div class="observe__anchor"></div>';
    document.body.appendChild(middle);
  }
})();
JS;
    $this
      ->getSession()
      ->executeScript(trim($js));
  }

  /**
   * Inject our UI element into the DOM relative to $css_selector.
   *
   * @param string $css_selector
   *   The element to attach our observation UI to.
   * @param string $position
   *   One of 'before' or 'after', which defines the placement of the button
   *   relative to the $css_selector.
   * @param string $button_title
   *   The title to display on the button.
   * @param string $short_message
   *   A short message to popup above the button.
   * @param array $button_classes
   *   An array of classes to apply to the button.
   *
   * @throws \Behat\Mink\Exception\DriverException
   * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
   */
  protected function injectObserverUiIntoDom($css_selector, $position, $button_title, $short_message = '', array $button_classes = []) {
    $this->injectCssStyles($this->getObserverUiCssStyles());
    if ($this->observationCss) {
      $this->injectCssStyles($this->observationCss);
    }
    array_unshift($button_classes, 'observe__next');
    $class_name = implode(' ', $button_classes);
    $selector = $this->getJavascriptSelectorCode($css_selector);
    $next_selector = $this->getJavascriptSelectorCode('.observe__next');
    if (!in_array($position, ['after', 'before'])) {
      throw new \InvalidArgumentException("\$position must be one of 'before' or 'after'; not \"$position\".");
    }

    // Create a "continue" button and then wait for it to be removed from the DOM.
    $js = <<<JS
(function(){ 
  var el = ${selector};
  var pointer = document.createElement('button');
  pointer.className = '${class_name}';
  pointer.addEventListener('click', function() {
    // window.scrollTo(0, 0);
    el.classList.remove('observe__target');
    this.remove();
  }, false);
  el.${position}(pointer);
  pointer.innerHTML = '${button_title}';
  el.classList.add('observe__target');
JS;
    if ($short_message) {
      $short_message = str_replace("'", "\'", strip_tags($short_message));
      $js .= <<<JS
   pointer.innerHTML += '<div class="observe__message">${short_message}</div>';     
   // var message = document.createElement('div');
   // message.innerHTML = '${short_message}';
   // message.className = 'observe__message';
   // pointer.before(message);
JS;
    }
    $js .= <<<JS
  pointer.innerHTML = '${button_title}';
  function getOffsetTop(element) {
    // Starting value is the offset from the top edge the button will appear.
    var offsetTop = -20;
    while(element) {
      offsetTop += element.offsetTop;
      element = element.offsetParent;
    }
    return offsetTop;
  }

  // Scroll to make sure the button is visible.
  var y = getOffsetTop(${next_selector});
  // document.documentElement.scrollTop = document.body.scrollTop = y;
})();
JS;
    $this
      ->getSession()
      ->executeScript(trim($js));
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
  height: initial;
  line-height: initial;
  white-space: normal;
  position: relative;
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
  outline: none;
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
.observe__next.is-final-screen {
  position: fixed;
  top: .25em;
  right: .25em;
  -moz-box-shadow:inset 0px 39px 0px -24px #c62d1f;
  -webkit-box-shadow:inset 0px 39px 0px -24px #c62d1f;
  box-shadow:inset 0px 39px 0px -24px #c62d1f;
  background-color:#e12f1f;
  border:1px solid #ab161b;
  text-shadow:0px 1px 0px #810e05;
  z-index: 10000;
}
.observe__next.is-final-screen:hover {
  background-color:#f24437;
}
.observe__next:after,
.observe__next:before {
  content: "";
  padding: 0;
}
.observe__target:not(.observe__anchor){
    -webkit-box-shadow: 0px 0px 5px 5px #ff5bb0 !important;
    -moz-box-shadow: 0px 0px 5px 5px #ff5bb0 !important;
    box-shadow: 0px 0px 5px 5px #ff5bb0 !important;
}
.observe__message {
    position: absolute;
    padding: 15px 20px;
    margin: 0;
    color: #fff;
    background-color:#ff5bb0;
    -moz-border-radius:4px;
    -webkit-border-radius:4px;
    border-radius:4px;
    text-align: left;
    transform: translateY(-100%);
    top: -30px;
    left: 0;
    font-size: 18px;
    line-height: 1.4;
    text-shadow: none;
    width: 240px
}
.observe__message:after {
    content: "";
    position: absolute;
    bottom: -35px;
    left: 30px;
    border-width: 0 0 35px 20px;
    border-style: solid;
    border-color: transparent #ff5bb0;
    display: block;
    width: 0;
}
.observe__center {
    position: fixed;
    top: 35%;
    left: 47%;
    z-index: 10000;
}
CSS;
  }

  /**
   * Allow tests to inject their own custom observation CSS.
   *
   * Once this is called, the CSS will be injected on every page loaded via
   * loadPageByUrl.
   *
   * @param string $css
   *   A valid CSS string.
   */
  public static function injectCss($css) {
    self::$injectedCss = $css;
  }

  /**
   * Allow tests to inject their own custom observation CSS.
   *
   * @param string $css
   *   A valid CSS string.
   */
  public function attachObservationCss($css) {
    $this->observationCss = $css;
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
      ->executeScript(trim($js));
  }

  /**
   * Get popup CSS.
   *
   * @return string
   *   The CSS to use for popup overlays.
   */
  protected function getPopupCssStyles() {
    return /** @lang CSS */ <<<CSS
.popup {
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
    background: rgba(30, 30, 40, .75)
}

.popup__container {
    font-size: 1.125rem;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", Helvetica, Arial, sans-serif;
    position: relative;
    width: 65%;
    height: auto;
    background: #fffffa;
    color: #333;
    padding: 2em;
    -webkit-box-shadow: 0px 0px 12px -1px rgba(10,10,10,0.65);
    -moz-box-shadow: 0px 0px 12px -1px rgba(10,10,10,0.65);
    box-shadow: 0px 0px 12px -1px rgba(10,10,10,0.65);
    height: 50%;
    overflow: visible;
    position: relative;
}

.popup__inner {
    height: 100%;
    overflow: auto;
}

.layout-two-col {
    display: flex;
    flex-direction: row;
    align-items: center;
}

.layout-two-col .popup__body {
    margin-left: 2em;
}

.popup__title {
    font-weight: bold;
    font-size: 1.6em;
    margin-top: 0;
}

.popup__subtitle {
    font-weight: bold;
    font-size: 1.2em;
    margin-top: 0;
}

.popup__container :first-child {
  margin-top: 0;
}
.popup__container :last-child {
  margin-bottom: 0;
}

.popup__close {
    text-align: center;
    line-height: 48px;
    width: 48px;
    height: 48px;
    font-size: 32px;
    cursor: pointer;
    top: 0;
    right: 0;
    position: absolute;
}

.popup__icon {
    position: absolute;
    top: 0;
    left: 50%;
    transform: translate(-50%, -70%);
}
CSS;
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
    font-size: 1.1em;
    line-height: 1.6;
}
.manual-test a {
    text-decoration: underline;
}

.manual-test.popup .popup__inner {
    display: flex;
    flex-direction: column;
}

.manual-test__steps {
    margin-bottom: 1.5em;
    list-style-position: inside;
    padding-left: 0;
}

.manual-test__assertions {
    margin-bottom: 2.5em;
    list-style: none;
    padding: 0;
    flex-grow: 1;
}

.manual-test__assertions li {
    padding-left: 1.4em;
    position: relative;
}

.manual-test__assertions li:before {
    position: absolute;
    left: 0;
    content: "✔ ";
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
   * A manual assertion is a popup that will appear and ask the observer to
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
  public function assertManual($assertions, array $prerequisite_steps = []) {
    $markdown = new \Parsedown();
    if ($prerequisite_steps) {
      $prerequisite_steps = array_map([$markdown, 'line'], $prerequisite_steps);
      $prerequisite_steps_markup = '<li>' . implode('</li><li>', $prerequisite_steps);
    }
    if (!is_array($assertions)) {
      $assertions = [$assertions];
    }
    $assertions = array_map([$markdown, 'line'], $assertions);
    $assertions_markup = '<li>' . implode('</li><li>', $assertions);

    $this->injectCssStyles($this->getPopupCssStyles());
    $this->injectCssStyles($this->getManualAssertUICssStyles());

    $js = "(function(){ 
  var manualTest = document.createElement('div');
  manualTest.className = 'popup manual-test';
  
  var container = document.createElement('div');
  container.className = 'popup__container';
  manualTest.appendChild(container);
  
  var inner = document.createElement('div');
  inner.className = 'popup__inner';
  container.appendChild(inner);";

    if ($prerequisite_steps) {
      $js .= "var steps = document.createElement('ol');
  steps.className = 'manual-test__steps';
  steps.innerHTML = '{$prerequisite_steps_markup}';
  inner.appendChild(steps);";
    }

    if ($assertions) {
      $js .= "var assertions = document.createElement('ol');
  assertions.className = 'manual-test__assertions';
  assertions.innerHTML = '{$assertions_markup}';
  inner.appendChild(assertions);";
    }

    $js .= "var buttons = document.createElement('div');
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
      ->executeScript(trim($js));

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

      // Once a choice has been made, then remove the popup overlay.
      if ($result !== NULL) {
        $js = "(function(){
  var test = document.getElementsByClassName('manual-test');
  document.getElementsByClassName('manual-test')[0].remove();
})();";

        $this
          ->getSession()
          ->executeScript(trim($js));
      }

      return is_null($result) ? FALSE : TRUE;

    }, 'Wait for manual assertion.', 0);

    $this->assertTrue($result, strip_tags(implode('; ', $assertions)));
  }

  /**
   * Make sure a web session has begun by visiting the homepage.
   *
   * @return \WebDriver\Session
   *   The web driver session.
   */
  protected function ensureWebSessionStarted() {
    $web_driver_session = $this->getSession()
      ->getDriver()
      ->getWebDriverSession();

    // We have to start up a web driver session if not yet started, or else
    // cookie sharing will fail.
    if (!$web_driver_session) {
      $web_driver_session = $this->loadPageByUrl('/')
        ->getSession()
        ->getDriver()
        ->getWebDriverSession();
    }

    return $web_driver_session;
  }

  /**
   * Return a headless client with the session and cookies from the browser.
   *
   * One use case where this is needed it to check the HTTP status code of an
   * endpoint, which cannot be done using the Selenium driver.  You can also
   * use it to check headers like content type.
   *
   * @param array $options
   *   Options for instantiation of the Guzzle client.  You may use this to
   *   override the defaults or set any additional.
   *
   * @return \GuzzleHttp\Client
   *   A new client instance with all the cookies from the current session
   *   attached.
   */
  public function getClient(array $options = []) {
    $web_driver_session = $this->getSession()
      ->getDriver()
      ->getWebDriverSession();

    // We have to start up a web driver session if not yet started, or else
    // cookie sharing will fail.
    if (!$web_driver_session) {
      $web_driver_session = $this->ensureWebSessionStarted();
    }

    // Import all the cookies from the current web-driver browsing session if
    // it's been started yet.
    if (($cookies = $web_driver_session->getAllCookies())) {
      foreach ($cookies as $cookie) {
        $cookie = new SetCookie([
          'Domain' => $cookie['domain'],
          'Expires' => $cookie['expiry'],
          'Name' => $cookie['name'],
          'Path' => $cookie['path'],
          'Value' => $cookie['value'],
          'HttpOnly' => $cookie['httpOnly'],
          'Secure' => $cookie['secure'],
        ]);
        $this->getCookieJar()->setCookie($cookie);
      }
    }

    return parent::getClient();
  }

  /**
   * Extract the numeric ID from current URL.
   *
   * This can be used to pull the Node ID or User ID, for example.
   *
   * @return int|null
   *   The id or NULL if not found.
   */
  public function getIdFromCurrentUrl() {
    return ($id = $this->getMatchedFromCurrentUrl('/\/(\d+)\/?/')) === NULL ? NULL : (int) $id;
  }

  /**
   * Extract an part of the current URL using a regex expression.
   *
   * @param string $regex
   *   The regex to use to match the desired part of the current url.  The
   *   value at [1] will be used so be sure to include parentheses.
   *
   * @return mixed
   *   Null if not found, otherwise the [1] as found by $regex.
   */
  public function getMatchedFromCurrentUrl(string $regex) {
    preg_match($regex, $this->getSession()->getCurrentUrl(), $matches);

    return $matches[1] ?? NULL;
  }

}
