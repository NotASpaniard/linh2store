<?php
/**
 * Setup DeepSeek API
 * Linh2Store - Hướng dẫn cấu hình DeepSeek API (MIỄN PHÍ)
 */

echo "<h1>🚀 Setup DeepSeek API</h1>";
echo "<p>Hướng dẫn cấu hình DeepSeek API cho Linh2Store (MIỄN PHÍ & MẠNH MẼ)</p>";

echo "<h2>🎉 TẠI SAO CHỌN DEEPSEEK?</h2>";
echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>✅ Ưu điểm DeepSeek:</h3>";
echo "<ul>";
echo "<li><strong>MIỄN PHÍ:</strong> Không cần trả phí API</li>";
echo "<li><strong>MẠNH MẼ:</strong> Hiệu suất tương đương GPT-4</li>";
echo "<li><strong>NHANH CHÓNG:</strong> Tốc độ phản hồi cao</li>";
echo "<li><strong>DỄ SỬ DỤNG:</strong> Tương thích OpenAI API</li>";
echo "<li><strong>KHÔNG GIỚI HẠN:</strong> Không bị giới hạn request</li>";
echo "</ul>";
echo "</div>";

echo "<h2>📋 BƯỚC 1: LẤY API KEY</h2>";
echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>🔑 Cách lấy API Key DeepSeek:</h3>";
echo "<ol>";
echo "<li><strong>Truy cập:</strong> <a href='https://platform.deepseek.com/api_keys' target='_blank'>https://platform.deepseek.com/api_keys</a></li>";
echo "<li><strong>Đăng ký/Đăng nhập:</strong> Tài khoản DeepSeek</li>";
echo "<li><strong>Tạo API Key:</strong> Click 'Create API Key'</li>";
echo "<li><strong>Copy Key:</strong> Bắt đầu với 'sk-'</li>";
echo "</ol>";
echo "<p><strong>Lưu ý:</strong> DeepSeek hoàn toàn miễn phí!</p>";
echo "</div>";

echo "<h2>📋 BƯỚC 2: CẤU HÌNH API KEY</h2>";
echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
echo "<h3>⚠️ CẢNH BÁO BẢO MẬT:</h3>";
echo "<p>API Key chứa thông tin nhạy cảm, không được commit lên Git!</p>";
echo "<p>File <code>config/chatgpt-config.php</code> đã được .gitignore</p>";
echo "</div>";

echo "<h2>📋 BƯỚC 3: KIỂM TRA CẤU HÌNH</h2>";
echo "<div style='background: #f0f8ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";

// Kiểm tra file cấu hình
if (file_exists('config/chatgpt-config.php')) {
    echo "<p>✅ File cấu hình tồn tại</p>";
    
    $config = file_get_contents('config/chatgpt-config.php');
    if (strpos($config, 'sk-your-deepseek-api-key-here') !== false) {
        echo "<p>❌ DeepSeek API Key chưa được cấu hình</p>";
        echo "<p><strong>Cần thay đổi:</strong> <code>sk-your-deepseek-api-key-here</code></p>";
    } else {
        echo "<p>✅ DeepSeek API Key đã được cấu hình</p>";
    }
} else {
    echo "<p>❌ File cấu hình không tồn tại</p>";
}

echo "</div>";

echo "<h2>📋 BƯỚC 4: TEST API</h2>";
echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>🧪 Test DeepSeek API:</h3>";
echo "<p><a href='test-deepseek-api.php' style='background: #EC407A; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test DeepSeek API</a></p>";
echo "</div>";

echo "<h2>📋 BƯỚC 5: TEST CHATBOT</h2>";
echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>🤖 Test Chatbot DeepSeek:</h3>";
echo "<p><a href='test-chatbot-deepseek.php' style='background: #EC407A; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Chatbot DeepSeek</a></p>";
echo "</div>";

echo "<h2>💰 CHI PHÍ DEEPSEEK</h2>";
echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>💳 Bảng giá DeepSeek:</h3>";
echo "<ul>";
echo "<li><strong>DeepSeek Chat:</strong> MIỄN PHÍ 100%</li>";
echo "<li><strong>Không giới hạn:</strong> Số lượng request</li>";
echo "<li><strong>Không cần thẻ:</strong> Không cần thông tin thanh toán</li>";
echo "<li><strong>Không hết hạn:</strong> API Key không bị hết hạn</li>";
echo "</ul>";
echo "<p><strong>So sánh:</strong> ChatGPT $0.01-0.05/cuộc trò chuyện vs DeepSeek MIỄN PHÍ</p>";
echo "</div>";

echo "<h2>🔧 CẤU HÌNH CHI TIẾT</h2>";
echo "<div style='background: #f0f8ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>📝 Cấu hình trong config/chatgpt-config.php:</h3>";
echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
echo "// DeepSeek Configuration (PRIMARY - FREE & POWERFUL)
define('DEEPSEEK_API_KEY', 'sk-your-actual-deepseek-key');
define('DEEPSEEK_API_URL', 'https://api.deepseek.com/v1/chat/completions');
define('DEEPSEEK_MODEL', 'deepseek-chat');";
echo "</pre>";
echo "</div>";

echo "<h2>💡 TROUBLESHOOTING</h2>";
echo "<div style='background: #ffebee; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>❌ Lỗi thường gặp:</h3>";
echo "<ul>";
echo "<li><strong>401 Unauthorized:</strong> API Key sai hoặc chưa cấu hình</li>";
echo "<li><strong>403 Forbidden:</strong> API Key không có quyền truy cập</li>";
echo "<li><strong>429 Too Many Requests:</strong> Vượt quá giới hạn (hiếm gặp)</li>";
echo "<li><strong>500 Internal Server Error:</strong> Lỗi server DeepSeek</li>";
echo "</ul>";
echo "</div>";

echo "<p><a href='index.php' style='background: #EC407A; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Về trang chủ</a></p>";
?>
