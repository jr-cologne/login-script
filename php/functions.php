<?php
	require_once('config.php');

	// register new user
	function register($pdo, string $username, string $email, string $password) {
		// create reponse array
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

			// are user data already assigned?
			$checkUserData = checkUserData($pdo, $username, $email);
			if ( $checkUserData !== false && is_string($checkUserData) ) {
				// user data are already assigned, return reponse from function checkUserData()
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
															$pdo,

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
	function db_insert($pdo, array $columns, array $values) {
		// loop through the values that should be inserted and add a colon to the front of it, so that it can be processed for the sql query
		foreach ($values as $key => $value) {
			unset($values[$key]);
			$values[':' . $key] = $value;
		}

		// create a string of all values that should be inserted to use it for the sql query
		$values_string = "'" . implode("','", $values) . "'";

		try {
			// create sql query
			$sql = "INSERT INTO users (" . implode(',', $columns) . ") VALUES (" . $values_string .")";
			// prepare statement for inserting data into the database
			$sql = $pdo->prepare($sql);
			// execute statement
			$statement = $sql->execute($values);
		} catch (PDOException $e) {
			echo '<pre>';
			print_r($e);
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
	function checkUserData($pdo, string $username, string $email) {
		// get already assigned user data from database
		$alreadyAssignedUserData = db_select( $pdo, 'users', [ 'username', 'email' ] );

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
				// retur response
				return ERR_HTML_START . 'Email is already assigned. Please choose another one.' . ERR_HTML_END;
			} else {	// nothing is already assigned
				return true;
			}
		} else {	// there is no already assigned user data
			return false;
		}
	}

	// select/get data from database
	function db_select($pdo, string $table, array $columns, string $where_condition=null, array $where_value=null, $fetch_mode=PDO::FETCH_ASSOC) {
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
		if ($statement && !empty($results)) {
			// return results/data
			return $results;
		} else {
			// there are no results or the statement hasn't been executed succesfully
			return false;
		}
	}

	function login($pdo, string $username, string $password) {
		// create reponse array
		$response = ['succes' => false, 'msg' => null];

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
			if (existsUser($pdo, $username)) {
				// user exists, get hashed password from database
				$pw_hash = getPasswordHash($pdo, $username)[0]['password'];

				// has the password hash been returned?
				if (!empty($pw_hash)) {
					// password hash has been returned, verify password
					if (password_verify($password . PEPPER, $pw_hash)) {
						// password was correct, return response
						$response = [ 'succes' => true, 'msg' => ERR_HTML_START.  'Your are succesfully logged in. <a href="index.php">Now you can go to the restricted area</a>!' . ERR_HTML_END ];
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

	function existsUser($pdo, string $username) {
		$existingUsers = db_select($pdo, 'users', [ 'username' ]);

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
	function getPasswordHash($pdo, string $username) {
		$pw_hash = db_select($pdo, 'users', [ 'password' ], 'username = ', [ 'str' => ':username', 'val' => $username ]);

		return $pw_hash;
	}

	// get user id for specific username from database
	function getUserId($pdo, string $username) {
		$user_id = db_select($pdo, 'users', [ 'id' ], 'username = ', [ 'str' => ':username', 'val' => $username ]);

		return $user_id;
	}
?> 