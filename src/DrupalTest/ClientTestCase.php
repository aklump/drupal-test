<?php

namespace AKlump\DrupalTest;

use AKlump\DrupalTest\Utilities\DestructiveTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use JsonSchema\Validator;

/**
 * A class to act as a headless client against URLs and API endpoints.
 *
 * Extend this class for test scenarios where you need to check URLs or consume
 * API endpoints.
 *
 * @link http://simplehtmldom.sourceforge.net/manual.htm
 */
abstract class ClientTestCase extends BrowserTestCase {

  use DestructiveTrait;

  public static $browsers = array(
    array(
      'driver' => 'goutte',
      'driverOptions' => array(
        'server_parameters' => array(),
        'guzzle_parameters' => array(),
      ),
    ),
  );

  /**
   * Holds autodiscovered schema filepaths.
   *
   * @var array
   */
  protected static $jsonSchema = [];

  /**
   * Holds the response of the last remote call.
   *
   * @var null
   */
  protected $response = NULL;

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
    static::handleBaseUrl();
  }

  /**
   * Return the current response object.
   *
   * @return mixed
   *   A response object.
   */
  public function getResponse() {
    return $this->response;
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
    try {
      $this->response = $this->getXmlClient()->get($url);
      $this->xml = simplexml_load_string($this->response
        ->getBody()
        ->getContents());
    }
    catch (BadResponseException $exception) {
      $this->response = $exception->getResponse();
      // TODO Handle this based on Content-Type header.
      $this->xml = simplexml_load_string($exception->getResponse()
        ->getBody()
        ->getContents());
    }

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
    try {
      $this->response = $this->getJsonClient()->get($url);
      $this->json = json_decode($this->response
        ->getBody()
        ->getContents());
    }
    catch (BadResponseException $exception) {
      $this->response = $exception->getResponse();
      // TODO Handle this based on Content-Type header.
      $this->json = json_decode($exception->getResponse()
        ->getBody()
        ->getContents());
    }

    return $this;
  }

  /**
   * Assert the inner html of a DOM node matches a regular expression.
   *
   * @param string $expected
   *   The regex pattern to use against the inner html at $css_selector.
   * @param string $css_selector
   *   The DOM selector.
   * @param int $index
   *   The index when the selector returns multiple nodes. Defaults to 0.
   *
   * @return \AKlump\DrupalTest\ClientTestCase
   *   Self for chaining.
   */
  public function assertElementRegExp($expected, $css_selector, $index = 0) {
    $els = $this->els($css_selector);
    if (empty($els[$index])) {
      $this->fail();
    }
    static::assertRegExp($expected, $els[$index]->getHtml());

    return $this;
  }

  /**
   * Assert an element is found in the DOM by CSS selector and is not empty.
   *
   * @param string $css_selector
   *   A CSS selector of the element you want to check for.
   *
   * @return \AKlump\DrupalTest\ClientTestCase
   *   Self for chaining.
   */
  public function assertElementNotEmpty($css_selector, $index = 0) {
    $els = $this->els($css_selector);
    if (empty($els[$index])) {
      $this->fail();
    }
    self::assertNotEmpty(trim($els[$index]->getText()), "$css_selector is not empty.");

    return $this;
  }

  /**
   * Assert a DOM element's attribute is not empty.
   *
   * @param string $css_selector
   *   A CSS selector of the element you want to check for.
   *
   * @return \AKlump\DrupalTest\ClientTestCase
   *   Self for chaining.
   */
  public function assertElementAttributeNotEmpty($attribute, $css_selector, $index = 0) {
    $els = $this->els($css_selector);
    if (empty($els[$index])) {
      $this->fail();
    }
    self::assertThat(!empty(trim($els[$index]->getAttribute($attribute))), self::isTrue(), "$css_selector.$attribute is not empty.");

    return $this;
  }

  /**
   * Assert the inner html of a DOM node is an exact type/value.
   *
   * @param string $expected
   *   The expected inner html at $css_selector.
   * @param string $css_selector
   *   The DOM selector.
   * @param int $index
   *   The index when the selector returns multiple nodes. Defaults to 0.
   *
   * @return \AKlump\DrupalTest\ClientTestCase
   *   Self for chaining.
   */
  public function assertElementSame($expected, $css_selector, $index = 0) {
    $els = $this->els($css_selector);
    if (empty($els[$index])) {
      $this->fail();
    }
    static::assertSame($expected, $els[$index]->getHtml());

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
   * @return \AKlump\DrupalTest\ClientTestCase
   *   Self for chaining.
   */
  public function assertMetaTagSame($expected, $name, $attribute) {
    static::assertSame($expected, $this->el('meta[name="' . $name . '"]')
      ->getAttribute('content'));

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
  public function getHtmlClient() {
    return $this->getClient([
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
  public function getXmlClient() {
    return $this->getClient([
      'headers' => static::getSharedRequestHeaders() + [
          'Accept' => 'application/xml',
        ],
    ]);
  }

  /**
   * Assert content type on $this->response.
   *
   * @param string $expected_type
   *   This will be matched case insensitively.
   *
   * @return $this
   *  Self for chaining.
   *
   * @deprecated
   */
  public function assertContentType($expected_type) {
    $actual_type = strtolower($this->getSession()
      ->getResponseHeader('Content-type'));
    $this->assertSame(strtolower($expected_type), $actual_type);

    return $this;
  }

  /**
   * Return a new client for returning vnd.drupal7+json.
   *
   * @return \GuzzleHttp\Client
   *   The client to use for requests.
   */
  public function getDrupalCommandsClient() {
    return $this->getClient([
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
  public function getJsonClient() {
    return $this->getClient([
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

    return $this;
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
   * {@inheritdoc}
   */
  public function tearDown() {
    $this->response = NULL;
    CommandAssertion::handleAssertions($this);
  }

}
