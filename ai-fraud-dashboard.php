<?php
/**
 * AI Fraud Dashboard
 * Linh2Store - AI Fraud Detection Dashboard
 */

require_once 'config/database.php';

echo "<h1>🛡️ AI Fraud Dashboard</h1>";
echo "<p>Bảng điều khiển phát hiện gian lận thông minh</p>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get fraud alerts summary
    $stmt = $conn->prepare("
        SELECT 
            alert_type,
            severity,
            COUNT(*) as alert_count,
            AVG(fraud_score) as avg_fraud_score
        FROM ai_fraud_alerts
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY alert_type, severity
        ORDER BY alert_count DESC
    ");
    $stmt->execute();
    $fraudAlerts = $stmt->fetchAll();
    
    echo "<h2>🚨 Fraud Alerts (30 ngày qua)</h2>";
    if (!empty($fraudAlerts)) {
        echo "<div style='background: #ffebee; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        foreach ($fraudAlerts as $alert) {
            $severityColors = [
                'low' => '#4caf50',
                'medium' => '#ff9800',
                'high' => '#f44336',
                'critical' => '#d32f2f'
            ];
            
            $color = $severityColors[$alert['severity']] ?? '#666';
            
            echo "<div style='background: white; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid {$color};'>";
            echo "<h4 style='color: {$color}; margin: 0 0 10px 0;'>" . strtoupper($alert['alert_type']) . " - " . strtoupper($alert['severity']) . "</h4>";
            echo "<p style='margin: 0;'><strong>Số lượng:</strong> {$alert['alert_count']}</p>";
            echo "<p style='margin: 0;'><strong>Điểm gian lận TB:</strong> " . round($alert['avg_fraud_score'] * 100, 1) . "%</p>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p style='color: green;'>✅ Không có cảnh báo gian lận trong 30 ngày qua</p>";
    }
    
    // Get payment fraud analysis
    $stmt = $conn->prepare("
        SELECT 
            payment_method,
            COUNT(*) as total_transactions,
            AVG(fraud_probability) as avg_fraud_prob,
            COUNT(CASE WHEN fraud_probability > 0.7 THEN 1 END) as high_risk_count
        FROM ai_payment_fraud
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY payment_method
        ORDER BY avg_fraud_prob DESC
    ");
    $stmt->execute();
    $paymentFraud = $stmt->fetchAll();
    
    echo "<h2>💳 Payment Fraud Analysis</h2>";
    if (!empty($paymentFraud)) {
        echo "<div style='background: #fff3e0; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;'>";
        
        foreach ($paymentFraud as $payment) {
            $riskColor = $payment['avg_fraud_prob'] > 0.7 ? '#f44336' : ($payment['avg_fraud_prob'] > 0.4 ? '#ff9800' : '#4caf50');
            
            echo "<div style='background: white; padding: 15px; border-radius: 8px; text-align: center; border-top: 4px solid {$riskColor};'>";
            echo "<h4 style='color: {$riskColor}; margin: 0 0 10px 0;'>{$payment['payment_method']}</h4>";
            echo "<p style='font-size: 20px; font-weight: bold; margin: 0;'>{$payment['total_transactions']}</p>";
            echo "<p style='color: #666; margin: 5px 0;'>Giao dịch</p>";
            echo "<p style='margin: 0;'><strong>Rủi ro TB:</strong> " . round($payment['avg_fraud_prob'] * 100, 1) . "%</p>";
            echo "<p style='margin: 0;'><strong>Rủi ro cao:</strong> {$payment['high_risk_count']}</p>";
            echo "</div>";
        }
        
        echo "</div>";
        echo "</div>";
    } else {
        echo "<p style='color: orange;'>⚠️ Chưa có dữ liệu phân tích thanh toán</p>";
    }
    
    // Get account security analysis
    $stmt = $conn->prepare("
        SELECT 
            AVG(security_score) as avg_security_score,
            COUNT(CASE WHEN security_score < 0.5 THEN 1 END) as low_security_count,
            COUNT(CASE WHEN device_trust_score < 0.3 THEN 1 END) as suspicious_devices
        FROM ai_account_security
        WHERE last_analysis >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $stmt->execute();
    $securityData = $stmt->fetch();
    
    echo "<h2>🔒 Account Security Analysis</h2>";
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;'>";
    
    $securityScore = $securityData['avg_security_score'] ?? 0;
    $securityColor = $securityScore > 0.8 ? '#4caf50' : ($securityScore > 0.6 ? '#ff9800' : '#f44336');
    
    echo "<div style='text-align: center; background: white; padding: 20px; border-radius: 8px;'>";
    echo "<h3 style='color: {$securityColor};'>" . round($securityScore * 100, 1) . "%</h3>";
    echo "<p>Điểm bảo mật TB</p>";
    echo "</div>";
    
    echo "<div style='text-align: center; background: white; padding: 20px; border-radius: 8px;'>";
    echo "<h3 style='color: #f44336;'>{$securityData['low_security_count']}</h3>";
    echo "<p>Tài khoản rủi ro</p>";
    echo "</div>";
    
    echo "<div style='text-align: center; background: white; padding: 20px; border-radius: 8px;'>";
    echo "<h3 style='color: #ff9800;'>{$securityData['suspicious_devices']}</h3>";
    echo "<p>Thiết bị đáng ngờ</p>";
    echo "</div>";
    
    echo "</div>";
    echo "</div>";
    
    echo "<h2>🎯 AI Fraud Detection Features:</h2>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;'>";
    
    echo "<div style='background: #ffebee; padding: 20px; border-radius: 8px;'>";
    echo "<h3>💳 Payment Fraud Detection</h3>";
    echo "<p>Phát hiện gian lận thanh toán trong thời gian thực</p>";
    echo "</div>";
    
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px;'>";
    echo "<h3>👤 Account Security</h3>";
    echo "<p>Giám sát bảo mật tài khoản và thiết bị</p>";
    echo "</div>";
    
    echo "<div style='background: #fff3e0; padding: 20px; border-radius: 8px;'>";
    echo "<h3>🔄 Transaction Monitoring</h3>";
    echo "<p>Theo dõi và phân tích giao dịch đáng ngờ</p>";
    echo "</div>";
    
    echo "<div style='background: #f3e5f5; padding: 20px; border-radius: 8px;'>";
    echo "<h3>🌍 Geographic Analysis</h3>";
    echo "<p>Phân tích vị trí địa lý và bất thường</p>";
    echo "</div>";
    
    echo "<div style='background: #e0f2f1; padding: 20px; border-radius: 8px;'>";
    echo "<h3>🤖 Behavioral Analysis</h3>";
    echo "<p>Phân tích hành vi bất thường của người dùng</p>";
    echo "</div>";
    
    echo "<div style='background: #f0f8ff; padding: 20px; border-radius: 8px;'>";
    echo "<h3>📊 Risk Scoring</h3>";
    echo "<p>Đánh giá rủi ro và điểm số gian lận</p>";
    echo "</div>";
    
    echo "</div>";
    
    echo "<p><a href='index.php' style='background: #EC407A; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Về trang chủ</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
</style>
