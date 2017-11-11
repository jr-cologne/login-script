<?php
  /***** Settings for Error Handling etc. *****/
  // deactivate notices
  error_reporting(E_ALL & ~E_NOTICE);
  // deactivate displaying of errors
  ini_set('display_errors', 'Off');
  // activate php error logging
  ini_set('log_errors', 'On');
  // log php errors there
  ini_set('error_log', '../login-script-php-errors.log');

  /***** Settings for checkForm() function *****/
  // defines the html code that is concatenated with the error message, which the function returns
  const ERR_HTML_START = '<p class="error-message"><strong>';
  const ERR_HTML_END = '</strong></p>';

  // defines the message that will be displayed in the error list
  const MSG_FOR_EMPTY_FIELDS = [
    'username' => 'Your Username',
    'email' => 'Your Email',
    'password' => 'Your Password'
  ];

  /***** Settings for DB *****/
  // Login Settings
  const DB_TYPE = 'mysql';
  const DB_HOST = 'localhost';
  const DB_NAME = 'login-script';
  const DB_TABLE = 'users';
  const DB_USER = 'root';
  const DB_PASSWORD = 'root';

  /***** Settings for password hashing *****/
  const PEPPER = 'S3Aze&H!qa8heXEka+UP';
  const PW_HASH_OPTIONS = [ 'cost' => 12 ];

  /***** Settings for Email Verification *****/
  const FROM = 'kontakt@jr-cologne.de';
  const VERIFY_EMAIL_SUBJECT = 'Restricted Area - Please verify your email!';
  const VERIFY_EMAIL_MSG =
    'Hello :username!' . PHP_EOL . PHP_EOL .
    'Welcome to the Restricted Area! You have to verify your email before you can access the Restricted Area.' . PHP_EOL .
    'Please click on the following link to verify your email: :url' . PHP_EOL . PHP_EOL .
    'Thanks!'
  ;

  /***** Settings for Google Auth *****/
  const AUTH_CONFIG_FILE = 'includes/google/client_secret_374519720876-f0vvtnsi6prh6oepehtj9e2vgif8u2fd.apps.googleusercontent.com.json';
  const REDIRECT_URI = 'http://localhost:8080/GitHub/login-script/login.php';
  const REDIRECT_URI_REGISTER = 'http://localhost:8080/GitHub/login-script/register.php';
  const SCOPES = 'email';

  /***** Settings for Session Security *****/
  ini_set('session.cookie_lifetime', 0);
  ini_set('session.use-cookies', 'On');
  ini_set('session.use_only_cookies', 'On');
  ini_set('session.use_strict_mode', 'On');
  ini_set('session.cookie_httponly', 'On');
  // only activate this if you are using https
  //ini_set('session.cookie_secure', 'On');
  ini_set('session.use_trans_sid', 'Off');
  ini_set('session.cache_limiter', 'nocache');
?>
