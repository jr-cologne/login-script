<?php
  require_once 'app/init.php';

  use LoginScript\{
    Session\Session,
    Config\Config,
    CSRF\CSRF
  };

  $controller = $app->controller('delete');

  $errors = Session::get(Config::get('errors/session_name'));
  Session::delete(Config::get('errors/session_name'));
?>

<!DOCTYPE html>
<html>
<head>
  <title>Restricted Area - Delete Account</title>
  <meta charset="utf-8">
  <meta name="robots" content="noindex, follow">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
</head>
<body>
  <div class="container">
    <header>
      <h1>Restricted Area - Delete Account</h1>
      <h2>Welcome to the the restricted area!</h2>
    </header>

    <main>
      <a href="profile.php">Back to the profile</a>

      <form action="" method="post" autocomplete="off">

        <?php echo !empty($errors['failed']) ? '<div class="alert alert-danger" role="alert"><p class="mb-0">' .  $errors['failed'] . '</p></div>' : '' ?>

        <?php echo !empty($errors['password'][0]) ? '<div class="alert alert-danger" role="alert"><p class="mb-0">' . $errors['password'][0] . '</p></div>' : '' ?>
        <div class="form-group">
          <label for="password">Your Password:</label>
          <input type="password" name="password" id="password" class="form-control">
        </div>

        <input type="hidden" name="csrf_token" value="<?php echo CSRF::getToken(); ?>">

        <input type="submit" name="delete" value="Delete account" class="btn btn-primary">
      </form>
    </main>
  </div>
</body>
</html>
