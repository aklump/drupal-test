# Client Tests

This is a type of tests where you will be testing endpoints of an API or URLs.  Use this to check for URL statuses and headers for example.  This has the same scope as unit tests, which means you can access class constants, but no Drupal bootstrap.  You are a consumer or client of the website.

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
    $ export CLIENT_TEST_BASE_URL=http://develop.globalonenessproject.loft; phpunit -c phpunit.xml --testsuite Client

