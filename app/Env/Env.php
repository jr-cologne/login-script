<?php

namespace LoginScript\Env;

use LoginScript\Env\Exception\EnvException;

class Env {

  protected static $throw_exception = false;

  public static function get(string $varname) {
    $env = getenv($varname);

    if (!$env && self::$throw_exception) {
      throw new EnvException('Invalid/Unknown environment variable');
    }

    return $env ?: null;
  }

  public static function getAll() : array {
    return getenv();
  }

}
