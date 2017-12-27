<?php

// require all files
require_once('includes/init.php');
require_once('includes/twitter/twitter.php');

if ($_GET['auth_type'] == 'register') {
  $_SESSION['response'] = twitter_register($_GET['oauth_verifier']);
  header('Location: register.php');
} else {
  $_SESSION['response'] = twitter_login($_GET['oauth_verifier']);
  header('Location: login.php');
}
