<?php

namespace LoginScript\Hash;

use LoginScript\{
  Hash\Exception\HashException,
  Config\Config
};

class Hash {

  public static function get(string $password, string $algorithm = 'default', array $options = []) : string {
    if ($algorithm == 'default') {
      $algorithm = Config::get('password/algorithm');
    }

    if (empty($options)) {
      $options = Config::get('password/options');
    }

    return self::generateHash($password, $algorithm, $options);
  }

  public static function check(string $password, string $hash, string $pepper = '') : bool {
    if (empty($pepper)) {
      $pepper = Config::get('password/options')['pepper'] ?? '';
    }

    return self::verifyPassword($password, $hash, $pepper);
  }

  protected static function generateHash(string $password, string $algorithm, array $options) : string {
    $algorithm = strtolower($algorithm);

    $pepper = $options['pepper'] ?? '';

    switch ($algorithm) {
      case 'password_default':
        return password_hash($password . $pepper, PASSWORD_DEFAULT, $options);
        break;

      case 'password_bcrypt':
        return password_hash($password . $pepper, PASSWORD_BCRYPT, $options);
        break;

      case 'password_argon2i':
        return password_hash($password . $pepper, PASSWORD_ARGON2I, $options);
        break;

      default:
        throw new HashException('Invalid/Unknown hashing algorithm');
        break;
    }
  }

  protected static function verifyPassword(string $password, string $hash, string $pepper) : bool {
    return password_verify($password . $pepper, $hash);
  }

}
