<?php
  require_once('includes/google/GoogleAuth.php');

  $google_client = new Google_Client;

  if (!empty($auth_type)) {
    $google_auth = new GoogleAuth($google_client, $auth_type);
  } else {
    $google_auth = new GoogleAuth($google_client);
  }
?>