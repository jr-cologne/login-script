<?php
  require_once 'app/init.php';

  use LoginScript\{
    Session\Session,
    Config\Config,
    CSRF\CSRF
  };

  $controller = $app->controller(':ResetPassword');

  $data = $controller->getResponseData(Session::get('password_reset_data'));
  Session::delete('password_reset_data');

  $errors = Session::get(Config::get('errors/session_name'));
  Session::delete(Config::get('errors/session_name'));
?>

<!DOCTYPE html>
<html>
<head>
  <title>Restricted Area - Reset Password</title>
  <meta charset="utf-8">
  <meta name="robots" content="noindex, follow">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
</head>
<body>
  <div class="container">
    <header>
      <h1>Restricted Area - Reset Password</h1>
      <h2>Welcome to the the restricted area!</h2>
    </header>

    <main>
      <a href="login.php">Back to the login page</a>

      <p>Enter your email in order to receive a new password via email.</p>

      <?php echo !empty($errors['success']) ? '<div class="alert alert-success" role="alert"><p class="mb-0">' .  $errors['success'] . '</p></div>' : '' ?>

      <?php echo !empty($errors['failed']) ? '<div class="alert alert-danger" role="alert"><p class="mb-0">' .  $errors['failed'] . '</p></div>' : '' ?>

      <form action="" method="post" autocomplete="off">

        <?php echo !empty($errors['failed']) ? '<div class="alert alert-danger" role="alert"><p class="mb-0">' .  $errors['failed'] . '</p></div>' : '' ?>

        <?php echo !empty($errors['email'][0]) ? '<div class="alert alert-danger" role="alert"><p class="mb-0">' . $errors['email'][0] . '</p></div>' : '' ?>
        <div class="form-group">
          <label for="email">Your email:</label>
          <input type="email" name="email" id="email" value="<?php echo $data['email'] ?? ''; ?>" class="form-control">
        </div>

        <input type="hidden" name="csrf_token" value="<?php echo CSRF::getToken(); ?>">

        <input type="submit" name="password_reset" value="Request new password" class="btn btn-primary">
      </form>
    </main>
  </div>
</body>
</html>
