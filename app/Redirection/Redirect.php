<?php

namespace LoginScript\Redirection;

class Redirect {

  public static function to(string $url) {
    $header = 'Location: ' . self::sanitizeUrl($url);

    header($header);
    exit();
  }

  protected static function sanitizeUrl(string $url) : string {
    $url = filter_var($url, FILTER_SANITIZE_URL);

    return $url;
  }

}
