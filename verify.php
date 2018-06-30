<?php
  require_once 'app/init.php';

  use LoginScript\{
    Session\Session,
    Config\Config
  };

  $controller = $app->controller('verify');

  $errors = Session::get(Config::get('errors/session_name'));
  Session::delete(Config::get('errors/session_name'));
?>

<!DOCTYPE html>
<html>
<head>
  <title>Restricted Area - Verify Email</title>
  <meta charset="utf-8">
  <meta name="robots" content="noindex, follow">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
</head>
<body>
  <div class="container">
    <header>
      <h1>Restricted Area - Verify Email</h1>
      <h2>Welcome to the the restricted area!</h2>
    </header>

    <main>
      <a href="index.php">Back to the homepage</a>

      <?php echo !empty($errors['success']) ? '<div class="alert alert-success" role="alert"><p class="mb-0">' .  $errors['success'] . '</p></div>' : '' ?>

      <?php echo !empty($errors['failed']) ? '<div class="alert alert-danger" role="alert"><p class="mb-0">' .  $errors['failed'] . '</p></div>' : '' ?>
    </main>
  </div>
</body>
</html>
