<?php
/**
 * Setup AI Fraud Detection
 * Linh2Store - Setup AI Fraud Detection System
 */

require_once 'config/database.php';

echo "<h1>🛡️ Setup AI Fraud Detection</h1>";
echo "<p>Thiết lập hệ thống phát hiện gian lận thông minh</p>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Read and execute schema
    $schemaFile = 'database/ai-fraud-detection-schema.sql';
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
        
        // Insert fraud alerts
        try {
            $stmt = $conn->prepare("
                INSERT INTO ai_fraud_alerts (alert_type, severity, description, fraud_score, user_id, transaction_id, created_at) VALUES
                ('suspicious_payment', 'high', 'Giao dịch thanh toán đáng ngờ với số tiền lớn', 0.85, 1, 1001, NOW()),
                ('multiple_failed_attempts', 'medium', 'Nhiều lần thử thanh toán thất bại', 0.65, 2, 1002, NOW()),
                ('unusual_location', 'low', 'Giao dịch từ vị trí địa lý bất thường', 0.45, 3, 1003, NOW()),
                ('rapid_successive_orders', 'high', 'Nhiều đơn hàng liên tiếp trong thời gian ngắn', 0.90, 4, 1004, NOW())
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Sample fraud alerts inserted</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Sample fraud alerts may already exist</p>";
        }
        
        // Insert payment fraud analysis
        try {
            $stmt = $conn->prepare("
                INSERT INTO ai_payment_fraud (transaction_id, payment_method, fraud_probability, risk_factors, created_at) VALUES
                (1001, 'credit_card', 0.85, 'High amount, new card, unusual time', NOW()),
                (1002, 'bank_transfer', 0.45, 'Normal amount, trusted account', NOW()),
                (1003, 'credit_card', 0.25, 'Low amount, regular customer', NOW()),
                (1004, 'e_wallet', 0.70, 'Multiple rapid transactions', NOW()),
                (1005, 'credit_card', 0.15, 'Low risk, verified customer', NOW())
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Sample payment fraud analysis inserted</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Sample payment fraud analysis may already exist</p>";
        }
        
        // Insert account security
        try {
            $stmt = $conn->prepare("
                INSERT INTO ai_account_security (user_id, security_score, device_trust_score, login_anomalies, last_analysis) VALUES
                (1, 0.95, 0.90, 0, NOW()),
                (2, 0.75, 0.80, 1, NOW()),
                (3, 0.60, 0.45, 3, NOW()),
                (4, 0.30, 0.20, 5, NOW()),
                (5, 0.85, 0.75, 0, NOW())
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Sample account security data inserted</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Sample account security data may already exist</p>";
        }
        
        // Insert behavioral analysis
        try {
            $stmt = $conn->prepare("
                INSERT INTO ai_behavioral_analysis (user_id, behavior_score, anomaly_detected, risk_level, created_at) VALUES
                (1, 0.95, 0, 'low', NOW()),
                (2, 0.80, 0, 'low', NOW()),
                (3, 0.60, 1, 'medium', NOW()),
                (4, 0.30, 1, 'high', NOW()),
                (5, 0.85, 0, 'low', NOW())
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Sample behavioral analysis inserted</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Sample behavioral analysis may already exist</p>";
        }
        
        echo "</div>";
        
        echo "<h2>✅ Setup Complete!</h2>";
        echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h3>AI Fraud Detection System is ready!</h3>";
        echo "<p><strong>Features available:</strong></p>";
        echo "<ul>";
        echo "<li>💳 Payment fraud detection</li>";
        echo "<li>👤 Account security monitoring</li>";
        echo "<li>🔄 Transaction monitoring</li>";
        echo "<li>🌍 Geographic analysis</li>";
        echo "<li>🤖 Behavioral analysis</li>";
        echo "<li>📊 Risk scoring</li>";
        echo "</ul>";
        echo "<p><a href='ai-fraud-dashboard.php' style='background: #4caf50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Open AI Fraud Dashboard</a></p>";
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
