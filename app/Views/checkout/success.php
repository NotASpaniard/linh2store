<?php
ob_start();
?>
<h1 style="font-family: 'Playfair Display', serif;">Đặt hàng thành công</h1>
<p>Cảm ơn bạn. Đơn hàng của bạn đang được xử lý.</p>
<a href="/"><button class="btn-cta">Về trang chủ</button></a>
<?php
$content = ob_get_clean();
$title = 'Đặt hàng thành công - Linh2Store';
include __DIR__ . '/../layout/base.php';
