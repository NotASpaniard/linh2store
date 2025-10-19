<?php
/**
 * Setup ChatGPT API
 * Linh2Store - Hướng dẫn cấu hình ChatGPT API
 */

echo "<h1>🔧 Setup ChatGPT API</h1>";
echo "<p>Hướng dẫn cấu hình ChatGPT API cho Linh2Store</p>";

echo "<h2>📋 BƯỚC 1: LẤY API KEY</h2>";
echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>🔑 Cách lấy API Key:</h3>";
echo "<ol>";
echo "<li><strong>Truy cập:</strong> <a href='https://platform.openai.com/api-keys' target='_blank'>https://platform.openai.com/api-keys</a></li>";
echo "<li><strong>Đăng nhập:</strong> Tài khoản OpenAI</li>";
echo "<li><strong>Tạo API Key:</strong> Click 'Create new secret key'</li>";
echo "<li><strong>Copy Key:</strong> Bắt đầu với 'sk-proj-'</li>";
echo "</ol>";
echo "<p><strong>Lưu ý:</strong> API Key chỉ hiển thị 1 lần, hãy copy ngay!</p>";
echo "</div>";

echo "<h2>📋 BƯỚC 2: CẤU HÌNH API KEY</h2>";
echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
echo "<h3>⚠️ CẢNH BÁO BẢO MẬT:</h3>";
echo "<p>API Key chứa thông tin nhạy cảm, không được commit lên Git!</p>";
echo "<p>Hãy thêm vào <code>.gitignore</code>:</p>";
echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
echo "config/chatgpt-config.php";
echo "</pre>";
echo "</div>";

echo "<h2>📋 BƯỚC 3: KIỂM TRA CẤU HÌNH</h2>";
echo "<div style='background: #f0f8ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";

// Kiểm tra file cấu hình
if (file_exists('config/chatgpt-config.php')) {
    echo "<p>✅ File cấu hình tồn tại</p>";
    
    $config = file_get_contents('config/chatgpt-config.php');
    if (strpos($config, 'sk-proj-') !== false) {
        echo "<p>✅ API Key đã được cấu hình</p>";
    } else {
        echo "<p>❌ API Key chưa được cấu hình đúng</p>";
        echo "<p><strong>Cần thay đổi:</strong> <code>sk-proj-your-actual-api-key-here</code></p>";
    }
} else {
    echo "<p>❌ File cấu hình không tồn tại</p>";
}

echo "</div>";

echo "<h2>📋 BƯỚC 4: TEST API</h2>";
echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>🧪 Test API Key:</h3>";
echo "<p><a href='test-chatgpt-api.php' style='background: #EC407A; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test API Key</a></p>";
echo "</div>";

echo "<h2>📋 BƯỚC 5: TEST CHATBOT</h2>";
echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>🤖 Test Chatbot:</h3>";
echo "<p><a href='test-chatbot-chatgpt.php' style='background: #EC407A; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Chatbot ChatGPT</a></p>";
echo "</div>";

echo "<h2>💡 TROUBLESHOOTING</h2>";
echo "<div style='background: #ffebee; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>❌ Lỗi thường gặp:</h3>";
echo "<ul>";
echo "<li><strong>401 Unauthorized:</strong> API Key sai hoặc chưa cấu hình</li>";
echo "<li><strong>403 Forbidden:</strong> API Key không có quyền truy cập</li>";
echo "<li><strong>429 Too Many Requests:</strong> Vượt quá giới hạn API</li>";
echo "<li><strong>500 Internal Server Error:</strong> Lỗi server OpenAI</li>";
echo "</ul>";
echo "</div>";

echo "<h2>💰 CHI PHÍ API</h2>";
echo "<div style='background: #f0f8ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>💳 Bảng giá ChatGPT:</h3>";
echo "<ul>";
echo "<li><strong>GPT-3.5-turbo:</strong> $0.0015/1K tokens input, $0.002/1K tokens output</li>";
echo "<li><strong>GPT-4:</strong> $0.03/1K tokens input, $0.06/1K tokens output</li>";
echo "<li><strong>Ước tính:</strong> 1 cuộc trò chuyện ≈ $0.01-0.05</li>";
echo "</ul>";
echo "<p><strong>Lưu ý:</strong> Có thể set giới hạn chi phí trong OpenAI dashboard</p>";
echo "</div>";

echo "<p><a href='index.php' style='background: #EC407A; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Về trang chủ</a></p>";
?>
