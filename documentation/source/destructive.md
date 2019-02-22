# Destructive Tests

**Prevent certain test(s) from running against production.**

In some cases, certain tests should not be allowed to run against certain base URLs.  For example, you should not let end to end tests that modify the database run against production.  These types of tests are called here as _destructive_.

## Prevent Entire Test Class

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

## Prevent Single Test Methods
      
* You may mark individual test methods `@destructive`

        /**
         * @destructive
         */
        public function testCanDeleteAUser() {
        ...

## Define Which URLs Are "Production"

* Anything marked with `@destructive` will be skipped unless `TEST_BASE_URL` is listed as shown next.
* Create the whitelist in _phpunit.xml_ like this:

        <phpunit ... >
            <allowDestructiveTests>
                <url>http://mysite.local</url>
                <url>https://stage.mysite.com</url>
            </allowDestructiveTests>
            ...

## How It Works

A test class must `use \AKlump\DrupalTest\Utilities\DestructiveTrait` if you want to use this convention.  The base classes: `\AKlump\DrupalTest\ClientTestBase` and `\AKlump\DrupalTest\EndToEndTestBase` already include this trait so you need only include the annotation as shown above.
