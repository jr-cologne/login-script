<?php
  require_once 'app/init.php';

  use LoginScript\{
    Session\Session,
    Config\Config
  };

  $controller = $app->controller('home');

  $guest = $controller->guest();

  if ($guest) {
    $errors = Session::get(Config::get('errors/session_name'));
    Session::delete(Config::get('errors/session_name'));
  }

  if (!$guest) {
    $user_data = $controller->getResponseData(Session::get('user_data'));
    Session::delete('user_data');
  }
?>

<!DOCTYPE html>
<html>
<head>
  <title>Restricted Area - Home</title>
  <meta charset="utf-8">
  <meta name="robots" content="noindex, follow">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
</head>
<body>
  <div class="container">
    <header>
      <h1>Restricted Area - Home</h1>
      <h2>Welcome to the the restricted area!</h2>
    </header>

    <main>
      <?php if (!$guest): ?>
        <p><strong>You are in the restricted area! Congratulations!</strong></p>

        <p>You are logged in as:</p>

        <?php if (!empty($user_data['username']) && !empty($user_data['email'])): ?>
          <div id="user_info">
            <ul>
              <li><?php echo $user_data['username']; ?></li>
              <li><?php echo $user_data['email']; ?></li>
            </ul>
            
            <a href="profile.php">Edit profile</a>
          </div>
        <?php endif; ?>

        <a href="logout.php">Log out</a>
      <?php else: ?>
        <?php echo !empty($errors['success']) ? '<div class="alert alert-success" role="alert"><p class="mb-0">' .  $errors['success'] . '</p></div>' : '' ?>

        <p>You want to have access to the restricted area?<p>
        <p>Then just go ahead and <a href="login.php">log in to your account</a> or <a href="register.php">register</a> if you don't already have an account!</p>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
