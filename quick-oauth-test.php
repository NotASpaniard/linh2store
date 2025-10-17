<?php
/**
 * Quick OAuth Test for Localhost
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

echo "<h1>🚀 Quick OAuth Test - Localhost</h1>";

echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h2>✅ Google OAuth hoạt động với localhost!</h2>";
echo "<p>Bạn có thể tạo Google OAuth ngay trên localhost mà không cần domain.</p>";
echo "</div>";

echo "<h2>📋 Checklist Setup Google OAuth:</h2>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px;'>";
echo "<ol>";
echo "<li>✅ Truy cập <a href='https://console.cloud.google.com/' target='_blank'>Google Cloud Console</a></li>";
echo "<li>✅ Tạo project mới</li>";
echo "<li>✅ Enable Google+ API</li>";
echo "<li>✅ Tạo OAuth 2.0 Client ID</li>";
echo "<li>✅ Thêm redirect URI: <code>http://localhost/linh2store/auth/oauth-callback.php</code></li>";
echo "<li>✅ Copy Client ID & Secret vào <code>config/oauth-config.php</code></li>";
echo "</ol>";
echo "</div>";

echo "<h2>🔗 Redirect URIs cần thêm:</h2>";
echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
echo "http://localhost/linh2store/auth/oauth-callback.php\n";
echo "http://127.0.0.1/linh2store/auth/oauth-callback.php\n";
echo "http://localhost:80/linh2store/auth/oauth-callback.php";
echo "</pre>";

echo "<h2>⚡ Test ngay:</h2>";
echo "<div style='display: flex; gap: 15px; margin: 20px 0;'>";
echo "<a href='test-oauth-setup.php' style='padding: 12px 24px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>🔧 Test OAuth Setup</a>";
echo "<a href='auth/dang-nhap.php' style='padding: 12px 24px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>🔐 Test Login</a>";
echo "<a href='auth/dang-ky.php' style='padding: 12px 24px; background: #FF9800; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>📝 Test Register</a>";
echo "</div>";

echo "<h2>📱 Screenshots hướng dẫn:</h2>";
echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; border-left: 4px solid #ffc107;'>";
echo "<h3>🔍 Trong Google Cloud Console:</h3>";
echo "<ol>";
echo "<li><strong>APIs & Services → Credentials</strong></li>";
echo "<li><strong>Create Credentials → OAuth 2.0 Client IDs</strong></li>";
echo "<li><strong>Application type: Web application</strong></li>";
echo "<li><strong>Name: Linh2Store OAuth</strong></li>";
echo "<li><strong>Authorized redirect URIs:</strong> Thêm localhost URLs ở trên</li>";
echo "</ol>";
echo "</div>";

echo "<h2>🎯 Sau khi setup xong:</h2>";
echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 8px;'>";
echo "<ol>";
echo "<li>Copy Client ID và Client Secret</li>";
echo "<li>Mở file <code>config/oauth-config.php</code></li>";
echo "<li>Thay thế <code>YOUR_GOOGLE_CLIENT_ID_HERE</code> bằng Client ID thật</li>";
echo "<li>Thay thế <code>YOUR_GOOGLE_CLIENT_SECRET_HERE</code> bằng Client Secret thật</li>";
echo "<li>Test lại bằng <a href='test-oauth-setup.php'>test-oauth-setup.php</a></li>";
echo "</ol>";
echo "</div>";

echo "<h2>🚨 Troubleshooting:</h2>";
echo "<div style='background: #ffebee; padding: 20px; border-radius: 8px;'>";
echo "<h4>Lỗi thường gặp:</h4>";
echo "<ul>";
echo "<li><strong>redirect_uri_mismatch:</strong> Kiểm tra URI trong Google Console</li>";
echo "<li><strong>invalid_client:</strong> Kiểm tra Client ID/Secret</li>";
echo "<li><strong>access_denied:</strong> User từ chối quyền</li>";
echo "<li><strong>invalid_request:</strong> Thiếu tham số trong request</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🎉 Kết quả mong đợi:</h2>";
echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px;'>";
echo "<p>Sau khi setup xong, bạn sẽ có thể:</p>";
echo "<ul>";
echo "<li>✅ Đăng nhập bằng Google trên localhost</li>";
echo "<li>✅ Tự động tạo tài khoản từ Google</li>";
echo "<li>✅ Lấy thông tin profile từ Google</li>";
echo "<li>✅ Hoạt động hoàn toàn trên localhost</li>";
echo "</ul>";
echo "</div>";
?>
