<?php
/**
 * Test Complete JWT System
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/jwt.php';
require_once 'config/auth-middleware.php';

echo "<h1>Test Complete JWT System</h1>";

echo "<h2>1. System Status</h2>";
echo "✅ JWT Class: " . (class_exists('JWT') ? "LOADED" : "NOT LOADED") . "<br>";
echo "✅ AuthMiddleware Class: " . (class_exists('AuthMiddleware') ? "LOADED" : "NOT LOADED") . "<br>";
echo "✅ Database Class: " . (class_exists('Database') ? "LOADED" : "NOT LOADED") . "<br>";

echo "<h2>2. Authentication Test</h2>";
$is_logged_in = AuthMiddleware::isLoggedIn();
echo "Current Login Status: " . ($is_logged_in ? "✅ LOGGED IN" : "❌ NOT LOGGED IN") . "<br>";

if ($is_logged_in) {
    $user = AuthMiddleware::getCurrentUser();
    echo "Current User: " . json_encode($user) . "<br>";
} else {
    echo "No user currently logged in<br>";
}

echo "<h2>3. JWT Token Test</h2>";
$token = JWT::getTokenFromCookie();
if ($token) {
    echo "Token found: " . substr($token, 0, 50) . "...<br>";
    $payload = JWT::decode($token);
    if ($payload) {
        echo "✅ Token is valid<br>";
        echo "Token payload: " . json_encode($payload) . "<br>";
    } else {
        echo "❌ Token is invalid or expired<br>";
    }
} else {
    echo "No token found in cookies<br>";
}

echo "<h2>4. Test All Pages</h2>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;'>";
echo "<a href='auth/dang-nhap.php' target='_blank' style='padding: 10px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>🔐 Đăng nhập</a>";
echo "<a href='auth/dang-ky.php' target='_blank' style='padding: 10px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px;'>📝 Đăng ký</a>";
echo "<a href='auth/dang-xuat.php' target='_blank' style='padding: 10px; background: #f44336; color: white; text-decoration: none; border-radius: 5px;'>🚪 Đăng xuất</a>";
echo "<a href='user/' target='_blank' style='padding: 10px; background: #FF9800; color: white; text-decoration: none; border-radius: 5px;'>👤 User Panel</a>";
echo "<a href='admin/' target='_blank' style='padding: 10px; background: #9C27B0; color: white; text-decoration: none; border-radius: 5px;'>⚙️ Admin Panel</a>";
echo "<a href='thanh-toan/' target='_blank' style='padding: 10px; background: #607D8B; color: white; text-decoration: none; border-radius: 5px;'>💳 Thanh toán</a>";
echo "<a href='api/cart.php' target='_blank' style='padding: 10px; background: #795548; color: white; text-decoration: none; border-radius: 5px;'>🛒 Cart API</a>";
echo "<a href='index.php' target='_blank' style='padding: 10px; background: #00BCD4; color: white; text-decoration: none; border-radius: 5px;'>🏠 Trang chủ</a>";
echo "</div>";

echo "<h2>5. Test Scenarios</h2>";
echo "<div style='background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>Scenario 1: New User Registration</h3>";
echo "<ol>";
echo "<li>Click 'Đăng ký' to create new account</li>";
echo "<li>Fill in registration form</li>";
echo "<li>Should auto-login after registration</li>";
echo "<li>Should redirect to home page</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>Scenario 2: User Login</h3>";
echo "<ol>";
echo "<li>Click 'Đăng nhập'</li>";
echo "<li>Enter username/email and password</li>";
echo "<li>Should login successfully</li>";
echo "<li>Should redirect to home page</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #fff3e0; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>Scenario 3: Protected Pages</h3>";
echo "<ol>";
echo "<li>After login, click 'User Panel'</li>";
echo "<li>Should access without redirect</li>";
echo "<li>Click 'Cart API'</li>";
echo "<li>Should return JSON data (not redirect)</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #ffebee; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>Scenario 4: Logout</h3>";
echo "<ol>";
echo "<li>Click 'Đăng xuất'</li>";
echo "<li>Should clear all tokens</li>";
echo "<li>Should redirect to login page</li>";
echo "<li>Try accessing protected pages - should redirect to login</li>";
echo "</ol>";
echo "</div>";

echo "<h2>6. Current Cookies</h2>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
if (!empty($_COOKIE)) {
    foreach ($_COOKIE as $name => $value) {
        echo "$name: " . (strlen($value) > 50 ? substr($value, 0, 50) . "..." : $value) . "\n";
    }
} else {
    echo "No cookies found";
}
echo "</pre>";

echo "<h2>7. Quick Actions</h2>";
echo "<a href='clear-jwt.php' style='padding: 10px; background: #f44336; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🗑️ Clear All Tokens</a>";
echo "<a href='test-jwt-system.php' style='padding: 10px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔧 JWT Debug</a>";
echo "<a href='debug-session-fix.php' style='padding: 10px; background: #9C27B0; color: white; text-decoration: none; border-radius: 5px;'>🐛 Session Debug</a>";

echo "<h2>8. Expected Results</h2>";
echo "<ul>";
echo "<li>✅ No more session timeout issues</li>";
echo "<li>✅ No need to clear session manually</li>";
echo "<li>✅ Login/register works consistently</li>";
echo "<li>✅ Cart functionality works after login</li>";
echo "<li>✅ Protected pages redirect properly</li>";
echo "<li>✅ Logout clears all authentication</li>";
echo "</ul>";

echo "<h2>9. Troubleshooting</h2>";
echo "<p>If you encounter issues:</p>";
echo "<ol>";
echo "<li>Click 'Clear All Tokens' to reset</li>";
echo "<li>Try registering a new account</li>";
echo "<li>Check browser console for JavaScript errors</li>";
echo "<li>Check PHP error logs</li>";
echo "<li>Ensure database connection is working</li>";
echo "</ol>";
?>
