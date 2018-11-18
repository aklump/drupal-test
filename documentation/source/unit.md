# Unit Tests        

* Test classnames should follow: _\*UnitTest_
* Unit tests do not have access to the Drupal bootstrap; these are very fast.  You should try to write unit tests whenever possible.  Only move to Kernel tests if unit tests are not appropriate.

* If a unit test requires a file that is not normally autoloaded, it should be added to the `autoload-dev` section of the module's composer file.

## A Note About Test Suite Order in XML

**You will probably not need to modify _phpunit.xml_. But if you do...**

Be very careful when modifying _phpunit.xml_, that you do not list even a single kernel test before any unit tests.  When any kernel test is run, Drupal will be bootstrapped which "pollutes" the global namespace with stuff that could bleed into your unit tests, giving them out-of-scope and misleading functions, classes, constants, etc.  In effect they could be acting like kernel tests, if you're not careful.

Notice how the unit tests come before the kernel tests in the code below; client tests must also precede the Kernerl tests.

    <testsuites>
        <testsuite name="Unit">
            <directory>../../web/sites/all/modules/custom/*/tests/src/Unit</directory>
        </testsuite>
        <testsuite name="Client">
            <directory>../../web/sites/all/modules/custom/*/tests/src/Client</directory>
        </testsuite>
        <testsuite name="Kernel">
            <directory>../../web/sites/all/modules/custom/*/tests/src/Kernel</directory>
        </testsuite>
    </testsuites>

To be sure, run the entire unit test suite standalone on occassion, e.g.,

    phpunit . --testsuite Unit

## Troubleshooting

### Fatal error: Class ... not found ...

