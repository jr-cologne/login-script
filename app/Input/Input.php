<?php

namespace LoginScript\Input;

class Input {

  public static function get(string $item, string $type = 'post') : string {
    if (!empty($_POST[$item]) && $type == 'post') {
      return self::escape($_POST[$item]);
    } else if (!empty($_GET[$item]) && $type == 'get') {
      return self::escape($_GET[$item]);
    }

    return '';
  }

  public static function escapeData(array $data, array $exclude = []) : array {
    foreach ($data as $key => $value) {
      if ( !empty($exclude) && in_array($key, $exclude) ) {
        continue;
      }

      $data[$key] = self::escape($value);
    }

    return $data;
  }

  protected static function escape(string $value) : string {
    return htmlspecialchars(stripslashes(trim($value)), ENT_QUOTES, 'UTF-8');
  }

}
