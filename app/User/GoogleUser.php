<?php

namespace LoginScript\User;

use LoginScript\{
  Config\Config,
  Hash\Hash,
  Token\Token,
  Session\Session,
  Google\Auth\GoogleAuth
};

class GoogleUser extends BaseUser {

  protected $google_client;

  protected $supported_user_data = [
    'username',
    'email',
    'password',
    'verified',
    'token',
    'google_id',
    'google_init_password'
  ];

  // Registration
  public function register(string $code) {
    return $this->registerUser($code);
  }

  // Login
  public function login(string $code) {
    return $this->loginUser($code);
  }

  // Registration
  protected function registerUser(string $code) {
    $google_auth = new GoogleAuth('register');

    $access_token = $google_auth->checkRedirectCode($code);

    if (!$access_token) {
      return false;
    }

    $payload = $google_auth->getPayload();

    $google_id = $payload['sub'] ?? null;
    $google_email = $payload['email'] ?? null;
    $google_email_verified = $payload['email_verified'] ?? null;

    if ( !$payload || !$google_id || !$google_email || $google_email_verified === null ) {
      return false;
    }

    if ($this->userExists($google_email, 'email')) {
      return [
        'failed' => 'An account with the same email address as your Google account is already existing. Just go ahead and <a href="login.php">log in</a> with this account or <a href="register.php">create a new account</a> using another email.'
      ];
    }

    $username = explode('@', $google_email)[0];

    if ($this->userExists($username, 'username')) {
      $username = Token::get(Config::get('social_auth/google/registration/username_length'));
    }

    $password = Token::get(Config::get('social_auth/google/registration/password_length'));

    $password = Hash::get($password);

    if (!$google_email_verified) {
      $token = Token::get(Config::get('email/token_length'));
    }

    $registered = $this->createUser([
      'username' => $username,
      'email' => $google_email,
      'password' => $password,
      'verified' => (int) $google_email_verified,
      'token' => $token ?? '',
      'google_id' => $google_id,
      'google_init_password' => 1
    ]);

    if (!$registered) {
      return false;
    }

    if (!$google_email_verified && isset($token)) {
      if ($this->sendUserVerificationMail($google_email, $token, $username)) {
        return [
          'success' => "You have been successfully registered with Google and an email to verify your email address has been sent to your inbox! Since you have registered with Google, we have set your username to {$username} and generated a password for you. You can change both things in your profile when your are logged in. For the first time, please <a href=\"login.php\">log in</a> with your Google account."
        ];
      }

      return [
        'failed' => "You have been successfully registered with Google, but unfortunately an error occured when trying to send a verification email to your inbox. Please try again and <a href=\"verify.php?resend=true&token={$token}&email={$google_email}\">order a new verification mail</a>. Since you have registered with Google, we have set your username to {$username} and generated a password for you. You can change both things in your profile when your are logged in. For the first time, please <a href=\"login.php\">log in</a> with your Google account."
      ];
    }

    return [
      'success' => "You have been successfully registered with Google. Since you have registered with Google, we have set your username to {$username} and generated a password for you. You can change both things in your profile when your are logged in. For the first time, please <a href=\"login.php\">log in</a> with your Google account."
    ];
  }

