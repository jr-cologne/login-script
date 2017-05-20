<?php
  require_once('vendor/autoload.php');
  require_once('php/GoogleAuth.php');

  $google_client = new Google_Client;
  $google_auth = new GoogleAuth($google_client);
?>