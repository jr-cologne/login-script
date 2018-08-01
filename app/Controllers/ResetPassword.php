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

class ResetPassword extends Controller {

  public function run() {
    if (!$this->guest()) {
      Redirect::to('index.php');
    }

    if ($this->post()) {
      if ( !CSRF::checkToken(Input::get('csrf_token')) ) {
        throw new ControllerException('Invalid CSRF token');
      }

      $validator = new Validator([
        'email' => [
          'required' => 'The email is required.',
          'email' => 'The email needs to be a valid email address.'
        ]
      ]);

      $data = $this->getValidationData($this->getRequestData());

      $validation = $validator->validate($data, [
        'email' => [
          'required' => true,
          'email' => true
        ]
      ]);

      if (!$validation->passed()) {
        Session::put('password_reset_data', $data);
        Session::put(Config::get('errors/session_name'), $validation->getErrors());
        Redirect::to('reset-password.php');
      }

      $password_mail_sent = $this->getUserInstance()->resetPassword($data['email']);

      if ($password_mail_sent === false) {
        Session::put('password_reset_data', $data);
        Session::put(Config::get('errors/session_name'), [
          'failed' => 'An email with your new password could not be sent. Please try again.'
        ]);
        Redirect::to('reset-password.php');
      } else if (is_array($password_mail_sent)) {
        Session::put('password_reset_data', $data);
        Session::put(Config::get('errors/session_name'), $password_mail_sent);
        Redirect::to('reset-password.php');
      }

      Session::put(Config::get('errors/session_name'), [
        'success' => 'An email with your new password has been successfully sent to your inbox.'
      ]);
      Redirect::to('login.php');
    }
  }

}
