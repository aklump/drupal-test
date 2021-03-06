> There is some overlap in assertions between methods of `ClientTestCase` and methods on Mink's `WebAssert` class, but the former always uses css selectors, so it's a little less verbose.

## Working with `DocumentElement` objects

Learn [more here](http://mink.behat.org/en/latest/guides/traversing-pages.html#documentelement-and-nodeelement).

{% include('_DocumentElement.md') %}

## Working with `NodeElement` objects

Learn [more here](http://mink.behat.org/en/latest/guides/traversing-pages.html#documentelement-and-nodeelement).

    <?php
    
    class Test {
    
      public function testExample() {
    
        // By using getDomElements we ensure only one of .t-link.
        $el = $this->loadPageByUrl('/node/9750')
          ->getDomElements([
            '.t-link',
          ]);
    
        // $el['.t-link'] is an instance of NodeElement.
        $el['.t-link']->click();
    
        // Altenatively you could do this.
        $this->el('.t-link')->click()
          }
    
    }

{% include('_NodeElement.md') %}

## Making Assertions with Mink

Load a page and then make assertions using Mink's _WebAssert_ class.
 
    <?php
    
    class Test {
    
      public function testExample() {
        $this->loadPageByUrl('/node/9750')
          ->assert()
          ->elementTextContains('css', '#button', 'English');
        
        $this->assert()
          ->elementTextContains('css', '#button-2', 'Spanish');
      }
      
    }

{% include('_WebAssert.md') %}
