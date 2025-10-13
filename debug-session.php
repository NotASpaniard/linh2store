<?php
/**
 * Debug session
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/session.php';

echo "<h1>Debug Session</h1>";
echo "<h2>Session Data:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>isLoggedIn():</h2>";
echo isLoggedIn() ? "TRUE" : "FALSE";

echo "<h2>getCurrentUser():</h2>";
$user = getCurrentUser();
if ($user) {
    echo "<pre>";
    print_r($user);
    echo "</pre>";
} else {
    echo "NULL";
}

echo "<h2>Session ID:</h2>";
echo session_id();

echo "<h2>Session Status:</h2>";
echo session_status();

echo "<h2>Links:</h2>";
echo "<a href='auth/dang-nhap.php'>Đăng nhập</a><br>";
echo "<a href='auth/dang-ky.php'>Đăng ký</a><br>";
echo "<a href='admin/'>Admin</a><br>";
echo "<a href='user/'>User</a><br>";
echo "<a href='auth/dang-xuat.php'>Đăng xuất</a><br>";
?>
