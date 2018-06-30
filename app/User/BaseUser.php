<?php

namespace LoginScript\User;

use LoginScript\{
  User\Exception\UserException,
  Config\Config,
  Hash\Hash,
  Token\Token,
  Mail\Mail,
  Mail\Message,
  Session\Session,
  Google\Auth\GoogleAuth,
  Twitter\Auth\TwitterAuth
};

abstract class BaseUser {

  protected $db;
  protected $table;

  protected $supported_user_data = [
    'username',
    'email',
    'password',
    'verified',
    'token'
  ];

  protected $supported_identifier_types = [
    'id',
    'username',
    'email'
  ];

  protected $user_id = null;

  public function __construct($db) {
    $this->db = $db;
    $this->table = Config::get('database/table');
  }

  public function isLoggedIn() : bool {
    return $this->isUserLoggedIn();
  }

  // Logout
  public function logout() : bool {
    return $this->logoutUser();
  }

  // Update
  public function update(array $new_user_data) {
    return $this->updateUser($new_user_data);
  }

  // Delete
  public function delete(string $password) {
    return $this->deleteUser($password);
  }

  // Verification
  public function sendVerificationMail(string $email, string $token = '') : bool {
    return $this->sendUserVerificationMail($email, $token);
  }

  public function isVerified(string $email) : bool {
    return $this->userVerified($email);
  }

  public function verify(string $email, string $token) : bool {
    return $this->verifyUser($email, $token);
  }

  public function updateToken(string $email, string $old_token, string $new_token) : bool {
    return $this->updateUserToken($email, $old_token, $new_token);
  }

  // Data
  public function getData() : array {
    if (!$this->user_id) {
      return [];
    }

    return $this->getUserData($this->user_id, 'id', [
      'id',
      'username',
      'email',
      'google_init_password',
      'twitter_init_password'
    ]);
  }

  // General
  public function exists($identifier, string $identifier_type = 'username') : bool {
    return $this->userExists($identifier, $identifier_type);
  }

  // Registration
  protected function createUser(array $user_data) : bool {
    foreach ($user_data as $key => $value) {
      if (!in_array($key, $this->supported_user_data)) {
        throw new UserException('Invalid/Unknown user data');
      }
    }

    return $this->db->table($this->table)->insert($user_data);
  }

  // Login
  protected function setUserLoggedIn(int $user_id) : bool {
    if (
      !Session::put(Config::get('login/session_name'), [
        'status' => true,
        'user_id' => $user_id
      ])
    ) {
      return false;
    }

    return true;
  }

  protected function isUserLoggedIn() : bool {
    $logged_in = Session::get(Config::get('login/session_name'));

    if ( $logged_in && $logged_in['status'] && is_int((int) $logged_in['user_id']) && $this->userExists($logged_in['user_id'], 'id') ) {
      $this->user_id = $logged_in['user_id'];
      return true;
    }

    return false;
  }

  // Logout
  protected function logoutUser() {
    $google_access_token = Session::get('google_access_token');
    $twitter_access_token = Session::get('twitter_access_token');

    if ($google_access_token) {
      $google_auth = new GoogleAuth();
      $google_auth->logout($google_access_token);
    } else if ($twitter_access_token) {
      $twitter_auth = new TwitterAuth();
      $twitter_auth->logout();
    }

    return Session::delete(Config::get('login/session_name'));
  }

