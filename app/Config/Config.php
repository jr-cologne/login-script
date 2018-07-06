<?php

namespace LoginScript\Config;

use LoginScript\Config\Exception\ConfigException;

class Config {

  protected static $config = [];

  public static function set(array $config) {
    self::$config = $config;
  }

  public static function get(string $path, bool $throw_exception = true) {
    $config = self::$config ?: $GLOBALS['config'];

    if (!$config) {
      throw new ConfigException('No config specified');
    }

    foreach (explode('/', $path) as $node) {
      if (isset($config[$node])) {
        $config = $config[$node];
      } else {
        if ($throw_exception) {
          throw new ConfigException('Invalid/Unknown config');
        } else {
          return null;
        }
      }
    }

    return $config;
  }

}
