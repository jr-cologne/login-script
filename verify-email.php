<?php
  require_once 'php/config.php';
  require_once 'php/db.php';
  require_once 'php/functions.php';

  if (empty($_GET['token']) && empty($_GET['resend'])) {
    header('Location: index.php');
  }

  // resend mail
  if ($_GET['resend'] == 'true' && !empty($_GET['id'])) {
    $response = resendVerificationMail($_GET['id']);
  }

  // verify email
  if (!empty($_GET['token']) && !empty($_GET['email'])) {
    $response = verifyEmail($_GET['token'], $_GET['email']);
  }
?>

<!DOCTYPE html>
<html>
<head>
  <title>Restricted Area - Verify your Email</title>
  <!-- Include Head -->
  <?php require_once 'includes/sections/head.html'; ?>
</head>
<body>
  <header>
    <h1>Restricted Area - Verify your Email</h1>
    <h2>Welcome to the the restricted area!</h2>
  </header>

  <main>
    <a href="register.php">Back to the registration page</a>

    <?php
      if (!empty($response['msg'])) {
        echo $response['msg'];
      }
    ?>
  </main>

  <!-- Include Footer -->
  <?php require_once 'includes/sections/footer.html'; ?>

  <!-- Include Foot -->
  <?php require_once 'includes/sections/foot.html'; ?>
</body>
</html>