<?php

namespace LoginScript\Env;

use LoginScript\Env\Exception\EnvException;

class Env {

  protected $throw_exception = false;

  public static function get(string $varname) : string {
    $env = getenv($varname);

    if (!$env && $this->throw_exception) {
      throw new EnvException('Invalid/Unknown environment variable');
    }

    return $env ?? '';
  }

  public static function getAll() : array {
    return getenv();
  }

}
