<?php

namespace AKlump\DrupalTest;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use JsonSchema\Validator;
use Sunra\PhpSimple\HtmlDomParser;

/**
 * A class to act as a client against URLs and API endpoints.
 *
 * Extend this class for test scenarios where you need to check URLs or consume
 * API endpoints.
 *
 * @link http://simplehtmldom.sourceforge.net/manual.htm
 */
abstract class ClientTestBase extends \PHPUnit_Framework_TestCase implements HttpTestInterface {

  use HttpTestBaseTrait;
  use DestructiveTrait;

  /**
   * Holds autodiscovered schema filepaths.
   *
   * @var array
   */
  protected static $jsonSchema = [];

  /**
   * This is read in from the environment variable TEST_BASE_URL.
   *
   * @var string
   */
  protected static $baseUrl = NULL;

  /**
   * Holds the cookie jar used by requests.
   *
   * @var \GuzzleHttp\Cookie\CookieJar
   */
  protected static $cookieJar;

  /**
   * Holds the response of the last remote call.
   *
   * @var null
   */
  protected $response = NULL;

  /**
   * A \simple_html_dom instance.
   *
   * @var \simple_html_dom
   */
  protected $dom;

  /**
   * The loaded html document from loadPageByUrl.
   *
   * @var string
   */
  protected $html;

  /**
   * Holds the response's JSON.
   *
   * @var \stdClass
   */
  protected $json;

  /**
   * Holds the response's XML.
   *
   * @var \SimpleXMLElement
   */
  protected $xml;

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass() {
    if (!($url = getenv('TEST_BASE_URL'))) {
      static::markTestSkipped('Missing environment variable: TEST_BASE_URL');
    }
    static::$baseUrl = $url;
    if (empty(static::$cookieJar)) {
      static::emptyCookieJar();
    }
  }

  /**
   * Empty the cookie jar to create a new browsing session.
   */
  public static function emptyCookieJar() {
    static::$cookieJar = new CookieJar();
  }

  /**
   * Load a DOM from an URL.
   *
   * This must be called in any test method using any assertDom* methods.
   * This loads the following:
   *   - $this->dom
   *   - $this->html
   *   - $this->response.
   *
   * @param string $url
   *   The URL to load into the DOM.
   *
   * @return \AKlump\DrupalTest\ClientTestBase
   *   Self for chaining.
   */
  public function loadPageByUrl($url) {
    $this->response = $this->getHtmlClient()->get($url);
    $this->html = $this->response->getBody()->__toString();
    $this->dom = HtmlDomParser::str_get_html($this->html);

    return $this;
  }

  /**
   * Load a remote URL's response XML.
   *
   * This populates:
   *   - $this->xml
   *   - $this->response.
   *
   * @param string $url
   *   The URL which returns XML.
   *
   * @return $this
   *   Self for chaining.
   */
  public function loadXmlByUrl($url) {
    $this->response = $this->getXmlClient()->get($url);
    $this->xml = simplexml_load_string($this->response->getBody()
      ->__toString());

    return $this;
  }

  /**
   * Load a remote URL's response JSON.
   *
   * This populates:
   *   - $this->json
   *   - $this->response.
   *
   * @param string $url
   *   The URL which returns JSON.
   *
   * @return $this
   *   Self for chaining.
   */
  public function loadJsonByUrl($url) {
    $this->response = $this->getJsonClient()->get($url);
    $this->json = json_decode($this->response->getBody()
      ->__toString());

    return $this;
  }

  /**
   * Mark test as incomplete if the dom has not yet been loaded.
   */
  public function verifyPageIsLoaded() {
    if (empty($this->dom)) {
      static::markTestIncomplete('DOM not loaded!');
    }
  }

  /**
   * Assert the inner html of a DOM node matches a regular expression.
   *
   * @param string $expected
   *   The regex pattern to use against the inner html at $selector.
   * @param string $selector
   *   The DOM selector.
   * @param int $index
   *   The index when the selector returns multiple nodes. Defaults to 0.
   *
   * @return \AKlump\DrupalTest\ClientTestBase
   *   Self for chaining.
   */
  public function assertDomElementRegExp($expected, $selector, $index = 0) {
    static::verifyPageIsLoaded();
    static::assertRegExp($expected, $this->dom->find($selector)[$index]->innertext);

    return $this;
  }

