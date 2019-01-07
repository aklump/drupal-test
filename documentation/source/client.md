# Client Tests

This is a type of tests where you will be testing endpoints of an API or URLs.  Use this to check for URL statuses and headers for example.  This has the same scope as unit tests, which means you can access class constants, but no Drupal bootstrap.  Tests act as a API consumer or client of the website.  There are custom assert methods on this class to help with such testing.

    /**
     * Assert confirmation page is forbidden.
     */
    public function testConfirmationPageIsForbidden() {
      $this->assertLocalUrlStatus(403, implode('/', [
        'node',
        EarthriseService::NID_FORM,
        'done',
      ]));
    }
      
## Configuration

The base URL must be set in an environment variable in order for client tests to work, e.g., 

    $ cd tests/phpunit
    $ export TEST_BASE_URL=http://my-website.loft; phpunit -c phpunit.xml --testsuite Client


## JSON Schema

The client tests provide the means of validation using JSON Schema.  You indicate where your schema files are located in _phpunit.xml_ using the custom key `jsonschema`, e.g., 

    <phpunit>
        <jsonschema>
            <directory>../web/sites/all/modules/custom/my_module/tests/schema</directory>
        </jsonschema>
    </phpunit>
