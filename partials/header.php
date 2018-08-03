<!DOCTYPE html>
<html>
<head>
  <title>Restricted Area - <?php echo $controller->getPageItem('title'); ?></title>
  <meta charset="utf-8">
  <meta name="robots" content="noindex, follow">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
  <header>
    <nav class="navbar navbar-light bg-light">
      <h1 class="navbar-brand"><a href="index.php"><i class="fas fa-lock"></i> Restricted Area</a> - <?php echo $controller->getPageItem('title'); ?></h1>
    </nav>
  </header>

  <div class="container py-5">
