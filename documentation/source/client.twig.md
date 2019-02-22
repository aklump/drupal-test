# Client Tests

Client tests use [Mink](http://mink.behat.org/en/latest/) for the most part, sometimes just [Guzzle](http://docs.guzzlephp.org/en/stable/).  These tests are headless.  If you need a full browser, take a look at End to End.

If you can achieve your test with a Client test instead of an End to End test, it will be faster.

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

## Cookies

* By default a single cookie jar is shared across all client tests.
* To reset the cookie jar for a given class use `static::emptyCookieJar();` inside of `::setUp()` on your test class.
* To reset the cookie jar for a given test use `static::emptyCookieJar();` inside the test method.   

## Assertions

In addition to the usual _PHPUnit_ assertions, you will find some new `assert*` methods on the class, see the code for more information.  Also be aware that you have access to all of Mink's [WebAssert](https://github.com/Behat/Mink/blob/master/src/Behat/Mink/WebAssert.php) methods when you use `assert()`.  See examples to follow. 
