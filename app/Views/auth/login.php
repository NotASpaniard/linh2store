<?php
use App\Core\CSRF;
use App\Core\Security;
ob_start();
?>
<h1 style="font-family: 'Playfair Display', serif;">Đăng nhập</h1>
<?php if (!empty($error)): ?>
  <div class="alert error"><?= Security::e($error) ?></div>
<?php endif; ?>
<form method="post" action="/dang-nhap" class="card" style="background:#fff;padding:16px;border-radius:12px;max-width:420px;">
  <input type="hidden" name="_csrf" value="<?= CSRF::token() ?>">
  <div style="margin-bottom:12px;">
    <label>Email</label>
    <input required type="email" name="email" style="width:100%;padding:10px;border-radius:8px;border:1px solid #ddd;">
  </div>
  <div style="margin-bottom:12px;">
    <label>Mật khẩu</label>
    <input required type="password" name="password" style="width:100%;padding:10px;border-radius:8px;border:1px solid #ddd;">
  </div>
  <button type="submit" class="btn-cta">Đăng nhập</button>
</form>
<p>Chưa có tài khoản? <a href="/dang-ky">Đăng ký</a></p>

<h3>Demo nhanh</h3>
<p>Nếu chưa tạo user trong DB, đăng ký tài khoản mới ở trang Đăng ký.</p>
<?php
$content = ob_get_clean();
$title = 'Đăng nhập - Linh2Store';
include __DIR__ . '/../layout/base.php';