  // Login
  protected function loginUser(string $code) {
    $google_auth = new GoogleAuth('login');

    $access_token = $google_auth->checkRedirectCode($code);

    if (!$access_token) {
      return false;
    }

    $payload = $google_auth->getPayload();

    $google_id = $payload['sub'] ?? null;
    $google_email = $payload['email'] ?? null;
    $google_email_verified = $payload['email_verified'] ?? null;

    if ( !$payload || !$google_id || !$google_email || $google_email_verified === null ) {
      return false;
    }

    $associated_user_without_email_check = $this->googleAccountAssociatedWithoutEmailCheck($google_id);

    if ( !$this->userExists($google_email, 'email') && !$associated_user_without_email_check ) {
      return [
        'failed' => 'No account with the same email address as your Google account is existing. It is not possible to use an Google acccount with an email address which is unknown to our system because then your account can not be matched to your Google account. If you do not care about that, you can <a href="register.php">register an new/seperate account</a> with your Google account. Otherwise you have to use an Google account which email matches to the email of your account here.'
      ];
    }

    $associated_user = $this->googleAccountAssociated($google_id, $google_email);

    if ( !$associated_user && !$associated_user_without_email_check ) {
      $update_data = [
        'google_id' => $google_id,
        'verified' => (int) $google_email_verified
      ];

      if ($google_email_verified) {
        $update_data['token'] = '';
      }

      $updated = $this->db->table($this->table)->update($update_data, [
        'email' => $google_email
      ]);

      if (!$updated) {
        return false;
      }

      $user_id = $this->getUserIdByEmail($google_email);

      if (!$user_id) {
        return false;
      }
    } else if ($associated_user) {
      $user_id = $associated_user;
    } else {
      $user_id = $associated_user_without_email_check;
    }

    $verified = $this->userVerified($user_id, 'id');

    if ( !$verified && !$google_email_verified ) {
      $data = $this->getUserData($user_id, 'id', [
        'email',
        'token'
      ]);

      $email = $data['email'];
      $token = $data['token'];

      if ($email || $token) {
        $errors = [
          'failed' => 'Your email is not verified yet. Before your email is verified, you will not be able to access your account, so please verify your email. In case you lost the verification mail, please <a href="verify.php?resend=true&token=' . $token . '&email=' . $email . '">request a new verification mail</a>. Instead, you can also verify your email at Google. Then, you do not have to verify both accounts.',
        ];
      } else {
        $errors = [
          'failed' => 'Your email is not verified yet. Before your email is verified, you will not be able to access your account, so please verify your email. Instead, you can also verify your email at Google. Then, you do not have to verify both accounts.',
        ];
      }
    } else if ( !$verified && $google_email_verified && $associated_user_without_email_check && !$associated_user ) {
      $data = $this->getUserData($user_id, 'id', [
        'email',
        'token'
      ]);

      $email = $data['email'];
      $token = $data['token'];

      if ($email || $token) {
        $errors = [
          'failed' => 'Your email is not verified yet. Before your email is verified, you will not be able to access your account, so please verify your email. In case you lost the verification mail, please <a href="verify.php?resend=true&token=' . $token . '&email=' . $email . '">request a new verification mail</a>.',
        ];
      } else {
        $errors = [
          'failed' => 'Your email is not verified yet. Before your email is verified, you will not be able to access your account, so please verify your email.',
        ];
      }
    } else if ( !$verified && $google_email_verified ) {
      $verified = $this->verifyUserWithoutToken($user_id);

      if (!$verified) {
        $errors = [
          'failed' => 'Your email is not verified yet. Before your email is verified, you will not be able to access your account, so please verify your email. In case your Google email is already verified, please try again. If it still does not work after several tries, please go ahead and verify your email through the normal verification process of our system.',
        ];
      }
    }

    if ($errors) {
      return $errors;
    }

    Session::put('google_access_token', $access_token);

    return $this->setUserLoggedIn($user_id);
  }

  protected function googleAccountAssociated(string $google_id, string $google_email) {
    $user = $this->db->table($this->table)->select('id', [
      'email' => $google_email,
      'google_id' => $google_id
    ])->retrieve('first');

    $user_id = $user['id'] ?? null;

    if ($user_id) {
      return $user_id;
    }

    return false;
  }

  protected function googleAccountAssociatedWithoutEmailCheck(string $google_id) {
    $user = $this->db->table($this->table)->select('id', [
      'google_id' => $google_id
    ])->retrieve('first');

    $user_id = $user['id'] ?? null;

    if ($user_id) {
      return $user_id;
    }

    return false;
  }

}
