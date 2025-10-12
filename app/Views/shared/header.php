<?php
use App\Core\Auth;
use App\Core\Security;
?>
<header class="site-header">
  <div class="container header-inner">
    <a class="logo" href="/">Linh2Store</a>
    <nav class="nav">
      <a href="/">Trang chủ</a>
      <a href="/san-pham">Sản phẩm</a>
      <a href="/gio-hang">Giỏ hàng</a>
      <a href="/yeu-thich">Yêu thích</a>
      <?php if (Auth::check()): $user = Auth::user(); ?>
        <a href="/tai-khoan">User Dashboard</a>
        <?php if (($user['role'] ?? 'user') === 'admin'): ?>
          <a href="/admin">Admin</a>
        <?php endif; ?>
        <form action="/dang-xuat" method="post" class="inline">
          <button class="btn-link" type="submit">Đăng xuất (<?= Security::e($user['name']) ?>)</button>
        </form>
      <?php else: ?>
        <a href="/dang-nhap">Đăng nhập</a>
        <a class="btn cta" href="/dang-ky">Đăng ký</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
