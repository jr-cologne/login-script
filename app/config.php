<?php

use LoginScript\Env\Env;

$db_url = Env::get('CLEARDB_DATABASE_URL');

if ($db_url) {
  $db_url = parse_url($db_url);
}

$GLOBALS['config'] = [
  'database' => [
    'type' => 'mysql',
    'name' => substr($db_url['path'], 1) ?: 'login-script',
    'host' => $db_url['host'] ?? '127.0.0.1',
    'user' => $db_url['user'] ?? 'root',
    'password' => $db_url['pass'] ?? 'root',
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
    'algorithm' => 'password_bcrypt',
    'options' => [
      'pepper' => Env::get('PEPPER') ?? 'S3Aze&H!qa8heXEka+UP',
      'cost' => 12
    ]
  ],
  'email' => [
    'token_length' => 32,
    'smtp_config_file' => Env::get('SMTP_CONFIG') ?? 'storage/mail/smtp/smtp-server-credentials.json',
    'url' => Env::get('EMAIL_URL') ?? 'http://localhost:8080/GitHub/login-script/verify.php',
    'from' => 'habichvergessen40@gmail.com',
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
      'config_file' => json_decode(Env::get('GOOGLE_CONFIG'), true) ?? 'storage/social_auth/google/client_secret_374519720876-f0vvtnsi6prh6oepehtj9e2vgif8u2fd.apps.googleusercontent.com.json',
      'scopes' => 'email',
      'redirect_uri' => [
        'login' => Env::get('GOOGLE_REDIRECT_URI_LOGIN') ?? 'http://localhost:8080/GitHub/login-script/google_login.php',
        'register' => Env::get('GOOGLE_REDIRECT_URI_REGISTER') ?? 'http://localhost:8080/GitHub/login-script/google_register.php'
      ],
      'registration' => [
        'username_length' => 8,
        'password_length' => 16
      ]
    ],
    'twitter' => [
      'config_file' => Env::get('TWITTER_CONFIG') ?? 'storage/social_auth/twitter/twitter-api-credentials.json',
      'redirect_uri' => [
        'login' => Env::get('TWITTER_REDIRECT_URI_LOGIN') ?? 'http://localhost:8080/GitHub/login-script/twitter_login.php',
        'register' => Env::get('TWITTER_REDIRECT_URI_REGISTER') ?? 'http://localhost:8080/GitHub/login-script/twitter_register.php'
      ],
      'registration' => [
        'username_length' => 8,
        'password_length' => 16
      ]
    ]
  ],
  'error_handling' => [
    'bugsnag' => [
      'config_file' => Env::get('BUGSNAG_CONFIG') ?? 'storage/error_handling/bugsnag/bugsnag-api-credentials.json',
    ]
  ],
  'dependencies' => [
    'db' => 'JRCologne\Utils\Database\DB',
    'swift_mailer' => 'Swift_Mailer',
  ]
];