  // Update
  protected function updateUser(array $new_user_data) {
    extract($new_user_data);

    if (empty($username) && empty($email) && empty($old_password) && empty($new_password)) {
      return null;
    }

    $old_user_data = $this->getUserData($this->user_id, 'id', [
      'id',
      'username',
      'email',
      'password',
      'google_id',
      'twitter_id'
    ]);

    if (!$old_user_data) {
      return false;
    }

    $errors = [];
    $user_data_to_update = [];

    if (!empty($username)) {
      if ($username == $old_user_data['username']) {
        $errors['username'][] = 'The username is the same as before.';
      }

      if ($this->usernameExists($username)) {
        $errors['username'][] = 'The username already exists.';
      }

      if (empty($errors['username'])) {
        $user_data_to_update[] = 'username';
      }
    }

    if (!empty($email)) {
      if ($email == $old_user_data['email']) {
        $errors['email'][] = 'The email is the same as before.';
      }

      if ($this->emailExists($email)) {
        $errors['email'][] = 'The email already exists.';
      }

      if (empty($errors['email'])) {
        $user_data_to_update[] = 'email';
      }
    }

    if (!empty($old_password)) {
      if ( strtolower($old_password) == 'google' && $this->initPassword($old_user_data['google_id'], 'google') ) {
        $old_password_valid = true;
        $social_auth_type = 'google';
      } else if ( strtolower($old_password) == 'twitter' && $this->initPassword($old_user_data['twitter_id'], 'twitter') ) {
        $old_password_valid = true;
        $social_auth_type = 'twitter';
      } else {
        if (!Hash::check($old_password, $old_user_data['password'])) {
          $errors['old_password'][] = 'The old password is wrong.';
        }

        if (empty($errors['old_password'])) {
          $old_password_valid = true;
        }
      }
    }

    if ( !empty($new_password) && $old_password_valid ) {
      $user_data_to_update[] = 'password';
    }

    if (!empty($errors)) {
      return $errors;
    }

    $new_user_data = [];

    foreach ($user_data_to_update as $value) {
      switch ($value) {
        case 'username':
          $new_user_data['username'] = $username;
          break;

        case 'email':
          $new_user_data['email'] = $email;
          break;

        case 'password':
          $new_user_data['password'] = Hash::get($new_password);
          break;
      }
    }

    $updated = $this->updateUserData($old_user_data['id'], 'id', $new_user_data);

    if (!$updated) {
      return false;
    }

    if ( isset($social_auth_type) && in_array('password', $user_data_to_update) ) {
      $this->setInitPasswordToFalse($old_user_data[$social_auth_type . '_id'], $social_auth_type);
    }

    if (in_array('email', $user_data_to_update)) {
      if (in_array('username', $user_data_to_update)) {
        $username = $old_user_data['username'];
      }

      $this->unverifyUser($email, Token::get(Config::get('email/token_length')));
      $this->sendUserVerificationMail($email, $this->getToken($email), $username);

      return [
        'success' => 'Your changes have been saved successfully and a verification mail has been sent to your inbox. Please log out and verify your new email. As long as you have not verified your new email, you will not be able to access your account.'
      ];
    }

    return true;
  }

  protected function initPassword(string $social_id, string $social_auth_type) : bool {
    switch ($social_auth_type) {
      case 'google':
        $user = $this->db->table($this->table)->select('google_init_password', [
          'google_id' => $social_id
        ])->retrieve('first');

        $init_password = $user['google_init_password'] ?? false;
        break;

      case 'twitter':
        $user = $this->db->table($this->table)->select('twitter_init_password', [
          'twitter_id' => $social_id
        ])->retrieve('first');

        $init_password = $user['twitter_init_password'] ?? false;
        break;

      default:
        throw new UserException('Invalid/Unknown social auth type');
        break;
    }

    return $init_password;
  }

  protected function setInitPasswordToFalse(string $social_id, string $social_auth_type) : bool {
    switch ($social_auth_type) {
      case 'google':
        return $this->db->table($this->table)->update([
          'google_init_password' => 0
        ], [
          'google_id' => $social_id
        ]);
        break;

      case 'twitter':
        return $this->db->table($this->table)->update([
          'twitter_init_password' => 0
        ], [
          'twitter_id' => $social_id
        ]);
        break;

      default:
        throw new UserException('Invalid/Unknown social auth type');
        break;
    }
  }

  protected function updateUserData($identifier, string $identifier_type, array $new_data) : bool {
    if (!$this->supportedIdentifierType($identifier_type)) {
      throw new UserException('Invalid/Unknown identifier type');
    }

    return $this->db->table($this->table)->update($new_data, [
      $identifier_type => $identifier
    ]);
  }

  // Delete
  protected function deleteUser(string $password) {
    $password_hash = $this->getPasswordByUserId($this->user_id);

    if (!$password_hash) {
      return false;
    }

    if (!Hash::check($password, $password_hash)) {
      return [
        'password' => [ 'Wrong password.' ]
      ];
    }

    $deleted = $this->deleteUserAccount($this->user_id);

    if (!$deleted) {
      return false;
    }

    $logged_out = $this->logoutUser();

    if (!$logged_out) {
      return [
        'failed' => 'Your account has been successfully deleted, but the logout failed. Please just close your browser in order to be logged out properly.'
      ];
    }

    return true;
  }

  protected function deleteUserAccount(int $user_id) : bool {
    return $this->db->table($this->table)->delete([ 'id' => $user_id ]);
  }