  /**
   * Assert a loaded page contains a string.
   *
   * @param string $expected
   *   The string to search for.
   *
   * @return $this
   */
  public function assertPageContains($expected) {
    static::verifyPageIsLoaded();
    static::assertContains($expected, $this->html);

    return $this;
  }

  /**
   * Assert an element is found in the DOM by CSS selector and is not empty.
   *
   * @param string $selector
   *   A CSS selector of the element you want to check for.
   *
   * @return \AKlump\DrupalTest\ClientTestBase
   *   Self for chaining.
   */
  public function assertDomElementNotEmpty($selector, $index = 0) {
    static::verifyPageIsLoaded();
    self::assertNotEmpty(trim($this->dom->find($selector)[$index]->innertext), "$selector is not empty.");

    return $this;
  }

  /**
   * Assert a DOM element's attribute is not empty.
   *
   * @param string $selector
   *   A CSS selector of the element you want to check for.
   *
   * @return \AKlump\DrupalTest\ClientTestBase
   *   Self for chaining.
   */
  public function assertDomElementAttributeNotEmpty($attribute, $selector, $index = 0) {
    static::verifyPageIsLoaded();
    self::assertThat(!empty(trim($this->dom->find($selector)[$index]->{$attribute})), self::isTrue(), "$selector.$attribute is not empty.");

    return $this;
  }

  /**
   * Assert an element is found in the DOM by CSS selector.
   *
   * @param string $selector
   *   A CSS selector of the element you want to check for.
   *
   * @return \AKlump\DrupalTest\ClientTestBase
   *   Self for chaining.
   */
  public function assertDomElementExists($selector) {
    static::verifyPageIsLoaded();
    self::assertThat(!empty($this->dom->find($selector)), self::isTrue(), "$selector exists in the DOM.");

    return $this;
  }

  /**
   * Assert the inner html of a DOM node is an exact type/value.
   *
   * @param string $expected
   *   The expected inner html at $selector.
   * @param string $selector
   *   The DOM selector.
   * @param int $index
   *   The index when the selector returns multiple nodes. Defaults to 0.
   *
   * @return \AKlump\DrupalTest\ClientTestBase
   *   Self for chaining.
   */
  public function assertDomElementSame($expected, $selector, $index = 0) {
    static::verifyPageIsLoaded();
    static::assertSame($expected, $this->dom->find($selector)[$index]->innertext);

    return $this;
  }

  /**
   * Assert a metatag by name has an attribute with a given exact value.
   *
   * @param string $expected
   *   The exact expected type/value.
   * @param string $name
   *   The name.
   * @param string $attribute
   *   THe attribute to check for value.
   *
   * @return \AKlump\DrupalTest\ClientTestBase
   *   Self for chaining.
   */
  public function assertDomMetaTagSame($expected, $name, $attribute) {
    static::verifyPageIsLoaded();
    static::assertSame($expected, $this->dom->find('meta[name="' . $name . '"]')[0]->content);

    return $this;
  }

  /**
   * Return an array of shared request headers.
   *
   * @return array
   *   An array of headers shared across all requests.
   */
  public static function getSharedRequestHeaders() {
    return [];
  }

  /**
   * Return a new client for returning vnd.drupal7+json.
   *
   * @return \GuzzleHttp\Client
   *   The client to use for requests.
   *
   * @code
   *   $data = $response->getBody();
   * @endcode
   *
   * @link http://docs.guzzlephp.org/en/stable/quickstart.html#using-responses
   */
  public static function getHtmlClient() {
    return new Client([
      'cookies' => static::$cookieJar,
      'base_uri' => static::$baseUrl,
      'headers' => static::getSharedRequestHeaders() + [
          'Accept' => 'application/html',
        ],
    ]);
  }

  /**
   * Return a new client for returning vnd.drupal7+json.
   *
   * @return \GuzzleHttp\Client
   *   The client to use for requests.
   *
   * @link http://docs.guzzlephp.org/en/stable/quickstart.html#using-responses
   */
  public static function getXmlClient() {
    return new Client([
      'cookies' => static::$cookieJar,
      'base_uri' => static::$baseUrl,
      'headers' => static::getSharedRequestHeaders() + [
          'Accept' => 'application/xml',
        ],
    ]);
  }

