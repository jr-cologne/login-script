<?php

use LoginScript\Env\Env;

$GLOBALS['config'] = [
  'database' => [
    'type' => Env::get('DATABASE_TYPE') ?? 'mysql',
    'name' => Env::get('DATABASE_NAME') ?? 'login-script',
    'host' => Env::get('DATABASE_HOST') ?? '127.0.0.1',
    'user' => Env::get('DATABASE_USER'),
    'password' => Env::get('DATABASE_PASSWORD'),
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
      'cost' => Env::get('COST') ?? 10
    ]
  ],
  'mail' => [
    'smtp_config_file' => Env::get('SMTP_CONFIG') ?? Env::get('SMTP_CONFIG_FILE')
  ],
  'verification_mail' => [
    'token_length' => 32,
    'url' => Env::get('EMAIL_URL'),
    'from' => Env::get('EMAIL_FROM'),
    'subject' => 'Welcome to the restricted area - Please verify your email!',
    'message' => 'Hello :username!' . PHP_EOL . PHP_EOL .
                 'Welcome to the restricted area! You have to verify your email before you can access the restricted area.' . PHP_EOL . PHP_EOL .
                 'Please click on the following link to verify your email: :url' . PHP_EOL . PHP_EOL .
                 'Thanks!'
  ],
  'password_reset_mail' => [
    'password_length' => 16,
    'from' => Env::get('EMAIL_FROM'),
    'subject' => 'Restricted area - Your new password',
    'message' => 'Hello :username!' . PHP_EOL . PHP_EOL .
                 'Here is your new password: :password' . PHP_EOL . PHP_EOL .
                 'For security reasons, please make sure to change the password after you logged in.' . PHP_EOL . PHP_EOL .
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
      'config_file' => json_decode(Env::get('GOOGLE_CONFIG'), true) ?? Env::get('GOOGLE_CONFIG_FILE'),
      'scopes' => 'email',
      'redirect_uri' => [
        'login' => Env::get('GOOGLE_REDIRECT_URI_LOGIN'),
        'register' => Env::get('GOOGLE_REDIRECT_URI_REGISTER')
      ],
      'registration' => [
        'username_length' => 8,
        'password_length' => 16
      ]
    ],
    'twitter' => [
      'config_file' => Env::get('TWITTER_CONFIG') ?? Env::get('TWITTER_CONFIG_FILE'),
      'redirect_uri' => [
        'login' => Env::get('TWITTER_REDIRECT_URI_LOGIN'),
        'register' => Env::get('TWITTER_REDIRECT_URI_REGISTER')
      ],
      'registration' => [
        'username_length' => 8,
        'password_length' => 16
      ]
    ]
  ],
  'error_handling' => [
    'bugsnag' => [
      'config_file' => Env::get('BUGSNAG_CONFIG') ?? Env::get('BUGSNAG_CONFIG_FILE'),
    ]
  ],
  'dependencies' => [
    'db' => 'JRCologne\Utils\Database\DB',
    'swift_mailer' => 'Swift_Mailer',
  ]
];
