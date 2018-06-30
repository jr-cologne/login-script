<?php

namespace LoginScript\Controllers;

use LoginScript\{
  Session\Session,
  Config\Config,
  Redirection\Redirect
};

class TwitterRegister extends Controller {

  public function run() {
    if (!$this->guest()) {
      Redirect::to('index.php');
    }

    if ($this->get()) {
      $data = $this->getRequestData('get', false);

      $oauth_verifier = $data['oauth_verifier'] ?? null;

      if (!$oauth_verifier) {
        Session::put(Config::get('errors/session_name'), [
          'failed' => 'Your registration with Twitter failed. Please try again.'
        ]);
        Redirect::to('register.php');
      }

      $user_registered = $this->getUserInstance('twitter')->register($oauth_verifier);

      if ($user_registered === false) {
        Session::put(Config::get('errors/session_name'), [
          'failed' => 'Your registration with Twitter failed. Please try again.'
        ]);
        Redirect::to('register.php');
      } else if (is_array($user_registered)) {
        Session::put(Config::get('errors/session_name'), $user_registered);
        Redirect::to('register.php');
      }
    } else {
      Redirect::to('register.php');
    }
  }

}
