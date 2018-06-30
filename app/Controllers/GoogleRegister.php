<?php

namespace LoginScript\Controllers;

use LoginScript\{
  Session\Session,
  Config\Config,
  Redirection\Redirect
};

class GoogleRegister extends Controller {

  public function run() {
    if (!$this->guest()) {
      Redirect::to('index.php');
    }

    if ($this->get()) {
      $data = $this->getRequestData('get', false);

      $code = $data['code'] ?? null;

      if (!$code) {
        Session::put(Config::get('errors/session_name'), [
          'failed' => 'Your registration with Google failed. Please try again.'
        ]);
        Redirect::to('register.php');
      }

      $user_registered = $this->getUserInstance('google')->register($code);

      if ($user_registered === false) {
        Session::put(Config::get('errors/session_name'), [
          'failed' => 'Your registration with Google failed. Please try again.'
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
