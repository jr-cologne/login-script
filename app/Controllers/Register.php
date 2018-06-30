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

class Register extends Controller {

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
          'required' => 'The username is required.',
          'minlength' => 'The username needs to have at least 3 characters.',
          'maxlength' => 'The username cannot have more than 50 characters.'
        ],
        'email' => [
          'required' => 'The email is required.',
          'email' => 'The email needs to be a valid email address.',
          'minlength' => 'The email needs to have at least 5 characters.',
          'maxlength' => 'The email cannot have more than 100 characters.'
        ],
        'password' => [
          'required' => 'The password is required.',
          'minlength' => 'The password needs to have at least 6 characters.',
          'maxlength' => 'The password cannot have more than 50 characters.'
        ]
      ]);

      $data = $this->getValidationData($this->getRequestData());

      $validation = $validator->validate($data, [
        'username' => [
          'required' => true,
          'minlength' => 3,
          'maxlength' => 50
        ],
        'email' => [
          'required' => true,
          'email' => true,
          'minlength' => 5,
          'maxlength' => 100
        ],
        'password' => [
          'required' => true,
          'minlength' => 6,
          'maxlength' => 50
        ]
      ]);

      if (!$validation->passed()) {
        Session::put('register_data', $data);
        Session::put(Config::get('errors/session_name'), $validation->getErrors());
        Redirect::to('register.php');
      }

      $user_registered = $this->getUserInstance()->register($data['username'], $data['email'], $data['password']);

      if ($user_registered === false) {
        Session::put('register_data', $data);
        Session::put(Config::get('errors/session_name'), [
          'failed' => 'Your registration failed. Please try again.'
        ]);
        Redirect::to('register.php');
      } else if (is_array($user_registered)) {
        Session::put('register_data', $data);
        Session::put(Config::get('errors/session_name'), $user_registered);
        Redirect::to('register.php');
      }

      Session::put(Config::get('errors/session_name'), [
        'success' => 'You have been registered successfully!'
      ]);
      Redirect::to('register.php');
    }
  }

}
