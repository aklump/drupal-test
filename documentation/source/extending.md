# Extending Classes

You may want to extend the classes for your Drupal website.

1. Place your extended classes in _src_ like so.

        .
        └── src
            └── module_name
                ├── ClientTestBase.php
                ├── EndToEndTestBase.php
                ├── KernelTestBase.php
                └── UnitTestBase.php
                
1. Add to the autoloader in _composer.json_.

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
