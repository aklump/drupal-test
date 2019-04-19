# Usage Without Drupal

Can I use this on another non-Drupal project?

Yes, you can.  Only the Kernel tests are intimately connected to Drupal, therefor the Client, End to End and Manual tests work just fine on other projects.  The Unit test runner can also be configured to work fine on non-Drupal projects.

## Setup Instructions.

- Duplicate _drupal_test_bootstrap.php_ as _test_bootstrap.php_.
- Remove any lines that refer to "Drupal" things from _test_bootstrap.php_.
- Change the bootstrap file in _phpunit.xml_ to `bootstrap="./test_bootstrap.php"`.
- Update the paths for `testsuites` as appropriate to your project in _phpunit.xml_, something like:

        <testsuites>
            <testsuite name="Client">
                <directory>./src/Client</directory>
            </testsuite>
            <testsuite name="EndToEnd">
                <directory>./src/EndToEnd</directory>
            </testsuite>
            <!--Unit tests must come before all Kernel tests.-->
            <testsuite name="Unit">
                <directory>./src/Unit</directory>
            </testsuite>
            <testsuite name="Kernel">
                <directory>./src/Kernel</directory>
            </testsuite>
        </testsuites>
