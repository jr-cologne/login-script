<?php
	// start session
	session_start();

	// require all other files
	require_once 'php/config.php';
	require_once 'php/db.php';
	require_once 'php/functions.php';

	// set user as logged out
	$logged_in = [ 'status' => false, 'user_id' => null ];

	// logged in?
	if (checkLogin()) {
		// set user as logged in
		$logged_in = [ 'status' => true, 'user_id' => $_SESSION['logged_in'] ];

		// get username and email of logged in user
		$user_data = getUserData($logged_in['user_id'], [ 'username', 'email' ]);
	}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Restricted Area</title>
	<!-- Include Head -->
  <?php require_once 'includes/sections/head.html'; ?>
</head>
<body>
	<header>
    <h1>Restricted Area</h1>
    <h2>Welcome to the the restricted area!</h2>
  </header>

	<main>
		<?php
			// display message and logout button if user is logged in
			if ($logged_in['status']) {
				?>
					<p><strong>You are in the restricted area! Congratulation!</strong></p>

					<p>You are logged in as:</p>
					
					<?php // also display user information, if we have any
						if ( !empty($user_data['username']) && !empty($user_data['email']) ) {
					?>
						<div id="user_info">
							<ul>
								<li><?php echo clean($user_data['username']); ?></li>
								<li><?php echo clean($user_data['email'], 'email'); ?></li>
							</ul>
							
							<a href="usercp.php">Edit Profile</a>
						</div>
					<?php
						}
					?>

					<a href="logout.php">Log out</a>

				<?php
			} else {	// user isn't logged in
				// display message for none registered or logged in users
				?>
				<p>You want to have access to it? Then just go ahead and <a href="login.php">log in</a> or <a href="register.php">register</a>, if you don't already have an account!</p>
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
