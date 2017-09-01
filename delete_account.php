<?php
  // require all files
  require_once('includes/init.php');
  require_once('includes/google/google.php');
  require_once('includes/csrf.php');

  // set user as logged out
  $logged_in = [ 'status' => false, 'user_id' => null ];
  // set response
  $response = null;

  // logged in?
  if (checkLogin()) {
    // set user as logged in
    $logged_in = [ 'status' => true, 'user_id' => $_SESSION['logged_in'] ];

    // delete_account form submitted?
    if ($_POST['delete_account'] == 'Delete account') {
      // delete account
      $response = deleteAccount($logged_in['user_id'], $_POST['password']);
    }
  } else if (google_checkLogin()) {
    // set user as logged in
    $logged_in = [ 'status' => true, 'user_id' => $_SESSION['logged_in'] ];

    // delete_account form submitted?
    if ($_POST['delete_account'] == 'Delete account') {
      // delete account
      $response = deleteAccount(getUserId($logged_in['user_id'], 'google_id'), $_POST['password']);

      if ($response['success']) {
        google_logout();
      }
    }
  } else {
    // user isn't logged in, redirect user
    header('Location: usercp.php');
  }
?>

<!DOCTYPE html>
<html>
<head>
  <title>Restricted Area - User Control Panel</title>
  <!-- Include Head -->
  <?php require_once 'includes/sections/head.html'; ?>
</head>
<body id="usercp">
  <header>
    <h1>Restricted Area - User Control Panel</h1>
    <h2>Welcome to the the restricted area!</h2>
  </header>

  <main>
    
    <?php
      if (!empty($response['msg'])) {
        echo $response['msg'];
      }
    ?>

    <a href="usercp.php">Back to the user control panel</a>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
      <div class="field_wrap">
        <label for="password">Password:</label>
        <input type="password" name="password" id="password">
      </div>
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?>">
      <input type="submit" name="delete_account" value="Delete account">
    </form>
  </main>

  <!-- Include Footer -->
  <?php require_once 'includes/sections/footer.html'; ?>

  <!-- Include Foot -->
  <?php require_once 'includes/sections/foot.html'; ?>
</body>
</html>
