<?php
	session_start();

	// log out user by unsetting logged_in session
	unset($_SESSION['logged_in']);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Restricted Area</title>
  <meta charset="UTF-8">
</head>
<body>
  <h1>Restricted Area</h1>

	<?php
		if (empty($_SESSION['logged_in'])) {
			?>
			<p><strong>You has been logged out succesfully. See you!</strong></p>
			<?php
		} else {
			?>
			<p><strong>An error occured while trying to log you out. Just close the browser and you will also be logged out. See you!</strong></p>
			<?php
		}
	?>
</body>
</html>