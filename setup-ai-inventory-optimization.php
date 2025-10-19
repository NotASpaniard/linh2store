<?php
/**
 * Setup AI Inventory Optimization
 * Linh2Store - Setup AI Inventory Optimization System
 */

require_once 'config/database.php';

echo "<h1>📦 Setup AI Inventory Optimization</h1>";
echo "<p>Thiết lập hệ thống tối ưu hóa kho hàng thông minh</p>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Read and execute schema
    $schemaFile = 'database/ai-inventory-optimization-schema.sql';
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
        
        // Insert demand forecasts
        try {
            $stmt = $conn->prepare("
                INSERT INTO ai_demand_forecasts (product_id, forecast_date, predicted_demand, confidence_score, model_used) VALUES
                (1, DATE_ADD(NOW(), INTERVAL 7 DAY), 25, 0.85, 'seasonal_arima'),
                (1, DATE_ADD(NOW(), INTERVAL 14 DAY), 30, 0.80, 'seasonal_arima'),
                (1, DATE_ADD(NOW(), INTERVAL 30 DAY), 35, 0.75, 'seasonal_arima'),
                (2, DATE_ADD(NOW(), INTERVAL 7 DAY), 15, 0.90, 'linear_regression'),
                (2, DATE_ADD(NOW(), INTERVAL 14 DAY), 18, 0.85, 'linear_regression'),
                (2, DATE_ADD(NOW(), INTERVAL 30 DAY), 22, 0.80, 'linear_regression')
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Sample demand forecasts inserted</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Sample demand forecasts may already exist</p>";
        }
        
        // Insert stock alerts
        try {
            $stmt = $conn->prepare("
                INSERT INTO ai_stock_alerts (product_id, alert_type, severity, message, recommended_action, created_at) VALUES
                (1, 'low_stock', 'medium', 'Sản phẩm MAC Ruby Woo sắp hết hàng (còn 5 sản phẩm)', 'Đặt hàng ngay lập tức', NOW()),
                (2, 'overstock', 'low', 'Sản phẩm MAC Velvet Teddy tồn kho nhiều (50 sản phẩm)', 'Giảm giá hoặc tăng marketing', NOW()),
                (3, 'reorder_point', 'high', 'Sản phẩm MAC Chili đã đến điểm đặt hàng', 'Liên hệ nhà cung cấp', NOW())
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Sample stock alerts inserted</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Sample stock alerts may already exist</p>";
        }
        
        // Insert supplier recommendations
        try {
            $stmt = $conn->prepare("
                INSERT INTO ai_supplier_recommendations (supplier_id, recommendation_type, reasoning, priority_score, estimated_savings, created_at) VALUES
                (1, 'bulk_purchase', 'Mua số lượng lớn để giảm giá đơn vị', 0.8, 500000, NOW()),
                (2, 'negotiate_price', 'Thương lượng giá với nhà cung cấp hiện tại', 0.7, 300000, NOW()),
                (3, 'alternative_supplier', 'Tìm nhà cung cấp thay thế với giá tốt hơn', 0.6, 200000, NOW())
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Sample supplier recommendations inserted</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Sample supplier recommendations may already exist</p>";
        }
        
        echo "</div>";
        
        echo "<h2>✅ Setup Complete!</h2>";
        echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h3>AI Inventory Optimization System is ready!</h3>";
        echo "<p><strong>Features available:</strong></p>";
        echo "<ul>";
        echo "<li>📊 Demand forecasting</li>";
        echo "<li>🚨 Smart stock alerts</li>";
        echo "<li>🏪 Supplier optimization</li>";
        echo "<li>📈 Pattern analysis</li>";
        echo "<li>💰 Cost optimization</li>";
        echo "<li>📦 Warehouse efficiency</li>";
        echo "</ul>";
        echo "<p><a href='ai-inventory-dashboard.php' style='background: #4caf50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Open AI Inventory Dashboard</a></p>";
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
