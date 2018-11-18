# Module Test Setup

Each module or theme defines it's tests relative to it's own root directory.  Following this convention will allow the test runner to discover these tests.  e.g.,

    .
    └── tests
        └── src
            ├── Client
            │   ├── Service
            │   │   └── EarthriseServiceClientTest.php
            ├── Kernel
            │   ├── Service
            │   │   ├── BreakpointServiceKernelTest.php
            │   └── TransformKernelTest.php
            ├── TestBase.php
            └── Unit
                ├── Service
                │   └── EarthriseServiceUnitTest.php
                └── TransformUnitTest.php


## Must Test Classes Test a Single Class?

Unit and Kernel tests do not have to test a single class, for example if you are writing a test to cover theme functions.  In order to make this happen you have to do the following in your test class:

        class Gop5ThemeKernelTest extends KernelTestBase {
        
          protected $schema = [
          
            // By setting this to false, we indicate we are not testing a class.
            'classToBeTested' => FALSE,
          ];
          
        ...
