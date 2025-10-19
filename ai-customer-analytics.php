<?php
/**
 * AI Customer Analytics
 * Linh2Store - AI Customer Behavior Analysis Dashboard
 */

require_once 'config/database.php';

echo "<h1>👥 AI Customer Analytics</h1>";
echo "<p>Phân tích hành vi khách hàng thông minh</p>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get customer segments
    $stmt = $conn->prepare("
        SELECT 
            CASE 
                WHEN total_orders >= 10 THEN 'VIP'
                WHEN total_orders >= 5 THEN 'Loyal'
                WHEN total_orders >= 2 THEN 'Regular'
                ELSE 'New'
            END as segment,
            COUNT(*) as customer_count,
            AVG(total_spent) as avg_spent,
            AVG(total_orders) as avg_orders
        FROM (
            SELECT 
                u.id,
                COUNT(o.id) as total_orders,
                COALESCE(SUM(o.total_amount), 0) as total_spent
            FROM users u
            LEFT JOIN orders o ON u.id = o.user_id AND o.status != 'cancelled'
            GROUP BY u.id
        ) customer_stats
        GROUP BY segment
        ORDER BY avg_spent DESC
    ");
    $stmt->execute();
    $segments = $stmt->fetchAll();
    
    echo "<h2>📊 Customer Segments</h2>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;'>";
    
    foreach ($segments as $segment) {
        $segmentColors = [
            'VIP' => '#d32f2f',
            'Loyal' => '#f57c00',
            'Regular' => '#1976d2',
            'New' => '#388e3c'
        ];
        
        $color = $segmentColors[$segment['segment']] ?? '#666';
        
        echo "<div style='background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-left: 4px solid {$color};'>";
        echo "<h3 style='color: {$color}; margin: 0 0 10px 0;'>{$segment['segment']}</h3>";
        echo "<p style='font-size: 24px; font-weight: bold; margin: 0;'>{$segment['customer_count']}</p>";
        echo "<p style='color: #666; margin: 5px 0;'>Khách hàng</p>";
        echo "<p style='margin: 0;'><strong>Trung bình:</strong> " . number_format($segment['avg_spent']) . "đ</p>";
        echo "<p style='margin: 0;'><strong>Đơn hàng:</strong> " . round($segment['avg_orders'], 1) . "</p>";
        echo "</div>";
    }
    
    echo "</div>";
    
    // Get customer lifetime value analysis
    $stmt = $conn->prepare("
        SELECT 
            AVG(total_spent) as avg_clv,
            MAX(total_spent) as max_clv,
            MIN(total_spent) as min_clv,
            COUNT(CASE WHEN total_spent > 1000000 THEN 1 END) as high_value_customers
        FROM (
            SELECT 
                u.id,
                COALESCE(SUM(o.total_amount), 0) as total_spent
            FROM users u
            LEFT JOIN orders o ON u.id = o.user_id AND o.status != 'cancelled'
            GROUP BY u.id
        ) customer_clv
    ");
    $stmt->execute();
    $clvData = $stmt->fetch();
    
    echo "<h2>💰 Customer Lifetime Value</h2>";
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;'>";
    
    echo "<div style='text-align: center;'>";
    echo "<h3 style='color: #1976d2;'>" . number_format($clvData['avg_clv']) . "đ</h3>";
    echo "<p>CLV Trung bình</p>";
    echo "</div>";
    
    echo "<div style='text-align: center;'>";
    echo "<h3 style='color: #4caf50;'>" . number_format($clvData['max_clv']) . "đ</h3>";
    echo "<p>CLV Cao nhất</p>";
    echo "</div>";
    
    echo "<div style='text-align: center;'>";
    echo "<h3 style='color: #f57c00;'>" . number_format($clvData['min_clv']) . "đ</h3>";
    echo "<p>CLV Thấp nhất</p>";
    echo "</div>";
    
    echo "<div style='text-align: center;'>";
    echo "<h3 style='color: #d32f2f;'>{$clvData['high_value_customers']}</h3>";
    echo "<p>Khách hàng VIP</p>";
    echo "</div>";
    
    echo "</div>";
    echo "</div>";
    
    // Get purchase patterns
    $stmt = $conn->prepare("
        SELECT 
            HOUR(created_at) as hour,
            COUNT(*) as order_count
        FROM orders 
        WHERE status != 'cancelled'
        GROUP BY HOUR(created_at)
        ORDER BY hour
    ");
    $stmt->execute();
    $purchasePatterns = $stmt->fetchAll();
    
    echo "<h2>📈 Purchase Patterns</h2>";
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>Thời gian mua hàng trong ngày:</h3>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 10px; margin: 20px 0;'>";
    
    foreach ($purchasePatterns as $pattern) {
        $percentage = ($pattern['order_count'] / array_sum(array_column($purchasePatterns, 'order_count'))) * 100;
        echo "<div style='text-align: center; background: white; padding: 10px; border-radius: 4px;'>";
        echo "<p style='margin: 0; font-weight: bold;'>{$pattern['hour']}:00</p>";
        echo "<p style='margin: 0; color: #666;'>{$pattern['order_count']} đơn</p>";
        echo "<div style='background: #4caf50; height: " . ($percentage * 2) . "px; margin: 5px 0; border-radius: 2px;'></div>";
        echo "</div>";
    }
    
    echo "</div>";
    echo "</div>";
    
    echo "<h2>🎯 AI Customer Analytics Features:</h2>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;'>";
    
    echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 8px;'>";
    echo "<h3>👥 Customer Segmentation</h3>";
    echo "<p>Phân loại khách hàng theo hành vi và giá trị</p>";
    echo "</div>";
    
    echo "<div style='background: #fff3e0; padding: 20px; border-radius: 8px;'>";
    echo "<h3>💰 Lifetime Value Prediction</h3>";
    echo "<p>Dự đoán giá trị khách hàng trong tương lai</p>";
    echo "</div>";
    
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px;'>";
    echo "<h3>📊 Purchase Pattern Analysis</h3>";
    echo "<p>Phân tích mẫu mua hàng và xu hướng</p>";
    echo "</div>";
    
    echo "<div style='background: #f3e5f5; padding: 20px; border-radius: 8px;'>";
    echo "<h3>🚨 Churn Prediction</h3>";
    echo "<p>Dự đoán khách hàng có thể rời bỏ</p>";
    echo "</div>";
    
    echo "<div style='background: #ffebee; padding: 20px; border-radius: 8px;'>";
    echo "<h3>🎯 Personalized Recommendations</h3>";
    echo "<p>Gợi ý sản phẩm cá nhân hóa</p>";
    echo "</div>";
    
    echo "<div style='background: #f0f8ff; padding: 20px; border-radius: 8px;'>";
    echo "<h3>📱 Engagement Scoring</h3>";
    echo "<p>Đánh giá mức độ tương tác khách hàng</p>";
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
