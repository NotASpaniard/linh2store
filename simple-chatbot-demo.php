<?php
/**
 * Linh2Store Chatbot Demo
 * Linh2Store - Demo chatbot đơn giản
 */

require_once 'config/Linh2Store-chatbot.php';

echo "<h1>🤖 Linh2Store Chatbot Demo</h1>";
echo "<p>Chatbot đơn giản và hiệu quả</p>";

echo "<h2>💬 Test Chatbot:</h2>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<div id='chat-container' style='border: 1px solid #ddd; height: 400px; overflow-y: auto; padding: 15px; background: white; border-radius: 8px;'>";
echo "<div id='chat-messages'></div>";
echo "</div>";
echo "<div style='margin-top: 15px; display: flex; gap: 10px;'>";
echo "<input type='text' id='user-input' placeholder='Nhập câu hỏi...' style='flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 6px;'>";
echo "<button onclick='sendMessage()' style='padding: 10px 20px; background: #EC407A; color: white; border: none; border-radius: 6px; cursor: pointer;'>Gửi</button>";
echo "</div>";
echo "</div>";

echo "<h2>✅ Tính năng:</h2>";
echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<ul>";
echo "<li>✅ FAQ cơ bản - Trả lời câu hỏi thường gặp</li>";
echo "<li>✅ Hướng dẫn mua hàng - Cách đặt hàng, thanh toán</li>";
echo "<li>✅ Thông tin sản phẩm - Giá, khuyến mãi</li>";
echo "<li>✅ Liên hệ hỗ trợ - Hotline, email</li>";
echo "<li>✅ Đơn giản - Không cần AI phức tạp</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🎯 Test Cases:</h2>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin: 20px 0;'>";
echo "<button onclick='testMessage(\"giá son môi\")' style='padding: 10px; background: #e3f2fd; border: 1px solid #1976d2; border-radius: 6px; cursor: pointer;'>Giá son môi</button>";
echo "<button onclick='testMessage(\"ship hàng\")' style='padding: 10px; background: #e8f5e8; border: 1px solid #388e3c; border-radius: 6px; cursor: pointer;'>Ship hàng</button>";
echo "<button onclick='testMessage(\"đổi trả\")' style='padding: 10px; background: #fff3e0; border: 1px solid #f57c00; border-radius: 6px; cursor: pointer;'>Đổi trả</button>";
echo "<button onclick='testMessage(\"khuyến mãi\")' style='padding: 10px; background: #fce4ec; border: 1px solid #c2185b; border-radius: 6px; cursor: pointer;'>Khuyến mãi</button>";
echo "<button onclick='testMessage(\"liên hệ\")' style='padding: 10px; background: #f3e5f5; border: 1px solid #7b1fa2; border-radius: 6px; cursor: pointer;'>Liên hệ</button>";
echo "<button onclick='testMessage(\"đặt hàng\")' style='padding: 10px; background: #e0f2f1; border: 1px solid #00695c; border-radius: 6px; cursor: pointer;'>Đặt hàng</button>";
echo "</div>";

echo "<p><a href='index.php' style='background: #EC407A; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Về trang chủ</a></p>";
?>

<script>
function sendMessage() {
    const input = document.getElementById('user-input');
    const message = input.value.trim();
    
    if (message === '') return;
    
    // Add user message
    addMessage(message, 'user');
    input.value = '';
    
    // Process message
    processMessage(message);
}

function testMessage(message) {
    document.getElementById('user-input').value = message;
    sendMessage();
}

function processMessage(message) {
    fetch('api/Linh2Store-chatbot.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ message: message })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            addMessage(data.response, 'assistant');
        } else {
            addMessage('Xin lỗi, có lỗi xảy ra. Vui lòng thử lại.', 'assistant');
        }
    })
    .catch(error => {
        addMessage('Xin lỗi, có lỗi xảy ra. Vui lòng thử lại.', 'assistant');
    });
}

function addMessage(text, role) {
    const chatMessages = document.getElementById('chat-messages');
    const messageDiv = document.createElement('div');
    messageDiv.style.marginBottom = '15px';
    messageDiv.style.padding = '12px';
    messageDiv.style.borderRadius = '8px';
    messageDiv.style.backgroundColor = role === 'user' ? '#e3f2fd' : '#f3e5f5';
    messageDiv.style.borderLeft = role === 'user' ? '4px solid #1976d2' : '4px solid #7b1fa2';
    
    const roleText = role === 'user' ? 'Bạn' : 'Linh (Linh2Store AI)';
    messageDiv.innerHTML = `<strong>${roleText}:</strong><br><span style="white-space: pre-line;">${text}</span>`;
    
    chatMessages.appendChild(messageDiv);
    
    // Scroll to bottom
    const chatContainer = document.getElementById('chat-container');
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

// Allow Enter key
document.getElementById('user-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
});

// Add welcome message
document.addEventListener('DOMContentLoaded', function() {
    addMessage('Xin chào! Tôi có thể giúp bạn gì?', 'assistant');
});
</script>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
#chat-container { border-radius: 8px; }
button { cursor: pointer; }
button:hover { opacity: 0.8; }
</style>
