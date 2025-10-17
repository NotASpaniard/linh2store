<?php
/**
 * Facebook OAuth Setup Guide
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

echo "<h1>📘 Facebook OAuth Setup Guide</h1>";

echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h2>🎯 Mục tiêu: Setup Facebook OAuth cho localhost</h2>";
echo "<p>Facebook OAuth cũng hoạt động tốt trên localhost, tương tự như Google OAuth.</p>";
echo "</div>";

echo "<h2>📋 Bước 1: Tạo Facebook App</h2>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px;'>";
echo "<ol>";
echo "<li><strong>Truy cập:</strong> <a href='https://developers.facebook.com/' target='_blank'>Facebook Developers</a></li>";
echo "<li><strong>Click "My Apps"</strong> → <strong>"Create App"</strong></li>";
echo "<li><strong>Chọn "Consumer"</strong> hoặc <strong>"Business"</strong></li>";
echo "<li><strong>Điền thông tin:</strong>";
echo "<ul>";
echo "<li><strong>App Name:</strong> Linh2Store</li>";
echo "<li><strong>App Contact Email:</strong> email của bạn</li>";
echo "<li><strong>App Purpose:</strong> Website bán mỹ phẩm</li>";
echo "</ul>";
echo "</li>";
echo "<li><strong>Click "Create App"</strong></li>";
echo "</ol>";
echo "</div>";

echo "<h2>📋 Bước 2: Thêm Facebook Login</h2>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px;'>";
echo "<ol>";
echo "<li><strong>Trong App Dashboard:</strong> Tìm <strong>"Add a Product"</strong></li>";
echo "<li><strong>Click "Set up"</strong> trên <strong>Facebook Login</strong></li>";
echo "<li><strong>Chọn "Web"</strong> platform</li>";
echo "<li><strong>Site URL:</strong> <code>http://localhost/linh2store</code></li>";
echo "<li><strong>Click "Save"</strong></li>";
echo "</ol>";
echo "</div>";

echo "<h2>📋 Bước 3: Cấu hình OAuth Settings</h2>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px;'>";
echo "<ol>";
echo "<li><strong>Vào Facebook Login → Settings</strong></li>";
echo "<li><strong>Valid OAuth Redirect URIs:</strong> Thêm các URL sau:";
echo "<ul style='margin: 10px 0;'>";
echo "<li><code>http://localhost/linh2store/auth/oauth-callback.php</code></li>";
echo "<li><code>http://127.0.0.1/linh2store/auth/oauth-callback.php</code></li>";
echo "<li><code>http://localhost:80/linh2store/auth/oauth-callback.php</code></li>";
echo "</ul>";
echo "</li>";
echo "<li><strong>Client OAuth Settings:</strong>";
echo "<ul>";
echo "<li>✅ <strong>Web OAuth Login</strong></li>";
echo "<li>✅ <strong>Enforce HTTPS</strong> (tắt cho localhost)</li>";
echo "<li>✅ <strong>Use Strict Mode for Redirect URIs</strong></li>";
echo "</ul>";
echo "</li>";
echo "<li><strong>Click "Save Changes"</strong></li>";
echo "</ol>";
echo "</div>";

echo "<h2>📋 Bước 4: Lấy App Credentials</h2>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px;'>";
echo "<ol>";
echo "<li><strong>Vào App Settings → Basic</strong></li>";
echo "<li><strong>Copy các thông tin sau:</strong>";
echo "<ul>";
echo "<li><strong>App ID:</strong> Số dài (ví dụ: 1234567890123456)</li>";
echo "<li><strong>App Secret:</strong> Click "Show" để hiện (ví dụ: abc123def456...)</li>";
echo "</ul>";
echo "</li>";
echo "<li><strong>Lưu lại thông tin này</strong> để cập nhật vào config</li>";
echo "</ol>";
echo "</div>";

echo "<h2>📋 Bước 5: Cấu hình App Review (Tùy chọn)</h2>";
echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; border-left: 4px solid #ffc107;'>";
echo "<h4>⚠️ Lưu ý quan trọng:</h4>";
echo "<ul>";
echo "<li><strong>Development Mode:</strong> Chỉ bạn và bạn bè có thể login</li>";
echo "<li><strong>Production Mode:</strong> Cần App Review để public</li>";
echo "<li><strong>Cho localhost:</strong> Development Mode là đủ</li>";
echo "</ul>";

echo "<h4>🔧 Cách chuyển sang Development Mode:</h4>";
echo "<ol>";
echo "<li><strong>Vào App Review → Permissions and Features</strong></li>";
echo "<li><strong>Toggle "Make app public"</strong> → OFF</li>";
echo "<li><strong>App sẽ ở Development Mode</strong></li>";
echo "</ol>";
echo "</div>";

echo "<h2>🔑 Bước 6: Cập nhật Credentials</h2>";
echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px;'>";
echo "<p><strong>Sau khi có App ID và App Secret, cập nhật vào:</strong></p>";
echo "<ol>";
echo "<li><strong>Mở file:</strong> <code>config/oauth-config.php</code></li>";
echo "<li><strong>Thay thế:</strong>";
echo "<ul>";
echo "<li><code>YOUR_FACEBOOK_APP_ID_HERE</code> → App ID thật</li>";
echo "<li><code>YOUR_FACEBOOK_APP_SECRET_HERE</code> → App Secret thật</li>";
echo "</ul>";
echo "</li>";
echo "<li><strong>Save file</strong></li>";
echo "</ol>";
echo "</div>";

echo "<h2>🧪 Bước 7: Test Facebook OAuth</h2>";
echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 8px;'>";
echo "<p><strong>Sau khi cập nhật credentials:</strong></p>";
echo "<div style='display: flex; gap: 15px; margin: 15px 0; flex-wrap: wrap;'>";
echo "<a href='test-facebook-oauth.php' style='background: #1877F2; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold;'>🧪 Test Facebook OAuth</a>";
echo "<a href='auth/dang-nhap.php' style='background: #4CAF50; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold;'>🔑 Test Login Page</a>";
echo "<a href='auth/dang-ky.php' style='background: #FF9800; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold;'>📝 Test Register Page</a>";
echo "</div>";
echo "</div>";

echo "<h2>📊 Checklist Facebook OAuth:</h2>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px;'>";
echo "<ul>";
echo "<li>⏳ <strong>Facebook App:</strong> Đã tạo</li>";
echo "<li>⏳ <strong>Facebook Login:</strong> Đã thêm</li>";
echo "<li>⏳ <strong>OAuth Redirect URIs:</strong> Đã cấu hình</li>";
echo "<li>⏳ <strong>App ID & Secret:</strong> Đã lấy</li>";
echo "<li>⏳ <strong>Credentials:</strong> Đã cập nhật vào config</li>";
echo "<li>⏳ <strong>Test OAuth:</strong> Chưa test</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🚨 Troubleshooting Facebook OAuth:</h2>";
echo "<div style='background: #ffebee; padding: 20px; border-radius: 8px;'>";
echo "<h4>Lỗi thường gặp:</h4>";
echo "<ul>";
echo "<li><strong>App Not Setup:</strong> Chưa thêm Facebook Login product</li>";
echo "<li><strong>Invalid Redirect URI:</strong> URI không khớp với Facebook Console</li>";
echo "<li><strong>App Not Public:</strong> App ở Development Mode, cần thêm test users</li>";
echo "<li><strong>Invalid App ID:</strong> App ID hoặc Secret sai</li>";
echo "</ul>";

echo "<h4>🔧 Cách sửa:</h4>";
echo "<ol>";
echo "<li>Kiểm tra App ID và Secret trong Facebook Console</li>";
echo "<li>Đảm bảo redirect URIs đã được thêm</li>";
echo "<li>Kiểm tra App đang ở Development Mode</li>";
echo "<li>Thêm test users nếu cần</li>";
echo "</ol>";
echo "</div>";

echo "<div style='margin-top: 30px; padding: 20px; background: #e8f5e8; border-radius: 8px; text-align: center;'>";
echo "<h2>🎯 Bắt đầu setup Facebook OAuth!</h2>";
echo "<p>Làm theo các bước trên để có Facebook OAuth hoạt động trên localhost.</p>";
echo "</div>";
?>
