<?php
/**
 * Clear JWT tokens
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/jwt.php';

// Clear JWT token cookie
JWT::clearTokenCookie();

// Clear any other auth cookies
setcookie('auth_token', '', time() - 3600, '/', '', false, true);
setcookie('csrf_token', '', time() - 3600, '/', '', false, true);
setcookie('redirect_after_login', '', time() - 3600, '/', '', false, true);

echo "<h1>JWT Tokens Cleared</h1>";
echo "<p>All JWT tokens and authentication cookies have been cleared.</p>";
echo "<p>You can now access login/register pages.</p>";

echo "<h2>Links:</h2>";
echo "<a href='auth/dang-nhap.php'>Đăng nhập</a><br>";
echo "<a href='auth/dang-ky.php'>Đăng ký</a><br>";
echo "<a href='test-jwt-system.php'>Test JWT System</a><br>";
echo "<a href='index.php'>Trang chủ</a><br>";

echo "<h2>Cookies Cleared:</h2>";
echo "<ul>";
echo "<li>auth_token</li>";
echo "<li>csrf_token</li>";
echo "<li>redirect_after_login</li>";
echo "</ul>";
?>
