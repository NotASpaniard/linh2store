<?php
/**
 * AI Inventory Dashboard
 * Linh2Store - AI Inventory Optimization Dashboard
 */

require_once 'config/database.php';
require_once 'config/ai-inventory-optimization.php';

echo "<h1>📦 AI Inventory Dashboard</h1>";
echo "<p>Bảng điều khiển tối ưu hóa kho hàng thông minh</p>";

try {
    $aiInventory = new AIInventoryOptimization();
    
    // Get inventory analytics
    $analytics = $aiInventory->getInventoryAnalytics();
    
    echo "<h2>📊 Inventory Analytics</h2>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;'>";
    
    echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h3 style='color: #1976d2;'>" . ($analytics['total_products'] ?? 0) . "</h3>";
    echo "<p>Tổng sản phẩm</p>";
    echo "</div>";
    
    echo "<div style='background: #fff3e0; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h3 style='color: #f57c00;'>" . ($analytics['low_stock_count'] ?? 0) . "</h3>";
    echo "<p>Sản phẩm sắp hết</p>";
    echo "</div>";
    
    echo "<div style='background: #ffebee; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h3 style='color: #d32f2f;'>" . ($analytics['overstock_count'] ?? 0) . "</h3>";
    echo "<p>Sản phẩm tồn kho</p>";
    echo "</div>";
    
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h3 style='color: #388e3c;'>" . round(($analytics['turnover_rate'] ?? 0) * 100, 1) . "%</h3>";
    echo "<p>Tỷ lệ luân chuyển</p>";
    echo "</div>";
    
    echo "<div style='background: #f3e5f5; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h3 style='color: #7b1fa2;'>" . number_format($analytics['carrying_cost'] ?? 0) . "đ</h3>";
    echo "<p>Chi phí lưu kho</p>";
    echo "</div>";
    
    echo "<div style='background: #e0f2f1; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h3 style='color: #00695c;'>" . round(($analytics['optimization_score'] ?? 0) * 100, 1) . "%</h3>";
    echo "<p>Điểm tối ưu hóa</p>";
    echo "</div>";
    
    echo "</div>";
    
    // Generate stock alerts
    $alerts = $aiInventory->generateStockAlerts();
    
    echo "<h2>🚨 Stock Alerts</h2>";
    if (!empty($alerts)) {
        echo "<div style='background: #fff3e0; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        foreach ($alerts as $alert) {
            $severityColor = [
                'low' => '#4caf50',
                'medium' => '#ff9800',
                'high' => '#f44336',
                'critical' => '#d32f2f'
            ][$alert['severity']] ?? '#666';
            
            echo "<div style='border-left: 4px solid {$severityColor}; padding: 15px; margin: 10px 0; background: white; border-radius: 4px;'>";
            echo "<h4 style='color: {$severityColor}; margin: 0 0 10px 0;'>" . strtoupper($alert['severity']) . "</h4>";
            echo "<p style='margin: 0;'>{$alert['message']}</p>";
            if ($alert['recommended_action']) {
                echo "<p style='margin: 10px 0 0 0; color: #666;'><strong>Khuyến nghị:</strong> {$alert['recommended_action']}</p>";
            }
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p style='color: green;'>✅ Không có cảnh báo nào</p>";
    }
    
    // Generate supplier recommendations
    $recommendations = $aiInventory->generateSupplierRecommendations();
    
    echo "<h2>🏪 Supplier Recommendations</h2>";
    if (!empty($recommendations)) {
        echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        foreach ($recommendations as $rec) {
            echo "<div style='background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>";
            echo "<h4 style='color: #1976d2; margin: 0 0 10px 0;'>" . strtoupper($rec['recommendation_type']) . "</h4>";
            echo "<p style='margin: 0 0 10px 0;'>{$rec['reasoning']}</p>";
            echo "<div style='display: flex; justify-content: space-between; align-items: center;'>";
            echo "<span style='color: #666;'>Độ ưu tiên: " . round($rec['priority_score'] * 100, 1) . "%</span>";
            echo "<span style='color: #4caf50; font-weight: bold;'>Tiết kiệm: " . number_format($rec['estimated_savings']) . "đ</span>";
            echo "</div>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p style='color: green;'>✅ Không có khuyến nghị nào</p>";
    }
    
    echo "<h2>🎯 AI Inventory Features:</h2>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;'>";
    
    echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 8px;'>";
    echo "<h3>📊 Demand Prediction</h3>";
    echo "<p>Dự đoán nhu cầu sản phẩm dựa trên lịch sử bán hàng</p>";
    echo "</div>";
    
    echo "<div style='background: #fff3e0; padding: 20px; border-radius: 8px;'>";
    echo "<h3>🚨 Smart Alerts</h3>";
    echo "<p>Cảnh báo thông minh về tình trạng kho hàng</p>";
    echo "</div>";
    
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px;'>";
    echo "<h3>🏪 Supplier Optimization</h3>";
    echo "<p>Tối ưu hóa nhà cung cấp và chi phí</p>";
    echo "</div>";
    
    echo "<div style='background: #f3e5f5; padding: 20px; border-radius: 8px;'>";
    echo "<h3>📈 Pattern Analysis</h3>";
    echo "<p>Phân tích mẫu tiêu thụ và xu hướng</p>";
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
