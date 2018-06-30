<?php

namespace LoginScript\CSRF;

use LoginScript\{
  Config\Config,
  Token\Token,
  Session\Session
};

class CSRF {

  public static function getToken() : string {
    return self::generateToken();
  }

  public static function checkToken(string $token) {
    $token_name = Config::get('csrf/session_name');

    if ( Session::exists($token_name) && hash_equals(Session::get($token_name), $token) ) {
      return true;
    }

    return false;
  }

  protected static function generateToken() : string {
    $token = Token::get(Config::get('csrf/token_length'));

    Session::put(Config::get('csrf/session_name'), $token);

    return $token;
  }

}
