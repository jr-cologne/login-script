<?php
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

  // login form submitted?
  if ($_POST['login'] == 'Log in') {
    // log in user and get response
    $response = login($_POST['username'], $_POST['password']);

    // login was successfully?
    if ($response['success'] && is_int($response['user_id'])) {
      // set user as logged in in session
      $_SESSION['logged_in'] = $response['user_id'];
    }
  } else if (!empty($_GET['code'])) {
    $response = google_login($_GET['code']);
  }
?>

<!DOCTYPE html>
<html>
<head>
  <title>Restricted Area - Login</title>
  <!-- Include Head -->
  <?php require_once 'includes/sections/head.html'; ?>
</head>
<body>
  <header>
    <h1>Restricted Area - Login</h1>
    <h2>Welcome to the the restricted area!</h2>
  </header>

  <main>
    <?php
      // display that only if the login failed or if the login form wasn't submitted
      if (!$response['success']) {
        ?>
        <p>Please log in to get into the restricted area!</p>
        <?php
      }

      // if a response exists, display the message
      if (!empty($response)) {
        echo $response['msg'];
      }

      // display login form etc. if the login failed
      if (!$response['success']) {
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
          <div class="field_wrap">
            <label for="username">Your Username:</label>
            <input type="text" name="username" id="username" value="<?php if (!empty($_POST['username'])) { echo clean($_POST['username']); } ?>">
          </div>
          <div class="field_wrap">
            <label for="password">Your Password:</label>
            <input type="password" name="password" id="password">
          </div>
          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?>">
          <input type="submit" name="login" value="Log in">
        </form>

        <?php
          // not logged in with google?
          if (!google_checkLogin()) { // display google sign in button
            echo google_getSignInButton();
          }

          // not logged in with twitter?
          if (!twitter_checkLogin()) {  // display twitter sign in button
            echo twitter_getSignInButton();
          }
        ?>
        
        <p>You don't already have an account? <a href="register.php">Register here!</a></p>
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
