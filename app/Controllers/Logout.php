<?php

namespace LoginScript\Controllers;

use LoginScript\{
  Controllers\Exception\ControllerException,
  Config\Config,
  Session\Session,
  Redirection\Redirect
};

class Logout extends Controller {

  public function run() {
    if (!Session::get(Config::get('errors/session_name'))) {
      if (!$this->guest()) {
        $user_logged_out = $this->getUserInstance()->logout();

        if (!$user_logged_out) {
          Session::put(Config::get('errors/session_name'), [
            'failed' => 'Logout failed. Please <a href="logout.php">try again</a> or just close your browser in order to be logged out properly.'
          ]);
          Redirect::to('logout.php');
        }

        Redirect::to('index.php');
      }
    }
  }

}
