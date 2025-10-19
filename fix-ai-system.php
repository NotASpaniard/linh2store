<?php
/**
 * Fix AI System Issues
 * Linh2Store - Fix all AI system issues
 */

require_once 'config/database.php';

echo "<h1>🔧 Fix AI System Issues</h1>";
echo "<p>Sửa chữa tất cả lỗi trong hệ thống AI</p>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<h2>🔍 Checking AI System Status:</h2>";
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    
    // Check if AI tables exist
    $aiTables = [
        'ai_recommendations',
        'ai_chatbot_conversations', 
        'ai_sentiment_analysis',
        'ai_image_recognition',
        'ai_price_config',
        'ai_demand_forecasts',
        'ai_customer_segments',
        'ai_email_personalization',
        'ai_fraud_alerts',
        'ai_voice_interactions'
    ];
    
    $missingTables = [];
    $existingTables = [];
    
    foreach ($aiTables as $table) {
        try {
            $stmt = $conn->prepare("SHOW TABLES LIKE '{$table}'");
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result) {
                $existingTables[] = $table;
                echo "<p style='color: green;'>✅ Table '{$table}' exists</p>";
            } else {
                $missingTables[] = $table;
                echo "<p style='color: red;'>❌ Table '{$table}' missing</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error checking table '{$table}': " . $e->getMessage() . "</p>";
        }
    }
    
    echo "</div>";
    
    // Create missing tables
    if (!empty($missingTables)) {
        echo "<h2>🔧 Creating Missing Tables:</h2>";
        echo "<div style='background: #fff3e0; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        
        // Create basic tables for missing AI systems
        $createTableQueries = [
            'ai_price_config' => "
                CREATE TABLE IF NOT EXISTS ai_price_config (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    config_key VARCHAR(100) NOT NULL UNIQUE,
                    config_value TEXT NOT NULL,
                    description TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            'ai_fraud_alerts' => "
                CREATE TABLE IF NOT EXISTS ai_fraud_alerts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    alert_type VARCHAR(100) NOT NULL,
                    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
                    description TEXT NOT NULL,
                    fraud_score DECIMAL(3,2) NOT NULL,
                    user_id INT NULL,
                    transaction_id INT NULL,
                    is_resolved BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            'ai_voice_interactions' => "
                CREATE TABLE IF NOT EXISTS ai_voice_interactions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    voice_input TEXT NOT NULL,
                    voice_output TEXT NOT NULL,
                    intent_recognized VARCHAR(100) NOT NULL,
                    response_time_ms INT NOT NULL,
                    satisfaction_score INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            "
        ];
        
        foreach ($missingTables as $table) {
            if (isset($createTableQueries[$table])) {
                try {
                    $conn->exec($createTableQueries[$table]);
                    echo "<p style='color: green;'>✅ Created table '{$table}'</p>";
                } catch (Exception $e) {
                    echo "<p style='color: red;'>❌ Failed to create table '{$table}': " . $e->getMessage() . "</p>";
                }
            } else {
                echo "<p style='color: orange;'>⚠️ No creation query for table '{$table}'</p>";
            }
        }
        
        echo "</div>";
    }
    
    // Test AI system functionality
    echo "<h2>🧪 Testing AI System Functionality:</h2>";
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    
    // Test AI Recommendations
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM ai_recommendations");
        $stmt->execute();
        $result = $stmt->fetch();
        echo "<p style='color: green;'>✅ AI Recommendations: {$result['count']} records</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ AI Recommendations: " . $e->getMessage() . "</p>";
    }
    
    // Test AI Chatbot
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM ai_chatbot_conversations");
        $stmt->execute();
        $result = $stmt->fetch();
        echo "<p style='color: green;'>✅ AI Chatbot: {$result['count']} conversations</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ AI Chatbot: " . $e->getMessage() . "</p>";
    }
    
    // Test AI Sentiment Analysis
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM ai_sentiment_analysis");
        $stmt->execute();
        $result = $stmt->fetch();
        echo "<p style='color: green;'>✅ AI Sentiment Analysis: {$result['count']} records</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ AI Sentiment Analysis: " . $e->getMessage() . "</p>";
    }
    
    echo "</div>";
    
    echo "<h2>✅ AI System Fix Complete!</h2>";
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>AI System Status:</h3>";
    echo "<p><strong>Existing tables:</strong> " . count($existingTables) . "</p>";
    echo "<p><strong>Missing tables:</strong> " . count($missingTables) . "</p>";
    echo "<p><strong>Total AI tables:</strong> " . count($aiTables) . "</p>";
    echo "<p><a href='setup-all-ai-tables.php' style='background: #4caf50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Setup All AI Tables</a></p>";
    echo "<p><a href='index.php' style='background: #EC407A; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Về trang chủ</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
</style>
