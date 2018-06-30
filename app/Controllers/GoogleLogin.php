<?php

namespace LoginScript\Controllers;

use LoginScript\{
  Session\Session,
  Config\Config,
  Redirection\Redirect
};

class GoogleLogin extends Controller {

  public function run() {
    if (!$this->guest()) {
      Redirect::to('index.php');
    }

    if ($this->get()) {
      $data = $this->getRequestData('get', false);

      $code = $data['code'] ?? null;

      if (!$code) {
        Session::put(Config::get('errors/session_name'), [
          'failed' => 'Your login with Google failed. Please try again.'
        ]);
        Redirect::to('login.php');
      }

      $user_logged_in = $this->getUserInstance('google')->login($code);

      if ($user_logged_in === false) {
        Session::put(Config::get('errors/session_name'), [
          'failed' => 'Your login with Google failed. Please try again.'
        ]);
        Redirect::to('login.php');
      } else if (is_array($user_logged_in)) {
        Session::put(Config::get('errors/session_name'), $user_logged_in);
        Redirect::to('login.php');
      }

      Redirect::to('index.php');
    } else {
      Redirect::to('login.php');
    }
  }

}
