<?php

require_once 'vendor/autoload.php';

use LoginScript\Core\App as LoginScript;

use JRCologne\Utils\Database\{ DB, QueryBuilder };

// set dependencies and configs
$app = new LoginScript([
  'db' => new DB(new QueryBuilder)
], $GLOBALS['config']);

unset($GLOBALS['config']);

// boot app
$app->boot();
