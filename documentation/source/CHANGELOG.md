## 0.5.0

* Replace `AKlump\DrupalTest\UnitTestCase` with `AKlump\DrupalTest\Drupal7\UnitTestBase`; notice `Case` turns to `Base` to match with Drupal 8.
* Replace `AKlump\DrupalTest\KernelTestCase` with `AKlump\DrupalTest\Drupal7\KernelTestBase`

## 0.4.0

* Added _drupal_test_config.yml_ to be used instead of modifying _composer.json_.  _composer.json_ should no longer be modified as it will now be overwritten during updates.
* You must migrate your autoloading and merge-plugin configuration to _drupal_test_config.yml_.  A backup file _composer--original.json_ should have been created on update.  Migrate your values and then delete _composer--original.json_.
* Do not make changes to _composer.json_ from now on.
* Run `./bin/update.sh` twice to ensure a proper update.

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