  // Verification
  protected function sendUserVerificationMail(string $email, string $token = '', string $username = '') : bool {
    if (!$token) {
      $token = Token::get(Config::get('email/token_length'));
    }

    if(!$username) {
      $username = $this->getUsernameByEmail($email);

      if (!$username) {
        return false;
      }
    }

    $message = new Message(Config::get('email/message'), [
      'username' => $username,
      'url' => Config::get('email/url') . "?token={ $token }&email={ $email }"
    ]);

    $mail = new Mail($email, Config::get('email/from'), Config::get('email/subject'), $message->getMessage());

    return $mail->sent();
  }

  protected function userVerified($identifier, string $identifier_type = 'email') : bool {
    if (!$this->supportedIdentifierType($identifier_type)) {
      throw new UserException('Invalid/Unknown identifier type');
    }

    $user = $this->db->table($this->table)->select('verified', [
      $identifier_type => $identifier
    ])->retrieve('first');

    return $user['verified'] ?? false;
  }

  protected function updateUserToken(string $email, string $old_token, string $new_token) : bool {
    return $this->db->table($this->table)->update([
      'token' => $new_token
    ], [
      'email' => $email,
      'token' => $old_token
    ]);
  }

  protected function updateUserTokenWithoutTokenCheck(string $email, string $new_token) : bool {
    return $this->db->table($this->table)->update([
      'token' => $new_token
    ], [
      'email' => $email
    ]);
  }

  protected function verifyUser(string $email, string $token) : bool {
    return $this->db->table($this->table)->update([
      'verified' => 1,
      'token' => ''
    ], [
      'email' => $email,
      'token' => $token
    ]);
  }

  protected function verifyUserWithoutToken(int $user_id) : bool {
    return $this->db->table($this->table)->update([
      'verified' => 1,
      'token' => ''
    ], [
      'id' => $user_id
    ]);
  }

  protected function unverifyUser(string $email, string $token) : bool {
    return $this->db->table($this->table)->update([
      'verified' => 0,
      'token' => $token
    ], [
      'email' => $email
    ]);
  }

  // General
  protected function getUserData($identifier, string $identifier_type, array $requested_data) : array {
    if (!$this->supportedIdentifierType($identifier_type)) {
      throw new UserException('Invalid/Unknown identifier type');
    }

    $user = $this->db->table($this->table)->select(implode(', ', $requested_data), [
      $identifier_type => $identifier
    ])->retrieve('first');

    $data = [];

    foreach ($requested_data as $value) {
      $data[$value] = $user[$value] ?? null;
    }

    return $data;
  }

  protected function getUserIdByEmail(string $email) : int {
    $user = $this->db->table($this->table)->select('id', [
      'email' => $email
    ])->retrieve('first');

    return $user['id'] ?? 0;
  }

  protected function getUsernameByEmail(string $email) : string {
    $user = $this->db->table($this->table)->select('username', [
      'email' => $email
    ])->retrieve('first');

    return $user['username'] ?? '';
  }

  protected function getPasswordByUserId(int $user_id) : string {
    $user = $this->db->table($this->table)->select('password', [
      'id' => $user_id
    ])->retrieve('first');

    return $user['password'] ?? '';
  }

  protected function getToken(string $email) : string {
    $user = $this->db->table($this->table)->select('token', [
      'email' => $email
    ])->retrieve('first');

    return $user['token'] ?? '';
  }

  protected function userExists($identifier, string $identifier_type) : bool {
    if (!$this->supportedIdentifierType($identifier_type)) {
      throw new UserException('Invalid/Unknown identifier type');
    }

    $user_exists = $this->db->table($this->table)->select('id', [
      $identifier_type => $identifier
    ])->retrieve('first');

    if ($user_exists) {
      return true;
    }

    return false;
  }

  protected function fieldExists($value, string $field) : bool {
    $field_exists = $this->db->table($this->table)->select('id', [
      $field => $value
    ])->retrieve('first');

    if (!$field_exists) {
      return false;
    }

    return true;
  }

  protected function usernameExists(string $username) : bool {
    return $this->fieldExists($username, 'username');
  }

  protected function emailExists(string $email) : bool {
    return $this->fieldExists($email, 'email');
  }

  // Helpers
  protected function supportedIdentifierType(string $identifier_type) : bool {
    return in_array($identifier_type, $this->supported_identifier_types);
  }

}
