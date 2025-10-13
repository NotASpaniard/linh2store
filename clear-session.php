<?php
/**
 * Clear session
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

session_start();
session_destroy();
session_start();

echo "<h1>Session Cleared</h1>";
echo "<p>Session đã được xóa. Bây giờ bạn có thể truy cập các trang đăng nhập/đăng ký.</p>";
echo "<a href='auth/dang-nhap.php'>Đăng nhập</a><br>";
echo "<a href='auth/dang-ky.php'>Đăng ký</a><br>";
echo "<a href='debug-session.php'>Debug Session</a><br>";
?>
