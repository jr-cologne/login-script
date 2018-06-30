<?php

$GLOBALS['config'] = [
  'database' => [
    'type' => 'mysql',
    'name' => 'login-script',
    'host' => '127.0.0.1',
    'user' => 'root',
    'password' => 'root',
    'table' => 'users'
  ],
  'csrf' => [
    'session_name' => 'csrf_token',
    'token_length' => 32
  ],
  'input' => [
    'do_not_escape' => [
      'password',
      'old_password',
      'new_password',
      'google_init_password',
      'twitter_init_password'
    ]
  ],
  'password' => [
    'algorithm' => 'password_argon2i',
    'options' => [
      'pepper' => 'S3Aze&H!qa8heXEka+UP',
      'memory_cost' => 1<<17,
      'time_cost' => 4,
      'threads' => 2,
    ]
  ],
  'email' => [
    'token_length' => 32,
    'url' => 'http://localhost:8080/verify.php',
    'from' => 'kontakt@jr-cologne.de',
    'subject' => 'Welcome to the restricted area - Please verify your email!',
    'message' => 'Hello :username!' . PHP_EOL . PHP_EOL .
                 'Welcome to the restricted area! You have to verify your email before you can access the restricted area.' . PHP_EOL .
                 'Please click on the following link to verify your email: :url' . PHP_EOL . PHP_EOL .
                 'Thanks!'
  ],
  'errors' => [
    'session_name' => 'errors'
  ],
  'login' => [
    'session_name' => 'logged_in',
  ],
  'social_auth' => [
    'google' => [
      'config_file' => 'storage/social_auth/google/client_secret_374519720876-f0vvtnsi6prh6oepehtj9e2vgif8u2fd.apps.googleusercontent.com.json',
      'scopes' => 'email',
      'redirect_uri' => [
        'login' => 'http://localhost:8080/GitHub/login-script/google_login.php',
        'register' => 'http://localhost:8080/GitHub/login-script/google_register.php'
      ],
      'registration' => [
        'username_length' => 8,
        'password_length' => 16
      ]
    ],
    'twitter' => [
      'config_file' => 'storage/social_auth/twitter/twitter-api-credentials.json',
      'redirect_uri' => [
        'login' => 'http://localhost:8080/GitHub/login-script/twitter_login.php',
        'register' => 'http://localhost:8080/GitHub/login-script/twitter_register.php'
      ],
      'registration' => [
        'username_length' => 8,
        'password_length' => 16
      ]
    ]
  ],
  'dependencies' => [
    'db' => 'JRCologne\Utils\Database\DB'
  ],
];
