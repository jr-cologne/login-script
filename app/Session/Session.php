<?php

namespace LoginScript\Session;

class Session {

  public static function init() : bool {
    session_start([
      'cookie_httponly' => true
    ]);
    session_regenerate_id(true);

    return (session_status() == PHP_SESSION_ACTIVE);
  }

  public static function exists(string $name) : bool {
    return isset($_SESSION[$name]);
  }

  public static function put(string $name, $value) : bool {
    $_SESSION[$name] = $value;
    
    if (self::exists($name)) {
      return true;
    }

    return false;
  }

  public static function get(string $name) {
    if (self::exists($name)) {
      return $_SESSION[$name];
    }

    return null;
  }

  public static function delete(string $name) {
    if (self::exists($name)) {
      unset($_SESSION[$name]);
    }

    return !self::exists($name);
  }

}
