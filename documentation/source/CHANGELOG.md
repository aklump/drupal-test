## 0.3.0

* BREAKING CHANGE
* The assert()->* are no longer chainable.

Before:

    $el = $this->assert()
      ->pageTextContains('Thank you for joining')
      ->getDomElements([
        '.t-educator-status__false',
        '.t-educator-status__true',
      ]);
    
After, in 0.2.6 onward

    $this->assert()->pageTextContains('Thank you for joining');
    $el = $this->getDomElements([
      '.t-educator-status__false',
      '.t-educator-status__true',
    ]);    
  
## 0.2.5

* You should manually update _composer.json_ with the following `require`:

      "aklump/manual-test": "^1.2.1",

## 0.2.0

* `TestClass::$schema` has been replaced with `TestClass::getSchema()`.
* You must replace all usages of the class property with a class method.
