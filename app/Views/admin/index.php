<?php
ob_start();
?>
<h1 style="font-family: 'Playfair Display', serif;">Admin Dashboard</h1>
<ul>
  <li><a href="/admin/san-pham">Quản lý sản phẩm</a></li>
  <li>Quản lý đơn hàng (Đang bảo trì)</li>
  <li>Quản lý người dùng (Đang bảo trì)</li>
  <li>Thống kê (Đang bảo trì)</li>
</ul>
<?php
$content = ob_get_clean();
$title = 'Admin - Linh2Store';
include __DIR__ . '/../layout/base.php';