  /**
   * Load an URL as $this->response.
   *
   * @param string $url
   *   May be absolute (begins with http) or local to the Drupal site.
   *
   * @return $this
   */
  public function loadHeadByUrl($url) {
    $options = [];
    if (strpos($url, 'http') !== 0) {
      $options = [
        'cookies' => static::$cookieJar,
        'base_uri' => static::$baseUrl,
        'headers' => static::getSharedRequestHeaders(),
      ];
    }
    $client = new Client($options);
    if (empty($url)) {
      throw new \RuntimeException("\$url cannot be empty");
    }
    try {
      $this->response = $client->head($url);
    }
    catch (ClientException $e) {
      $this->response = $e->getResponse();
    }

    return $this;
  }

  /**
   * Assert an HTTP status.
   *
   * @param int $status
   *   The desired status to pass.
   *
   * @return $this
   *   Self for chaining.
   *
   * @code
   * $this->loadHeadByUrl('get/discussion-guide/22')
   *   ->assertHttpStatus(200);
   * @endcode
   */
  public function assertHttpStatus($status) {
    $this->assertSame($status, $this->response->getStatusCode());

    return $this;
  }

  /**
   * Assert content type on $this->response.
   *
   * @param string $header
   *   This will be matched case insensitively.
   *
   * @return $this
   *  Self for chaining.
   *
   * @code
   * $this->loadHeadByUrl('get/discussion-guide/22')
   *   ->assertContentType('application/pdf');
   * @endcode
   */
  public function assertContentType($header) {
    $type = $this->response->getHeader('Content-type');
    $type = strtolower(reset($type));
    $this->assertSame(strtolower($header), $type);

    return $this;
  }

  /**
   * Return a new client for returning vnd.drupal7+json.
   *
   * @return \GuzzleHttp\Client
   *   The client to use for requests.
   */
  public static function getDrupalCommandsClient() {
    return new Client([
      'cookies' => static::$cookieJar,
      'base_uri' => static::$baseUrl,
      'headers' => static::getSharedRequestHeaders() + [
          'Accept' => 'application/vnd.drupal7+json',
        ],
    ]);
  }

  /**
   * Return a new client for returning json.
   *
   * @return \GuzzleHttp\Client
   *   The client to use for requests that return json.
   *
   * @link http://docs.guzzlephp.org/en/stable/quickstart.html#using-responses
   */
  public static function getJsonClient() {
    return new Client([
      'cookies' => static::$cookieJar,
      'base_uri' => static::$baseUrl,
      'headers' => static::getSharedRequestHeaders() + [
          'Accept' => 'application/json',
        ],
    ]);
  }

  /**
   * Return the filepath by basename of a schema file.
   *
   * The directories to search are defined in phpunit.xml as jsonschema and
   * works in the same manner as defining test suite directories.
   *
   * @code
   * <phpunit>
   *   <jsonschema>
   *     <directory>../web/sites/all/modules/custom/my_module/tests/jsonschema</directory>
   *   </jsonschema>
   * </phpunit>
   * @endcode
   *
   * @param string $schema_filename
   *   The basename.
   *
   * @return string
   *   The filepath to the schema.
   */
  protected function resolveSchemaFilename($schema_filename) {
    if (!array_key_exists($schema_filename, static::$jsonSchema)) {
      static::$jsonSchema[$schema_filename] = NULL;
      global $__PHPUNIT_CONFIGURATION_FILE;
      $dir = dirname($__PHPUNIT_CONFIGURATION_FILE);
      $config = simplexml_load_file($__PHPUNIT_CONFIGURATION_FILE);
      $schema_dirs = [];
      foreach ($config->jsonschema->directory as $path) {
        $paths = glob($dir . '/' . $path);
        $schema_dirs = array_merge($schema_dirs, $paths);
      }
      $schema_dirs = array_filter(array_map('realpath', $schema_dirs));
      foreach ($schema_dirs as $schema_dir) {
        if (is_dir($schema_dir) && ($items = scandir($schema_dir))) {
          $path = array_filter($items, function ($item) use ($schema_filename) {
            return $item === $schema_filename;
          });
          if ($path) {
            static::$jsonSchema[$schema_filename] = rtrim($schema_dir, '/') . '/' . reset($path);
            break;
          }
        }
      }
    }

    return static::$jsonSchema[$schema_filename];
  }

