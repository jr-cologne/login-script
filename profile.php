<?php
  require_once 'app/init.php';

  use LoginScript\{
    Session\Session,
    Config\Config,
    CSRF\CSRF
  };

  $controller = $app->controller('profile');

  $controller->setPageItems([
    'title' => 'Profile',
    'back_link_href' => 'index.php',
    'back_link_description' => 'Back to the homepage'
  ]);

  $user_data = $controller->getResponseData(Session::get('user_data'));
  Session::delete('user_data');

  $data = $controller->getResponseData(Session::get('profile_data'));
  Session::delete('profile_data');

  $errors = Session::get(Config::get('errors/session_name'));
  Session::delete(Config::get('errors/session_name'));

  $google_init_password = $user_data['google_init_password'] ?? null;
  $twitter_init_password = $user_data['twitter_init_password'] ?? null;
?>

<?php require_once 'partials/header.php'; ?>

    <main>
      <?php require_once 'components/back-link.php'; ?>

      <p>Edit your profile settings below.</p>

      <?php if ($google_init_password): ?>
        <div class="alert alert-info">
          <p>Your password is still the initial one since you registered with Google, right? Then just fill in "google" into the old password field and enter your wished new password, so that you are able to login with your username and password as well.</p>
        </div>
      <?php endif; ?>

      <?php if ($twitter_init_password): ?>
        <div class="alert alert-info">
          <p>Your password is still the initial one since you registered with Twitter, right? Then just fill in "twitter" into the old password field and enter your wished new password, so that you are able to login with your username and password as well.</p>
        </div>
      <?php endif; ?>

      <form action="" method="post" autocomplete="off">
        <?php echo !empty($errors['success']) ? '<div class="alert alert-success" role="alert"><p>' .  $errors['success'] . '</p></div>' : '' ?>

        <?php echo !empty($errors['failed']) ? '<div class="alert alert-danger" role="alert"><p>' .  $errors['failed'] . '</p></div>' : '' ?>

        <?php echo !empty($errors['username'][0]) ? '<div class="alert alert-danger" role="alert"><p>' . $errors['username'][0] . '</p></div>' : '' ?>
        <div class="form-group">
          <label for="new_username">Username:</label>
          <input type="text" name="username" id="new_username" value="<?php echo $data['username'] ?? ''; ?>" placeholder="<?php echo $user_data['username'] ?? ''; ?>" class="form-control">
        </div>

        <?php echo !empty($errors['email'][0]) ? '<div class="alert alert-danger" role="alert"><p>' . $errors['email'][0] . '</p></div>' : '' ?>
        <div class="form-group">
          <label for="new_email">Email:</label>
          <input type="email" name="email" id="new_email" value="<?php echo $data['email'] ?? ''; ?>" placeholder="<?php echo $user_data['email'] ?? ''; ?>" class="form-control">
        </div>

        <?php echo !empty($errors['old_password'][0]) ? '<div class="alert alert-danger" role="alert"><p>' . $errors['old_password'][0] . '</p></div>' : '' ?>
        <div class="form-group">
          <label for="old_password">Old Password:</label>
          <input type="password" name="old_password" id="old_password" class="form-control">
        </div>

        <?php echo !empty($errors['new_password'][0]) ? '<div class="alert alert-danger" role="alert"><p>' . $errors['new_password'][0] . '</p></div>' : '' ?>
        <div class="form-group">
          <label for="new_password">New Password</label>
          <input type="password" name="new_password" id="new_password" class="form-control">
        </div>

        <input type="hidden" name="csrf_token" value="<?php echo CSRF::getToken(); ?>">

        <button type="submit" name="profile" class="btn btn-primary"><i class="fas fa-save"></i> Save changes</button>
      </form>

      <a href="delete.php" class="btn btn-danger mt-3"><i class="fas fa-trash"></i> Delete account</a>
    </main>

<?php require_once 'partials/footer.php'; ?>
