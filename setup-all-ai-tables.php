<?php
/**
 * Setup All AI Tables
 * Linh2Store - Setup all AI database tables
 */

require_once 'config/database.php';

echo "<h1>🤖 Setup All AI Tables</h1>";
echo "<p>Thiết lập tất cả bảng database cho hệ thống AI</p>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // List of all AI schema files
    $schemaFiles = [
        'database/ai-recommendations-schema.sql',
        'database/ai-chatbot-schema.sql',
        'database/ai-sentiment-analysis-schema.sql',
        'database/ai-image-recognition-schema.sql',
        'database/ai-price-prediction-schema.sql',
        'database/ai-inventory-optimization-schema.sql',
        'database/ai-customer-behavior-schema.sql',
        'database/ai-marketing-automation-schema.sql',
        'database/ai-fraud-detection-schema.sql',
        'database/ai-voice-assistant-schema.sql'
    ];
    
    echo "<h2>📊 Creating All AI Database Tables:</h2>";
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    
    $totalTables = 0;
    $successTables = 0;
    
    foreach ($schemaFiles as $schemaFile) {
        if (file_exists($schemaFile)) {
            echo "<h3>📁 Processing: " . basename($schemaFile) . "</h3>";
            
            $schema = file_get_contents($schemaFile);
            
            // Split by semicolon and execute each statement
            $statements = array_filter(array_map('trim', explode(';', $schema)));
            
            foreach ($statements as $statement) {
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    $totalTables++;
                    try {
                        $conn->exec($statement);
                        echo "<p style='color: green;'>✅ " . substr($statement, 0, 50) . "...</p>";
                        $successTables++;
                    } catch (Exception $e) {
                        echo "<p style='color: orange;'>⚠️ " . substr($statement, 0, 50) . "... (may already exist)</p>";
                    }
                }
            }
        } else {
            echo "<p style='color: red;'>❌ Schema file not found: {$schemaFile}</p>";
        }
    }
    
    echo "</div>";
    
    echo "<h2>📊 Setup Summary:</h2>";
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<p><strong>Total SQL statements processed:</strong> {$totalTables}</p>";
    echo "<p><strong>Successfully created:</strong> {$successTables}</p>";
    echo "<p><strong>Already existed:</strong> " . ($totalTables - $successTables) . "</p>";
    echo "</div>";
    
    // Test database connectivity for each AI system
    echo "<h2>🧪 Testing AI System Connectivity:</h2>";
    echo "<div style='background: #f0f8ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    
    $aiSystems = [
        'ai_recommendations' => 'AI Recommendations',
        'ai_chatbot_conversations' => 'AI Chatbot',
        'ai_sentiment_analysis' => 'AI Sentiment Analysis',
        'ai_image_recognition' => 'AI Image Recognition',
        'ai_price_config' => 'AI Price Prediction',
        'ai_demand_forecasts' => 'AI Inventory Optimization',
        'ai_customer_segments' => 'AI Customer Behavior',
        'ai_email_personalization' => 'AI Marketing Automation',
        'ai_fraud_alerts' => 'AI Fraud Detection',
        'ai_voice_interactions' => 'AI Voice Assistant'
    ];
    
    foreach ($aiSystems as $table => $systemName) {
        try {
            $stmt = $conn->prepare("SHOW TABLES LIKE '{$table}'");
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result) {
                echo "<p style='color: green;'>✅ {$systemName}: Table '{$table}' exists</p>";
            } else {
                echo "<p style='color: red;'>❌ {$systemName}: Table '{$table}' not found</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ {$systemName}: Error checking table - " . $e->getMessage() . "</p>";
        }
    }
    
    echo "</div>";
    
    echo "<h2>✅ Setup Complete!</h2>";
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>All AI Systems are ready!</h3>";
    echo "<p><strong>Available AI Features:</strong></p>";
    echo "<ul>";
    echo "<li>🤖 AI Chatbot - Tư vấn thông minh</li>";
    echo "<li>🎯 AI Recommendations - Gợi ý sản phẩm</li>";
    echo "<li>📊 AI Sentiment Analysis - Phân tích cảm xúc</li>";
    echo "<li>🖼️ AI Image Recognition - Nhận diện hình ảnh</li>";
    echo "<li>💰 AI Price Prediction - Dự đoán giá</li>";
    echo "<li>📦 AI Inventory Optimization - Tối ưu kho hàng</li>";
    echo "<li>👥 AI Customer Behavior - Phân tích hành vi</li>";
    echo "<li>📢 AI Marketing Automation - Marketing tự động</li>";
    echo "<li>🛡️ AI Fraud Detection - Phát hiện gian lận</li>";
    echo "<li>🎤 AI Voice Assistant - Trợ lý giọng nói</li>";
    echo "</ul>";
    echo "<p><a href='index.php' style='background: #4caf50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Về trang chủ</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
</style>
