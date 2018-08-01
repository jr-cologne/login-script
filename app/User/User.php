<?php

namespace LoginScript\User;

use LoginScript\{
  Config\Config,
  Hash\Hash,
  Token\Token
};

class User extends BaseUser {

  // Registration
  public function register(string $username, string $email, string $password) {
    return $this->registerUser($username, $email, $password);
  }

  // Login
  public function login(string $username, string $password) {
    return $this->loginUser($username, $password);
  }

  // Registration
  protected function registerUser(string $username, string $email, string $password) {
    $username_exists = $this->usernameExists($username);
    $email_exists = $this->emailExists($email);

    $errors = [];

    if ($username_exists && $email_exists) {
      $errors = [
        'username' => [ 'Username already exists.' ],
        'email' => [ 'Email already exists.' ]
      ];
    } else if ($username_exists) {
      $errors = [
        'username' => [ 'Username already exists.' ]
      ];
    } else if ($email_exists) {
      $errors = [
        'email' => [ 'Email already exists.' ]
      ];
    }

    if ($errors) {
      return $errors;
    }

    $password = Hash::get($password);

    $token = Token::get(Config::get('verification_mail/token_length'));

    $user_registered = $this->createUser(compact('username', 'email', 'password', 'token'));

    if (!$user_registered) {
      return false;
    }

    if (!$this->sendUserVerificationMail($email, $token, $username)) {
      return [
        'failed' => 'You have been registered successfully, but an error occurred when trying to send the verification mail. Please try to <a href="verify.php?resend=true&token=' . $token . '&email=' . $email . '">resend the verification mail</a>.'
      ];
    }

    return $user_registered;
  }

  // Login
  protected function loginUser(string $username, string $password) {
    if (!$this->userExists($username, 'username')) {
      return [
        'username' => [ 'Wrong username.' ]
      ];
    }

    $verified = $this->userVerified($username, 'username');

    $errors = [];

    if (!$verified) {
      $data = $this->getUserData($username, 'username', [
        'email',
        'token'
      ]);

      $email = $data['email'];
      $token = $data['token'];

      if ($email || $token) {
        $errors = [
          'failed' => 'Your email is not verified yet. Before your email is verified, you will not be able to access your account. In case you lost the verification mail, please <a href="verify.php?resend=true&token=' . $token . '&email=' . $email . '">request a new verification mail</a>.',
        ];
      } else {
        $errors = [
          'failed' => 'Your email is not verified yet. Before your email is verified, you will not be able to access your account.',
        ];
      }
    }

    if ($errors) {
      return $errors;
    }

    $data = $this->getUserData($username, 'username', [
      'id',
      'password'
    ]);

    $user_id = $data['id'];
    $password_hash = $data['password'];

    if (!$user_id || !$password_hash) {
      return false;
    }

    if (!Hash::check($password, $password_hash)) {
      return [
        'password' => [ 'Wrong password.' ]
      ];
    }

    return $this->setUserLoggedIn($user_id);
  }

}
