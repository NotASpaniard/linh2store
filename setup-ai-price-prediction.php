<?php
/**
 * Setup AI Price Prediction
 * Linh2Store - Setup AI Price Prediction System
 */

require_once 'config/database.php';

echo "<h1>💰 Setup AI Price Prediction</h1>";
echo "<p>Thiết lập hệ thống dự đoán giá sản phẩm thông minh</p>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Read and execute schema
    $schemaFile = 'database/ai-price-prediction-schema.sql';
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
        
        // Insert price config
        try {
            $stmt = $conn->prepare("
                INSERT INTO ai_price_config (config_key, config_value, description) VALUES
                ('prediction_horizon_days', '30', 'Số ngày dự đoán trước'),
                ('confidence_threshold', '0.7', 'Ngưỡng tin cậy tối thiểu'),
                ('seasonal_analysis', '1', 'Bật phân tích mùa vụ'),
                ('market_analysis', '1', 'Bật phân tích thị trường'),
                ('competitor_analysis', '1', 'Bật phân tích đối thủ')
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Price configuration inserted</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Price configuration may already exist</p>";
        }
        
        // Insert sample price history
        try {
            $stmt = $conn->prepare("
                INSERT INTO ai_price_history (product_id, price, date_recorded, source) VALUES
                (1, 650000, DATE_SUB(NOW(), INTERVAL 30 DAY), 'manual'),
                (1, 655000, DATE_SUB(NOW(), INTERVAL 25 DAY), 'manual'),
                (1, 660000, DATE_SUB(NOW(), INTERVAL 20 DAY), 'manual'),
                (1, 665000, DATE_SUB(NOW(), INTERVAL 15 DAY), 'manual'),
                (1, 670000, DATE_SUB(NOW(), INTERVAL 10 DAY), 'manual'),
                (1, 675000, DATE_SUB(NOW(), INTERVAL 5 DAY), 'manual'),
                (2, 450000, DATE_SUB(NOW(), INTERVAL 30 DAY), 'manual'),
                (2, 455000, DATE_SUB(NOW(), INTERVAL 25 DAY), 'manual'),
                (2, 460000, DATE_SUB(NOW(), INTERVAL 20 DAY), 'manual'),
                (2, 465000, DATE_SUB(NOW(), INTERVAL 15 DAY), 'manual'),
                (2, 470000, DATE_SUB(NOW(), INTERVAL 10 DAY), 'manual'),
                (2, 475000, DATE_SUB(NOW(), INTERVAL 5 DAY), 'manual')
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Sample price history inserted</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Sample price history may already exist</p>";
        }
        
        // Insert sample market trends
        try {
            $stmt = $conn->prepare("
                INSERT INTO ai_market_trends (trend_type, trend_value, date_recorded, confidence) VALUES
                ('overall_market', 0.05, DATE_SUB(NOW(), INTERVAL 7 DAY), 0.8),
                ('beauty_industry', 0.08, DATE_SUB(NOW(), INTERVAL 7 DAY), 0.9),
                ('lipstick_category', 0.12, DATE_SUB(NOW(), INTERVAL 7 DAY), 0.85),
                ('luxury_segment', 0.15, DATE_SUB(NOW(), INTERVAL 7 DAY), 0.75)
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Sample market trends inserted</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Sample market trends may already exist</p>";
        }
        
        echo "</div>";
        
        echo "<h2>✅ Setup Complete!</h2>";
        echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h3>AI Price Prediction System is ready!</h3>";
        echo "<p><strong>Features available:</strong></p>";
        echo "<ul>";
        echo "<li>📊 Historical trend analysis</li>";
        echo "<li>🌱 Seasonal pattern detection</li>";
        echo "<li>🏪 Market trend analysis</li>";
        echo "<li>🏆 Competitor price analysis</li>";
        echo "<li>🤖 Ensemble prediction models</li>";
        echo "<li>📈 Confidence scoring</li>";
        echo "</ul>";
        echo "<p><a href='ai-price-prediction-demo.php' style='background: #4caf50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test AI Price Prediction</a></p>";
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
