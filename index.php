<?php
	// start session
	session_start();

	// set user as logged out
	$logged_in = false;

	// logged in?
	if (!empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
		// set user as logged in
		$logged_in = true;
	}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Restricted Area</title>
  <meta charset="UTF-8">
</head>
<body>
  <h1>Restricted Area</h1>
  <p><strong>Welcome to the the restricted area!</strong></p>

	<?php
		// display message and logout button if user is logged in
		if ($logged_in) {
			?>
				<p><strong>You are in the restricted area! Congratulation!</strong></p>

				<a href="logout.php">Log out</a>
			<?php
		} else {	// user isn't logged in
			// display message for none registered or logged in users
			?>
			<p>You want to have access to it? Then just go ahead and <a href="login.php">log in</a> or <a href="register.php">register</a>, if you don't already have an account!</p>
			<?php
		}
	?>
</body>
</html>