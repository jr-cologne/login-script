<?php
  require_once('includes/google/GoogleAuth.php');

  // Accept clock skew between your server and the Google server of 10 seconds
  use \Firebase\JWT\JWT;
  JWT::$leeway = 10;

  $google_client = new Google_Client;
  $google_auth = new GoogleAuth($google_client);
?>