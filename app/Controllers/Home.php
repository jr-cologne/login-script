<?php

namespace LoginScript\Controllers;

use LoginScript\{
  Controllers\Exception\ControllerException,
  Input\Input,
  Session\Session,
  Redirection\Redirect
};

class Home extends Controller {

  public function run() {
    if (!$this->guest()) {
      $data = $this->getUserInstance()->getData();

      Session::put('user_data', $data);
    }
  }

}
