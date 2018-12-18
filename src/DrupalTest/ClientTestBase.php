<?php

namespace AKlump\DrupalTest;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use JsonSchema\Validator;
use Sunra\PhpSimple\HtmlDomParser;

/**
 * A class to act as a client against URLs and API endpoints.
 *
 * Extend this class for test scenarios where you need to check URLs or consume
 * API endpoints.
 */
abstract class ClientTestBase extends \PHPUnit_Framework_TestCase {

  /**
   * Holds autodiscovered schema filepaths.
   *
   * @var array
   */
  protected static $jsonSchema = [];

  /**
   * This is read in from the environment variable CLIENT_TEST_BASE_URL.
   *
   * @var string
   */
  protected static $baseUrl = NULL;

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
   * {@inheritdoc}
   */
  public function setUp() {
    if (!($url = getenv('CLIENT_TEST_BASE_URL'))) {
      $this->markTestSkipped('Missing environment variable: CLIENT_TEST_BASE_URL');
    }
    static::$baseUrl = $url;
  }

  /**
   * Load a DOM from an URL.
   *
   * This must be called in any test method using any assertDom* methods.
   *
   * @param string $url
   *   The URL to load into the DOM.
   */
  public function loadDomByUrl($url) {
    $client = $this->getHtmlClient()->get($url);
    $body = $client->getBody()->__toString();

    $this->dom = HtmlDomParser::str_get_html($body);
  }

  /**
   * Mark test as incomplete if the dom has not yet been loaded.
   */
  public function verifyDomIsLoaded() {
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
   */
  public function assertDomRegExp($expected, $selector, $index = 0) {
    static::verifyDomIsLoaded();
    static::assertRegExp($expected, $this->dom->find($selector)[$index]->innertext);
  }

  /**
   * Assert an element is found in the DOM by CSS selector.
   *
   * @param string $selector
   *   A CSS selector of the element you want to check for.
   */
  public function assertDomElementExists($selector) {
    static::verifyDomIsLoaded();
    static::assertNotEmpty($this->dom->find($selector));
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
   */
  public function assertDomSame($expected, $selector, $index = 0) {
    static::verifyDomIsLoaded();
    static::assertSame($expected, $this->dom->find($selector)[$index]->innertext);
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
   */
  public function assertDomMetaTagSame($expected, $name, $attribute) {
    static::verifyDomIsLoaded();
    static::assertSame($expected, $this->dom->find('meta[name="' . $name . '"]')[0]->content);
  }

  /**
   * Return a new client for returning vnd.drupal7+json.
   *
   * @return \GuzzleHttp\Client
   *   The client to use for requests.
   */
  public function getHtmlClient() {
    return new Client([
      'base_uri' => static::$baseUrl,
      'headers' => [
        'Accept' => 'application/html',
      ],
    ]);
  }

  /**
   * Return a new client for returning vnd.drupal7+json.
   *
   * @return \GuzzleHttp\Client
   *   The client to use for requests.
   */
  public function getXmlClient() {
    return new Client([
      'base_uri' => static::$baseUrl,
      'headers' => [
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
        'base_uri' => static::$baseUrl,
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
   *
   * @return $this
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
  public function getDrupalCommandsClient() {
    return new Client([
      'base_uri' => static::$baseUrl,
      'headers' => [
        'Accept' => 'application/vnd.drupal7+json',
      ],
    ]);
  }

  /**
   * Return a new client for returning json.
   *
   * @return \GuzzleHttp\Client
   *   The client to use for requests that return json.
   */
  public function getJsonClient() {
    return new Client([
      'base_uri' => static::$baseUrl,
      'headers' => [
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
      foreach ($config->jsonschema->directory as $item) {
        $schema_dirs[] = realpath($dir . '/' . (string) $item);
      }
      foreach (array_filter($schema_dirs) as $schema_dir) {
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
   *   The response object.
   */
  public function assertResponseMatchesSchema($schema_filename, Response $response) {

    $filepath = $this->resolveSchemaFilename($schema_filename);

    $data = json_decode($response->getBody()->__toString());

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
  public function assertResponseIsAjaxCommands(Response $response) {
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

}
