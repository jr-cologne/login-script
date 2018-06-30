<?php

namespace LoginScript\Controllers;

use LoginScript\{
  Controllers\Exception\ControllerException,
  CSRF\CSRF,
  Input\Input,
  Validation\Validator,
  Config\Config,
  Session\Session,
  Redirection\Redirect
};

class Profile extends Controller {

  public function run() {
    if ($this->guest()) {
      Redirect::to('index.php');
    }

    if ($this->post()) {
      if ( !CSRF::checkToken(Input::get('csrf_token')) ) {
        throw new ControllerException('Invalid CSRF token');
      }

      $validator = new Validator([
        'username' => [
          'minlength' => 'The username needs to have at least 3 characters.',
          'maxlength' => 'The username cannot have more than 50 characters.'
        ],
        'email' => [
          'email' => 'The email needs to be a valid email address.',
          'minlength' => 'The email needs to have at least 5 characters.',
          'maxlength' => 'The email cannot have more than 100 characters.'
        ],
        'old_password' => [
          'required_if_filled' => 'The old password is required in order to change your password.'
        ],
        'new_password' => [
          'required_if_filled' => 'The new password is required in order to change your password.',
          'minlength' => 'The new password needs to have at least 6 characters.',
          'maxlength' => 'The new password cannot have more than 50 characters.'
        ]
      ]);

      $data = $this->getValidationData($this->getRequestData());

      $validation = $validator->validate($data, [
        'username' => [
          'optional' => true,
          'minlength' => 3,
          'maxlength' => 50
        ],
        'email' => [
          'optional' => true,
          'email' => true,
          'minlength' => 5,
          'maxlength' => 100
        ],
        'old_password' => [
          'optional' => true,
          'required_if_filled' => 'new_password',
        ],
        'new_password' => [
          'optional' => true,
          'required_if_filled' => 'old_password',
          'minlength' => 6,
          'maxlength' => 50
        ]
      ]);

      if (!$validation->passed()) {
        Session::put('profile_data', $data);
        Session::put(Config::get('errors/session_name'), $validation->getErrors());
        Redirect::to('profile.php');
      }

      $user_updated = $this->getUserInstance()->update([
        'username' => $data['username'],
        'email' => $data['email'],
        'old_password' => $data['old_password'],
        'new_password' => $data['new_password']
      ]);

      if ($user_updated === false) {
        Session::put('profile_data', $data);
        Session::put(Config::get('errors/session_name'), [
          'failed' => 'Changes could not be saved. Please try again.'
        ]);
        Redirect::to('profile.php');
      } else if (is_array($user_updated)) {
        Session::put('profile_data', $data);
        Session::put(Config::get('errors/session_name'), $user_updated);
        Redirect::to('profile.php');
      } else if (is_null($user_updated)) {
        Session::put('profile_data', $data);
        Session::put(Config::get('errors/session_name'), [
          'failed' => 'No changes have been made.'
        ]);
        Redirect::to('profile.php');
      }

      Session::put(Config::get('errors/session_name'), [
        'success' => 'Your changes have been saved successfully!'
      ]);
      Redirect::to('profile.php');
    }

    $data = $this->getUserInstance()->getData();

    Session::put('user_data', $data);
  }

}
