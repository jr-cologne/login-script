<?php
  require_once 'app/init.php';

  use LoginScript\{
    Session\Session,
    Config\Config
  };

  $controller = $app->controller('verify');

  $controller->setPageItem([
    'title' => 'Verify Email',
    'back_link_href' => 'index.php',
    'back_link_description' => 'Back to the homepage'
  ]);

  $errors = Session::get(Config::get('errors/session_name'));
  Session::delete(Config::get('errors/session_name'));
?>

<?php require_once 'partials/header.php'; ?>

    <main>
      <?php require_once 'components/back-link.php'; ?>

      <?php echo !empty($errors['success']) ? '<div class="alert alert-success" role="alert"><p>' .  $errors['success'] . '</p></div>' : '' ?>

      <?php echo !empty($errors['failed']) ? '<div class="alert alert-danger" role="alert"><p>' .  $errors['failed'] . '</p></div>' : '' ?>
    </main>

<?php require_once 'partials/footer.php'; ?>
