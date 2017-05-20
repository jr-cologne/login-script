<?php
  // start session
  session_start();

  // require all files
  require_once('php/functions.php');
  require_once('php/google.php');

  // is user logged in?
  if (checkLogin()) {
    // log out user by unsetting logged_in session
    unset($_SESSION['logged_in']);
  } else if (google_checkLogin()) {
    google_logout();
  } else {
    // user isn't logged in, redirect user to login page
    header('Location: login.php');
  }
?>

<!DOCTYPE html>
<html>
<head>
  <title>Restricted Area</title>
  <meta charset="UTF-8">
</head>
<body>
	<header>
  	<h1>Restricted Area - Logout</h1>
		<!-- Include Head -->
	  <?php require_once 'includes/sections/head.html'; ?>
	</header>

	<main>
		<?php
			if (!checkLogin() && !google_checkLogin()) {
				?>
				<p><strong>You has been logged out successfully. See you!</strong></p>
				<?php
			} else {
				?>
				<p><strong>An error occured while trying to log you out. Just close the browser and you will also be logged out. See you!</strong></p>
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
