<?php

namespace AKlump\DrupalTest;

use AKlump\PHPUnit\Drupal\CommandAssertion;
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
   * This is read in from the environment variable CLIENT_TEST_BASE_URL.
   *
   * @var string
   */
  protected static $baseUrl = NULL;

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
   * Check a remote URL for an http status code.
   *
   * @param int $http_status
   *   The expected status code.
   * @param string $url
   *   The absolute url.
   */
  public function assertRemoteUrlStatus($http_status, $url) {
    if (empty($url)) {
      throw new \RuntimeException("\$url cannot be empty");
    }
    $client = new Client();
    try {
      $response = $client->head($url);
    }
    catch (ClientException $e) {
      $response = $e->getResponse();
    }
    $this->assertSame($http_status, $response->getStatusCode());
  }

  /**
   * Check a local URL for an http status code.
   *
   * @param int $http_status
   *   The expected status code.
   * @param string $local_url
   *   The relative from drupal root.
   */
  public function assertLocalUrlStatus($http_status, $local_url) {
    if (empty($local_url)) {
      throw new \RuntimeException("\$local_url cannot be empty");
    }
    $client = new Client([
      'base_uri' => static::$baseUrl,
    ]);
    try {
      $response = $client->head($local_url);
    }
    catch (ClientException $e) {
      $response = $e->getResponse();
    }
    $this->assertSame($http_status, $response->getStatusCode());
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
   * @param string $schema_filename
   *   The basename.
   *
   * @return string
   *   The filepath to the schema.
   */
  protected function resolveSchemaFilename($schema_filename) {
    return getcwd() . '/schema/' . trim($schema_filename, '/');
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
   * {@inheritdoc}
   */
  public function tearDown() {
    $this->dom = NULL;
    CommandAssertion::handleAssertions($this);
  }

}
