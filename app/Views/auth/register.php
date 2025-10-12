<?php
use App\Core\CSRF;
use App\Core\Security;
ob_start();
?>
<h1 style="font-family: 'Playfair Display', serif;">Đăng ký</h1>
<?php if (!empty($error)): ?>
  <div class="alert error"><?= Security::e($error) ?></div>
<?php endif; ?>
<form method="post" action="/dang-ky" class="card" style="background:#fff;padding:16px;border-radius:12px;max-width:420px;">
  <input type="hidden" name="_csrf" value="<?= CSRF::token() ?>">
  <div style="margin-bottom:12px;">
    <label>Họ tên</label>
    <input required type="text" name="name" style="width:100%;padding:10px;border-radius:8px;border:1px solid #ddd;">
  </div>
  <div style="margin-bottom:12px;">
    <label>Email</label>
    <input required type="email" name="email" style="width:100%;padding:10px;border-radius:8px;border:1px solid #ddd;">
  </div>
  <div style="margin-bottom:12px;">
    <label>Mật khẩu</label>
    <input required minlength="6" type="password" name="password" style="width:100%;padding:10px;border-radius:8px;border:1px solid #ddd;">
  </div>
  <button type="submit" class="btn-cta">Tạo tài khoản</button>
</form>
<p>Đã có tài khoản? <a href="/dang-nhap">Đăng nhập</a></p>
<?php
$content = ob_get_clean();
$title = 'Đăng ký - Linh2Store';
include __DIR__ . '/../layout/base.php';
