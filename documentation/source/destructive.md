# Destructive Tests

Use `\AKlump\DrupalTest\Utilities\DestructiveTrait` if you want to use a convention whereby certain tests will only be allowed to run against certain base URLs.  For example, you should not let end to end tests that modify the database run against production.  Such tests should be marked as destructive.

* You may mark entire classes `@destructive`, in which case all tests in the class will be skipped if the `TEST_BASE_URL` is not in the defined list.

      <?php
      
      namespace Drupal\Tests\my_module;
      
      /**
       * Ensures we can create a new user via registration modal.
       *
       * @destructive
       */
      class ModalRegistrationEndToEndTest extends EndToEndTestBase {
      ...
      
* You may mark individual test methods `@destructive`

      /**
       * @destructive
       */
      public function testCanDeleteAUser() {
      ...
      
* You must define which `TEST_BASE_URL` values can run `@destructive` items using _phpunit.xml_.

      <phpunit ... >
          <allowDestructiveTests>
              <url>http://mysite.local</url>
              <url>https://stage.mysite.com</url>
          </allowDestructiveTests>
          ...
