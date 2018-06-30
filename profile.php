<?php
  require_once 'app/init.php';

  use LoginScript\{
    Session\Session,
    Config\Config,
    CSRF\CSRF
  };

  $controller = $app->controller('profile');

  $user_data = $controller->getResponseData(Session::get('user_data'));
  Session::delete('user_data');

  $data = $controller->getResponseData(Session::get('profile_data'));
  Session::delete('profile_data');

  $errors = Session::get(Config::get('errors/session_name'));
  Session::delete(Config::get('errors/session_name'));

  $google_init_password = $user_data['google_init_password'] ?? null;
  $twitter_init_password = $user_data['twitter_init_password'] ?? null;
?>

<!DOCTYPE html>
<html>
<head>
  <title>Restricted Area - Profile</title>
  <meta charset="utf-8">
  <meta name="robots" content="noindex, follow">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
</head>
<body>
  <div class="container">
    <header>
      <h1>Restricted Area - User Control Panel</h1>
      <h2>Welcome to the the restricted area!</h2>
    </header>

    <main>
      <a href="index.php">Back to the homepage</a>

      <?php if ($google_init_password): ?>
        <div class="alert alert-info">
          <p class="mb-0">Your password is still the initial one since you registered with Google, right? Then just fill in "google" into the old password field and enter your wished new password, so that you are able to login with your username and password as well.</p>
        </div>
      <?php endif; ?>

      <?php if ($twitter_init_password): ?>
        <div class="alert alert-info">
          <p class="mb-0">Your password is still the initial one since you registered with Twitter, right? Then just fill in "twitter" into the old password field and enter your wished new password, so that you are able to login with your username and password as well.</p>
        </div>
      <?php endif; ?>

      <form action="" method="post" autocomplete="off">
        <?php echo !empty($errors['success']) ? '<div class="alert alert-success" role="alert"><p class="mb-0">' .  $errors['success'] . '</p></div>' : '' ?>

        <?php echo !empty($errors['failed']) ? '<div class="alert alert-danger" role="alert"><p class="mb-0">' .  $errors['failed'] . '</p></div>' : '' ?>

        <?php echo !empty($errors['username'][0]) ? '<div class="alert alert-danger" role="alert"><p class="mb-0">' . $errors['username'][0] . '</p></div>' : '' ?>
        <div class="form-group">
          <label for="new_username">Username:</label>
          <input type="text" name="username" id="new_username" value="<?php echo $data['username'] ?? ''; ?>" placeholder="<?php echo $user_data['username'] ?? ''; ?>" class="form-control">
        </div>

        <?php echo !empty($errors['email'][0]) ? '<div class="alert alert-danger" role="alert"><p class="mb-0">' . $errors['email'][0] . '</p></div>' : '' ?>
        <div class="form-group">
          <label for="new_email">Email:</label>
          <input type="email" name="email" id="new_email" value="<?php echo $data['email'] ?? ''; ?>" placeholder="<?php echo $user_data['email'] ?? ''; ?>" class="form-control">
        </div>

        <?php echo !empty($errors['old_password'][0]) ? '<div class="alert alert-danger" role="alert"><p class="mb-0">' . $errors['old_password'][0] . '</p></div>' : '' ?>
        <div class="form-group">
          <label for="old_password">Old Password:</label>
          <input type="password" name="old_password" id="old_password" class="form-control">
        </div>

        <?php echo !empty($errors['new_password'][0]) ? '<div class="alert alert-danger" role="alert"><p class="mb-0">' . $errors['new_password'][0] . '</p></div>' : '' ?>
        <div class="form-group">
          <label for="new_password">New Password</label>
          <input type="password" name="new_password" id="new_password" class="form-control">
        </div>

        <input type="hidden" name="csrf_token" value="<?php echo CSRF::getToken(); ?>">

        <input type="submit" name="profile" value="Save changes" class="btn btn-primary">
      </form>

      <a href="delete.php">Delete account</a>
    </main>
  </div>
</body>
</html>
