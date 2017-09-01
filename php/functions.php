<?php
	// register new user
	function register(string $username, string $email, string $password) {
		// globalize database connection
		global $db;

		// create response array
		$response = ['success' => false, 'msg' => null];

		// clean username and e-mail
		$username = clean($username);
		$email = clean($email, 'email');

		// define array with the form data
		$form_data = [
			'username' => $username,
			'email' => $email,
			'password' => $password
		];

		// check form data
		$checkForm = checkForm($form_data);

		// is form data ok?
		if ($checkForm === true) {
			// check connection with database
			if (!$db->connected()) {
				// connection to database failed, return reponse
				$response['msg'] = ERR_HTML_START . 'Connection to database failed!' . ERR_HTML_END;
				return $response;
			}

			// check user data
			$checkUserData = checkUserData($username, $email);

			// is user data already assigned?
			if ( $checkUserData !== false && is_string($checkUserData) ) {
				// some user data is already assigned, return reponse from function checkUserData()
				$response['msg'] = $checkUserData;
				return $response;
			} else if (!$checkUserData) {
				// failed to check the availability of user data, save additional error message in var
				$error = ERR_HTML_START . 'Something went wrong while checking the availability of your username and email. Nevertheless we will try to register you.' . ERR_HTML_END;
			}

			// hash password for storing it in database
			$password = password_hash($password . PEPPER, PASSWORD_DEFAULT, PW_HASH_OPTIONS);

			// create token for email verification
			$token = bin2hex(random_bytes(16));

			// insert user into database
			$userRegistered = $db->insert(
				"INSERT INTO `" . DB_TABLE . "` (`username`, `email`, `password`, `token`) VALUES (:username, :email, :password, :token)",
				[
				 	'username' => $username,
				 	'email' => $email,
				 	'password' => $password,
				 	'token' => $token
				]
			);

			// has user successfully been registered?
			if ($userRegistered === true) {
				// send email for verification
				$verification_mail = sendVerificationMail($username, $email, $token);

				// verification mail sent successfully?
				if ($verification_mail) {
					if (empty($error)) {
						$response = [ 'success' => true, 'msg' => ERR_HTML_START . 'You have been registered successfully and an email to verify your email address has been sent to your inbox!' . ERR_HTML_END ];
						return $response;
					} else {
						$response = [ 'success' => true, 'msg' => $error . ERR_HTML_START . 'You have been registered successfully and an email to verify your email address has been sent to your inbox!' . ERR_HTML_END ];
						return $response;
					}
				} else {
					if (empty($error)) {
						$response = [ 'success' => false, 'msg' => ERR_HTML_START . 'You have been registered successfully, but unfortunately an error occured when trying to send an email to your inbox in order to verify your email address! Please try again and <a href="verify-email.php?resend=true&id=' . getUserId($username) . '">order a new verification mail</a>.' . ERR_HTML_END ];
						return $response;
					} else {
						$response = [ 'success' => false, 'msg' => $error . ERR_HTML_START . 'You have been registered successfully, but unfortunately an error occured when trying to send an email to your inbox in order to verify your email address! Please <a href="verify-email.php?resend=true&id=' . getUserId($user_id) . '">go to this page to get help</a>.' . ERR_HTML_END ];
						return $response;
					}
				}
			} else {	// registration failed
				// are there any additional errors?
				if (empty($error)) {
					// return normal response
					$response['msg'] = ERR_HTML_START . 'Registration failed. Please try again.' . ERR_HTML_END;
					return $response;
				} else {
					// return additional errors + normal response
					$response['msg'] = $error . ERR_HTML_START . 'Registration failed. Please try again.' . ERR_HTML_END;
					return $response;
				}
			}
		} else {	// form data isn't ok
			// return reponse from function checkForm()
			$response['msg'] = $checkForm;
			return $response;
		}
	}

	// clean data
	function clean(string $data, string $type=null) {
		$data = htmlspecialchars(stripcslashes(trim($data)));

		// shall an email be cleaned?
		if ($type == 'email') {
			// in case of an email injection, just take the first email
			$data = explode(',', $data);
			$data = $data[0];
		}

		return $data;
	}

	// check the data from the form
	function checkForm(array $form_data, string $mode='register') {
		// define $empty_fields arrray
		$empty_fields = [];

		// get empty fields and put their names into the array $empty_fields
		foreach ($form_data as $key => $value) {
			if (empty($value)) {
				$empty_fields[] = $key;
			}
		}

		// all fields are empty?
		if (count($form_data) == count($empty_fields)) {
			// return reponse
			return ERR_HTML_START . 'All fields are empty. Please enter your data.' . ERR_HTML_END;
		} else if (count($empty_fields) >= 1) {	// at least one field is empty
			// create an error list by looping through the $empty_fields array and getting the corresponding message for each field
			$err_html_list = '<ul>';
			foreach ($empty_fields as $value) {
				$err_html_list .= '<li>' . MSG_FOR_EMPTY_FIELDS[$value] . '</li>';
			}
			$err_html_list .= '</ul>';

			// return response
			return ERR_HTML_START . 'You have not entered the following things:' . ERR_HTML_END . $err_html_list;
		} else {	// no fields are empty
			// login mode?
			if ($mode == 'login') {
				return true;
			}

			// loop through $form_data array
			foreach ($form_data as $key => $value) {
				// check each specific form data value
				$checkSpecificFormData = checkSpecificFormData($value, $key);

				// specific form data value is ok?
				if ($checkSpecificFormData === true) {
					// save keys of checked form data values in array $checked_form_data
					$checked_form_data[] = $key;
				} else {	// specific form data value isn't ok
					// return response from checkSpecificFormData() function
					return $checkSpecificFormData;
				}
			}

			// are all form data values ok?
			if (count($checked_form_data) == count($form_data)) {
				return true;
			}
		}
	}

	// check specific form data
	function checkSpecificFormData(string $value, string $type) {
		switch ($type) {
			case 'username':
				// has username at least two characters?
				if (strlen($value) >= 2) {
					// has username not more than 50 characters?
					if (strlen($value) <= 50) {
						// username is ok
						return true;
					} else {	// username is too long
						// return response
						return ERR_HTML_START . 'Your username is too long. Only 50 characters are allowed.' . ERR_HTML_END;
					}
				} else {	// username is too short
					// return response
					return ERR_HTML_START . 'Your username is too short. Please use at least two characters.' . ERR_HTML_END;
				}

				break;

			case 'email':
				// is the email valid?
				if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
					// has email not more than 100 characters?
					if (strlen($value) <= 100) {
						// email is ok
						return true;
					} else {	// email is too long
						// return response
						return ERR_HTML_START . 'Your email is too long. Only 100 characters are allowed.' . ERR_HTML_END;
					}
				} else {	// email is not valid
					// return response
					return ERR_HTML_START . 'Your email is not valid. Please use a valid one.' . ERR_HTML_END;
				}

				break;

			case 'password':
				// has password at least six characters?
				if (strlen($value) >= 6) {
					// has password not more than 30 characters?
					if (strlen($value) <= 30) {
						// password is ok
						return true;
					} else {	// password is too long
						// return response
						return ERR_HTML_START . 'Your password is too long. Only 30 characters are allowed.' . ERR_HTML_END;
					}
				} else {	// password is too short
					// return response
					return ERR_HTML_START . 'Your password is too short. Please use at least six characters for safety reasons.' . ERR_HTML_END;
				}

				break;
		}
	}

	// check if user data is already assigned
	function checkUserData(string $username, string $email) {
		// globalize database connection
		global $db;

		// get already assigned user data from database
		$alreadyAssignedUserData = $db->select("SELECT `username`, `email` FROM `" . DB_TABLE . "`");

		// check if there is already assigned user data
		if ( !empty($alreadyAssignedUserData) && $alreadyAssignedUserData !== false && !$db->error() ) {
			// reorder $alreadyAssignedUserData array to one-dimensional array to make the checks easier
			foreach ($alreadyAssignedUserData as $key => $row) {
				$alreadyAssignedUserData[] = strtolower($row['username']);
				$alreadyAssignedUserData[] = strtolower($row['email']);
			}

			// are username and email already assigned?
			if ( in_array(strtolower($username), $alreadyAssignedUserData) && in_array(strtolower($email), $alreadyAssignedUserData) ) {
				// return response
				return ERR_HTML_START . 'Username and Email are already assigned. Please choose another one.' . ERR_HTML_END;
			} else if (in_array($username, $alreadyAssignedUserData)) {	// username is already assigned
				// return response
				return ERR_HTML_START . 'Username is already assigned. Please choose another one.' . ERR_HTML_END;
			} else if (in_array($email, $alreadyAssignedUserData)) {	// email is already assigned
				// return response
				return ERR_HTML_START . 'Email is already assigned. Please choose another one.' . ERR_HTML_END;
			} else {	// nothing is already assigned
				return true;
			}
		} else if ( empty($alreadyAssignedUserData) && $alreadyAssignedUserData !== false && !$db->error() ) {	// there is no already assigned user data
			return true;
		} else {
			return false;
		}
	}

	function login(string $username, string $password) {
		// globalize database connection
		global $db;

		// create response array
		$response = ['success' => false, 'msg' => null, 'user_id' => null ];

		// clean username
		$username = clean($username);

		// define array with the form data
		$form_data = [
			'username' => $username,
			'password' => $password
		];

		// check form data
		$checkForm = checkForm($form_data, 'login');

		// is form data ok?
		if ($checkForm === true) {
			// check connection with database
			if (!$db->connected()) {
				// connection to database failed, return reponse
				$response['msg'] = ERR_HTML_START . 'Connection to database failed!' . ERR_HTML_END;
				return $response;
			}

			$existsUser = existsUser($username);
			if ($existsUser === false) {
				$response['msg'] = ERR_HTML_START . 'An error occured when trying to check if the user exists. Please try again.' . ERR_HTML_END;
				return $response;
			}

			// does the user exist?
			if ($existsUser) {
				// get user id
				$user_id = getUserId($username);

				// email verified?
				$email_verified = (bool) $db->select("SELECT `verified` FROM " . DB_TABLE . " WHERE `id` = :user_id", [ 'user_id' => $user_id ])[0]['verified'];

				if (!$email_verified || $db->error()) {
					$response['msg'] = ERR_HTML_START . 'Your email is not verified or an error connected to that occured. Please make sure that your email is verified. In case you lost the verification mail, you can <a href="verify-email.php?resend=true&id=' . $user_id . '">order a new one</a>.' . ERR_HTML_END;
					return $response;
				}

				// user exists, get hashed password from database
				$pw_hash = getPasswordHash($username);

				// has the password hash been returned?
				if (!empty($pw_hash)) {
					// password hash has been returned, verify password
					if (password_verify($password . PEPPER, $pw_hash)) {
						// password was correct, get user id of logged in user
						$user_id = getUserId($username);

						// check if an user id has been returned
						if (empty($user_id)) {
							// no user id has been returned, return response
							$response['msg'] = ERR_HTML_START . 'Your password was correct, but unfortunately an error occured while trying to log you in.' . ERR_HTML_END;
							return $response;
						}

						// return response
						$response = [ 'success' => true, 'msg' => ERR_HTML_START .  'You are successfully logged in. <a href="index.php">Now you can go to the restricted area</a>!' . ERR_HTML_END, 'user_id' => $user_id ];
						return $response;
					} else {	// password is incorrect
						// return response
						$response['msg'] = ERR_HTML_START . 'The password is incorrect. Please try again.' . ERR_HTML_END;
						return $response;
					}
				} else {	// no password hash has been returned
					// return response
					$response['msg'] = ERR_HTML_START . 'An error is occured while getting the password from the database.' . ERR_HTML_END;
					return $response;
				}
			} else {	// user does not exist
				// return response
				$response['msg'] = ERR_HTML_START . 'The user does not exist.' . ERR_HTML_END;
				return $response;
			}
		} else {	// form data isn't ok
			// return reponse from function checkForm()
			$response['msg'] = $checkForm;
			return $response;
		}
	}

	function existsUser(string $username) {
		// globalize database connection
		global $db;

		$existingUsers = $db->select("SELECT `username` FROM `" . DB_TABLE . "`");

		// is an error occured when getting list of existing users?
		if ($db->error()) {
			return false;
		}

		// reorder $existingUsers array to one-dimensional array to make the following check easier
		foreach ($existingUsers as $key => $value) {
			$existingUsers[] = $value['username'];
		}

		if (in_array($username, $existingUsers)) {
			return true;
		} else {
			return 0;
		}
	}

	// get password hash for user from database
	function getPasswordHash($identifier, string $identifier_type='username') {
		// globalize database connection
		global $db;

		if ($identifier_type == 'username') {
			$pw_hash = $db->select("SELECT `password` FROM `" . DB_TABLE . "` WHERE `username` = :username", [ 'username' => $identifier ]);
		} else if ($identifier_type == 'user_id') {
			$pw_hash = $db->select("SELECT `password` FROM `" . DB_TABLE . "` WHERE `id` = :user_id", [ 'user_id' => $identifier ]);
		}

		if ( !empty($pw_hash) && $pw_hash !== false && !$db->error() ) {
			return $pw_hash[0]['password'];
		}

		return false;
	}

	// get user id from database
	function getUserId($identifier, string $identifier_type='username') {
		// globalize database connection
		global $db;

		switch ($identifier_type) {
			case 'username':
				return (int) $db->select("SELECT `id` FROM `" . DB_TABLE . "` WHERE `username` = :username", [ 'username' => $identifier ])[0]['id'];
				break;
			
			case 'email':
				return (int) $db->select("SELECT `id` FROM `" . DB_TABLE . "` WHERE `email` = :email", [ 'email' => $identifier ])[0]['id'];
				break;

			case 'google_id':
				return $db->select("SELECT `id` FROM `" . DB_TABLE . "` WHERE `google_id` = :google_id", [ 'google_id' => $identifier ])[0]['id'];
				break;
		}
	}

	// is someone logged in?
	function checkLogin() {
		if ( isset($_SESSION['logged_in']) && is_int($_SESSION['logged_in']) ) {
			return true;
		} else {
			return false;
		}
	}

	// get user data
	function getUserData($user_id, array $wanted_data, string $login_type='standard') {
		// globalize database connection
		global $db;

		$fields = '`' . implode('`, `', $wanted_data) . '`';

		if ($login_type == 'google') {
			return $db->select("SELECT " . $fields . " FROM `" . DB_TABLE . "` WHERE `google_id` = :user_id", [ 'user_id' => $user_id ])[0];
		} else {
			return $db->select("SELECT " . $fields . " FROM `" . DB_TABLE . "` WHERE `id` = :user_id", [ 'user_id' => $user_id ])[0];
		}
	}

	// update profile for a specific user
	function updateProfile($user_id, string $username=null, string $email=null, string $new_username=null, string $new_email=null, string $old_password=null, string $new_password=null, bool $google_is_init_password=false) {
		// globalize database connection
		global $db;

		if (!$user_id) {
			$response['msg'] = ERR_HTML_START . 'An error occured while saving your changes.' . ERR_HTML_END;
			return $response;
		}

		// create response array
		$response = ['success' => false, 'msg' => null];

		// clean username and email
		$new_username = clean($new_username);
		$new_email = clean($new_email);

		// define array with the form data
		$form_data = [
			'new_username' => $new_username,
			'new_email' => $new_email,
			'old_password' => $old_password,
			'new_password' => $new_password
		];

		// loop through form data and get array of entered fields
		foreach ($form_data as $key => $value) {
			if (!empty($value)) {
				$entered_fields[] = $key;
			}
		}

		// at least one field is entered?
		if (count($entered_fields) >= 1) {
			// old password empty?
			if (empty($old_password) && !empty($new_password)) {
				$response['msg'] = ERR_HTML_START . 'The old password is empty. Please enter it.' . ERR_HTML_END;
				return $response;
			} else if (empty($new_password) && !empty($old_password)) {	// new password is empty
				// return response
				$response['msg'] = ERR_HTML_START . 'The new password is empty. Please enter it.' . ERR_HTML_END;
				return $response;
			}

			// loop through entered fields
			foreach ($entered_fields as $field) {
				// which field do we have?
				switch ($field) {
					case 'new_username':
						// is new username the same as before?
						if ($username == $new_username) {
							// new username is the same as before, return response
							$response['msg'] = ERR_HTML_START . 'The username is the same as before. Please choose a new one.' . ERR_HTML_END;
							return $response;
						}

						// check new username
						$checked_new_username = checkSpecificFormData($new_username, 'username');

						// is new username ok?
						if ($checked_new_username === true) {
							// new username is ok, add it to array with the data that should be updated
							$userdata_to_update[] = 'username';
						} else {
							// new username isn't ok, return response from checkSpecificFormData()
							$response['msg'] = $checked_new_username;
							return $response;
						}

						break;

					case 'new_email':
						// is new email the same as before?
						if ($email == $new_email) {
							// new email is the same as before, return response
							$response['msg'] = ERR_HTML_START . 'The email is the same as before. Please choose a new one.' . ERR_HTML_END;
							return $response;
						}

						// check new email
						$checked_new_email = checkSpecificFormData($new_email, 'email');

						// is new email ok?
						if ($checked_new_email === true) {
							// new email is ok, add it to array with the data that should be updated
							$userdata_to_update[] = 'email';
						} else {
							// new email isn't ok, return response from checkSpecificFormData()
							$response['msg'] = $checked_new_email;
							return $response;
						}

						break;

					case 'old_password':
            if ($google_is_init_password && strtolower($old_password) == 'google') {
              $old_password_correct = true;
              break;
            }

						// get hashed password from database
						$pw_hash = getPasswordHash($user_id, 'user_id');

						// has the password hash been returned?
						if (!empty($pw_hash)) {
							// password hash has been returned, verify password
							if (password_verify($old_password . PEPPER, $pw_hash)) {
								// old password is correct
								$old_password_correct = true;
							} else {	// old password is incorrect
								// return response
								$response['msg'] = ERR_HTML_START . 'The old password is incorrect. Please try again.' . ERR_HTML_END;
								return $response;
							}
						} else {	// no password hash has been returned
							// return response
							$response['msg'] = ERR_HTML_START . 'An error is occured while getting the old password from the database.' . ERR_HTML_END;
							return $response;
						}

						break;

					case 'new_password':
						// check new password
						$checked_new_password = checkSpecificFormData($new_password, 'password');

						// is new password ok?
						if ($checked_new_password === true) {
							// new password is ok, was old password correct?
							if ($old_password_correct) {
								// old password was correct, add it to array with the data that should be updated
								$userdata_to_update[] = 'password';
							}
						} else {	// new password isn't ok
							// return response from checkSpecificFormData()
							$response['msg'] = $checked_new_password;
							return $response;
						}

						break;
				}
			}

			// define array that should contain the values of the individual data to be updated
			$update_values = [];

			// loop through the array with the info which data should be updated
			foreach ($userdata_to_update as $key => $value) {
				// check which data type it is and put the related data in the array update_values
				switch ($value) {
					case 'username':
						$update_values[$value] = $new_username;
						break;

					case 'email':
						$update_values[$value] = $new_email;
						break;

					case 'password':
						// hash password for storing it in database
						$new_password = password_hash($new_password . PEPPER, PASSWORD_DEFAULT, PW_HASH_OPTIONS);

						$update_values[$value] = $new_password;
						break;
				}
			}

      $set = '';

			foreach ($userdata_to_update as $value) {
				$set .= '`' . $value . '` = :' . $value;

				if ( !empty(next($userdata_to_update)) ) {
					$set .= ', ';
				}
			}

			// update the data in the database
			$data_updated = $db->update(
				"UPDATE `" . DB_TABLE . "` SET " . $set . " WHERE `id` = :user_id",
				array_merge($update_values, [ 'user_id' => $user_id ])
			);

			// was updating of the data successful?
			if ($data_updated === true) {
				// updating was successful, initial google password has been overwritten?
        if (in_array('password', $userdata_to_update) && strtolower($old_password) == 'google') {
          // set that account no longer has the initial google password
          google_setInitPasswordToFalse($user_id);
        }

        // return response
				$response = [ 'success' => true, 'msg' => ERR_HTML_START . 'Your changes have been saved successfully.' . ERR_HTML_END ];
				return $response;
			} else {	// updating wasn't successful
				// return response
				$response['msg'] = ERR_HTML_START . 'An error occured while saving your changes.' . ERR_HTML_END;
				return $response;
			}
		} else {	// all fields are empty
			// return response
			$response['msg'] = ERR_HTML_START . 'All fields are empty. Please enter the data you want to change.' . ERR_HTML_END;
			return $response;
		}

		return $response;
	}

	// delete account of specific user
	function deleteAccount(int $user_id, string $password) {
		// globalize database connection
		global $db;

		// create response array
		$response = ['success' => false, 'msg' => null];

		// password not entered?
		if (empty($password)) {
			$response['msg'] = ERR_HTML_START . 'Password is empty. Please enter it.' . ERR_HTML_END;
			return $response;
		}

		// check if password is correct
		// get hashed password from database
		$pw_hash = getPasswordHash($user_id, 'user_id');

		// has the password hash been returned?
		if (!empty($pw_hash)) {
			// password hash has been returned, verify password
			if (password_verify($password . PEPPER, $pw_hash)) {
				// password is correct, delete account
				$deleted = $db->delete("DELETE FROM `" . DB_TABLE . "` WHERE `id` = :user_id", [ 'user_id' => $user_id ]);

				// deleted account successsfully?
				if ($deleted === true) {
					// destroy session/log out user
					unset($_SESSION['logged_in']);

					// return response
					$response = [ 'success' => true, 'msg' => ERR_HTML_START . 'Your account has been deleted successfully. <a href="index.php">Back to homepage</a>' . ERR_HTML_END ];
					return $response;
				} else {
					// return response
					$response['msg'] = ERR_HTML_START . 'Something went wrong deleting your account. Please try again.' . ERR_HTML_END;
					return $response;
				}
			} else {	// password is incorrect
				// return response
				$response['msg'] = ERR_HTML_START . 'The password is incorrect. Please try again.' . ERR_HTML_END;
				return $response;
			}
		} else {	// no password hash has been returned
			// return response
			$response['msg'] = ERR_HTML_START . 'An error is occured while getting the password from the database.' . ERR_HTML_END;
			return $response;
		}
	}

	function sendVerificationMail(string $username, string $email, string $token) {
		$subject = '=?UTF-8?B?'.base64_encode(VERIFY_EMAIL_SUBJECT).'?=';
    $headers = [
      "MIME-Version: 1.0",
      "Content-type: text/plain; charset=utf-8",
      'From: ' . FROM,
      'Reply-To: ' . FROM,
      "Subject: {$subject}",
      "X-Mailer: PHP/".phpversion()
    ];

    if ( !empty($email) && !empty($token) &&
    		 mail( $email, $subject, createVerificationMailMessage([ ':username', ':url' ], [ 'username' => $username, 'url' => 'http://jr-cologne.16mb.com/login-script/verify-email.php?token=' . $token . '&email=' . $email ], VERIFY_EMAIL_MSG), implode("\r\n", $headers) )
    ) {
      return true;
    } else {
      return false;
    }
	}

	function createVerificationMailMessage(array $placeholders, array $replacements, string $message) {
		// replace placeholders in message with replacements and return new message
		return str_replace($placeholders, $replacements, $message);
	}

	function resendVerificationMail(int $user_id) {
		// globalize database connection
		global $db;

		// create response array
		$response = ['success' => false, 'msg' => null];

		// email already verified?
		$email_already_verified = $db->select("SELECT `verified` FROM " . DB_TABLE . " WHERE `id` = :user_id", [ 'user_id' => $user_id ]);

		if ($db->error()) {
			$error = ERR_HTML_START . 'Something went wrong when trying to check if your email is already verified. Nevertheless we will try to send a new verification mail to your inbox.' . ERR_HTML_END;
		}

		if (!empty($email_already_verified) && $email_already_verified['verified'] == true) {
			if (!empty($error)) {
				$response['msg'] = $error . ERR_HTML_START . 'Your email is already verified. You can simply go ahead and <a href="login.php">log in</a> to your account.' . ERR_HTML_END;
			} else {
				$response['msg'] = ERR_HTML_START . 'Your email is already verified. You can simply go ahead and <a href="login.php">log in</a> to your account.' . ERR_HTML_END;
			}
			return $response;
		}

    // create new token for user and update in db
    $token = bin2hex(random_bytes(16));

    $token_updated = $db->update("UPDATE " . DB_TABLE . " SET `token` = :token WHERE `id` = :user_id", [ 'token' => $token, 'user_id' => $user_id ]);

    if ($token_updated !== true && $db->error()) {
    	if (!empty($error)) {
    		$response['msg'] = $error . ERR_HTML_START . 'Something went wrong when creating a new token for the verification process. Please try again.' . ERR_HTML_END;
    	} else {
    		$response['msg'] = ERR_HTML_START . 'Something went wrong when creating a new token for the verification process. Please try again.' . ERR_HTML_END;
    	}
    	return $response;
    }
    
    // send new mail
    if ($token_updated === true) {
    	$userdata = getUserData($user_id, [ 'username', 'email' ]);

    	if (!empty($userdata) && $userdata !== false && !$db->error()) {
    		if (sendVerificationMail($userdata['username'], $userdata['email'], $token)) {
    			if (!empty($error)) {
    				$response = [ 'success' => true, 'msg' => $error . ERR_HTML_START . 'The new mail to verify your email address was successfully sent to your inbox.' . ERR_HTML_END];
    			} else {
    				$response = [ 'success' => true, 'msg' => ERR_HTML_START . 'The new mail to verify your email address was successfully sent to your inbox.' . ERR_HTML_END];
    			}
    		} else {
    			if (!empty($error)) {
    				$response['msg'] = $error . ERR_HTML_START . 'Something went wrong when trying to send the new mail to your inbox in order to verify your email address. Please try again.' . ERR_HTML_END;
    			} else {
    				$response['msg'] = ERR_HTML_START . 'Something went wrong when trying to send the new mail to your inbox in order to verify your email address. Please try again.' . ERR_HTML_END;
    			}
    		}

    		return $response;
    	}
    }
	}

	function verifyEmail(string $token, string $email) {
		// globalize database connection
		global $db;

		// create response array
		$response = ['success' => false, 'msg' => null];

    // verify email where token and email matches token and email in db
    $verified = $db->update("UPDATE " . DB_TABLE . " SET `verified` = 1 WHERE `token` = :token && `email` = :email", [ 'token' => $token, 'email' => $email ]);

    if ($verified === true) {
    	// clear token
    	clearToken($email);
    	$response = [ 'success' => true, 'msg' => ERR_HTML_START . 'Your email address has successfully been verified. Now you can go ahead and <a href="login.php">log in</a> to your account!' . ERR_HTML_END ];
    } else {
    	$user_id = getUserId($email);

    	if (is_int($user_id)) {
    		$response['msg'] = ERR_HTML_START . 'Something went wrong when trying to verify your email address. Please try again or <a href="verify-email.php?resend=true&id=' . $user_id . '">order a new verification mail</a>.' . ERR_HTML_END;
    	} else {
    		$response['msg'] = ERR_HTML_START . 'Something went wrong when trying to verify your email address. Please try again.' . ERR_HTML_END;
    	}
    }

    return $response;
	}

	function clearToken(string $email) {
		global $db;

		if (!empty($email)) {
			if ( $db->update("UPDATE " . DB_TABLE . " SET `token` = NULL WHERE `email` = :email", [ 'email' => $email ]) ) {
				return true;
			}
		}

		return false;
	}

	function google_login($code) {
		global $google_auth;
		global $db;

		// create response array
		$response = ['success' => false, 'msg' => null];

		// check redirect code
		$access_token = $google_auth->checkRedirectCode($code);

	  // successfully logged in with google?
	  if (!empty($access_token)) {
	  	// store access token in session
	  	$_SESSION['access_token'] = $access_token;

	  	// get payload/data
	  	$payload = $google_auth->getPayload();
	  	$google_id = $payload['sub'];
	  	$google_email = $payload['email'];
	  	$google_email_verified = $payload['email_verified'];

	  	// account with that email already exists?
	  	if (emailAssigned($google_email)) {
	  		// account already associated with this google account?
	  		$associated = $db->select("SELECT `google_id` FROM `" . DB_TABLE . "` WHERE `email` = :email && `google_id` = :google_id", [ 'email' => $google_email, 'google_id' => $google_id ]);

	  		if (is_array($associated)) {
	  			// set user as logged in
	  			$_SESSION['logged_in'] = $google_id;

	  			$response = [ 'success' => true, 'msg' => ERR_HTML_START . 'You are successfully logged in with Google. <a href="index.php">Now you can go to the restricted area</a>!' . ERR_HTML_END ];
	  			return $response;
	  		}

	  		// store everything in database
	  		$updated = $db->update("UPDATE `" . DB_TABLE . "` SET `google_id` = :google_id WHERE `email` = :email", [ 'google_id' => $google_id, 'email' => $google_email ]);

	  		if ($updated === true) {
	  			// set user as logged in
	  			$_SESSION['logged_in'] = $google_id;

	  			$response = [ 'success' => true, 'msg' => ERR_HTML_START . 'You are successfully logged in with Google. <a href="index.php">Now you can go to the restricted area</a>!' . ERR_HTML_END ];
	  		} else {
	  			// logout user from google
	  			google_logout();

	  			$response['msg'] = ERR_HTML_START . 'Your login with Google failed. Please try again.' . ERR_HTML_END;
	  		}
	  	} else {
	  		// logout user from google
	  		google_logout();

	  		$response['msg'] = ERR_HTML_START . 'No account with the same email address as your Google account is existing. It is not possible to use an Google acccount with an unused email for the login, because then your account can not be matched to your Google account. If you do not care about that, you can <a href="register.php">register an new/seperate account</a> with your Google account. Otherwise you have to use an Google account, which email matches to the email of your account here.' . ERR_HTML_END;
	  	}
	  } else {
	  	$response['msg'] = ERR_HTML_START . 'Authentication with Google failed. Please try again.' . ERR_HTML_END;
	  }

	  return $response;
	}

	function google_checkLogin() {
		return !empty($_SESSION['access_token']);
	}

	function google_getSignInButton() {
		global $google_auth;

    return '<a href="' . $google_auth->getAuthUrl() . '">Sign in with Google</a>';
	}

	function google_logout() {
		global $google_auth;

		$google_auth->logout($_SESSION['access_token']);

		unset($_SESSION['access_token']);
	}

	function emailAssigned(string $email) {
		global $db;

		$assignedEmails = $db->select("SELECT `email` FROM `" . DB_TABLE . "`");

		foreach ($assignedEmails as $key => $value) {
			unset($assignedEmails[$key]);
			$assignedEmails[] = $value['email'];
		}

		if (in_array($email, $assignedEmails)) {
			return true;
		} else {
			return false;
		}
	}

	function google_register($code) {
		global $google_auth;
		global $db;

		// create response array
		$response = ['success' => false, 'msg' => null];

		// check redirect code
		$access_token = $google_auth->checkRedirectCode($code);

	  // successfully logged in with google?
	  if (!empty($access_token)) {
	  	// get payload/data
	  	$payload = $google_auth->getPayload();
	  	$google_id = $payload['sub'];
	  	$google_email = $payload['email'];
	  	$google_email_verified = $payload['email_verified'];

	  	// account with that email does not already exist?
	  	if (!emailAssigned($google_email)) {
	  		// create password
				$password = bin2hex(random_bytes(16));

	  		// hash password for storing it in database
				$password = password_hash($password . PEPPER, PASSWORD_DEFAULT, PW_HASH_OPTIONS);

				if (!$google_email_verified) {
					// create token for email verification
					$token = bin2hex(random_bytes(16));

					// insert user into database
					$registered = $db->insert(
						"INSERT INTO `" . DB_TABLE . "` (`username`, `email`, `password`, `token`, `google_id`, `google_init_password`) VALUES (:username, :email, :password, :token, :google_id, :google_init_password)",
						[
						 	'username' => $google_email,
						 	'email' => $google_email,
						 	'password' => $password,
						 	'token' => $token,
						 	'google_id' => $google_id,
              'google_init_password' => 1
						]
					);
				}

				// insert user into database
				$registered = $db->insert(
					"INSERT INTO `" . DB_TABLE . "` (`username`, `email`, `password`, `verified`, `token`, `google_id`, `google_init_password`) VALUES (:username, :email, :password, :verified, :token, :google_id, :google_init_password)",
					[
					 	'username' => $google_email,
					 	'email' => $google_email,
					 	'password' => $password,
					 	'verified' => 1,
					 	'token' => '',
					 	'google_id' => $google_id,
            'google_init_password' => 1
					]
				);

				// has user successfully been registered?
				if ($registered === true) {
					// should a verification mail be sent?
					if (!$google_email_verified) {
						// send email for verification
						$verification_mail = sendVerificationMail($google_email, $google_email, $token);

						// verification mail sent successfully?
						if ($verification_mail) {
							$response = [ 'success' => true, 'msg' => ERR_HTML_START . 'You have successfully been registered with Google and an email to verify your email address has been sent to your inbox! Because you have registered with Google, we have set your username to your email address and generated a password for you. You can change both things in your control panel, when your are logged in.' . ERR_HTML_END ];
							return $response;
						} else {
							$response = [ 'success' => false, 'msg' => ERR_HTML_START . 'You have successfully been registered with Google, but unfortunately an error occured when trying to send an email to your inbox in order to verify your email address! Please try again and <a href="verify-email.php?resend=true&id=' . getUserId($google_email) . '">order a new verification mail</a>. Because you have registered with Google, we have set your username to your email address and generated a password for you. You can change both things in your control panel, when your are logged in.' . ERR_HTML_END ];
							return $response;
						}
					}

	  			$response = [ 'success' => true, 'msg' => ERR_HTML_START . 'You have successfully been registered with Google. Because you have registered with Google, we have set your username to your email address and generated a password for you. You can change both things in your control panel, when your are logged in. For the first time, you have to log in with Google.' . ERR_HTML_END ];
				} else {	// registration failed
					// return response
					$response['msg'] = ERR_HTML_START . 'Registration with Google failed. Please try again.' . ERR_HTML_END;
				}
	  	} else {
	  		$response['msg'] = ERR_HTML_START . 'There is already an account with the same email address your Google account is using. If you want to connect your Google account to the existing account with that email address here, you can simply <a href="login.php">log in to your existing account with Google</a>. Otherwise, if you are trying to create an new/seperate account, just use an other Google account, which email address is not yet used here in order to register.' . ERR_HTML_END;
	  	}
	  } else {
  		$response['msg'] = ERR_HTML_START . 'Authentication with Google failed. Please try again.' . ERR_HTML_END;
  	}

	  return $response;
	}

	function google_getSignUpButton() {
		global $google_auth;

    return '<a href="' . $google_auth->getAuthUrl() . '">Sign up with Google</a>';
	}

  function google_isInitPassword(int $user_id) {
    global $db;

    return (bool) $db->select("SELECT `google_init_password` FROM " . DB_TABLE . " WHERE `id` = :user_id", [ 'user_id' => $user_id ])[0]['google_init_password'];
  }

  function google_setInitPasswordToFalse(int $user_id) {
    global $db;

    return $db->update(
      "UPDATE " . DB_TABLE . " SET `google_init_password` = :google_init_password WHERE `id` = :user_id",
      [ 'google_init_password' => 0, 'user_id' => $user_id ]
    );
  }
?>
