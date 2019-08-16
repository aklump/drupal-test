# An Example End To End Test

1. Notice we load the page using relative links.

        $this->loadPageByUrl('/');

1. Then we get the elements by CSS selectors, which we'll need to work the test.  Notice the latter two begin with `.t-`.  To keep tests as flexible as possible I prefer to add _test only_ classes directly to elements, using these over any other kind of identification (class, id, XPath, etc).  These test classes will only be rendered during testing.  `::getDomElements` requires there is only one DOM node with each given class.

        $el = $this->getDomElements([
          $widget = '.search-widget',
          $link = '.page-header__search a',
          $input = '.t-text-search__input',
          $submit = '.t-text-search__submit',
        ]);

1. If all elements are located in the DOM then you will be able to work with them as in the example; if not the test will fail.  Each element is a [Node Element](http://mink.behat.org/en/latest/guides/traversing-pages.html), with all the corresponding methods.

        $el[$widget]->isVisible()

1. Notice how we use aliasing `.search-widget` to `$widget` to be more DRY, by defining it in `$this->getDomElements()` it can be reused, both as a key to `$el` as well as an argument to a method requiring a CSS selector.

1. Notice the messages passed to the assertions.  These should be an affirmative statement describing the correct result.  You should include these as assert arguments, rather than as code comments in order to make your tests more readable.

        $this->assertFalse(
          $el[$widget]->isVisible(),
          "The search modal is hidden by default."
        );

1. The use of `waitFor` is only necessary if you have to wait for some JS to execute, or to wait for an AJAX load.

1. Lastly notice the use of `::readPage`, it must be called because the session has changed pages, and we want to make assertions on the newest page.

## The Entire Test

    <?php
    
    namespace Drupal\Tests\my_project;
    
    use AKlump\DrupalTest\EndToEndTestCase;
    
    /**
     * Tests the Story resource endpoint.
     */
    class SearchEndToEndTest extends EndToEndTestCase {
    
      public function testSearchLinkInHeaderSearchesByTextAndReturnsResults() {
        $this->loadPageByUrl('/');
    
        $el = $this->getDomElements([
          $widget = '.search-widget',
          $link = '.page-header__search a',
          $input = '.t-text-search__input',
          $submit = '.t-text-search__submit',
        ]);
    
        $this->assertFalse(
          $el[$widget]->isVisible(),
          "The search modal is hidden by default."
        );
    
        $el[$link]->click();
    
        $this->assertTrue(
          $el[$widget]->isVisible(),
          "The search modal is revealed after clicking link."
        );
    
        $this->assertFalse(
          $el[$submit]->isVisible(),
          "The text search submit button is hidden by default."
        );
        $el[$input]->setValue('tree');
    
        $this->waitFor(function () use ($el) {
          return $el[$submit]->isVisible();
        },
          'Submit button is made visible after entering a search phrase.'
        );
        $el[$submit]->click();
    
        $this->readPage()
          ->assertPageContains('Planting a Tree of Peace', 'Search term yielded expected results.')
          ->assertPageContains('The Axis and the Sycamore');
      }
    
    }
