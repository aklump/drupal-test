# Client Tests

This is a type of tests where you will be testing endpoints of an API or URLs.  Use this to check for URL statuses and headers for example.  This has the same scope as unit tests, which means you can access class constants, but no Drupal bootstrap.  Tests act as an API consumer or client of the website.  There are custom assert methods on this class to help with such testing.  These types can test for:

* Redirects
* Page contents
* HTTP status codes
* REST responses
      
## Configuration

The base URL must be set in an environment variable in order for client tests to work, e.g., 

    $ cd tests/phpunit
    $ export TEST_BASE_URL=http://my-website.loft; phpunit -c phpunit.xml --testsuite Client

If the site under test is behind http authorization you must also provide that, e.g.
    
    $ export TEST_BASE_URL=http://user:pass@my-website.loft; phpunit -c phpunit.xml --testsuite Client


## Assert Page Content

Using a string search:

    $this->loadPageByUrl('collections')
      ->assertPageContains('logo.jpg')

Using CSS selectors:
      
    $this->loadPageByUrl('search')
      ->assertDomElementExists('.views-widget-filter-id')

## Assert HTTP Status

    public function testBlogPage() {
      $this->loadHeadByUrl('blog')->assertHttpStatus(200);
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
        ->assertResponseMatchesSchema('resource.json');
  
      $this->assertSame(123, (int) $this->xml->id);
      $this->assertSame('name', (string) $this->xml->name);
    }  

