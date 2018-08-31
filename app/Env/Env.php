<?php

namespace LoginScript\Env;

use LoginScript\Env\Exception\EnvException;

class Env {

  protected static $throw_exception = false;

  public static function loadEnvVars(string $dotenv_file = '.env') {
    if (!file_exists($dotenv_file)) {
      throw new EnvException("The file ({$dotenv_file}) to load the environment variables from does not exist");
    }

    $dotenv = file($dotenv_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if (!$dotenv) {
      throw new EnvException("Failed to read environment variables file ({$dotenv_file})");
    }

    foreach ($dotenv as $setting) {
      if (!self::put($setting)) {
        throw new EnvException('Failed to load environment variables');
      }
    }
  }

  public static function put(string $setting) {
    return putenv($setting);
  }

  public static function get(string $varname, bool $throw_exception = false) {
    $env = getenv($varname);

    if (!$env && self::$throw_exception) {
      throw new EnvException('Invalid/Unknown environment variable');
    }

    return $env ?: null;
  }

}
