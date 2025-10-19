<?php
/**
 * AI Price Prediction Demo
 * Linh2Store - AI Price Prediction System
 */

require_once 'config/database.php';
require_once 'config/ai-price-prediction.php';

echo "<h1>💰 AI Price Prediction Demo</h1>";
echo "<p>Hệ thống dự đoán giá sản phẩm thông minh</p>";

try {
    $aiPricePrediction = new AIPricePrediction();
    
    // Test price prediction
    $productId = 1; // Test with product ID 1
    $predictions = $aiPricePrediction->predictPrice($productId, 30); // 30 days ahead
    
    echo "<h2>📊 Price Predictions for Product ID {$productId}:</h2>";
    
    if (!empty($predictions)) {
        echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h3>Dự đoán giá trong 30 ngày tới:</h3>";
        echo "<ul>";
        foreach (array_slice($predictions, 0, 10) as $prediction) {
            $date = date('d/m/Y', strtotime($prediction['date']));
            $price = number_format($prediction['predicted_price']);
            $confidence = round($prediction['confidence_score'] * 100, 1);
            echo "<li><strong>{$date}:</strong> {$price}đ (Độ tin cậy: {$confidence}%)</li>";
        }
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<p style='color: orange;'>⚠️ Chưa có dữ liệu để dự đoán. Cần có lịch sử giá sản phẩm.</p>";
    }
    
    // Test market analysis
    $marketAnalysis = $aiPricePrediction->analyzeMarketTrends();
    
    echo "<h2>📈 Market Analysis:</h2>";
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>Phân tích xu hướng thị trường:</h3>";
    echo "<ul>";
    echo "<li><strong>Xu hướng chung:</strong> " . ($marketAnalysis['overall_trend'] ?? 'Tăng trưởng ổn định') . "</li>";
    echo "<li><strong>Biến động giá:</strong> " . ($marketAnalysis['price_volatility'] ?? 'Thấp') . "</li>";
    echo "<li><strong>Mùa vụ:</strong> " . ($marketAnalysis['seasonal_pattern'] ?? 'Không có mùa vụ rõ ràng') . "</li>";
    echo "</ul>";
    echo "</div>";
    
    // Test competitor analysis
    $competitorAnalysis = $aiPricePrediction->analyzeCompetitorPrices();
    
    echo "<h2>🏆 Competitor Analysis:</h2>";
    echo "<div style='background: #fff3e0; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>Phân tích giá đối thủ:</h3>";
    echo "<ul>";
    echo "<li><strong>Giá trung bình thị trường:</strong> " . number_format($competitorAnalysis['market_average'] ?? 0) . "đ</li>";
    echo "<li><strong>Vị thế cạnh tranh:</strong> " . ($competitorAnalysis['competitive_position'] ?? 'Trung bình') . "</li>";
    echo "<li><strong>Khuyến nghị:</strong> " . ($competitorAnalysis['recommendation'] ?? 'Giữ nguyên giá') . "</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h2>🎯 AI Price Prediction Features:</h2>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;'>";
    
    echo "<div style='background: #f0f8ff; padding: 20px; border-radius: 8px;'>";
    echo "<h3>📊 Historical Trend Analysis</h3>";
    echo "<p>Phân tích xu hướng giá lịch sử để dự đoán tương lai</p>";
    echo "</div>";
    
    echo "<div style='background: #f0fff0; padding: 20px; border-radius: 8px;'>";
    echo "<h3>🌱 Seasonal Analysis</h3>";
    echo "<p>Phân tích mùa vụ và chu kỳ giá sản phẩm</p>";
    echo "</div>";
    
    echo "<div style='background: #fff8f0; padding: 20px; border-radius: 8px;'>";
    echo "<h3>🏪 Market Analysis</h3>";
    echo "<p>Phân tích thị trường và xu hướng ngành</p>";
    echo "</div>";
    
    echo "<div style='background: #f8f0ff; padding: 20px; border-radius: 8px;'>";
    echo "<h3>🏆 Competitor Analysis</h3>";
    echo "<p>Phân tích giá đối thủ và vị thế cạnh tranh</p>";
    echo "</div>";
    
    echo "<div style='background: #f0f8f8; padding: 20px; border-radius: 8px;'>";
    echo "<h3>🤖 Ensemble Prediction</h3>";
    echo "<p>Kết hợp nhiều mô hình AI để dự đoán chính xác</p>";
    echo "</div>";
    
    echo "<div style='background: #fff0f8; padding: 20px; border-radius: 8px;'>";
    echo "<h3>📈 Confidence Scoring</h3>";
    echo "<p>Đánh giá độ tin cậy của dự đoán</p>";
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
ul { list-style-type: none; padding: 0; }
li { margin: 10px 0; padding: 10px; background: white; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
</style>
