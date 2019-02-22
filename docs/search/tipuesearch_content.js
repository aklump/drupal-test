var tipuesearch = {"pages":[{"title":"0.2.5","text":"   You should manually update composer.json with the following require:  \"aklump\/manual-test\": \"^1.2.1\",   0.2.0   TestClass::$schema has been replaced with TestClass::getSchema(). You must replace all usages of the class property with a class method.  ","tags":"","url":"CHANGELOG.html"},{"title":"Drupal Test","text":"    Summary  This is a complete testing solution for using PhpUnit with Drupal 7 websites.  It provides Unit, Kernel, and Client abstract test classes to use in writing your tests, a single test runner, testing for modules and themes, support for JsonSchema validation, as well as a set of guidelines and processes for better testing of Drupal 7 modules and websites.  All of this using a single test runner that can be divided by test suite or filtered by test class using normal PhpUnit options.  Finally, manual functional tests are supported as well.  Visit https:\/\/aklump.github.io\/drupal-test for full documentation.  Quick Start  After installation (see below), follow instructions in the documentation (docs\/index.html) to write and run tests.  Requirements   Composer PHPUnit   Contributing  If you find this project useful... please consider making a donation.  Installation  From inside the directory above the web root run this one-liner:  [ ! -d tests ] &amp;&amp; git clone https:\/\/github.com\/aklump\/drupal-test.git tests &amp;&amp; (cd tests &amp;&amp; .\/bin\/install.sh) || echo \"Installation error, nothing installed.\"   About the files in this project  The following files are considered core and should never be modified.  drupal_test.yml drupal_test_bootstrap.php LICENSE README.md   Additionally, do not add files to the following folders, which are replaced on every update.  It is safe to add classes to src so long as you avoid src\/DrupalTest.  docs src\/DrupalTest   Do not modify any of the files in bin, which are provided by this module.  You may add your own files to bin, if you wish.  Update to the latest version  From inside the tests directory, run:  .\/bin\/update.sh   This will copy over the core files from the latest repository, but leave the non-core files alone, namely phpunit.xml and composer.json, which you most-likely will have modified.  Configuration  See documentation for more information about configuration.   Open tests\/composer.json and add module and theme composer.json filepaths. From tests run composer update --lock. Open tests\/phpunit.xml and add any JSON schema directories.   Usage  Run All Tests  $ cd tests $ phpunit -c phpunit.xml   Run All Unit Tests  $ cd tests $ phpunit -c phpunit.xml --testsuite Unit   Run All Kernel Tests  $ cd tests $ phpunit -c phpunit.xml --testsuite Kernel   Run All Client Tests  $ cd tests $ phpunit -c phpunit.xml --testsuite Client   Refer to the documentation for more info. ","tags":"","url":"README.html"},{"title":"Client Tests","text":"  Client tests use Mink for the most part, sometimes just Guzzle.  These tests are headless.  If you need a full browser, take a look at End to End.  If you can achieve your test with a Client test instead of an End to End test, it will be faster.  This is a type of tests where you will be testing endpoints of an API or URLs.  Use this to check for URL statuses and headers for example.  This has the same scope as unit tests, which means you can access class constants, but no Drupal bootstrap.  Tests act as an API consumer or client of the website.  There are custom assert methods on this class to help with such testing.  These types can test for:   Redirects Page contents HTTP status codes REST responses   Configuration  The base URL must be set in an environment variable in order for client tests to work, e.g.,  $ cd tests\/phpunit $ export TEST_BASE_URL=http:\/\/my-website.loft; phpunit -c phpunit.xml --testsuite Client   If the site under test is behind http authorization you must also provide that, e.g.  $ export TEST_BASE_URL=http:\/\/user:pass@my-website.loft; phpunit -c phpunit.xml --testsuite Client   Cookies   By default a single cookie jar is shared across all client tests. To reset the cookie jar for a given class use static::emptyCookieJar(); inside of ::setUp() on your test class. To reset the cookie jar for a given test use static::emptyCookieJar(); inside the test method.   Assertions  In addition to the usual PHPUnit assertions, you will find some new assert* methods on the class, see the code for more information.  Also be aware that you have access to all of Mink's WebAssert methods when you use assert().  See examples to follow. ","tags":"","url":"client.html"},{"title":"Cheatsheet for Client Tests","text":"       ClientTestBase       assert ($fail_message = '')     assertContentType ($expected_type)     assertElementAttributeNotEmpty ($attribute, $selector, $index = 0)     assertElementExists ($css_selector, $failure_message = '')     assertElementNotEmpty ($selector, $index = 0)     assertElementNotExists ($css_selector, $failure_message = '')     assertElementNotVisible ($css_selector, $failure_message = '')     assertElementRegExp ($expected, $selector, $index = 0)     assertElementSame ($expected, $selector, $index = 0)     assertElementVisible ($css_selector, $failure_message = '')     assertMetaTagSame ($expected, $name, $attribute)     assertResponseIsAjaxCommands (GuzzleHttp\\Psr7\\Response or NULL $response = NULL)     assertResponseMatchesSchema ($schema_filename, GuzzleHttp\\Psr7\\Response or NULL $response = NULL)     assertUrlRedirectsTo ($redirected_url, $url)     el ($css_selector)     els ($css_selector)     emptyCookieJar ()     generate ($method)     getDomElements (array $css_selectors)     getDrupalCommandsClient ()     getHtmlClient ()     getJsonClient ()     getSession ()     getStored ($key, $default = NULL)     getXmlClient ()     handleBaseUrl ()     loadJsonByUrl ($url)     loadPageByUrl ($url)     loadXmlByUrl ($url)     resolveUrl ($url, $remove_authentication_credentials = false)     store ($key, $value)     Working with NodeElement objects  Learn [more here].(http:\/\/mink.behat.org\/en\/latest\/guides\/traversing-pages.html#documentelement-and-nodeelement)  &lt;?php  class Test {    public function testExample() {      \/\/ By using getDomElements we ensure only one of .t-link.     $el = $this-&gt;loadPageByUrl('\/node\/9750')       -&gt;getDomElements([         '.t-link',       ]);      \/\/ $el['.t-link'] is an instance of NodeElement.     $el['.t-link']-&gt;click();      \/\/ Altenatively you could do this.  But it will not break if there is     \/\/ more than one '.t-link' on the page. So it's less certain.     $this-&gt;el('.t-link')-&gt;click()       }  }        NodeElement       attachFile ($path)     blur ()     check ()     click ()     doubleClick ()     dragTo (Behat\\Mink\\Element\\ElementInterface $destination)     focus ()     getAttribute ($name)     getParent ()     getTagName ()     getValue ()     getXpath ()     hasAttribute ($name)     hasClass ($className)     isChecked ()     isSelected ()     isVisible ()     keyDown ($char, $modifier = NULL)     keyPress ($char, $modifier = NULL)     keyUp ($char, $modifier = NULL)     mouseOver ()     press ()     rightClick ()     selectOption ($option, $multiple = false)     setValue ($value)     submit ()     uncheck ()     Making Assertions with Mink  Load a page and then make assertions using Mink's WebAssert class.  &lt;?php  class Test {    public function testExample() {     $this-&gt;loadPageByUrl('\/node\/9750')       -&gt;assert()       -&gt;elementTextContains('css', '#button', 'English');      $this-&gt;assert()       -&gt;elementTextContains('css', '#button-2', 'Spanish');   }  }        WebAssert       addressEquals ($page)     addressMatches ($regex)     addressNotEquals ($page)     assert ($condition, $message)     assertElement ($condition, $message, Behat\\Mink\\Element\\Element $element)     assertElementText ($condition, $message, Behat\\Mink\\Element\\Element $element)     assertResponseText ($condition, $message)     checkboxChecked ($field, Behat\\Mink\\Element\\TraversableElement or NULL $container = NULL)     checkboxNotChecked ($field, Behat\\Mink\\Element\\TraversableElement or NULL $container = NULL)     cleanUrl ($url)     cookieEquals ($name, $value)     cookieExists ($name)     elementAttributeContains ($selectorType, $selector, $attribute, $text)     elementAttributeExists ($selectorType, $selector, $attribute)     elementAttributeNotContains ($selectorType, $selector, $attribute, $text)     elementContains ($selectorType, $selector, $html)     elementExists ($selectorType, $selector, Behat\\Mink\\Element\\ElementInterface or NULL $container = NULL)     elementNotContains ($selectorType, $selector, $html)     elementNotExists ($selectorType, $selector, Behat\\Mink\\Element\\ElementInterface or NULL $container = NULL)     elementsCount ($selectorType, $selector, $count, Behat\\Mink\\Element\\ElementInterface or NULL $container = NULL)     elementTextContains ($selectorType, $selector, $text)     elementTextNotContains ($selectorType, $selector, $text)     fieldExists ($field, Behat\\Mink\\Element\\TraversableElement or NULL $container = NULL)     fieldNotExists ($field, Behat\\Mink\\Element\\TraversableElement or NULL $container = NULL)     fieldValueEquals ($field, $value, Behat\\Mink\\Element\\TraversableElement or NULL $container = NULL)     fieldValueNotEquals ($field, $value, Behat\\Mink\\Element\\TraversableElement or NULL $container = NULL)     getCurrentUrlPath ()     getMatchingElementRepresentation ($selectorType, $selector, $plural = false)     pageTextContains ($text)     pageTextMatches ($regex)     pageTextNotContains ($text)     pageTextNotMatches ($regex)     responseContains ($text)     responseHeaderContains ($name, $value)     responseHeaderEquals ($name, $value)     responseHeaderMatches ($name, $regex)     responseHeaderNotContains ($name, $value)     responseHeaderNotEquals ($name, $value)     responseHeaderNotMatches ($name, $regex)     responseMatches ($regex)     responseNotContains ($text)     responseNotMatches ($regex)     statusCodeEquals ($code)     statusCodeNotEquals ($code)    ","tags":"","url":"cs--client.html"},{"title":"Cheatsheet for End to End Tests","text":"       EndToEndTestBase       assert ($fail_message = '')     assertElementExists ($css_selector, $failure_message = '')     assertElementNotExists ($css_selector, $failure_message = '')     assertElementNotVisible ($css_selector, $failure_message = '')     assertElementVisible ($css_selector, $failure_message = '')     debugger ()     el ($css_selector)     els ($css_selector)     generate ($method)     getDomElements (array $css_selectors)     getSession ()     getStored ($key, $default = NULL)     handleBaseUrl ()     loadPageByUrl ($url)     resolveUrl ($url, $remove_authentication_credentials = false)     scrollTop ()     store ($key, $value)     wait ($seconds)     waitFor (callable $test, $description = NULL, $timeout = NULL)     waitForElement ($css_selector, $timeout = NULL)     waitForElementNotVisible ($css_selector, $timeout = NULL)     waitForElementVisible ($css_selector, $timeout = NULL)     waitForPageContains ($substring, $timeout = NULL)     Working with NodeElement objects  Learn [more here].(http:\/\/mink.behat.org\/en\/latest\/guides\/traversing-pages.html#documentelement-and-nodeelement)  &lt;?php  class Test {    public function testExample() {      \/\/ By using getDomElements we ensure only one of .t-link.     $el = $this-&gt;loadPageByUrl('\/node\/9750')       -&gt;getDomElements([         '.t-link',       ]);      \/\/ $el['.t-link'] is an instance of NodeElement.     $el['.t-link']-&gt;click();      \/\/ Altenatively you could do this.  But it will not break if there is     \/\/ more than one '.t-link' on the page. So it's less certain.     $this-&gt;el('.t-link')-&gt;click()       }  }        NodeElement       attachFile ($path)     blur ()     check ()     click ()     doubleClick ()     dragTo (Behat\\Mink\\Element\\ElementInterface $destination)     focus ()     getAttribute ($name)     getParent ()     getTagName ()     getValue ()     getXpath ()     hasAttribute ($name)     hasClass ($className)     isChecked ()     isSelected ()     isVisible ()     keyDown ($char, $modifier = NULL)     keyPress ($char, $modifier = NULL)     keyUp ($char, $modifier = NULL)     mouseOver ()     press ()     rightClick ()     selectOption ($option, $multiple = false)     setValue ($value)     submit ()     uncheck ()     Making Assertions with Mink  Load a page and then make assertions using Mink's WebAssert class.  &lt;?php  class Test {    public function testExample() {     $this-&gt;loadPageByUrl('\/node\/9750')       -&gt;assert()       -&gt;elementTextContains('css', '#button', 'English');      $this-&gt;assert()       -&gt;elementTextContains('css', '#button-2', 'Spanish');   }  }        WebAssert       addressEquals ($page)     addressMatches ($regex)     addressNotEquals ($page)     assert ($condition, $message)     assertElement ($condition, $message, Behat\\Mink\\Element\\Element $element)     assertElementText ($condition, $message, Behat\\Mink\\Element\\Element $element)     assertResponseText ($condition, $message)     checkboxChecked ($field, Behat\\Mink\\Element\\TraversableElement or NULL $container = NULL)     checkboxNotChecked ($field, Behat\\Mink\\Element\\TraversableElement or NULL $container = NULL)     cleanUrl ($url)     cookieEquals ($name, $value)     cookieExists ($name)     elementAttributeContains ($selectorType, $selector, $attribute, $text)     elementAttributeExists ($selectorType, $selector, $attribute)     elementAttributeNotContains ($selectorType, $selector, $attribute, $text)     elementContains ($selectorType, $selector, $html)     elementExists ($selectorType, $selector, Behat\\Mink\\Element\\ElementInterface or NULL $container = NULL)     elementNotContains ($selectorType, $selector, $html)     elementNotExists ($selectorType, $selector, Behat\\Mink\\Element\\ElementInterface or NULL $container = NULL)     elementsCount ($selectorType, $selector, $count, Behat\\Mink\\Element\\ElementInterface or NULL $container = NULL)     elementTextContains ($selectorType, $selector, $text)     elementTextNotContains ($selectorType, $selector, $text)     fieldExists ($field, Behat\\Mink\\Element\\TraversableElement or NULL $container = NULL)     fieldNotExists ($field, Behat\\Mink\\Element\\TraversableElement or NULL $container = NULL)     fieldValueEquals ($field, $value, Behat\\Mink\\Element\\TraversableElement or NULL $container = NULL)     fieldValueNotEquals ($field, $value, Behat\\Mink\\Element\\TraversableElement or NULL $container = NULL)     getCurrentUrlPath ()     getMatchingElementRepresentation ($selectorType, $selector, $plural = false)     pageTextContains ($text)     pageTextMatches ($regex)     pageTextNotContains ($text)     pageTextNotMatches ($regex)     responseContains ($text)     responseHeaderContains ($name, $value)     responseHeaderEquals ($name, $value)     responseHeaderMatches ($name, $regex)     responseHeaderNotContains ($name, $value)     responseHeaderNotEquals ($name, $value)     responseHeaderNotMatches ($name, $regex)     responseMatches ($regex)     responseNotContains ($text)     responseNotMatches ($regex)     statusCodeEquals ($code)     statusCodeNotEquals ($code)    ","tags":"","url":"cs--end-to-end.html"},{"title":"Destructive Tests","text":"  Prevent certain test(s) from running against production.  In some cases, certain tests should not be allowed to run against certain base URLs.  For example, you should not let end to end tests that modify the database run against production.  These types of tests are called here as destructive.  Prevent Entire Test Class   You may mark entire classes @destructive, in which case all tests in the class will be skipped if the TEST_BASE_URL is not in the defined list.  &lt;?php  namespace Drupal\\Tests\\my_module;  \/**  * Ensures we can create a new user via registration modal.  *  * @destructive  *\/ class ModalRegistrationEndToEndTest extends EndToEndTestBase { ...    Prevent Single Test Methods   You may mark individual test methods @destructive  \/**  * @destructive  *\/ public function testCanDeleteAUser() { ...    Define Which URLs Are \"Production\"   Anything marked with @destructive will be skipped unless TEST_BASE_URL is listed as shown next. Create the whitelist in phpunit.xml like this:  &lt;phpunit ... &gt;     &lt;allowDestructiveTests&gt;         &lt;url&gt;http:\/\/mysite.local&lt;\/url&gt;         &lt;url&gt;https:\/\/stage.mysite.com&lt;\/url&gt;     &lt;\/allowDestructiveTests&gt;     ...    How It Works  A test class must use \\AKlump\\DrupalTest\\Utilities\\DestructiveTrait if you want to use this convention.  The base classes: \\AKlump\\DrupalTest\\ClientTestBase and \\AKlump\\DrupalTest\\EndToEndTestBase already include this trait so you need only include the annotation as shown above. ","tags":"","url":"destructive.html"},{"title":"End to End Testing","text":"     End-to-end testing is a Software testing methodology to test an application flow from start to end. The purpose of end to end testing is to simulate the real user scenario and validate the system under test and its components for integration and data integrity.   Create these tests by extending \\AKlump\\DrupalTest\\EndToEndTestBase.  They should be saved in your Client folders.  These tests are the slowest as they offer a full browser environment, so they facilitate the testing of forms, multi-page workflows, and Javascript interaction.  If you just want to check for elements on a single page, use \\AKlump\\DrupalTest\\ClientTestBase instead.  These tests use Mink and Selenium to have a fully controllable browser, but run against a real site in it's active state.  These should be used when you wish to interact at the Browser level with a website in it's current state, just as you would as a human running manual tests.     These are not the same as Drupal 8's Browser Tests because: \"Browser tests create a complete Drupal installation and a virtual web browser and then use the virtual web browser to walk the Drupal install through a series of tests, just like you would do if you were doing it by hand.\"      These tests DO NOT create a complete Drupal installation.   Installation   You must have a running selenium server to use these tests.   Installing Selenium Standalone Server  This should be as simple as downloading a file, and starting a Java process on that file.  Follow these steps:   Download from this page; the link should be at the top.  This needs to happen just once.  Place it in a logical location on your system, maybe your home folder. Create a shortcut script .\/bin\/selenium.sh to launch your Selenium server with contents like this, adjusted for your situation.  #!\/usr\/bin\/env bash java -jar \/Users\/aklump\/selenium\/selenium-server-standalone-3.141.59.jar  -host 127.0.0.1 -port 4444  Make that script executable chmod u+x .\/bin\/selenium.sh Start your server in a new terminal window:  cd {path to this lib} .\/bin\/selenium.sh  Verify it's running by visiting: http:\/\/127.0.0.1:4444\/wd\/hub\/static\/resource\/hub.html   Organizing Tests   Think of a single test class as one end-to-end scenario. Have lots of shorter test classes. Break each scenario (class) into small units, as test methods. Keep test methods small and named very discriptively.   Annotations   Give the test class a summary such as User is able to login... If the test modifies the database in any way mark the class @destructive. Make test method names very descriptive so they can be parsed: testLoggedInUserCanAccessUserSettingsPage  &lt;?php  namespace Drupal\\Tests\\gop;  \/**   User is able to login, change password, logout and login with new password.  @destructive *\/ class UserCanChangePasswordEndToEndTest extends EndToEndTestBase {  public function testLoggedInUserCanAccessUserSettingsPage() { ...    #  Using Mink's WebAssert   All of the methods of \\Behat\\Mink\\WebAssert are available in your test methods through the use of ::assert(), e.g.  public function testPageTextOnHomepage() {   $this-&gt;loadPageByUrl('\/');    \/\/ This is a method from \\Behat\\Mink\\WebAssert that has been made available by \\AKlump\\DrupalTest\\EndToEndTestBase   $this-&gt;assert()-&gt;pageTextContains('Welcome to My Website'); }  ::assert() returns you a wrapper around \\Behat\\Mink\\WebAssert that handles assertion tallies and failure exceptions normally throw by \\Behat\\Mink\\WebAssert in a way native to PhpUnit. There is some duplication with certain shorthand and common assertions, for example take these two examples of the same assertion.  The first one has an opinion that all elements should be searched using a CSS selector, so there's less typing, however the second is more powerful, and in fact necessary if you want to assert using XPath.  $this-&gt;assertElementExists('.t-trigger-account-dropdown'); $this-&gt;assert()-&gt;elementExists('css', '.t-trigger-account-dropdown');    An Example End To End Test   Notice we load the page using relative links.  $this-&gt;loadPageByUrl('\/');  Then we get the elements by CSS selectors, which we'll need to work the test.  Notice the latter two begin with .t-.  To keep tests as flexible as possible I prefer to add test only classes directly to elements, using these over any other kind of identification (class, id, XPath, etc).  These test classes will only be rendered during testing.  ::getDomElements requires there is only one DOM node with each given class.  $el = $this-&gt;getDomElements([   '.search-widget',   '.page-header__search a',   '.t-text-search__input',   '.t-text-search__submit', ]);  If all elements are located in the DOM then you will be able to work with them as in the example; if not the test will fail.  Each element is a Node Element, with all the corresponding methods.  $el['.search-widget']-&gt;isVisible()  Notice the messages passed to the assertions.  These should be an affirmative statement describing the correct result.  You should include these as assert arguments, rather than as code comments in order to make your tests more readable.  $this-&gt;assertFalse(   $el['.search-widget']-&gt;isVisible(),   \"The search modal is hidden by default.\" );  The use of waitFor is only necessary if you have to wait for some JS to execute, or to wait for an AJAX load. Lastly notice the use of ::readPage, it must be called because the session has changed pages, and we want to make assertions on the newest page.   The Entire Test  &lt;?php  namespace Drupal\\Tests\\my_project;  use AKlump\\DrupalTest\\EndToEndTestBase;  \/**  * Tests the Story resource endpoint.  *\/ class SearchEndToEndTest extends EndToEndTestBase {    public function testSearchLinkInHeaderSearchesByTextAndReturnsResults() {     $this-&gt;loadPageByUrl('\/');      $el = $this-&gt;getDomElements([       '.search-widget',       '.page-header__search a',       '.t-text-search__input',       '.t-text-search__submit',     ]);      $this-&gt;assertFalse(       $el['.search-widget']-&gt;isVisible(),       \"The search modal is hidden by default.\"     );      $el['.page-header__search a']-&gt;click();      $this-&gt;assertTrue(       $el['.search-widget']-&gt;isVisible(),       \"The search modal is revealed after clicking link.\"     );      $this-&gt;assertFalse(       $el['.t-text-search__submit']-&gt;isVisible(),       \"The text search submit button is hidden by default.\"     );     $el['.t-text-search__input']-&gt;setValue('tree');      $this-&gt;waitFor(function () use ($el) {       return $el['.t-text-search__submit']-&gt;isVisible();     },       'Submit button is made visible after entering a search phrase.'     );     $el['.t-text-search__submit']-&gt;click();      $this-&gt;readPage()       -&gt;assertPageContains('Planting a Tree of Peace', 'Search term yielded expected results.')       -&gt;assertPageContains('The Axis and the Sycamore');   }  }  ","tags":"","url":"end-to-end.html"},{"title":"Client Test Examples","text":"  Assert Page Content  Using a string search:  $this-&gt;loadPageByUrl('\/collections')   -&gt;assert()-&gt;responseContains('logo.jpg')  $this-&gt;loadPageByUrl('\/collections')   -&gt;assert()-&gt;pageTextContains('Welcome home!')   Using CSS selectors:  $this-&gt;loadPageByUrl('\/search')   -&gt;assertElementExists('.views-widget-filter-id')   Assert HTTP Status  public function testBlogPage() {   $this-&gt;loadPageByUrl('\/blog')-&gt;assert()-&gt;statusCodeEquals(200); }   Response validation with JSON Schema  https:\/\/json-schema.org\/latest\/json-schema-validation.html#rfc.section.6.3.3  The client tests provide the means of validation using JSON Schema.  You indicate where your schema files are located in phpunit.xml using the custom key jsonschema.  Child nodes of directory are relative to phpunit.xml; you may use globs; you may have more than one directory node.  &lt;phpunit&gt;     &lt;jsonschema&gt;         &lt;directory&gt;..\/web\/sites\/all\/modules\/custom\/*\/tests\/schema&lt;\/directory&gt;     &lt;\/jsonschema&gt; &lt;\/phpunit&gt;   Then to validate an URL do something like the following:  Validating XML Responses  This example shows how load an endpoint that returns XML and validate that using a JSON Schema; then it checks for specific values in the XML.  public function testXMLEndpoint() {   $this-&gt;loadXmlByUrl('api\/1\/some\/endpoint')     -&gt;assertResponseMatchesSchema('resource.json');    $this-&gt;assertSame(123, (int) $this-&gt;xml-&gt;id);   $this-&gt;assertSame('name', (string) $this-&gt;xml-&gt;name); }    ","tags":"","url":"ex--client.html"},{"title":"Extending Classes","text":"  You may want to extend the classes for your Drupal website.  For example you may want to add a method that can be shared by all end to end tests, client, etc.   Place your extended classes in the src directory of this project like so. Place it in a folder that is namespaced with a logical name related to your project.  . \u2514\u2500\u2500 src     \u251c\u2500\u2500 DrupalTest     \u2502\u00a0\u00a0 \u251c\u2500\u2500 ...     \u2514\u2500\u2500 module_name         \u251c\u2500\u2500 ClientTestBase.php         \u251c\u2500\u2500 EndToEndTestBase.php         \u251c\u2500\u2500 KernelTestBase.php         \u2514\u2500\u2500 UnitTestBase.php  Make sure your classes do extend the parent:  &lt;?php  namespace Drupal\\Tests\\module_name;  use \\AKlump\\DrupalTest\\ClientTestBase as Parent;  abstract class ClientTest extends Parent {    ...   Add your namespace to the autoloader in composer.json so your extended classes can be located.  {     \"autoload\": {         \"psr-4\": {             \"AKlump\\\\\": \"src\",             \"Drupal\\\\Tests\\\\module_name\\\\\": \"src\/module_name\"         }     } }  composer dumpautoload Now create your test classes using your extended base class instead, e.g.,  &lt;?php  namespace Drupal\\Tests\\module_name\\Metrics;  use Drupal\\Tests\\module_name\\ClientTestBase;  \/**  * Client coverage for Curriculum.  *  * @group module_name  * @SuppressWarnings(PHPMD.StaticAccess)  * @SuppressWarnings(PHPMD.TooManyPublicMethods)  *\/ class CurriculumClientTest extends ClientTestBase {    ...    ","tags":"","url":"extending.html"},{"title":"Kernel Tests","text":"   Test classnames should follow: &#42;KernelTest Kernel tests have a full Drupal bootstrap and access to the database, global functions and constants.   Data Providers and Kernel Tests   Bootstrapped Drupal elements, e.g. constants are not available in the data provider methods of a test class. Class constants are available, however.  ","tags":"","url":"kernel.html"},{"title":"Add Manual Tests","text":"  This project uses aklump\/manual-test for manual tests.  This page shows how to integrate manual tests into your Drupal project.  Configuration   Add configuration like the following (replacing tokens) to phpunit.xml:  &lt;phpunit ...&gt;   ...   &lt;manualtests&gt;       &lt;title&gt;{{ website or domain}}&lt;\/title&gt;       &lt;tester&gt;{{ default tester name }}&lt;\/tester&gt;       &lt;output&gt;{{ path to pdf output file }}&lt;\/output&gt;       &lt;testsuite name=\"Manual\"&gt;           &lt;directory&gt;..\/web\/sites\/all\/modules\/custom\/*\/tests\/src\/Manual&lt;\/directory&gt;           &lt;directory&gt;..\/web\/sites\/all\/modules\/custom\/*\/tests\/src\/Manual\/*&lt;\/directory&gt;       &lt;\/testsuite&gt;   &lt;\/manualtests&gt; &lt;\/phpunit&gt;  Here is an example for a Drupal 8 site.  &lt;phpunit ...&gt;   ...   &lt;manualtests&gt;       &lt;title&gt;www.mysite.org&lt;\/title&gt;       &lt;tester&gt;Aaron Klump&lt;\/tester&gt;       &lt;output&gt;..\/private\/default\/mysite-manual-tests.pdf&lt;\/output&gt;       &lt;testsuite name=\"Manual\"&gt;           &lt;directory&gt;..\/web\/modules\/custom\/*\/tests\/src\/Manual&lt;\/directory&gt;           &lt;directory&gt;..\/web\/modules\/custom\/*\/tests\/src\/Manual\/*&lt;\/directory&gt;       &lt;\/testsuite&gt;   &lt;\/manualtests&gt; &lt;\/phpunit&gt;    Generate tests  To create the PDF file for manual test running... See the documentation for more info.  cd tests export TEST_BASE_URL=\"http:\/\/www.mysite.com\"; .\/vendor\/bin\/generate --configuration=phpunit.xml --output=mysite-manual-tests.com.pdf --tester=\"Aaron Klump\"   Hint, create a shortcut file, something like: manual.sh  #!\/usr\/bin\/env bash source=\"${BASH_SOURCE[0]}\" while [ -h \"$source\" ]; do # resolve $source until the file is no longer a symlink   dir=\"$( cd -P \"$( dirname \"$source\" )\" &amp;&amp; pwd )\"   source=\"$(readlink \"$source\")\"   [[ $source != \/* ]] &amp;&amp; source=\"$dir\/$source\" # if $source was a relative symlink, we need to resolve it relative to the path where the symlink file was located done root=\"$( cd -P \"$( dirname \"$source\" )\" &amp;&amp; pwd )\" cd \"$root\/..\" export TEST_BASE_URL=\"http:\/\/www.mysite.com\"; .\/vendor\/bin\/generate --configuration=phpunit.xml --output=mysite-manual-tests.loft.pdf --tester=\"Aaron Klump\" $@  ","tags":"","url":"manual-tests.html"},{"title":"Module and Theme Testing","text":"     In order for unit testing to work with a module or a theme, you must add the module or theme's composer.json file to test\/composers.json and run composer install --lock from the test directory.   Quick Start   When you see tests\/composer.json, we are referring the file included in the dist folder of this project, which is the root directory containing the test runner phpunit.xml. Create composer.json in the module's folder ensuring it will autoload the module's classes that will be tested.  If not testing classes, but functions, make sure the module's composer.json file loads all files that contain the functions to be tested, usually using requre-dev.  This file may or may not have anything to do with Drupal (depending on the module's implementation), but it is required by the test runner's unit testing strategy. Add the path to the test runner's composer.json to tests\/composer.json, and you must call composer update --lock on the test runner for dependencies to be installed for the test runner. Add one or more tests to the module (according the file structure convention) extending one of the abstract classes provided by this module, e.g. \\AKlump\\DrupalTest\\UnitTestBase, `   Test File Structure  Each module or theme provides tests and schema files relative to it's own directory (.).  Following this convention allows the test runner to auto-discover these tests.  e.g.,  . \u2514\u2500\u2500 tests     \u251c\u2500\u2500 jsonschema     \u2502\u00a0\u00a0 \u2514\u2500\u2500 story_resource.json     \u2514\u2500\u2500 src         \u251c\u2500\u2500 Client         \u2502\u00a0\u00a0 \u251c\u2500\u2500 Service         \u2502\u00a0\u00a0 \u2502\u00a0\u00a0 \u2514\u2500\u2500 EarthriseServiceClientTest.php         \u251c\u2500\u2500 Kernel         \u2502\u00a0\u00a0 \u251c\u2500\u2500 Service         \u2502\u00a0\u00a0 \u2502\u00a0\u00a0 \u251c\u2500\u2500 BreakpointServiceKernelTest.php         \u2502\u00a0\u00a0 \u2514\u2500\u2500 TransformKernelTest.php         \u251c\u2500\u2500 TestBase.php         \u2514\u2500\u2500 Unit             \u251c\u2500\u2500 Service             \u2502\u00a0\u00a0 \u2514\u2500\u2500 EarthriseServiceUnitTest.php             \u2514\u2500\u2500 TransformUnitTest.php   Add the Module's composer.json  For a module to be unit testable it must have a composer.json file, which autoloads it's classes, the path of which must be added to this project's composer.json file, in the section extra.merge-plugin.require.  This is how the unit tests are able to autoload classes without bootstrapping Drupal, e.g.,      \"extra\": {         \"merge-plugin\": {             \"require\": [                 \"..\/web\/sites\/all\/modules\/custom\/gop3_core\/composer.json\"             ]         }     }   Add the module file as an autoload file  If you are unit testing a module that does not use classes, but functions and it has no dependencies, you do not need to add it's composer.json file to projects composer.json.  Instead you can tell this project to autoload the module files that have the functions you need to test, e.g.      \"autoload-dev\": {         \"files\": [             \"..\/web\/sites\/all\/modules\/custom\/twiggy\/twiggy.module\"         ]     },           Must Test Classes Test a Single Class?  Unit and Kernel tests do not have to test a single class, for example if you are writing a test to cover theme functions.  In order to make this happen you have to do the following in your test class:      class InTheLoftThemeKernelTest extends KernelTestBase {        protected $schema = [          \/\/ By setting this to false, we indicate we are not testing a class.         'classToBeTested' =&gt; FALSE,       ];      ...  ","tags":"","url":"modules.html"},{"title":"Search Results","text":" ","tags":"","url":"search--results.html"},{"title":"Unit Tests","text":"   Test classnames should follow: &#42;UnitTest Unit tests do not have access to the Drupal bootstrap; these are very fast.  You should try to write unit tests whenever possible.  Only move to Kernel tests if unit tests are not appropriate. If a unit test requires a file that is not normally autoloaded, it should be added to the autoload-dev section of the module's composer file.   A Note About Test Suite Order in XML  You will probably not need to modify phpunit.xml. But if you do...  Be very careful when modifying phpunit.xml, that you do not list even a single kernel test before any unit tests.  When any kernel test is run, Drupal will be bootstrapped which \"pollutes\" the global namespace with stuff that could bleed into your unit tests, giving them out-of-scope and misleading functions, classes, constants, etc.  In effect they could be acting like kernel tests, if you're not careful.  Notice how the unit tests come before the kernel tests in the code below; client tests must also precede the Kernel tests.  &lt;testsuites&gt;     &lt;testsuite name=\"Client\"&gt;         &lt;directory&gt;..\/..\/web\/sites\/all\/modules\/custom\/*\/tests\/src\/Client&lt;\/directory&gt;     &lt;\/testsuite&gt;     &lt;testsuite name=\"EndToEnd\"&gt;         &lt;directory&gt;..\/web\/sites\/all\/modules\/custom\/*\/tests\/src\/EndToEnd&lt;\/directory&gt;         &lt;directory&gt;..\/web\/sites\/all\/modules\/custom\/*\/tests\/src\/EndToEnd\/**&lt;\/directory&gt;         &lt;directory&gt;..\/web\/sites\/all\/themes\/*\/tests\/src\/EndToEnd&lt;\/directory&gt;         &lt;directory&gt;..\/web\/sites\/all\/themes\/*\/tests\/src\/EndToEnd\/**&lt;\/directory&gt;     &lt;\/testsuite&gt;     &lt;testsuite name=\"Unit\"&gt;         &lt;directory&gt;..\/..\/web\/sites\/all\/modules\/custom\/*\/tests\/src\/Unit&lt;\/directory&gt;     &lt;\/testsuite&gt;     &lt;testsuite name=\"Kernel\"&gt;         &lt;directory&gt;..\/..\/web\/sites\/all\/modules\/custom\/*\/tests\/src\/Kernel&lt;\/directory&gt;     &lt;\/testsuite&gt; &lt;\/testsuites&gt;   To be sure, run the entire unit test suite standalone on occassion, e.g.,  phpunit . --testsuite Unit   Troubleshooting  Fatal error: Class ... not found ...   Try running composer update --lock from tests (the test runner directory).  ","tags":"","url":"unit.html"}]};
