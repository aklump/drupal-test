# Extra Fixtures

`\AKlump\DrupalTest\BrowserTestCase` adds two new fixtures where `$this` is available, yet they are only fired once each per test suite.

    ::onBeforeFirstTest
    ::onAfterLastTest 
