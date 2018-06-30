<?php

namespace LoginScript\Controllers;

use LoginScript\{
  Controllers\Exception\ControllerException,
  CSRF\CSRF,
  Input\Input,
  Validation\Validator,
  Session\Session,
  Config\Config,
  Redirection\Redirect
};

class Login extends Controller {

  public function run() {
    if (!$this->guest()) {
      Redirect::to('index.php');
    }

    if ($this->post()) {
      if ( !CSRF::checkToken(Input::get('csrf_token')) ) {
        throw new ControllerException('Invalid CSRF token');
      }

      $validator = new Validator([
        'username' => [
          'required' => 'The username is required.'
        ],
        'password' => [
          'required' => 'The password is required.'
        ]
      ]);

      $data = $this->getValidationData($this->getRequestData());

      $validation = $validator->validate($data, [
        'username' => [
          'required' => true
        ],
        'password' => [
          'required' => true
        ]
      ]);

      if (!$validation->passed()) {
        Session::put('login_data', $data);
        Session::put(Config::get('errors/session_name'), $validation->getErrors());
        Redirect::to('login.php');
      }

      $user_logged_in = $this->getUserInstance()->login($data['username'], $data['password']);

      if ($user_logged_in === false) {
        Session::put('login_data', $data);
        Session::put(Config::get('errors/session_name'), [
          'failed' => 'Your login failed. Please try again.'
        ]);
        Redirect::to('login.php');
      } else if (is_array($user_logged_in)) {
        Session::put('login_data', $data);
        Session::put(Config::get('errors/session_name'), $user_logged_in);
        Redirect::to('login.php');
      }

      Redirect::to('index.php');
    }
  }

}
