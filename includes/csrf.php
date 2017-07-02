<?php
  // csrf token set?
  if (empty($_SESSION['csrf_token'])) {
    // token not set, generate csrf token
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
  } else {
    // token set
    if (!empty($_POST)) {
      // check csrf token against csrf token from post request
      if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF Token!');
      }
    }
  }
?>