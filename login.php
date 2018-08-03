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

  $controller->setPageItems([
    'title' => 'Login',
    'back_link_href' => 'index.php',
    'back_link_description' => 'Back to the homepage',
    'google_btn_description' => 'Log in with Google',
    'twitter_btn_description' => 'Log in with Twitter',
    'auth_type' => 'login'
  ]);

  $data = $controller->getResponseData(Session::get('login_data'));
  Session::delete('login_data');

  $errors = Session::get(Config::get('errors/session_name'));
  Session::delete(Config::get('errors/session_name'));
?>

<?php require_once 'partials/header.php'; ?>

    <main>
      <?php require_once 'components/back-link.php'; ?>

      <p>Please log in via username and password ...</p>

      <form action="" method="post" autocomplete="off">

        <?php echo !empty($errors['success']) ? '<div class="alert alert-success" role="alert"><p>' .  $errors['success'] . '</p></div>' : '' ?>

        <?php echo !empty($errors['failed']) ? '<div class="alert alert-danger" role="alert"><p>' .  $errors['failed'] . '</p></div>' : '' ?>

        <?php echo !empty($errors['username'][0]) ? '<div class="alert alert-danger" role="alert"><p>' . $errors['username'][0] . '</p></div>' : '' ?>
        <div class="form-group">
          <label for="username">Your Username:</label>
          <input type="text" name="username" id="username" value="<?php echo $data['username'] ?? ''; ?>" class="form-control">
        </div>

        <?php echo !empty($errors['password'][0]) ? '<div class="alert alert-danger" role="alert"><p>' . $errors['password'][0] . '</p></div>' : '' ?>
        <div class="form-group">
          <label for="password">Your Password:</label>
          <input type="password" name="password" id="password" class="form-control">
        </div>

        <input type="hidden" name="csrf_token" value="<?php echo CSRF::getToken(); ?>">

        <button type="submit" name="login" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Log in</button>
      </form>

      <p>... or choose one of the following services:</p>

      <?php require_once 'components/google-btn.php'; ?>

      <?php require_once 'components/twitter-btn.php'; ?>

      <p>You have forgotten your password? <a href="reset-password.php">Request a new one here!</a></p>

      <p>You don't have an account yet? <a href="register.php">Register here!</a></p>
    </main>

<?php require_once 'partials/footer.php'; ?>
