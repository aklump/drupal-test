# Code Coverage Reports

1. Add something like the following to the runner's _phpunit.xml_. [more info](https://phpunit.de/manual/6.5/en/appendixes.configuration.html#appendixes.configuration.whitelisting-files)

        <filter>
            <whitelist processUncoveredFilesFromWhitelist="true">
                <directory suffix=".php">../web/modules/custom/loft_core</directory>
                <exclude>
                    <file>../web/modules/custom/loft_core/src/StaticContentStreamWrapper.php</file>
                </exclude>
                <!-- By definition test classes have no tests. -->
                <exclude>
                  <directory suffix="Test.php">./</directory>
                  <directory suffix="TestBase.php">./</directory>
                </exclude>
            </whitelist>
        </filter>

1. Use the CLI flag `--coverage-html <directory>`

## PHPStorm Configuration

    --testsuite Unit --filter LoftCoreUnitTest  --coverage-html coverage


  <filter>
    <whitelist>
      <directory>./includes</directory>
      <directory>./lib</directory>
      <directory>./modules</directory>
      <directory>../modules</directory>
      <directory>../sites</directory>

     </whitelist>
  </filter>
