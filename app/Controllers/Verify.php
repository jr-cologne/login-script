<?php

namespace LoginScript\Controllers;

use LoginScript\{
  Input\Input,
  Redirection\Redirect,
  Token\Token,
  Config\Config,
  Session\Session
};

class Verify extends Controller {

  public function run() {
    if (!$this->guest()) {
      Redirect::to('index.php');
    }

    if ($this->get()) {
      $token = Input::get('token', 'get');
      $email = Input::get('email', 'get');

      if (!$token || !$email) {
        Redirect::to('index.php');
      }

      $user = $this->getUserInstance();

      if ($user->isVerified($email)) {
        Session::put(Config::get('errors/session_name'), [
          'failed' => 'Your email is already verified. Just go ahead and log in to your account.'
        ]);
        Redirect::to('login.php');
      }

      if (Input::get('resend', 'get') == 'true') {
        $new_token = Token::get(Config::get('verification_mail/token_length'));

        if (!$user->updateToken($email, $token, $new_token)) {
          Session::put(Config::get('errors/session_name'), [
            'failed' => 'Sending verification mail failed. Please <a href="verify.php?resend=true&token=' . $token . '&email=' . $email . '">try again</a>.'
          ]);
          Redirect::to('verify.php');
        }

        if (!$user->sendVerificationMail($email, $token)) {
          Session::put(Config::get('errors/session_name'), [
            'failed' => 'Sending verification mail failed. Please <a href="verify.php?resend=true&token=' . $new_token . '&email=' . $email . '">try again</a>.'
          ]);
          Redirect::to('verify.php');
        }

        Session::put(Config::get('errors/session_name'), [
          'success' => 'A verification mail has been successfully sent to your inbox.'
        ]);
        Redirect::to('login.php');
      }

      if (!$user->verify($email, $token)) {
        Session::put(Config::get('errors/session_name'), [
          'failed' => 'Verification failed. Please <a href="verify.php?token=' . $token . '&email=' . $email . '">try again</a>. In case it still does not work after several tries, please <a href="verify.php?resend=true&token=' . $token . '&email=' . $email .'">request a new verification mail</a>.'
        ]);
        Redirect::to('verify.php');
      }

      Session::put(Config::get('errors/session_name'), [
        'success' => 'Your email has been successfully verified. You can now go ahead and log in to your account.'
      ]);
      Redirect::to('login.php');
    } else {
      if (!Session::get(Config::get('errors/session_name'))) {
        Redirect::to('index.php');
      }
    }
  }

}
