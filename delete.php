<?php
  require_once 'app/init.php';

  use LoginScript\{
    Session\Session,
    Config\Config,
    CSRF\CSRF
  };

  $controller = $app->controller('delete');

  $controller->setPageItems([
    'title' => 'Delete Account',
    'back_link_href' => 'profile.php',
    'back_link_description' => 'Back to the profile'
  ]);

  $errors = Session::get(Config::get('errors/session_name'));
  Session::delete(Config::get('errors/session_name'));
?>

<?php require_once 'partials/header.php'; ?>

    <main>
      <?php require_once 'components/back-link.php'; ?>

      <p>Enter your password below to confirm that you want to delete your account.</p>

      <form action="" method="post" autocomplete="off">

        <?php echo !empty($errors['failed']) ? '<div class="alert alert-danger" role="alert"><p>' .  $errors['failed'] . '</p></div>' : '' ?>

        <?php echo !empty($errors['password'][0]) ? '<div class="alert alert-danger" role="alert"><p>' . $errors['password'][0] . '</p></div>' : '' ?>
        <div class="form-group">
          <label for="password">Your Password:</label>
          <input type="password" name="password" id="password" class="form-control">
        </div>

        <input type="hidden" name="csrf_token" value="<?php echo CSRF::getToken(); ?>">

        <input type="submit" name="delete" value="Delete account" class="btn btn-primary">
      </form>
    </main>

<?php require_once 'partials/footer.php'; ?>
