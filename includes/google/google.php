<?php
  require_once('vendor/autoload.php');
  require_once('includes/google/GoogleAuth.php');

  $google_client = new Google_Client;
  $google_auth = new GoogleAuth($google_client);
?>