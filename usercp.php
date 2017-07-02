<?php
  // require all other files
  require_once('php/config.php');
  require_once('includes/session.php');
  require_once('includes/db.php');
  require_once('php/functions.php');
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

    // get username and email of logged in user
    $user_data = getUserData($logged_in['user_id'], [ 'username', 'email' ]);

    // update_profile form submitted?
    if ($_POST['update_profile'] == 'Save changes') {
      // update profile and get response
      $response = updateProfile($logged_in['user_id'], $user_data['username'], $user_data['email'], $_POST['new_username'], $_POST['new_email'], $_POST['old_password'], $_POST['new_password']);
    }
  } else if (google_checkLogin()) {
    // set user as logged in
    $logged_in = [ 'status' => true, 'user_id' => $_SESSION['logged_in'] ];

    // get username and email of logged in user
    $user_data = getUserData($logged_in['user_id'], [ 'username', 'email' ], 'google');

    // update_profile form submitted?
    if ($_POST['update_profile'] == 'Save changes') {
      // update profile and get response
      $response = updateProfile(getUserId($logged_in['user_id'], 'google_id'), $user_data['username'], $user_data['email'], $_POST['new_username'], $_POST['new_email'], $_POST['old_password'], $_POST['new_password']);
    }
  } else {
    // user isn't logged in, redirect user
    header('Location: index.php');
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

    <a href="index.php">Back to the homepage</a>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
      <div class="field_wrap">
        <label for="new_username">New Username:</label>
        <input type="text" name="new_username" id="new_username" value="<?php if (!empty($_POST['new_username'])) { echo clean($_POST['new_username']); } ?>" placeholder="<?php if (!empty($user_data['username'])) { echo clean($user_data['username']); } ?>">
      </div>
      <div class="field_wrap">
        <label for="new_email">New Email:</label>
        <input type="email" name="new_email" id="new_email" value="<?php if (!empty($_POST['new_email'])) { echo clean($_POST['new_email']); } ?>" placeholder="<?php if (!empty($user_data['email'])) { echo clean($user_data['email']); } ?>">
      </div>
      <div class="field_wrap">
        <label for="old_password">Old Password:</label>
        <input type="password" name="old_password" id="old_password">
      </div>
      <div class="field_wrap">
        <label for="new_password">New Password</label>
        <input type="password" name="new_password" id="new_password" >
      </div>
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?>">
      <input type="submit" name="update_profile" value="Save changes">
    </form>

    <a href="delete_account.php">Delete Account</a>
  </main>

  <!-- Include Footer -->
  <?php require_once 'includes/sections/footer.html'; ?>

  <!-- Include Foot -->
  <?php require_once 'includes/sections/foot.html'; ?>
</body>
</html>
