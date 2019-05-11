---
id: client
---
# Client Tests

Client tests use [Mink](http://mink.behat.org/en/latest/) for the most part, sometimes just [Guzzle](http://docs.guzzlephp.org/en/stable/).  These tests are headless.  If you need a full browser, take a look at [End to End](@end-to-end).

If you can achieve your test with a Client test instead of an End to End test, it will be faster.

This is a type of tests where you will be testing endpoints of an API or URLs.  Use this to check for URL statuses and headers for example.  This has the same scope as unit tests, which means you can access class constants, but no Drupal bootstrap.  Tests act as an API consumer or client of the website.  There are custom assert methods on this class to help with such testing.  These types can test for:

* Redirects
* Page contents
* HTTP status codes
* REST responses
      
## Configuration

The base URL must be set in an environment variable (`TEST_BASE_URL` or `SIMPLETEST_BASE_URL`) in order for client tests to work, e.g., 

    $ cd tests/phpunit
    $ export TEST_BASE_URL=http://my-website.loft; phpunit -c phpunit.xml --testsuite Client
    
Or to match with Drupal 8 core you can do like this:
    
    $ export SIMPLETEST_BASE_URL=http://my-website.loft; phpunit -c phpunit.xml --testsuite Client

If the site under test is behind http authorization you must also provide that, e.g.
    
    $ export TEST_BASE_URL=http://user:pass@my-website.loft; phpunit -c phpunit.xml --testsuite Client

### Setting Environment Variables in _phpunit.xml_

Alternately you can [set them like so](https://phpunit.readthedocs.io/en/8.0/configuration.html#setting-php-ini-settings-constants-and-global-variables):

    <phpunit ... >
      <php>
        <env name="SIMPLETEST_BASE_URL" value="http://my-website.loft"/>
      </php>
      ...
    </phpunit>
    
## Cookies

* By default a single cookie jar is shared across all client tests.
* To reset the cookie jar for a given class use `static::emptyCookieJar();` inside of `::setUp()` on your test class.
* To reset the cookie jar for a given test use `static::emptyCookieJar();` inside the test method.   

## Assertions

In addition to the usual assertions, you will find some new `assert*` methods on the class, see the code for more information.  Also be aware that you have access to all of Mink's [WebAssert](https://github.com/Behat/Mink/blob/master/src/Behat/Mink/WebAssert.php) methods when you use `assert()`.  See examples to follow. 

## Assert Page Content

Using a string search:

    $this->loadPageByUrl('/collections')
      ->assert()->responseContains('logo.jpg')

    $this->loadPageByUrl('/collections')
      ->assert()->pageTextContains('Welcome home!')

Using CSS selectors:
      
    $this->loadPageByUrl('/search')
      ->assertElementExists('.views-widget-filter-id')

## Assert HTTP Status

    public function testBlogPage() {
      $this->loadPageByUrl('/blog')->assert()->statusCodeEquals(200);
    }
    
## Response validation with JSON Schema

<https://json-schema.org/latest/json-schema-validation.html#rfc.section.6.3.3>

The client tests provide the means of validation using JSON Schema.  You indicate where your schema files are located in _phpunit.xml_ using the custom key `jsonschema`.  Child nodes of `directory` are relative to _phpunit.xml_; you may use globs; you may have more than one `directory` node. 

    <phpunit>
        <jsonschema>
            <directory>../web/sites/all/modules/custom/*/tests/schema</directory>
        </jsonschema>
    </phpunit>

Then to validate an URL do something like the following:

### Validating XML Responses

This example shows how load an endpoint that returns XML and validate that using a JSON Schema; then it checks for specific values in the XML.

    public function testXMLEndpoint() {
      $this->loadXmlByUrl('api/1/some/endpoint')
        ->assertResponseMatchesSchema('resource.json')
        ->assert()->statusCodeEquals(200);
  
      $this->assertSame(123, (int) $this->xml->id);
      $this->assertSame('name', (string) $this->xml->name);
    }  

