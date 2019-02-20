# Extending Classes

You may want to extend the classes for your Drupal website.  For example you may want to add a method that can be shared by all end to end tests, client, etc.

1. Place your extended classes in the _src_ directory of this project like so. Place it in a folder that is namespaced with a logical name related to your project.

        .
        └── src
            ├── DrupalTest
            │   ├── ...
            └── module_name
                ├── ClientTestBase.php
                ├── EndToEndTestBase.php
                ├── KernelTestBase.php
                └── UnitTestBase.php
1. Make sure your classes do extend the parent:

        <?php
                
        namespace Drupal\Tests\module_name;
        
        use \AKlump\DrupalTest\ClientTestBase as Parent;
        
        abstract class ClientTest extends Parent { 
          ... 
        
1. Add your namespace to the autoloader in _composer.json_ so your extended classes can be located. 

        {
            "autoload": {
                "psr-4": {
                    "AKlump\\": "src",
                    "Drupal\\Tests\\module_name\\": "src/module_name"
                }
            }
        }
1. `composer dumpautoload`
1. Now create your test classes using your extended base class instead, e.g.,

        <?php
        
        namespace Drupal\Tests\module_name\Metrics;
        
        use Drupal\Tests\module_name\ClientTestBase;
        
        /**
         * Client coverage for Curriculum.
         *
         * @group module_name
         * @SuppressWarnings(PHPMD.StaticAccess)
         * @SuppressWarnings(PHPMD.TooManyPublicMethods)
         */
        class CurriculumClientTest extends ClientTestBase { 
          ... 
