<?php

namespace LoginScript\User;

use LoginScript\{
  Config\Config,
  Hash\Hash,
  Token\Token,
  Session\Session,
  Twitter\Auth\TwitterAuth
};

class TwitterUser extends BaseUser {

  protected $twitter_client;

  protected $supported_user_data = [
    'username',
    'email',
    'password',
    'verified',
    'token',
    'twitter_id',
    'twitter_init_password'
  ];

  // Registration
  public function register(string $verifier) {
    return $this->registerUser($verifier);
  }

  // Login
  public function login(string $verifier) {
    return $this->loginUser($verifier);
  }

  // Registration
  protected function registerUser(string $verifier) {
    $twitter_auth = new TwitterAuth('login');

    $access_tokens = $twitter_auth->checkCallback($verifier);

    if ( !$access_tokens || !$access_tokens['oauth_token'] || !$access_tokens['oauth_token_secret'] ) {
      return false;
    }

    $payload = $twitter_auth->getPayload();

    $twitter_id = $payload['user_id'] ?? null;
    $twitter_email = $payload['email'] ?? null;
    $twitter_email_verified = true;

    if ( !$payload || !$twitter_id || !$twitter_email || $twitter_email_verified === null ) {
      return false;
    }

    if ($this->userExists($twitter_email, 'email')) {
      return [
        'failed' => 'An account with the same email address as your Twitter account is already existing. Just go ahead and <a href="login.php">log in</a> with this account or <a href="register.php">create a new account</a> using another email.'
      ];
    }

    $username = explode('@', $twitter_email)[0];

    if ($this->userExists($username, 'username')) {
      $username = Token::get(Config::get('social_auth/twitter/registration/username_length'));
    }

    $password = Token::get(Config::get('social_auth/twitter/registration/password_length'));

    $password = Hash::get($password);

    if (!$twitter_email_verified) {
      $token = Token::get(Config::get('email/token_length'));
    }

    $registered = $this->createUser([
      'username' => $username,
      'email' => $twitter_email,
      'password' => $password,
      'verified' => (int) $twitter_email_verified,
      'token' => $token ?? '',
      'twitter_id' => $twitter_id,
      'twitter_init_password' => 1
    ]);

    if (!$registered) {
      return false;
    }

    if (!$twitter_email_verified && isset($token)) {
      if ($this->sendUserVerificationMail($twitter_email, $token, $username)) {
        return [
          'success' => "You have been successfully registered with Twitter and an email to verify your email address has been sent to your inbox! Since you have registered with Twitter, we have set your username to {$username} and generated a password for you. You can change both things in your profile when your are logged in. For the first time, please <a href=\"login.php\">log in</a> with your Twitter account."
        ];
      }

      return [
        'failed' => "You have been successfully registered with Twitter, but unfortunately an error occured when trying to send a verification email to your inbox. Please try again and <a href=\"verify.php?resend=true&token={$token}&email={$google_email}\">order a new verification mail</a>. Since you have registered with Twitter, we have set your username to {$username} and generated a password for you. You can change both things in your profile when your are logged in. For the first time, please <a href=\"login.php\">log in</a> with your Twitter account."
      ];
    }

    return [
      'success' => "You have been successfully registered with Twitter. Since you have registered with Twitter, we have set your username to {$username} and generated a password for you. You can change both things in your profile when your are logged in. For the first time, please <a href=\"login.php\">log in</a> with your Twitter account."
    ];
  }

  // Login
  protected function loginUser(string $verifier) {
    $twitter_auth = new TwitterAuth('login');

    $access_tokens = $twitter_auth->checkCallback($verifier);

    if ( !$access_tokens || !$access_tokens['oauth_token'] || !$access_tokens['oauth_token_secret'] ) {
      return false;
    }

    $payload = $twitter_auth->getPayload();

    $twitter_id = $payload['user_id'] ?? null;
    $twitter_email = $payload['email'] ?? null;
    $twitter_email_verified = true;

    if ( !$payload || !$twitter_id || !$twitter_email || $twitter_email_verified === null ) {
      return false;
    }

    $associated_user_without_email_check = $this->twitterAccountAssociatedWithoutEmailCheck($twitter_id);

    if ( !$this->userExists($twitter_email, 'email') && !$associated_user_without_email_check ) {
      return [
        'failed' => 'No account with the same email address as your Twitter account is existing. It is not possible to use an Twitter acccount with an email address which is unknown to our system because then your account can not be matched to your Twitter account. If you do not care about that, you can <a href="register.php">register an new/seperate account</a> with your Twitter account. Otherwise you have to use an Twitter account which email matches to the email of your account here.'
      ];
    }

    $associated_user = $this->twitterAccountAssociated($twitter_id, $twitter_email);

    if ( !$associated_user && !$associated_user_without_email_check ) {
      $update_data = [
        'twitter_id' => $twitter_id,
        'verified' => (int) $twitter_email_verified
      ];

      if ($twitter_email_verified) {
        $update_data['token'] = '';
      }

      $updated = $this->db->table($this->table)->update($update_data, [
        'email' => $twitter_email
      ]);

      if (!$updated) {
        return false;
      }

      $user_id = $this->getUserIdByEmail($twitter_email);

      if (!$user_id) {
        return false;
      }
    } else if ($associated_user) {
      $user_id = $associated_user;
    } else {
      $user_id = $associated_user_without_email_check;
    }

    $verified = $this->userVerified($user_id, 'id');

    if ( !$verified && !$twitter_email_verified ) {
      $data = $this->getUserData($user_id, 'id', [
        'email',
        'token'
      ]);

      $email = $data['email'];
      $token = $data['token'];

      if ($email || $token) {
        $errors = [
          'failed' => 'Your email is not verified yet. Before your email is verified, you will not be able to access your account, so please verify your email. In case you lost the verification mail, please <a href="verify.php?resend=true&token=' . $token . '&email=' . $email . '">request a new verification mail</a>. Instead, you can also verify your email at Twitter. Then, you do not have to verify both accounts.'
        ];
      } else {
        $errors = [
          'failed' => 'Your email is not verified yet. Before your email is verified, you will not be able to access your account, so please verify your email. Instead, you can also verify your email at Twitter. Then, you do not have to verify both accounts.'
        ];
      }
    } else if ( !$verified && $twitter_email_verified && $associated_user_without_email_check && !$associated_user ) {
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
    } else if ( !$verified && $twitter_email_verified ) {
      $verified = $this->verifyUserWithoutToken($user_id);

      if (!$verified) {
        $errors = [
          'failed' => 'Your email could not be verified. Before your email is verified, you will not be able to access your account, so please verify your email. In case your Twitter email is already verified, please try again. If it still does not work after several tries, please go ahead and verify your email through the normal verification process of our system.'
        ];
      }
    }

    if ($errors) {
      return $errors;
    }

    Session::put('twitter_access_token', $access_tokens);

    return $this->setUserLoggedIn($user_id);
  }

  protected function twitterAccountAssociated(string $twitter_id, string $twitter_email) {
    $user = $this->db->table($this->table)->select('id', [
      'email' => $twitter_email,
      'twitter_id' => $twitter_id
    ])->retrieve('first');

    $user_id = $user['id'] ?? null;

    if ($user_id) {
      return $user_id;
    }

    return false;
  }

  protected function twitterAccountAssociatedWithoutEmailCheck(string $twitter_id) {
    $user = $this->db->table($this->table)->select('id', [
      'twitter_id' => $twitter_id
    ])->retrieve('first');

    $user_id = $user['id'] ?? null;

    if ($user_id) {
      return $user_id;
    }

    return false;
  }

}
