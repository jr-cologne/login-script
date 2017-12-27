<?php
  // set Google/Twitter auth type
  $auth_type = 'register';

  // require all files
  require_once 'includes/init.php';
  require_once 'includes/google/google.php';
  require_once 'includes/twitter/twitter.php';
  require_once 'includes/csrf.php';

  // user logged in?
  if (checkLogin() || google_checkLogin() || twitter_checkLogin()) {
    // redirect user to restricted area
    header('Location: index.php');
  }

  // set response
  $response = null;

  if (!empty($_SESSION['response'])) {
    $response = $_SESSION['response'];
    unset($_SESSION['response']);
  }

  // register form submitted?
	if ($_POST['register'] == 'Register') {
    // register user and get response
		$response = register($_POST['username'], $_POST['email'], $_POST['password']);
	} else if (!empty($_GET['code'])) {
    $response = google_register($_GET['code']);
  }
?>

<!DOCTYPE html>
<html>
<head>
  <title>Restricted Area - Register</title>
  <!-- Include Head -->
  <?php require_once 'includes/sections/head.html'; ?>
</head>
<body>
  <header>
    <h1>Restricted Area - Register</h1>
    <h2>Welcome to the the restricted area!</h2>
  </header>

  <main>
    <?php
      // display that only if the registration failed or if the registration form wasn't submitted
      if (!$response['success']) {
        ?>
        <p>Register for getting access to the restricted area!</p>
        <?php
      }

      // if a response exists, display the message
    	if (!empty($response)) {
        echo $response['msg'];
      }

      // was the registration successfully?
      if ($response['success']) {
        // display additional response
        echo ERR_HTML_START . 'Now go ahead and <a href="login.php">log in</a> to your account. Have fun!' . ERR_HTML_END;
      } else {  // registration failed
        // display registration form etc.
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
          <div class="field_wrap">
            <label for="username">Your Username:</label>
            <input type="text" name="username" id="username" value="<?php if (!empty($_POST['username'])) { echo clean($_POST['username']); } ?>">
          </div>
          <div class="field_wrap">
            <label for="email">Your Email:</label>
            <input type="text" name="email" id="email" value="<?php if (!empty($_POST['email'])) { echo clean($_POST['email']); } ?>">
          </div>
          <div class="field_wrap">
            <label for="password">Your Password:</label>
            <input type="password" name="password" id="password">
          </div>
          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?>">
          <input type="submit" name="register" value="Register">
        </form>

        <?php
          // not logged in with google?
          if (!google_checkLogin()) { // display google sign in button
            echo google_getSignUpButton();
          }

          // not logged in with twitter?
          if (!twitter_checkLogin()) {  // display twitter sign up button
            echo twitter_getSignUpButton();
          }
        ?>

        <p>Do you already have an account? <a href="login.php">Log in here!</a></p>
        <?php
      }
    ?>
  </main>

  <!-- Include Footer -->
  <?php require_once 'includes/sections/footer.html'; ?>

  <!-- Include Foot -->
  <?php require_once 'includes/sections/foot.html'; ?>
</body>
</html>
