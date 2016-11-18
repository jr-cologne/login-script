<?php
	require_once('config.php');

	// register new user
	function register(string $username, string $email, string $password) {
		// globalize database connection
		global $pdo;

		// create response array
		$response = ['succes' => false, 'msg' => null];

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
			if (!$pdo) {
				// connection to database failed, return reponse
				$response['msg'] = ERR_HTML_START . 'Connection to database failed!' . ERR_HTML_END;
				return $response;
			}

			// check user data
			$checkUserData = checkUserData($username, $email);

			// are user data already assigned?
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

			// insert user into database
			$userRegistered = db_insert(
																			DB_TABLE,

																		 [
																		 		'username',
																		 		'email',			// columns in database
																		 		'password'
																		 ],

																		 [
																		 		'username' => $username,
																		 		'email' => $email,				// values that should be inserted
																		 		'password' => $password
																		 ]
																 );

			// has user succesfully been registered?
			if ($userRegistered) {
				if (empty($error)) {
					$response = [ 'succes' => true, 'msg' => ERR_HTML_START . 'You have been registered succesfully!' . ERR_HTML_END ];
					return $response;
				} else {
					$response = [ 'succes' => true, 'msg' => $error . ERR_HTML_START . 'You have been registered succesfully!' . ERR_HTML_END ];
					return $response;
				}
			} else {	// registration failed
				// are there any additional errors?
				if (empty($error)) {
					// return normal response
					$response['msg'] = ERR_HTML_START . 'Registration failed!' . ERR_HTML_END;
					return $response;
				} else {
					// return additional errors + normal response
					$response['msg'] = $error . ERR_HTML_START . 'Registration failed!' . ERR_HTML_END;
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
			// in case of a email injection, just take the first email
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

	// insert data into database
	function db_insert(string $table, array $columns, array $values) {
		// globalize database connection
		global $pdo;

		// loop through the values that should be inserted and add a colon to the front of it, so that it can be processed for the sql query
		foreach ($values as $key => $value) {
			unset($values[$key]);
			$values[':' . $key] = $value;
		}

		// create a string of all values that should be inserted to use it for the sql query
		$values_string = "'" . implode("','", $values) . "'";

		try {
			// create sql query
			$sql = "INSERT INTO $table (" . implode(',', $columns) . ") VALUES (" . $values_string .")";
			// prepare statement for inserting data into the database
			$sql = $pdo->prepare($sql);
			// execute statement
			$statement = $sql->execute($values);
		} catch (PDOException $e) {
			// an error is occured while inserting data into the database
			return false;
		}

		// has statement been executed succesfully?
		if ($statement) {
			return true;
		} else {
			return false;
		}
	}

	// check if user data is already assigned
	function checkUserData(string $username, string $email) {
		// globalize database connection
		global $pdo;

		// get already assigned user data from database
		$alreadyAssignedUserData = db_select( DB_TABLE, [ 'username', 'email' ] );

		// reorder $alreadyAssignedUserData array to one-dimensional array to make the checks easier
		foreach ($alreadyAssignedUserData as $key => $row) {
			$alreadyAssignedUserData[] = strtolower($row['username']);
			$alreadyAssignedUserData[] = strtolower($row['email']);
		}

		// if there is already assigned user data
		if ( !empty($alreadyAssignedUserData) && $alreadyAssignedUserData !== false ) {
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
		} else if (empty($alreadyAssignedUserData) && $alreadyAssignedUserData !== false) {	// there is no already assigned user data
			return true;
		} else {
			return false;
		}
	}

	// select/get data from database
	function db_select(string $table, array $columns, string $where_condition=null, array $where_value=null, $fetch_mode=PDO::FETCH_ASSOC) {
		// globalize database connection
		global $pdo;

		try {
			// create sql query
			$sql = "SELECT " . implode(',', $columns) . " FROM $table";

			if (!empty($where_condition) && !empty($where_value['str'])) {
				$sql .= " WHERE $where_condition " . $where_value['str'];
			}

			// prepare statement for selecting data from the database
			$sql = $pdo->prepare($sql);

			if (!empty($where_condition) && !empty($where_value)) {
				// execute statement
				$statement = $sql->execute( [ $where_value['str'] => $where_value['val'] ] );
			} else {
				// execute statement
				$statement = $sql->execute();
			}

			// fetch all data from database
			$results = $sql->fetchAll($fetch_mode);
		} catch (PDOException $e) {
			// an error is occured while selecting data from the database
			return false;
		}

		// if statement has been executed succesfully and there are results
		if ($statement === true && !empty($results)) {
			// return results/data
			return $results;
		} else if ($statement === true && empty($results)) {
			// there are no results
			return null;
		} else {
			// an error is occured while executing the statement
			return false;
		}
	}

	function login(string $username, string $password) {
		// globalize database connection
		global $pdo;

		// create reponse array
		$response = ['succes' => false, 'msg' => null, 'user_id' => null ];

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
			if (!$pdo) {
				// connection to database failed, return reponse
				$response['msg'] = ERR_HTML_START . 'Connection to database failed!' . ERR_HTML_END;
				return $response;
			}

			// does the user exists?
			if (existsUser($username)) {
				// user exists, get hashed password from database
				$pw_hash = getPasswordHash($username)[0]['password'];

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
						$response = [ 'succes' => true, 'msg' => ERR_HTML_START .  'Your are succesfully logged in. <a href="index.php">Now you can go to the restricted area</a>!' . ERR_HTML_END, 'user_id' => $user_id ];
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
		global $pdo;

		$existingUsers = db_select(DB_TABLE, [ 'username' ]);

		// reorder $existingUsers array to one-dimensional array to make the following check easier
		foreach ($existingUsers as $key => $value) {
			$existingUsers[] = $value['username'];
		}

		if (in_array($username, $existingUsers)) {
			return true;
		} else {
			return false;
		}
	}

	// get password hash for user from database
	function getPasswordHash($identifier, string $indentifier_type='username') {
		// globalize database connection
		global $pdo;

		if ($indentifier_type == 'username') {
			$pw_hash = db_select(DB_TABLE, [ 'password' ], 'username = ', [ 'str' => ':username', 'val' => $identifier ]);
		} else if ($indentifier_type == 'user_id') {
			$pw_hash = db_select(DB_TABLE, [ 'password' ], 'id = ', [ 'str' => ':id', 'val' => $identifier ]);
		}

		return $pw_hash;
	}

	// get user id for specific username from database
	function getUserId(string $username) {
		// globalize database connection
		global $pdo;

		$user_id = db_select(DB_TABLE, [ 'id' ], 'username = ', [ 'str' => ':username', 'val' => $username ]);

		$user_id = (int) $user_id[0]['id'];

		return $user_id;
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
	function getUserData(int $user_id, array $wanted_data) {
		// globalize database connection
		global $pdo;

		$user_data = db_select(DB_TABLE, $wanted_data, 'id = ', [ 'str' => ':user_id', 'val' => $user_id ] )[0];

		return $user_data;
	}

	// update profile for a specific user
	function updateProfile(string $username=null, string $email=null, string $new_username=null, string $new_email=null, string $old_password=null, string $new_password=null) {
		// globalize database connection
		global $pdo;

		// create response array
		$response = ['succes' => false, 'msg' => null];

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

		// get user id of currently logged in user from session
		$user_id = $_SESSION['logged_in'];

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
				// old password is empty, return response
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
						// get hashed password from database
						$pw_hash = getPasswordHash($user_id, 'user_id')[0]['password'];

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

			// update the data in the database
			$data_updated = db_update(
																		DB_TABLE,

																		$userdata_to_update,	// columns to update

																		$update_values,	// data to replace the old data

																		'id = ',	// only for a specific user id

																		[ 'str' => ':id', 'val' => $user_id ]
															 );

			// was updating of the data succesful?
			if ($data_updated) {
				// updating was succesful, return response
				$response = [ 'succes' => true, 'msg' => ERR_HTML_START . 'Your changes have been saved succesfully.' . ERR_HTML_END ];
				return $response;
			} else {	// updating wasn't succesful
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

	function db_update(string $table, array $columns, array $values, string $where_condition, array $where_value) {
		// globalize database connection
		global $pdo;

		// loop through the values that should be the new values and add a colon to the front of it, so that it can be processed for the sql query
		foreach ($values as $key => $value) {
			unset($values[$key]);
			$values[':' . $key] = $value;
		}

		$update_string = '';
		$i = 0;

		// only one column to update?
		if (count($columns) == 1 && count($values) == 1) {
			// create update string
			$update_string = $columns[0] . ' = ' . key($values);
		} else {	// at least two columns to be updated
			// loop through all columns and values to be updated
			while (count($columns) > $i && count($values) > $i) {
				// is it the last column to be updated?
				if ( count($columns) - 1 == $i ) {
					// add last chunk to update string
					$update_string .= $columns[$i] . ' = ' . key($values);
				} else {	// not the last column
					// add next chunk to update string
					$update_string .= $columns[$i] . ' = ' . key($values) . ', ';
				}

				// got to next value and column
				next($values);
				$i++;
			}
		}

		// create array with the value of the where condition
		$where = [ $where_value['str'] => $where_value['val'] ];

		// add array with the where value to the values array
		$values = array_merge($values, $where);

		try {
			// create sql query
			$sql = "UPDATE $table SET $update_string WHERE $where_condition" . $where_value['str'];
			// prepare statement for inserting data into the database
			$sql = $pdo->prepare($sql);
			// execute statement
			$statement = $sql->execute($values);
		} catch (PDOException $e) {
			// an error is occured while inserting data into the database
			return false;
		}

		// has statement been executed succesfully?
		if ($statement) {
			return true;
		} else {
			return false;
		}
	}
?>
