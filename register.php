<?php
  // start session
  session_start();

  // require all other files
	require_once('php/config.php');
	require_once('php/functions.php');
  require_once('php/db.php');

  // user logged in?
  if (!empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // redirect user to restricted area
    header('Location: index.php');
  }

  // set response
  $response = null;

  // register form submitted?
	if ($_POST['register'] == 'Register') {
    // register user and get response
		$response = register($pdo, $_POST['username'], $_POST['email'], $_POST['password']);
	}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Restricted Area - Register</title>
  <meta charset="UTF-8">
</head>
<body>
  <h1>Restricted Area - Register</h1>
  <p><strong>Welcome to the the restricted area!</strong></p>
  
  <?php
    // display that only if the registration failed or if the registration form wasn't submitted
    if (!$response['succes']) {
      ?>
      <p>Register for getting access to the restricted area!</p>
      <?php
    }

    // if a response exists, display the message
  	if (!empty($response)) { 
      echo $response['msg']; 
    }

    // was the registration succesfully?
    if ($response['succes']) {
      // display additional response
      echo ERR_HTML_START . 'Now go ahead and <a href="login.php">log in</a> to your account. Have fun!' . ERR_HTML_END;
    } else {  // registration failed
      // display registration form etc.
      ?>
      <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <label>Your Username: <input type="text" name="username" value="<?php if (!empty($_POST['username'])) { echo clean($_POST['username']); } ?>"></label>
        <label>Your Email: <input type="text" name="email" value="<?php if (!empty($_POST['email'])) { echo clean($_POST['email']); } ?>"></label>
        <label>Your Password: <input type="password" name="password"></label>
        <input type="submit" name="register" value="Register">
      </form>

      <p>Do you already have an account? <a href="login.php">Log in here!</a></p>
      <?php
    }
  ?>
</body>
</html>