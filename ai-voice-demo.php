<?php
/**
 * AI Voice Demo
 * Linh2Store - AI Voice Assistant Demo
 */

require_once 'config/database.php';

echo "<h1>🎤 AI Voice Assistant Demo</h1>";
echo "<p>Trợ lý giọng nói thông minh cho Linh2Store</p>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get voice interaction stats
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_interactions,
            AVG(response_time_ms) as avg_response_time,
            AVG(satisfaction_score) as avg_satisfaction,
            COUNT(CASE WHEN satisfaction_score >= 4 THEN 1 END) as satisfied_users
        FROM ai_voice_interactions
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute();
    $voiceStats = $stmt->fetch();
    
    echo "<h2>📊 Voice Interaction Statistics</h2>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;'>";
    
    echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h3 style='color: #1976d2;'>" . ($voiceStats['total_interactions'] ?? 0) . "</h3>";
    echo "<p>Tương tác (30 ngày)</p>";
    echo "</div>";
    
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h3 style='color: #388e3c;'>" . round($voiceStats['avg_response_time'] ?? 0) . "ms</h3>";
    echo "<p>Thời gian phản hồi TB</p>";
    echo "</div>";
    
    echo "<div style='background: #fff3e0; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h3 style='color: #f57c00;'>" . round(($voiceStats['avg_satisfaction'] ?? 0) * 20, 1) . "%</h3>";
    echo "<p>Độ hài lòng TB</p>";
    echo "</div>";
    
    echo "<div style='background: #f3e5f5; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h3 style='color: #7b1fa2;'>" . ($voiceStats['satisfied_users'] ?? 0) . "</h3>";
    echo "<p>Người dùng hài lòng</p>";
    echo "</div>";
    
    echo "</div>";
    
    // Get top intents
    $stmt = $conn->prepare("
        SELECT 
            intent_recognized,
            COUNT(*) as intent_count,
            AVG(satisfaction_score) as avg_satisfaction
        FROM ai_voice_interactions
        WHERE intent_recognized IS NOT NULL
        AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY intent_recognized
        ORDER BY intent_count DESC
        LIMIT 5
    ");
    $stmt->execute();
    $topIntents = $stmt->fetchAll();
    
    echo "<h2>🎯 Top Voice Intents</h2>";
    if (!empty($topIntents)) {
        echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        foreach ($topIntents as $intent) {
            $satisfactionColor = $intent['avg_satisfaction'] > 4 ? '#4caf50' : ($intent['avg_satisfaction'] > 3 ? '#ff9800' : '#f44336');
            echo "<div style='background: white; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid {$satisfactionColor};'>";
            echo "<h4 style='margin: 0 0 10px 0;'>" . ucfirst(str_replace('_', ' ', $intent['intent_recognized'])) . "</h4>";
            echo "<p style='margin: 0;'><strong>Số lần:</strong> {$intent['intent_count']}</p>";
            echo "<p style='margin: 0;'><strong>Hài lòng:</strong> " . round($intent['avg_satisfaction'] * 20, 1) . "%</p>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p style='color: orange;'>⚠️ Chưa có dữ liệu voice interactions</p>";
    }
    
    // Voice commands demo
    echo "<h2>🎤 Voice Commands Demo</h2>";
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>Thử nghiệm Voice Commands:</h3>";
    echo "<div id='voice-demo' style='text-align: center; margin: 20px 0;'>";
    echo "<button id='start-voice' style='background: #4caf50; color: white; padding: 15px 30px; border: none; border-radius: 8px; font-size: 16px; cursor: pointer;'>🎤 Bắt đầu Voice Demo</button>";
    echo "<div id='voice-status' style='margin: 20px 0; padding: 15px; background: white; border-radius: 8px; display: none;'>";
    echo "<p id='voice-text'>Đang lắng nghe...</p>";
    echo "<p id='voice-response'></p>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    
    echo "<h2>🎯 AI Voice Assistant Features:</h2>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;'>";
    
    echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 8px;'>";
    echo "<h3>🎤 Voice Recognition</h3>";
    echo "<p>Nhận diện giọng nói tiếng Việt chính xác</p>";
    echo "</div>";
    
    echo "<div style='background: #fff3e0; padding: 20px; border-radius: 8px;'>";
    echo "<h3>🧠 Intent Recognition</h3>";
    echo "<p>Hiểu ý định và mục đích của người dùng</p>";
    echo "</div>";
    
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px;'>";
    echo "<h3>💬 Natural Language Processing</h3>";
    echo "<p>Xử lý ngôn ngữ tự nhiên và trả lời thông minh</p>";
    echo "</div>";
    
    echo "<div style='background: #f3e5f5; padding: 20px; border-radius: 8px;'>";
    echo "<h3>🛍️ Shopping Assistant</h3>";
    echo "<p>Hỗ trợ mua sắm bằng giọng nói</p>";
    echo "</div>";
    
    echo "<div style='background: #ffebee; padding: 20px; border-radius: 8px;'>";
    echo "<h3>📊 Analytics & Insights</h3>";
    echo "<p>Phân tích tương tác và cải thiện trải nghiệm</p>";
    echo "</div>";
    
    echo "<div style='background: #f0f8ff; padding: 20px; border-radius: 8px;'>";
    echo "<h3>🌐 Multi-language Support</h3>";
    echo "<p>Hỗ trợ đa ngôn ngữ và địa phương hóa</p>";
    echo "</div>";
    
    echo "</div>";
    
    echo "<p><a href='index.php' style='background: #EC407A; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Về trang chủ</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<script>
// Voice demo functionality
document.getElementById('start-voice').addEventListener('click', function() {
    const voiceStatus = document.getElementById('voice-status');
    const voiceText = document.getElementById('voice-text');
    const voiceResponse = document.getElementById('voice-response');
    
    voiceStatus.style.display = 'block';
    voiceText.textContent = 'Đang lắng nghe...';
    voiceResponse.textContent = '';
    
    // Simulate voice recognition
    setTimeout(() => {
        voiceText.textContent = 'Bạn nói: "Tôi muốn tìm son môi màu đỏ"';
        voiceResponse.textContent = 'AI: Tôi hiểu bạn đang tìm son môi màu đỏ. Tôi có thể gợi ý một số sản phẩm phù hợp...';
    }, 2000);
});

// Voice commands examples
const voiceCommands = [
    "Tôi muốn tìm son môi",
    "Giá sản phẩm này bao nhiêu?",
    "Thêm vào giỏ hàng",
    "Kiểm tra đơn hàng của tôi",
    "Tìm thương hiệu MAC",
    "Giao hàng đến đâu?",
    "Thanh toán như thế nào?"
];

// Display voice commands
document.addEventListener('DOMContentLoaded', function() {
    const voiceDemo = document.getElementById('voice-demo');
    const commandsList = document.createElement('div');
    commandsList.innerHTML = '<h4>Ví dụ Voice Commands:</h4><ul style="text-align: left; display: inline-block;">';
    voiceCommands.forEach(cmd => {
        commandsList.innerHTML += `<li style="margin: 5px 0;">"${cmd}"</li>`;
    });
    commandsList.innerHTML += '</ul>';
    voiceDemo.appendChild(commandsList);
});
</script>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
button { cursor: pointer; }
button:hover { opacity: 0.8; }
</style>
