<?php
  require_once 'app/init.php';

  use LoginScript\{
    Session\Session,
    Config\Config,
    CSRF\CSRF,
    Google\Auth\GoogleAuth,
    Twitter\Auth\TwitterAuth
  };

  $controller = $app->controller('login');

  $data = $controller->getResponseData(Session::get('login_data'));
  Session::delete('login_data');

  $errors = Session::get(Config::get('errors/session_name'));
  Session::delete(Config::get('errors/session_name'));
?>

<!DOCTYPE html>
<html>
<head>
  <title>Restricted Area - Login</title>
  <meta charset="utf-8">
  <meta name="robots" content="noindex, follow">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
</head>
<body>
  <div class="container">
    <header>
      <h1>Restricted Area - Login</h1>
      <h2>Welcome to the the restricted area!</h2>
    </header>

    <main>
      <a href="index.php">Back to the homepage</a>

      <p>Please log in via username and password ...</p>

      <form action="" method="post" autocomplete="off">

        <?php echo !empty($errors['success']) ? '<div class="alert alert-success" role="alert"><p class="mb-0">' .  $errors['success'] . '</p></div>' : '' ?>

        <?php echo !empty($errors['failed']) ? '<div class="alert alert-danger" role="alert"><p class="mb-0">' .  $errors['failed'] . '</p></div>' : '' ?>

        <?php echo !empty($errors['username'][0]) ? '<div class="alert alert-danger" role="alert"><p class="mb-0">' . $errors['username'][0] . '</p></div>' : '' ?>
        <div class="form-group">
          <label for="username">Your Username:</label>
          <input type="text" name="username" id="username" value="<?php echo $data['username'] ?? ''; ?>" class="form-control">
        </div>

        <?php echo !empty($errors['password'][0]) ? '<div class="alert alert-danger" role="alert"><p class="mb-0">' . $errors['password'][0] . '</p></div>' : '' ?>
        <div class="form-group">
          <label for="password">Your Password:</label>
          <input type="password" name="password" id="password" class="form-control">
        </div>

        <input type="hidden" name="csrf_token" value="<?php echo CSRF::getToken(); ?>">

        <input type="submit" name="login" value="Log in" class="btn btn-primary">
      </form>

      <p>... or choose one of the following services:</p>

      <a href="<?php echo GoogleAuth::getAuthUrl(); ?>" class="btn">Log in with Google</a>

      <a href="<?php echo TwitterAuth::getAuthUrl(); ?>" class="btn">Log in with Twitter</a>

      <p>You have forgotten your password? <a href="reset-password.php">Request a new one here!</a></p>

      <p>You don't have an account yet? <a href="register.php">Register here!</a></p>
    </main>
  </div>
</body>
</html>
