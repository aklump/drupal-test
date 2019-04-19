---
id: manual
---
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
              <testsuite name="Custom">
                  <directory>../web/sites/all/modules/custom/*/tests/src/Manual</directory>
                  <directory>../web/sites/all/modules/custom/*/tests/src/Manual/*</directory>
              </testsuite>
          </manualtests>
        </phpunit>

1. Here is an example for a Drupal 8 site.

        <phpunit ...>
          ...
          <manualtests>
              <title>www.mysite.org</title>
              <tester>Aaron Klump</tester>
              <output>../private/default/mysite-manual-tests.pdf</output>
              <testsuite name="Contrib">
                  <directory>../web/modules/contrib/*/tests/src/Manual</directory>
                  <directory>../web/modules/contrib/*/tests/src/Manual/*</directory>
              </testsuite>
              <testsuite name="Custom">
                  <directory>../web/modules/custom/*/tests/src/Manual</directory>
                  <directory>../web/modules/custom/*/tests/src/Manual/*</directory>
              </testsuite>              
          </manualtests>
        </phpunit>
           
### Using Website Perms

Add the following to the perms config:

    executable_paths:
      - tests/bin/*.sh
      - tests/vendor/aklump/manual-test/generate
                
## Generate tests

To create the PDF file for manual test running... See the [documentation](https://github.com/aklump/manual-test) for more info.
  
    cd tests
    export TEST_BASE_URL="http://www.mysite.com"; ./vendor/bin/generate --configuration=phpunit.xml --output=mysite-manual-tests.com.pdf --tester="Aaron Klump"

### Hint, create a shortcut file, something like: _manual.sh_

    #!/usr/bin/env bash
    source="${BASH_SOURCE[0]}"
    while [ -h "$source" ]; do # resolve $source until the file is no longer a symlink
      dir="$( cd -P "$( dirname "$source" )" && pwd )"
      source="$(readlink "$source")"
      [[ $source != /* ]] && source="$dir/$source" # if $source was a relative symlink, we need to resolve it relative to the path where the symlink file was located
    done
    root="$( cd -P "$( dirname "$source" )" && pwd )"
    cd "$root/.."
    export TEST_BASE_URL="http://www.mysite.com"; ./vendor/bin/generate --configuration=phpunit.xml --output=mysite-manual-tests.loft.pdf --tester="Aaron Klump" "$@"
