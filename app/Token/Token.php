<?php

namespace LoginScript\Token;

class Token {

  public static function get(int $length = 32) : string {
    return self::generate($length);
  }

  protected static function generate(int $length) : string {
    return bin2hex(random_bytes( (int) ($length / 2) ));
  }

}
