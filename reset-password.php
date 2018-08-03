<?php
  require_once 'app/init.php';

  use LoginScript\{
    Session\Session,
    Config\Config,
    CSRF\CSRF
  };

  $controller = $app->controller(':ResetPassword');

  $controller->setPageItems([
    'title' => 'Reset Password',
    'back_link_href' => 'login.php',
    'back_link_description' => 'Back to the login page'
  ]);

  $data = $controller->getResponseData(Session::get('password_reset_data'));
  Session::delete('password_reset_data');

  $errors = Session::get(Config::get('errors/session_name'));
  Session::delete(Config::get('errors/session_name'));
?>

<?php require_once 'partials/header.php'; ?>

    <main>
      <?php require_once 'components/back-link.php'; ?>

      <p>Enter your email in order to receive a new password via email.</p>

      <?php echo !empty($errors['success']) ? '<div class="alert alert-success" role="alert"><p>' .  $errors['success'] . '</p></div>' : '' ?>

      <?php echo !empty($errors['failed']) ? '<div class="alert alert-danger" role="alert"><p>' .  $errors['failed'] . '</p></div>' : '' ?>

      <form action="" method="post" autocomplete="off">

        <?php echo !empty($errors['failed']) ? '<div class="alert alert-danger" role="alert"><p>' .  $errors['failed'] . '</p></div>' : '' ?>

        <?php echo !empty($errors['email'][0]) ? '<div class="alert alert-danger" role="alert"><p>' . $errors['email'][0] . '</p></div>' : '' ?>
        <div class="form-group">
          <label for="email">Your email:</label>
          <input type="email" name="email" id="email" value="<?php echo $data['email'] ?? ''; ?>" class="form-control">
        </div>

        <input type="hidden" name="csrf_token" value="<?php echo CSRF::getToken(); ?>">

        <button type="submit" name="password_reset" class="btn btn-primary"><i class="fas fa-key"></i> Request new password</button>
      </form>
    </main>

<?php require_once 'partials/footer.php'; ?>
