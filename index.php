<?php
  require_once 'app/init.php';

  use LoginScript\{
    Session\Session,
    Config\Config
  };

  $controller = $app->controller('home');

  $controller->setPageItem('title', 'Home');

  $guest = $controller->guest();

  if ($guest) {
    $errors = Session::get(Config::get('errors/session_name'));
    Session::delete(Config::get('errors/session_name'));
  }

  if (!$guest) {
    $user_data = $controller->getResponseData(Session::get('user_data'));
    Session::delete('user_data');
  }
?>

<?php require_once 'partials/header.php'; ?>

    <main>
      <?php if (!$guest): ?>
        <p><strong>You are in the restricted area! Congratulations!</strong></p>

        <p>You are logged in as:</p>

        <?php if (!empty($user_data['username']) && !empty($user_data['email'])): ?>
          <div class="jumbotron">
            <ul class="list-group mb-3">
              <li class="list-group-item"><i class="fas fa-user"></i> <?php echo $user_data['username']; ?></li>
              <li class="list-group-item"><i class="fas fa-at"></i> <?php echo $user_data['email']; ?></li>
            </ul>

            <a href="profile.php" class="btn btn-primary"><i class="fas fa-pen"></i> Edit profile</a>
          </div>
        <?php endif; ?>

        <a href="logout.php" class="btn btn-secondary"><i class="fas fa-sign-out-alt"></i> Log out</a>
      <?php else: ?>
        <?php echo !empty($errors['success']) ? '<div class="alert alert-success" role="alert"><p>' .  $errors['success'] . '</p></div>' : '' ?>

        <p>You want to have access to the restricted area?<p>
        <p>Then just go ahead and <a href="login.php">log in to your account</a> or <a href="register.php">register</a> if you don't already have an account!</p>
      <?php endif; ?>
    </main>

<?php require_once 'partials/footer.php'; ?>
