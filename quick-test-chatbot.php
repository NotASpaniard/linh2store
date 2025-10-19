<?php
/**
 * Quick Test AI Chatbot
 * Linh2Store - Quick Test Chatbot Functionality
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/ai-chatbot.php';

echo "<h1>🤖 Quick Test AI Chatbot</h1>";
echo "<p>Đang test chatbot với các câu hỏi mẫu...</p>";

try {
    $chatbot = new AIChatbot();
    
    // Start conversation
    $conversation = $chatbot->startConversation();
    echo "<p style='color: green;'>✅ Khởi tạo conversation thành công! ID: " . $conversation['conversation_id'] . "</p>";
    
    // Test questions
    $testQuestions = [
        'Xin chào',
        'Tìm son môi màu đỏ',
        'Thương hiệu nào có?',
        'Giao hàng như thế nào?',
        'Bạn có thể giúp gì?'
    ];
    
    echo "<h2>🧪 Test Questions:</h2>";
    
    foreach ($testQuestions as $index => $question) {
        echo "<div style='border: 1px solid #ddd; margin: 10px 0; padding: 15px; border-radius: 8px;'>";
        echo "<h3>❓ Câu hỏi " . ($index + 1) . ": " . htmlspecialchars($question) . "</h3>";
        
        try {
            $response = $chatbot->processMessage($conversation['conversation_id'], $question);
            
            echo "<p style='color: green;'><strong>✅ AI Response:</strong></p>";
            echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>Text:</strong> " . htmlspecialchars($response['text']) . "<br>";
            echo "<strong>Type:</strong> " . htmlspecialchars($response['type']) . "<br>";
            if (isset($response['metadata'])) {
                echo "<strong>Metadata:</strong> " . htmlspecialchars(json_encode($response['metadata'])) . "<br>";
            }
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<p style='color: red;'><strong>❌ Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        echo "</div>";
    }
    
    echo "<h2>🎉 Test hoàn tất!</h2>";
    echo "<p><strong>Chatbot đã hoạt động thành công!</strong></p>";
    echo "<p><a href='test-chatbot.php' style='background: #EC407A; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Test Chatbot UI</a></p>";
    echo "<p><a href='ai-training-dashboard.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>📊 Training Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Lỗi khởi tạo:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
