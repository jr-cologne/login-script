<?php

require_once 'includes/twitter/TwitterAuth.php';

use Codebird\Codebird;

if (!empty($auth_type)) {
  $twitter_auth = new TwitterAuth(Codebird::getInstance(), $auth_type);
} else {
  $twitter_auth = new TwitterAuth(Codebird::getInstance());
}
