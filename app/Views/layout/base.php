<?php
use App\Core\Security;
$config = require __DIR__ . '/../../../config/app.php';
$appName = $config['app_name'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= Security::e($title ?? $appName) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/main.css" />
  <script defer src="/assets/js/main.js"></script>
</head>
<body class="bg-main">
  <?php include __DIR__ . '/../shared/header.php'; ?>
  <main class="container">
    <?php if (!empty($flash_success)): ?>
      <div class="alert success"><?= Security::e($flash_success) ?></div>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
      <div class="alert error"><?= Security::e($flash_error) ?></div>
    <?php endif; ?>
    <?= $content ?? '' ?>
  </main>
  <?php include __DIR__ . '/../shared/footer.php'; ?>
</body>
</html>
