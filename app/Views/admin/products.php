<?php
ob_start();
?>
<h1 style="font-family: 'Playfair Display', serif;">Quản lý sản phẩm</h1>
<p>CRUD đầy đủ: Đang bảo trì. Sẽ kết nối DB sau.</p>
<?php
$content = ob_get_clean();
$title = 'Quản lý sản phẩm - Linh2Store';
include __DIR__ . '/../layout/base.php';
