<?php
	require_once('config.php');

	try {
		$pdo = new PDO(DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO_ERROR_MODE);
	} catch (PDOException $e) {
		$pdo = false;
	}
?>
