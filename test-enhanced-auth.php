<?php
/**
 * Test Enhanced Authentication System
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/jwt.php';
require_once 'config/auth-middleware.php';
require_once 'config/password-validator.php';
require_once 'config/oauth.php';

echo "<h1>Test Enhanced Authentication System</h1>";

echo "<h2>1. Password Strength Testing</h2>";

$test_passwords = [
    '123' => 'Very weak',
    'password' => 'Weak (common)',
    'Password123' => 'Fair',
    'MyPassword123!' => 'Good',
    'SuperStrong123!@#' => 'Strong'
];

foreach ($test_passwords as $password => $description) {
    $result = PasswordValidator::checkStrength($password);
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px;'>";
    echo "<strong>Password:</strong> " . htmlspecialchars($password) . " ($description)<br>";
    echo "<strong>Score:</strong> {$result['score']}/5<br>";
    echo "<strong>Strength:</strong> {$result['strength']['label']}<br>";
    echo "<strong>Valid:</strong> " . ($result['is_valid'] ? '✅ Yes' : '❌ No') . "<br>";
    
    if (!empty($result['feedback'])) {
        echo "<strong>Feedback:</strong><br>";
        foreach ($result['feedback'] as $feedback) {
            echo "• " . htmlspecialchars($feedback) . "<br>";
        }
    }
    echo "</div>";
}

echo "<h2>2. OAuth Configuration Test</h2>";
echo "✅ Google OAuth URL: " . OAuthProvider::getGoogleAuthUrl() . "<br>";
echo "✅ Facebook OAuth URL: " . OAuthProvider::getFacebookAuthUrl() . "<br>";

echo "<h2>3. Authentication Status</h2>";
$is_logged_in = AuthMiddleware::isLoggedIn();
echo "Current Login Status: " . ($is_logged_in ? "✅ LOGGED IN" : "❌ NOT LOGGED IN") . "<br>";

if ($is_logged_in) {
    $user = AuthMiddleware::getCurrentUser();
    echo "Current User: " . json_encode($user) . "<br>";
}

echo "<h2>4. Test All Enhanced Features</h2>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin: 20px 0;'>";

echo "<div style='padding: 15px; background: #e8f5e8; border-radius: 8px;'>";
echo "<h3>🔐 Enhanced Login</h3>";
echo "<p>Login với OAuth Google/Facebook</p>";
echo "<a href='auth/dang-nhap.php' target='_blank' style='display: inline-block; padding: 8px 16px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>Test Login</a>";
echo "</div>";

echo "<div style='padding: 15px; background: #e3f2fd; border-radius: 8px;'>";
echo "<h3>📝 Enhanced Registration</h3>";
echo "<p>Đăng ký với password strength checker</p>";
echo "<a href='auth/dang-ky.php' target='_blank' style='display: inline-block; padding: 8px 16px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px;'>Test Registration</a>";
echo "</div>";

echo "<div style='padding: 15px; background: #fff3e0; border-radius: 8px;'>";
echo "<h3>🛒 Protected Pages</h3>";
echo "<p>Test các trang cần đăng nhập</p>";
echo "<a href='user/' target='_blank' style='display: inline-block; padding: 8px 16px; background: #FF9800; color: white; text-decoration: none; border-radius: 4px;'>User Panel</a>";
echo "</div>";

echo "<div style='padding: 15px; background: #fce4ec; border-radius: 8px;'>";
echo "<h3>🔧 Password Strength API</h3>";
echo "<p>Test password strength checker</p>";
echo "<a href='api/check-password-strength.php' target='_blank' style='display: inline-block; padding: 8px 16px; background: #E91E63; color: white; text-decoration: none; border-radius: 4px;'>Test API</a>";
echo "</div>";

echo "<div style='padding: 15px; background: #f3e5f5; border-radius: 8px;'>";
echo "<h3>🗑️ Clear Tokens</h3>";
echo "<p>Reset authentication</p>";
echo "<a href='clear-jwt.php' target='_blank' style='display: inline-block; padding: 8px 16px; background: #9C27B0; color: white; text-decoration: none; border-radius: 4px;'>Clear Tokens</a>";
echo "</div>";

echo "<div style='padding: 15px; background: #e0f2f1; border-radius: 8px;'>";
echo "<h3>🏠 Home Page</h3>";
echo "<p>Trang chủ với JWT</p>";
echo "<a href='index.php' target='_blank' style='display: inline-block; padding: 8px 16px; background: #009688; color: white; text-decoration: none; border-radius: 4px;'>Home Page</a>";
echo "</div>";

echo "</div>";

echo "<h2>5. New Features Summary</h2>";
echo "<div style='background: #f5f5f5; padding: 20px; border-radius: 8px;'>";
echo "<h3>✅ Completed Enhancements:</h3>";
echo "<ul>";
echo "<li><strong>OAuth Integration:</strong> Google & Facebook login</li>";
echo "<li><strong>Password Strength Checker:</strong> Real-time validation with 5-point scale</li>";
echo "<li><strong>Enhanced Registration:</strong> Better UX with strength indicators</li>";
echo "<li><strong>JWT Authentication:</strong> Replaced problematic PHP sessions</li>";
echo "<li><strong>Clean Codebase:</strong> Removed all old session files</li>";
echo "</ul>";

echo "<h3>🎯 Password Strength Rules:</h3>";
echo "<ul>";
echo "<li>9+ characters: +1 point</li>";
echo "<li>Lowercase letter: +1 point</li>";
echo "<li>Uppercase letter: +1 point</li>";
echo "<li>Special character: +1 point</li>";
echo "<li>Number: +1 point</li>";
echo "<li><strong>Minimum required:</strong> 3/5 points</li>";
echo "</ul>";

echo "<h3>🔧 Setup Required:</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>";
echo "<p><strong>⚠️ OAuth Setup Needed:</strong></p>";
echo "<ol>";
echo "<li>Get Google OAuth credentials from <a href='https://console.developers.google.com' target='_blank'>Google Console</a></li>";
echo "<li>Get Facebook App credentials from <a href='https://developers.facebook.com' target='_blank'>Facebook Developers</a></li>";
echo "<li>Update credentials in <code>config/oauth.php</code></li>";
echo "<li>Add redirect URIs to OAuth providers</li>";
echo "</ol>";
echo "</div>";
echo "</div>";

echo "<h2>6. Testing Instructions</h2>";
echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px;'>";
echo "<ol>";
echo "<li><strong>Test Password Strength:</strong> Try different passwords in registration form</li>";
echo "<li><strong>Test Registration:</strong> Create account with strong password</li>";
echo "<li><strong>Test Login:</strong> Login with created account</li>";
echo "<li><strong>Test Protected Pages:</strong> Access user panel after login</li>";
echo "<li><strong>Test OAuth:</strong> Try Google/Facebook login (after setup)</li>";
echo "<li><strong>Test Logout:</strong> Verify logout clears all tokens</li>";
echo "</ol>";
echo "</div>";

echo "<h2>7. Database Schema Update</h2>";
echo "<p>You may need to add OAuth accounts table:</p>";
echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
echo "CREATE TABLE oauth_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    provider VARCHAR(50) NOT NULL,
    provider_id VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_provider (provider, provider_id)
);";
echo "</pre>";
?>
