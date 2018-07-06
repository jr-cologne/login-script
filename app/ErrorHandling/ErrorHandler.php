<?php

namespace LoginScript\ErrorHandling;

use LoginScript\{
  ErrorHandling\Exception\ErrorHandlerException,
  Config\Config
};

use Bugsnag\{
  Client as BugsnagClient,
  Handler as BugsnagHandler
};

class ErrorHandler {

  public function __construct() {
    $bugsnag = BugsnagClient::make($this->getApiKey());
    BugsnagHandler::register($bugsnag);
  }

  protected function getApiKey() : string {
    $config = Config::get('error_handling/bugsnag/config_file');

    if (is_string($config) && !file_exists($config)) {
      $api_key = json_decode($config, true);
    } else {
      $api_key = json_decode(file_get_contents($config, true), true);
    }

    $api_key = $api_key['api_key'] ?? null;

    if (!$api_key) {
      throw new ErrorHandlerException("Could not get Bugsnag API Key");
    }

    return $api_key;
  }

}
