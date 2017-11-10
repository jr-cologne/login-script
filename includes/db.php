<?php
  use JRCologne\Utils\Database\DB;
  use JRCologne\Utils\Database\QueryBuilder;

	$db = new DB(new QueryBuilder);

  $db->connect(DB_TYPE . ':dbname=' . DB_NAME . ';host=' . DB_HOST . ';charset=utf8', DB_USER, DB_PASSWORD);
?>
