<?php
/**
 * Setup AI Voice Assistant
 * Linh2Store - Setup AI Voice Assistant System
 */

require_once 'config/database.php';

echo "<h1>🎤 Setup AI Voice Assistant</h1>";
echo "<p>Thiết lập hệ thống trợ lý giọng nói thông minh</p>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Read and execute schema
    $schemaFile = 'database/ai-voice-assistant-schema.sql';
    if (file_exists($schemaFile)) {
        $schema = file_get_contents($schemaFile);
        
        // Split by semicolon and execute each statement
        $statements = array_filter(array_map('trim', explode(';', $schema)));
        
        echo "<h2>📊 Creating Database Tables:</h2>";
        echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^--/', $statement)) {
                try {
                    $conn->exec($statement);
                    echo "<p style='color: green;'>✅ " . substr($statement, 0, 50) . "...</p>";
                } catch (Exception $e) {
                    echo "<p style='color: orange;'>⚠️ " . substr($statement, 0, 50) . "... (may already exist)</p>";
                }
            }
        }
        
        echo "</div>";
        
        // Insert sample data
        echo "<h2>📝 Inserting Sample Data:</h2>";
        echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        
        // Insert voice interactions
        try {
            $stmt = $conn->prepare("
                INSERT INTO ai_voice_interactions (user_id, voice_input, voice_output, intent_recognized, response_time_ms, satisfaction_score, created_at) VALUES
                (1, 'Tôi muốn tìm son môi màu đỏ', 'Tôi có thể gợi ý một số son môi màu đỏ cho bạn. Bạn thích tone da nào?', 'product_search', 1200, 4, NOW()),
                (2, 'Giá sản phẩm này bao nhiêu?', 'Sản phẩm MAC Ruby Woo có giá 650,000đ. Bạn có muốn thêm vào giỏ hàng không?', 'price_inquiry', 800, 5, NOW()),
                (3, 'Thêm vào giỏ hàng', 'Đã thêm sản phẩm vào giỏ hàng. Bạn có muốn thanh toán ngay không?', 'add_to_cart', 600, 4, NOW()),
                (4, 'Kiểm tra đơn hàng của tôi', 'Bạn có 2 đơn hàng đang xử lý. Đơn hàng gần nhất sẽ được giao trong 2-3 ngày.', 'order_status', 1000, 5, NOW()),
                (5, 'Tìm thương hiệu MAC', 'Tôi tìm thấy 15 sản phẩm từ thương hiệu MAC. Bạn muốn xem sản phẩm nào?', 'brand_search', 900, 4, NOW())
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Sample voice interactions inserted</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Sample voice interactions may already exist</p>";
        }
        
        // Insert voice commands
        try {
            $stmt = $conn->prepare("
                INSERT INTO ai_voice_commands (command_text, intent, response_template, created_at) VALUES
                ('Tôi muốn tìm son môi', 'product_search', 'Tôi có thể giúp bạn tìm son môi. Bạn thích màu gì?', NOW()),
                ('Giá sản phẩm này bao nhiêu?', 'price_inquiry', 'Sản phẩm {product_name} có giá {price}đ', NOW()),
                ('Thêm vào giỏ hàng', 'add_to_cart', 'Đã thêm {product_name} vào giỏ hàng', NOW()),
                ('Kiểm tra đơn hàng', 'order_status', 'Bạn có {order_count} đơn hàng. Đơn hàng gần nhất: {latest_order}', NOW()),
                ('Tìm thương hiệu {brand}', 'brand_search', 'Tôi tìm thấy {product_count} sản phẩm từ {brand}', NOW()),
                ('Giao hàng đến đâu?', 'shipping_info', 'Chúng tôi giao hàng toàn quốc. Phí ship: {shipping_fee}đ', NOW()),
                ('Thanh toán như thế nào?', 'payment_info', 'Chúng tôi hỗ trợ thanh toán: {payment_methods}', NOW())
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Sample voice commands inserted</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Sample voice commands may already exist</p>";
        }
        
        // Insert speech recognition
        try {
            $stmt = $conn->prepare("
                INSERT INTO ai_speech_recognition (user_id, audio_file_path, transcribed_text, confidence_score, language, created_at) VALUES
                (1, '/audio/user1_001.wav', 'Tôi muốn tìm son môi màu đỏ', 0.95, 'vi', NOW()),
                (2, '/audio/user2_001.wav', 'Giá sản phẩm này bao nhiêu', 0.90, 'vi', NOW()),
                (3, '/audio/user3_001.wav', 'Thêm vào giỏ hàng', 0.98, 'vi', NOW()),
                (4, '/audio/user4_001.wav', 'Kiểm tra đơn hàng của tôi', 0.92, 'vi', NOW()),
                (5, '/audio/user5_001.wav', 'Tìm thương hiệu MAC', 0.88, 'vi', NOW())
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Sample speech recognition data inserted</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Sample speech recognition data may already exist</p>";
        }
        
        // Insert text to speech
        try {
            $stmt = $conn->prepare("
                INSERT INTO ai_text_to_speech (text_content, audio_file_path, voice_type, speed, created_at) VALUES
                ('Chào bạn! Tôi có thể giúp gì cho bạn?', '/audio/tts_001.wav', 'female', 1.0, NOW()),
                ('Tôi có thể gợi ý một số son môi cho bạn', '/audio/tts_002.wav', 'female', 1.0, NOW()),
                ('Sản phẩm này có giá 650,000đ', '/audio/tts_003.wav', 'female', 1.0, NOW()),
                ('Đã thêm sản phẩm vào giỏ hàng', '/audio/tts_004.wav', 'female', 1.0, NOW()),
                ('Cảm ơn bạn đã sử dụng dịch vụ!', '/audio/tts_005.wav', 'female', 1.0, NOW())
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Sample text to speech data inserted</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Sample text to speech data may already exist</p>";
        }
        
        echo "</div>";
        
        echo "<h2>✅ Setup Complete!</h2>";
        echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h3>AI Voice Assistant System is ready!</h3>";
        echo "<p><strong>Features available:</strong></p>";
        echo "<ul>";
        echo "<li>🎤 Voice recognition</li>";
        echo "<li>🧠 Intent recognition</li>";
        echo "<li>💬 Natural language processing</li>";
        echo "<li>🛍️ Shopping assistant</li>";
        echo "<li>📊 Analytics & insights</li>";
        echo "<li>🌐 Multi-language support</li>";
        echo "</ul>";
        echo "<p><a href='ai-voice-demo.php' style='background: #4caf50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test AI Voice Assistant</a></p>";
        echo "</div>";
        
    } else {
        echo "<p style='color: red;'>❌ Schema file not found: {$schemaFile}</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
</style>
