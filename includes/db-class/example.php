<?php
  require_once('DB.php');

  DB::initErrorHandler(
    [
      0 => 'success',
      1 => 'Sorry, the connection to the database is failed!',
      2 => 'Sorry, we are currently not able to receive data from the database!',
      3 => 'Sorry, we are currently not able to insert your data to the database!',
      4 => 'Sorry, we are currently not able to delete your data from the database!',
      5 => 'Sorry, we are currently not able to update your data in the database!',
    ]
  );

  $db = new DB('db-class-example', 'root', '');

  if (!$db->connected()) {
    if ($db->error()) {
      echo $db->getError()['msg'];
    }
    die();
  }

  echo 'You are successfully connected to the database!<br><br>';

  for ($i=1; $i <= 10; $i++) {
    $inserted[] = $db->insert("INSERT INTO users (id, username, password) VALUES (:id, :username, :password)", [ 'id' => $i, 'username' => 'test' . $i, 'password' => 'hello' . $i ]);
  }

  $data_inserted = 0;

  foreach ($inserted as $value) {
    if ($value === true) {
      $data_inserted++;
    }
  }

  if ($data_inserted == 10) {
    echo 'Everything has been successfully inserted to the database.<br><br>';
  } else {
    echo 'Some errors are occured when trying to insert data to the database.<br><br>';
  }

  $data = $db->select("SELECT * FROM users");

  if (!empty($data)) {
    echo 'Your data:<br><br>';
    echo '<pre>', print_r($data, true), '</pre>';
  } else {
    echo 'An error is occured when trying to get your data from the database or there is no data.<br><br>';
  }

  $deleted = $db->delete("DELETE FROM users");

  if ($deleted === true) {
    echo 'Your data has been successfully deleted from the database.<br><br>';
  } else {
    echo 'Some errors are occured when trying to delete the data from the database.<br><br>';
  }
?>