---
id: autoload
---
# Autoloading, Includes and Dependencies

## Autoloading

Without a class autoload map for dependency classes, your tests will fail.  At first glance you may want to update the `autoload` section of _tests/composer.json_, but that should not be done, as that file gets overwritten on update.  Instead you will edit _drupal_test_config.yml_.

Here is an example for when a unit test uses `\Drupal\node\NodeInterface`.  You can see that we are mapping the PSR-4 namespace `Drupal\node` to _../web/core/modules/node/src_.  This should look familiar if you are used to adding PSR-4 [autoloading to _composer.json_ files](https://getcomposer.org/doc/04-schema.md#autoload).

Note: the configuration seen below will apply to all tests, so you don't need to do anything else for another test or test subject that needs to have access to `\Drupal\node\NodeInterface`.

    autoload_psr4:
      Drupal\node\: ../web/core/modules/node/src

By manually configuring only what needs to be autoloaded for our particular tests, we are able to keep unit testing very fast, without the need to scan for files and dynamically create an autoload map. 

Changes to the `autoload_*` configuration does not require `composer update --lock` to be called, but you may need to run `composer dump`.

You may skip `autoload_psr4` if you are testing a module that handles autoloading via it's own _composer.json_ file, in which case you want to use _merge_composer.json_, instead.

### Autoloading Keys

The following keys correspond to [Composer autoloading](https://getcomposer.org/doc/04-schema.md#autoload) and should be used in the same way as described for Composer.

* `autoload_psr4`
* `autoload_psr0`
* `autoload_classmap` 
* `autoload_files`

## Merging _composer.json_ Files

When testing modules with their own _composer.json_ files, you probably need to register those module _composer.json_ files in _drupal_test_config.yml_.  This will inform the test runner to pull in those dependencies so they are available during testing, if your tests require that.  Here is an example of what that could look like.

    merge_composer.json:
      - ../web/modules/custom/alter_partials/composer.json
      - ../web/modules/custom/render_patterns/composer.json
      - ../web/modules/custom/loft_core/composer.json

Whenever you alter this section of _drupal_test_config.yml_, you must call `composer update --lock` from the Drupal test root directory.  This is what pulls in the dependencies.  Under the hood, this feature uses the [Composer Merge Plugin](https://github.com/wikimedia/composer-merge-plugin).

##:module Module Setup

Drupal Test uses Composer for autoloading when Unit testing modules.

1. In your module's directory, create _composer.json_ and add it's path to `merge_composer.json`, in _drupal_test_config.yml_.
1. Also in the module's _composer.json_, use `autoload-dev` to create a namespace map for your module so it's classes can be autoloaded.

        {
            "autoload-dev": {
                "psr-4": {
                    "Drupal\\my_module\\": "src/"
                }
            }
        }

1. If any tests or test subjects rely on Drupal Core classes then map those namespaces directly in _drupal_test_config.yml_.  Do not add anything to `autoload-dev`, that is outside of your module's directory.
1. If you are writing tests that cover functions, then add the file defining those functions in your module's _composer.json_, e.g.,

        {
            "autoload-dev": {
                ...
                "files": [
                    "my_module.module"
                ]
            }
        }        

1. In the Drupal Test _phpunit.xml_, make sure your unit tests are discoverable, e.g.,

        <phpunit ...>
            <testsuites>
                <testsuite name="Unit">
                    <directory>../web/modules/custom/*/tests/src/Unit</directory>
                </testsuite>
            </testsuites>
        </phpunit>      