  /**
   * Assert response matches schema.
   *
   * @param string $schema_filename
   *   The basename of the schema file.
   * @param \GuzzleHttp\Psr7\Response $response
   *   The response object.  Leave blank to use $this->response.
   */
  public function assertResponseMatchesSchema($schema_filename, Response $response = NULL) {
    if (is_null($response)) {
      $response = $this->response;
    }
    $filepath = $this->resolveSchemaFilename($schema_filename);
    $type = $response->getHeader('Content-Type');
    $type = reset($type);
    if (stristr($type, '/xml')) {
      $data = simplexml_load_string($response->getBody()->__toString());
      $data = json_decode(json_encode($data));
    }
    elseif (stristr($type, '/json')) {
      $data = json_decode($response->getBody()->__toString());
    }
    else {
      throw new \RuntimeException("Cannot understand response content type: $type");
    }

    $validator = new Validator();
    $validator->validate($data, (object) ['$ref' => 'file://' . $filepath]);

    $this->assertSame([], $validator->getErrors());
  }

  /**
   * Assert a given response is an array of ajax commands.
   *
   * @param \GuzzleHttp\Psr7\Response $response
   *   The response object.
   */
  public function assertResponseIsAjaxCommands(Response $response = NULL) {
    if (is_null($response)) {
      $response = $this->response;
    }
    $test = new CommandAssertion($response);
    $test->shouldHaveCommand('settings')
      ->withInternalType('object', 'settings')
      ->with(TRUE, 'merge')
      ->once();

    $this->assertResponseMatchesSchema('d7_command_array.json', $response);
  }

  /**
   * Check a local URL for an http status code.
   *
   * @param int $http_status
   *   The expected status code.
   * @param string $local_url
   *   The relative from drupal root.
   *
   * @deprecated Use ::loadHeadByUrl && ::assertHttpStatus instead.
   */
  public function assertLocalUrlStatus($http_status, $local_url) {
    $this->assertSame($http_status, $this->loadHeadByUrl($local_url)->response->getStatusCode());
  }

  /**
   * Check a remote URL for an http status code.
   *
   * @param int $http_status
   *   The expected status code.
   * @param string $url
   *   The absolute url.
   *
   * @deprecated Use ::loadHeadByUrl && ::assertHttpStatus instead.
   */
  public function assertRemoteUrlStatus($http_status, $url) {
    $this->assertSame($http_status, $this->loadHeadByUrl($url)->response->getStatusCode());
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    $this->dom = NULL;
    $this->response = NULL;
    CommandAssertion::handleAssertions($this);
  }

  /**
   * Prepend the baseUrl to paths not beginning with http.
   *
   * @param string $path
   *   The path to prepend to if local.
   * @param bool $remove_authentication_credentials
   *   True to remove http authentication from absolute paths.
   *
   * @return string
   *   The resolved or original path.
   */
  private function resolvePath($path, $remove_authentication_credentials = FALSE) {
    if (strpos($path, 'http') !== 0) {
      $path = rtrim(static::$baseUrl, '/') . "/$path";
      $parts = parse_url($path);
      if ($remove_authentication_credentials) {
        $auth = [];
        if (!empty($parts['user'])) {
          $auth[] = $parts['user'];
        }
        if (!empty($parts['pass'])) {
          $auth[] = $parts['pass'];
        }
        if ($auth) {
          $find = $parts['scheme'] . '://' . implode(':', $auth) . '@';
          $replace = $parts['scheme'] . '://';
          $path = str_replace($find, $replace, $path);
        }
      }
    }

    return $path;
  }

  /**
   * Assert that one URL redirects to another.
   *
   * @param string $redirected_url
   *   The expected destinaton URL.
   * @param string $url
   *   The URL that will redirect.
   *
   * @return $this
   *   Self for chaining.
   */
  public function assertUrlRedirectsTo($redirected_url, $url) {
    $options = [
      'cookies' => static::$cookieJar,
      'allow_redirects' => FALSE,
      'headers' => static::getSharedRequestHeaders(),
    ];
    $url = $this->resolvePath($url);
    $client = new Client($options);
    if (empty($url)) {
      throw new \RuntimeException("\$url cannot be empty");
    }
    try {
      $response = $client->head($url);
      $location = $response->getHeader('location')[0];
      $redirected_url = $this->resolvePath($redirected_url, TRUE);
      static::assertThat($redirected_url === $location, static::isTrue(), "Failed asserting that $url redirects to $redirected_url.");
    }
    catch (ClientException $e) {
      $this->fail($e->getMessage());
    }

    return $this;
  }

}
