# Add Manual Tests

This project uses [aklump/manual-test](https://github.com/aklump/manual-test) for manual tests.  This page shows how to integrate manual tests into your Drupal project.

## Configuration

1. Add configuration like the following (replacing tokens) to _phpunit.xml_:

        <phpunit ...>
          ...
          <manualtests>
              <title>{{ website or domain}}</title>
              <tester>{{ default tester name }}</tester>
              <output>{{ path to pdf output file }}</output>
              <testsuite name="Manual">
                  <directory>../web/sites/all/modules/custom/*/tests/src/Manual/*</directory>
              </testsuite>
          </manualtests>
        </phpunit>
    
## Generate tests

To create the PDF file for manual test running... See the [documentation](https://github.com/aklump/manual-test) for more info.
  
    cd tests
    export CLIENT_TEST_BASE_URL="http://www.mysite.com"; ./vendor/bin/generate --configuration=phpunit.xml
