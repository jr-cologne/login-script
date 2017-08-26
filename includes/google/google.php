<?php
  require_once('includes/google/GoogleAuth.php');

  // Accept clock skew between your server and the Google server of 10 seconds
  use \Firebase\JWT\JWT;
  JWT::$leeway = 10;

  $google_client = new Google_Client;

  if (!empty($auth_type)) {
    $google_auth = new GoogleAuth($google_client, $auth_type);
  } else {
    $google_auth = new GoogleAuth($google_client);
  }
?>