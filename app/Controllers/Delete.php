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

class Delete extends Controller {

  public function run() {
    if ($this->guest()) {
      Redirect::to('index.php');
    }

    if ($this->post()) {
      if ( !CSRF::checkToken(Input::get('csrf_token')) ) {
        throw new ControllerException('Invalid CSRF token');
      }

      $validator = new Validator([
        'password' => [
          'required' => 'The password is required.'
        ]
      ]);

      $data = $this->getValidationData($this->getRequestData());

      $validation = $validator->validate($data, [
        'password' => [
          'required' => true
        ]
      ]);

      if (!$validation->passed()) {
        Session::put('delete_data', $data);
        Session::put(Config::get('errors/session_name'), $validation->getErrors());
        Redirect::to('delete.php');
      }

      $user_deleted = $this->getUserInstance()->delete($data['password']);

      if ($user_deleted === false) {
        Session::put(Config::get('errors/session_name'), [
          'failed' => 'Deleting account failed. Please try again.'
        ]);
        Redirect::to('delete.php');
      } else if (is_array($user_deleted)) {
        Session::put(Config::get('errors/session_name'), $user_deleted);
        Redirect::to('delete.php');
      }

      Session::put(Config::get('errors/session_name'), [
        'success' => 'Your account has been successfully deleted.'
      ]);
      Redirect::to('index.php');
    }
  }

}
