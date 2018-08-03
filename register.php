<?php
  require_once 'app/init.php';

  use LoginScript\{
    Session\Session,
    Config\Config,
    CSRF\CSRF,
    Google\Auth\GoogleAuth,
    Twitter\Auth\TwitterAuth
  };

  $controller = $app->controller('register');

  $controller->setPageItems([
    'title' => 'Register',
    'back_link_href' => 'index.php',
    'back_link_description' => 'Back to the homepage',
    'google_btn_description' => 'Register with Google',
    'twitter_btn_description' => 'Register with Twitter',
    'auth_type' => 'register'
  ]);

  $data = $controller->getResponseData(Session::get('register_data'));
  Session::delete('register_data');

  $errors = Session::get(Config::get('errors/session_name'));
  Session::delete(Config::get('errors/session_name'));
?>

<?php require_once 'partials/header.php'; ?>

    <main>
      <?php require_once 'components/back-link.php'; ?>

      <p>Please register via username, email and password ...</p>

      <form action="" method="post" autocomplete="off">

        <?php echo !empty($errors['success']) ? '<div class="alert alert-success" role="alert"><p>' .  $errors['success'] . '</p></div>' : '' ?>

        <?php echo !empty($errors['failed']) ? '<div class="alert alert-danger" role="alert"><p>' .  $errors['failed'] . '</p></div>' : '' ?>

        <?php echo !empty($errors['username'][0]) ? '<div class="alert alert-danger" role="alert"><p>' . $errors['username'][0] . '</p></div>' : '' ?>
        <div class="form-group">
          <label for="username">Your Username:</label>
          <input type="text" name="username" id="username" value="<?php echo $data['username'] ?? ''; ?>" class="form-control">
        </div>

        <?php echo !empty($errors['email'][0]) ? '<div class="alert alert-danger" role="alert"><p>' . $errors['email'][0] . '</p></div>' : '' ?>
        <div class="form-group">
          <label for="email">Your Email:</label>
          <input type="email" name="email" id="email" value="<?php echo $data['email'] ?? ''; ?>" class="form-control">
        </div>

        <?php echo !empty($errors['password'][0]) ? '<div class="alert alert-danger" role="alert"><p>' . $errors['password'][0] . '</p></div>' : '' ?>
        <div class="form-group">
          <label for="password">Your Password:</label>
          <input type="password" name="password" id="password" class="form-control">
        </div>

        <input type="hidden" name="csrf_token" value="<?php echo CSRF::getToken(); ?>">

        <button type="submit" name="register" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Register</button>
      </form>

      <p>... or choose one of the following services:</p>

      <?php require_once 'components/google-btn.php'; ?>

      <?php require_once 'components/twitter-btn.php'; ?>

      <p>You already have an account? <a href="login.php">Log in here!</a></p>
    </main>

<?php require_once 'partials/footer.php'; ?>
