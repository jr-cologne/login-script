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

  // login form submitted?
  if ($_POST['login'] == 'Log in') {
    // log in user and get response
    $response = login($pdo, $_POST['username'], $_POST['password']);

    // login was succesfully?
    if ($response['succes']) {
      // set user as logged in in session
      $_SESSION['logged_in'] = true;
    }
  }
?>

<!DOCTYPE html>
<html>
<head>
  <title>Restricted Area - Login</title>
  <meta charset="UTF-8">
</head>
<body>
  <h1>Restricted Area - Login</h1>
  <p><strong>Welcome to the the restricted area!</strong></p>

  <?php
    // display that only if the login failed or if the login form wasn't submitted
    if (!$response['succes']) {
      ?>
      <p>Please log in to get into the restricted area!</p>
      <?php
    }
    
    // if a response exists, display the message
    if (!empty($response)) {
      echo $response['msg'];
    }

    // display login form etc. if the login failed
    if (!$response['succes']) {
      ?>
      <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <label>Your Username: <input type="text" name="username" value="<?php if (!empty($_POST['username'])) { echo clean($_POST['username']); } ?>"></label>
        <label>Your Password: <input type="password" name="password"></label>
        <input type="submit" name="login" value="Log in">
      </form>

      <p>You don't already have an account? <a href="register.php">Register here!</a></p>
      <?php
    }
  ?>
</body>
</html>