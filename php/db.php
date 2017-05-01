<?php
	require_once('config.php');
  require_once('includes/db-class/DB.php');

  DB::initErrorHandler([], 'development');

	$db = new DB(DB_NAME, DB_USER, DB_PASSWORD, DB_TYPE, DB_HOST, PDO_ERROR_MODE);
?>
