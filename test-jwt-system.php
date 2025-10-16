<?php
/**
 * Test JWT System
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/jwt.php';
require_once 'config/auth-middleware.php';

echo "<h1>Test JWT System</h1>";

echo "<h2>1. Test JWT Functions</h2>";

// Test tạo token
$test_user = [
    'id' => 1,
    'username' => 'test_user',
    'email' => 'test@example.com',
    'full_name' => 'Test User',
    'role' => 'user'
];

$token = JWT::createUserToken($test_user);
echo "Token created: " . substr($token, 0, 50) . "...<br>";

// Test decode token
$payload = JWT::decode($token);
if ($payload) {
    echo "Token decode success: " . json_encode($payload) . "<br>";
} else {
    echo "Token decode failed<br>";
}

// Test validate token
$is_valid = JWT::validate($token);
echo "Token validation: " . ($is_valid ? "VALID" : "INVALID") . "<br>";

// Test get user from token
$user_from_token = JWT::getUserFromToken($token);
if ($user_from_token) {
    echo "User from token: " . json_encode($user_from_token) . "<br>";
} else {
    echo "Failed to get user from token<br>";
}

echo "<h2>2. Test AuthMiddleware</h2>";

// Test current user (should be null if not logged in)
$current_user = AuthMiddleware::getCurrentUser();
if ($current_user) {
    echo "Current user: " . json_encode($current_user) . "<br>";
} else {
    echo "No user logged in<br>";
}

// Test login status
$is_logged_in = AuthMiddleware::isLoggedIn();
echo "Is logged in: " . ($is_logged_in ? "TRUE" : "FALSE") . "<br>";

echo "<h2>3. Test CSRF Token</h2>";
$csrf_token = AuthMiddleware::generateCSRFToken();
echo "CSRF Token: " . $csrf_token . "<br>";

$csrf_valid = AuthMiddleware::verifyCSRFToken($csrf_token);
echo "CSRF Token validation: " . ($csrf_valid ? "VALID" : "INVALID") . "<br>";

echo "<h2>4. Test Links</h2>";
echo "<a href='auth/dang-nhap.php' target='_blank'>Test Đăng nhập</a><br>";
echo "<a href='auth/dang-ky.php' target='_blank'>Test Đăng ký</a><br>";
echo "<a href='auth/dang-xuat.php' target='_blank'>Test Đăng xuất</a><br>";
echo "<a href='user/' target='_blank'>Test User Panel</a><br>";
echo "<a href='admin/' target='_blank'>Test Admin Panel</a><br>";
echo "<a href='thanh-toan/' target='_blank'>Test Thanh toán</a><br>";
echo "<a href='api/cart.php' target='_blank'>Test Cart API</a><br>";

echo "<h2>5. Current Session Data</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

echo "<h2>6. Test Instructions</h2>";
echo "<ol>";
echo "<li>Click 'Test Đăng ký' to create a new account</li>";
echo "<li>Click 'Test Đăng nhập' to login</li>";
echo "<li>After login, click 'Test User Panel' to check authentication</li>";
echo "<li>Click 'Test Cart API' to check API authentication</li>";
echo "<li>Click 'Test Đăng xuất' to logout</li>";
echo "</ol>";

echo "<h2>7. JWT Advantages</h2>";
echo "<ul>";
echo "<li>✅ Stateless - no server-side session storage</li>";
echo "<li>✅ Secure - signed tokens prevent tampering</li>";
echo "<li>✅ Scalable - works across multiple servers</li>";
echo "<li>✅ Mobile-friendly - works with mobile apps</li>";
echo "<li>✅ No session timeout issues</li>";
echo "<li>✅ Works with XAMPP without configuration</li>";
echo "</ul>";

echo "<h2>8. Debug Info</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Current Time: " . date('Y-m-d H:i:s') . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
?>
