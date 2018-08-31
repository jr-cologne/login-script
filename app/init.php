<?php

require_once 'vendor/autoload.php';

use LoginScript\{
  Core\App as LoginScript,
  Env\Env,
  ErrorHandling\ErrorHandler
};

use JRCologne\Utils\Database\{ DB, QueryBuilder };

Env::loadEnvVars();

require_once 'config.php';

// init error handling
$error_handler = new ErrorHandler;

// set dependencies and configs
$app = new LoginScript([
  'db' => new DB(new QueryBuilder)
], $GLOBALS['config']);

unset($GLOBALS['config']);

// boot app
$app->boot();
