<?php

namespace AKlump\DrupalTest;

use Composer\Package\RootPackageInterface;
use Composer\Script\Event as ScriptEvent;
use Symfony\Component\Yaml\Yaml;
use Wikimedia\Composer\MergePlugin;

/**
 * Handles special config for Drupal Test.
 *
 * @link https://getcomposer.org/doc/articles/scripts.md#writing-custom-commands
 */
class Config {

  /**
   * Holds the loaded config array.
   *
   * @var array
   */
  protected static $config = [];

  /**
   * True once the config has been loaded.
   *
   * @var bool
   */
  protected static $configIsLoaded = FALSE;

  /**
   * Add our custom configuration.
   *
   * @param \Composer\Script\Event $event
   *   The update event.
   */
  public static function addConfig(ScriptEvent $event) {
    $package = $event->getComposer()->getPackage();
    $package->setAutoload(self::getMergedAutoload($package));
    $package->setExtra([
      'merge-plugin' => self::getMergedMergePlugin($package),
    ]);

    // We have to call the merge plugin manually here, otherwise the autoload
    // gets messed up.  And we loose autoload paths gleaned in the update
    // command.
    $plugins = $event->getComposer()->getPluginManager()->getPlugins();
    $merge_plugin = array_filter($plugins, function ($plugin) {
      return $plugin instanceof MergePlugin;
    });
    $merge_plugin = reset($merge_plugin);
    if (empty($merge_plugin)) {
      throw new \RuntimeException("Unable to call: MergePlugin::onInstallUpdateOrDump. The autoload files my be corrupt.");
    }
    if ($merge_plugin) {
      $merge_plugin->onInstallUpdateOrDump($event);
    }
  }

  /**
   * Return our autoload merged into existing.
   *
   * @param \Composer\Package\RootPackageInterface $package
   *   The package instance.
   *
   * @return array
   *   The autoload array ready for $package->setAutoload().
   */
  public static function getMergedAutoload(RootPackageInterface $package) {
    $autoload = $package->getAutoload();
    $config_map = [
      'autoload_psr4' => 'psr-4',
      'autoload_psr0' => 'psr-0',
      'autoload_classmap' => 'classmap',
      'autoload_files' => 'files',
    ];
    $config = self::getConfig();
    foreach ($config_map as $config_key => $key) {
      if (!isset($config[$config_key])) {
        continue;
      }
      foreach ((array) $config[$config_key] as $prefix => $paths) {
        if (!is_array($paths)) {
          $paths = [$paths];
        }

        // If existing we need to make sure an array, add ours and make unique.
        if (!isset($autoload[$key][$prefix])) {
          $autoload[$key][$prefix] = [];
        }
        elseif (!is_array($autoload[$key][$prefix])) {
          $autoload[$key][$prefix] = [$autoload[$key][$prefix]];
        }

        $autoload[$key][$prefix] = array_unique(array_merge($autoload[$key][$prefix], $paths));
        if (count($autoload[$key][$prefix]) === 1) {
          $autoload[$key][$prefix] = reset($autoload[$key][$prefix]);
        }

      }
    }

    return $autoload;
  }

  /**
   * Return our merge-plugin merged into existing.
   *
   * @param \Composer\Package\RootPackageInterface $package
   *   The package instance.
   *
   * @return array
   *   The merge-plugin array ready for $package->setExtra().
   */
  public static function getMergedMergePlugin(RootPackageInterface $package) {
    $extra = $package->getExtra();
    $extra += [
      'merge-plugin' => [],
    ];
    $merge = $extra['merge-plugin'];
    $merge += [
      'require' => [],
    ];

    $config = self::getConfig();
    $config += [
      'merge_composer.json' => [],
    ];

    return ['require' => array_unique(array_merge($merge['require'], (array) $config['merge_composer.json']))];
  }

  /**
   * Retrieve the Drupal Test configuration.
   *
   * @return array
   *   The Drupal Test configuration.
   */
  public static function getConfig() {
    if (!self::$configIsLoaded) {
      self::$configIsLoaded = TRUE;
      $config_filepath = getcwd() . '/drupal_test_config.yml';
      self::$config = [];
      if (file_exists($config_filepath)) {
        self::$config = Yaml::parse(file_get_contents($config_filepath));
      }
    }

    return self::$config;
  }

}
