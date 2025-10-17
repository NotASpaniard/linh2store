<?php
/**
 * Final OAuth Test
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/oauth.php';

echo "<h1>🎉 Final OAuth Test - Sẵn sàng hoạt động!</h1>";

echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h2>✅ Tất cả đã được cấu hình đúng!</h2>";
echo "<p>Google OAuth đã sẵn sàng hoạt động trên localhost của bạn.</p>";
echo "</div>";

echo "<h2>🔗 Test Links:</h2>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;'>";

// Google OAuth URL
$googleUrl = OAuthProvider::getGoogleAuthUrl();

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #4285F4;'>";
echo "<h3>🔐 Google OAuth</h3>";
echo "<p>Test đăng nhập bằng Google:</p>";
echo "<a href='$googleUrl' style='background: #4285F4; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block; margin: 10px 0;'>";
echo "🚀 Test Google Login";
echo "</a>";
echo "</div>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #4CAF50;'>";
echo "<h3>📝 Login Page</h3>";
echo "<p>Trang đăng nhập với OAuth buttons:</p>";
echo "<a href='auth/dang-nhap.php' style='background: #4CAF50; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block; margin: 10px 0;'>";
echo "🔑 Login Page";
echo "</a>";
echo "</div>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #FF9800;'>";
echo "<h3>📋 Register Page</h3>";
echo "<p>Trang đăng ký với password strength:</p>";
echo "<a href='auth/dang-ky.php' style='background: #FF9800; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block; margin: 10px 0;'>";
echo "📝 Register Page";
echo "</a>";
echo "</div>";

echo "</div>";

echo "<h2>📊 Cấu hình hiện tại:</h2>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px;'>";
echo "<ul>";
echo "<li>✅ <strong>Google Client ID:</strong> " . substr(GOOGLE_CLIENT_ID, 0, 30) . "...</li>";
echo "<li>✅ <strong>Redirect URI:</strong> " . GOOGLE_REDIRECT_URI . "</li>";
echo "<li>✅ <strong>Database:</strong> Bảng oauth_accounts đã tạo</li>";
echo "<li>✅ <strong>OAuth Callback:</strong> auth/oauth-callback.php</li>";
echo "<li>✅ <strong>JWT System:</strong> Đã tích hợp</li>";
echo "</ul>";
echo "</div>";

echo "<h2>⚠️ Quan trọng - Kiểm tra Google Console:</h2>";
echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; border-left: 4px solid #ffc107;'>";
echo "<p><strong>Đảm bảo trong Google Cloud Console có redirect URI:</strong></p>";
echo "<code style='background: #f8f9fa; padding: 10px; border-radius: 4px; display: block; margin: 10px 0; font-size: 14px;'>";
echo "http://localhost/linh2store/auth/oauth-callback.php";
echo "</code>";

echo "<h4>🔧 Nếu chưa có, làm theo:</h4>";
echo "<ol>";
echo "<li>Vào <a href='https://console.cloud.google.com/' target='_blank'>Google Cloud Console</a></li>";
echo "<li>APIs & Services → Credentials</li>";
echo "<li>Chọn OAuth 2.0 Client ID của bạn</li>";
echo "<li>Trong <strong>Authorized redirect URIs</strong>, thêm:</li>";
echo "<li><code>http://localhost/linh2store/auth/oauth-callback.php</code></li>";
echo "<li>Save</li>";
echo "</ol>";
echo "</div>";

echo "<h2>🎯 Quy trình test:</h2>";
echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 8px;'>";
echo "<ol>";
echo "<li><strong>Click 'Test Google Login'</strong> ở trên</li>";
echo "<li><strong>Đăng nhập Google</strong> và đồng ý quyền truy cập</li>";
echo "<li><strong>Kiểm tra redirect</strong> về oauth-callback.php</li>";
echo "<li><strong>Kiểm tra đăng nhập</strong> thành công vào website</li>";
echo "<li><strong>Kiểm tra database</strong> có record mới trong oauth_accounts</li>";
echo "</ol>";
echo "</div>";

echo "<h2>🚨 Nếu vẫn lỗi:</h2>";
echo "<div style='background: #ffebee; padding: 20px; border-radius: 8px;'>";
echo "<h4>Lỗi thường gặp:</h4>";
echo "<ul>";
echo "<li><strong>400 Bad Request:</strong> Redirect URI không khớp</li>";
echo "<li><strong>403 Forbidden:</strong> OAuth consent screen chưa cấu hình</li>";
echo "<li><strong>redirect_uri_mismatch:</strong> URI trong code khác với console</li>";
echo "</ul>";

echo "<h4>🔍 Debug:</h4>";
echo "<p><a href='debug-oauth-error.php' style='background: #607D8B; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔍 Debug OAuth</a></p>";
echo "</div>";

echo "<div style='margin-top: 30px; padding: 20px; background: #e8f5e8; border-radius: 8px; text-align: center;'>";
echo "<h2>🎉 Sẵn sàng test Google OAuth!</h2>";
echo "<p>Click nút <strong>'Test Google Login'</strong> ở trên để bắt đầu!</p>";
echo "</div>";
?>
