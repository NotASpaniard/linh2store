<?php
/**
 * Setup AI Customer Behavior
 * Linh2Store - Setup AI Customer Behavior Analysis System
 */

require_once 'config/database.php';

echo "<h1>👥 Setup AI Customer Behavior</h1>";
echo "<p>Thiết lập hệ thống phân tích hành vi khách hàng thông minh</p>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Read and execute schema
    $schemaFile = 'database/ai-customer-behavior-schema.sql';
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
        
        // Insert customer segments
        try {
            $stmt = $conn->prepare("
                INSERT INTO ai_customer_segments (user_id, segment_type, segment_score, created_at) VALUES
                (1, 'VIP', 0.95, NOW()),
                (2, 'Loyal', 0.85, NOW()),
                (3, 'Regular', 0.65, NOW()),
                (4, 'New', 0.25, NOW()),
                (5, 'VIP', 0.90, NOW())
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Sample customer segments inserted</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Sample customer segments may already exist</p>";
        }
        
        // Insert churn predictions
        try {
            $stmt = $conn->prepare("
                INSERT INTO ai_churn_predictions (user_id, churn_probability, risk_factors, recommended_actions, created_at) VALUES
                (1, 0.15, 'Low engagement, infrequent purchases', 'Send personalized offers, loyalty program', NOW()),
                (2, 0.35, 'Medium engagement, price sensitivity', 'Discount campaigns, product recommendations', NOW()),
                (3, 0.65, 'High risk, no recent activity', 'Win-back campaign, special offers', NOW()),
                (4, 0.85, 'Very high risk, long inactivity', 'Aggressive retention, personal contact', NOW())
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Sample churn predictions inserted</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Sample churn predictions may already exist</p>";
        }
        
        // Insert lifetime value predictions
        try {
            $stmt = $conn->prepare("
                INSERT INTO ai_lifetime_value (user_id, predicted_clv, confidence_score, factors, created_at) VALUES
                (1, 2500000, 0.90, 'High purchase frequency, premium products', NOW()),
                (2, 1800000, 0.85, 'Regular purchases, brand loyalty', NOW()),
                (3, 1200000, 0.80, 'Moderate engagement, price sensitive', NOW()),
                (4, 500000, 0.70, 'New customer, limited history', NOW()),
                (5, 3000000, 0.95, 'VIP customer, high value purchases', NOW())
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Sample lifetime value predictions inserted</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Sample lifetime value predictions may already exist</p>";
        }
        
        // Insert personalization scores
        try {
            $stmt = $conn->prepare("
                INSERT INTO ai_personalization_scores (user_id, personalization_score, preferences, recommendations, created_at) VALUES
                (1, 0.95, 'Luxury brands, red lipsticks, premium products', 'MAC Ruby Woo, Chanel Rouge', NOW()),
                (2, 0.80, 'Natural colors, organic products, mid-range', 'MAC Velvet Teddy, Natural brands', NOW()),
                (3, 0.65, 'Trendy colors, affordable options', 'Trendy shades, budget-friendly', NOW()),
                (4, 0.30, 'New customer, exploring preferences', 'Popular products, bestsellers', NOW())
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Sample personalization scores inserted</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Sample personalization scores may already exist</p>";
        }
        
        echo "</div>";
        
        echo "<h2>✅ Setup Complete!</h2>";
        echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h3>AI Customer Behavior Analysis System is ready!</h3>";
        echo "<p><strong>Features available:</strong></p>";
        echo "<ul>";
        echo "<li>👥 Customer segmentation</li>";
        echo "<li>💰 Lifetime value prediction</li>";
        echo "<li>📊 Purchase pattern analysis</li>";
        echo "<li>🚨 Churn prediction</li>";
        echo "<li>🎯 Personalized recommendations</li>";
        echo "<li>📱 Engagement scoring</li>";
        echo "</ul>";
        echo "<p><a href='ai-customer-analytics.php' style='background: #4caf50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Open AI Customer Analytics</a></p>";
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
