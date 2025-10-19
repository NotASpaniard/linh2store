<?php
/**
 * AI Marketing Dashboard
 * Linh2Store - AI Marketing Automation Dashboard
 */

require_once 'config/database.php';

echo "<h1>📢 AI Marketing Dashboard</h1>";
echo "<p>Bảng điều khiển marketing tự động thông minh</p>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get marketing campaigns data
    $stmt = $conn->prepare("
        SELECT 
            'Email' as campaign_type,
            COUNT(*) as total_campaigns,
            AVG(open_rate) as avg_open_rate,
            AVG(click_rate) as avg_click_rate
        FROM ai_email_personalization
        UNION ALL
        SELECT 
            'Social Media' as campaign_type,
            COUNT(*) as total_campaigns,
            AVG(engagement_rate) as avg_open_rate,
            AVG(conversion_rate) as avg_click_rate
        FROM ai_social_insights
    ");
    $stmt->execute();
    $campaignData = $stmt->fetchAll();
    
    echo "<h2>📊 Marketing Campaigns Performance</h2>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;'>";
    
    foreach ($campaignData as $campaign) {
        echo "<div style='background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>";
        echo "<h3 style='color: #1976d2; margin: 0 0 10px 0;'>{$campaign['campaign_type']}</h3>";
        echo "<p style='font-size: 24px; font-weight: bold; margin: 0;'>{$campaign['total_campaigns']}</p>";
        echo "<p style='color: #666; margin: 5px 0;'>Campaigns</p>";
        echo "<p style='margin: 0;'><strong>Open Rate:</strong> " . round($campaign['avg_open_rate'] ?? 0, 1) . "%</p>";
        echo "<p style='margin: 0;'><strong>Click Rate:</strong> " . round($campaign['avg_click_rate'] ?? 0, 1) . "%</p>";
        echo "</div>";
    }
    
    echo "</div>";
    
    // Get A/B testing results
    $stmt = $conn->prepare("
        SELECT 
            test_name,
            test_type,
            winner_variant,
            confidence_level,
            CASE 
                WHEN winner_variant = 'A' THEN 'Variant A Wins'
                WHEN winner_variant = 'B' THEN 'Variant B Wins'
                ELSE 'Inconclusive'
            END as result
        FROM ai_ab_testing
        WHERE test_end IS NOT NULL
        ORDER BY confidence_level DESC
        LIMIT 5
    ");
    $stmt->execute();
    $abTests = $stmt->fetchAll();
    
    echo "<h2>🧪 A/B Testing Results</h2>";
    if (!empty($abTests)) {
        echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        foreach ($abTests as $test) {
            $confidenceColor = $test['confidence_level'] > 0.8 ? '#4caf50' : ($test['confidence_level'] > 0.6 ? '#ff9800' : '#f44336');
            echo "<div style='background: white; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid {$confidenceColor};'>";
            echo "<h4 style='margin: 0 0 10px 0;'>{$test['test_name']}</h4>";
            echo "<p style='margin: 0;'><strong>Type:</strong> {$test['test_type']}</p>";
            echo "<p style='margin: 0;'><strong>Result:</strong> {$test['result']}</p>";
            echo "<p style='margin: 0;'><strong>Confidence:</strong> " . round($test['confidence_level'] * 100, 1) . "%</p>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p style='color: orange;'>⚠️ Chưa có dữ liệu A/B testing</p>";
    }
    
    // Get content generation stats
    $stmt = $conn->prepare("
        SELECT 
            content_type,
            COUNT(*) as total_content,
            AVG(quality_score) as avg_quality,
            SUM(usage_count) as total_usage
        FROM ai_content_generation
        GROUP BY content_type
    ");
    $stmt->execute();
    $contentStats = $stmt->fetchAll();
    
    echo "<h2>📝 AI Content Generation</h2>";
    if (!empty($contentStats)) {
        echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;'>";
        
        foreach ($contentStats as $content) {
            echo "<div style='background: white; padding: 15px; border-radius: 8px; text-align: center;'>";
            echo "<h4 style='color: #1976d2; margin: 0 0 10px 0;'>" . ucfirst(str_replace('_', ' ', $content['content_type'])) . "</h4>";
            echo "<p style='font-size: 20px; font-weight: bold; margin: 0;'>{$content['total_content']}</p>";
            echo "<p style='color: #666; margin: 5px 0;'>Content Generated</p>";
            echo "<p style='margin: 0;'><strong>Quality:</strong> " . round($content['avg_quality'] * 100, 1) . "%</p>";
            echo "<p style='margin: 0;'><strong>Usage:</strong> {$content['total_usage']}</p>";
            echo "</div>";
        }
        
        echo "</div>";
        echo "</div>";
    } else {
        echo "<p style='color: orange;'>⚠️ Chưa có dữ liệu content generation</p>";
    }
    
    echo "<h2>🎯 AI Marketing Features:</h2>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;'>";
    
    echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 8px;'>";
    echo "<h3>📧 Email Personalization</h3>";
    echo "<p>Tự động hóa email marketing với nội dung cá nhân hóa</p>";
    echo "</div>";
    
    echo "<div style='background: #fff3e0; padding: 20px; border-radius: 8px;'>";
    echo "<h3>📱 Social Media Insights</h3>";
    echo "<p>Phân tích hiệu suất và xu hướng mạng xã hội</p>";
    echo "</div>";
    
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px;'>";
    echo "<h3>🧪 A/B Testing</h3>";
    echo "<p>Thử nghiệm và tối ưu hóa chiến dịch marketing</p>";
    echo "</div>";
    
    echo "<div style='background: #f3e5f5; padding: 20px; border-radius: 8px;'>";
    echo "<h3>📝 Content Generation</h3>";
    echo "<p>Tự động tạo nội dung marketing chất lượng cao</p>";
    echo "</div>";
    
    echo "<div style='background: #ffebee; padding: 20px; border-radius: 8px;'>";
    echo "<h3>🎯 Audience Targeting</h3>";
    echo "<p>Phân khúc và nhắm mục tiêu khách hàng chính xác</p>";
    echo "</div>";
    
    echo "<div style='background: #f0f8ff; padding: 20px; border-radius: 8px;'>";
    echo "<h3>📊 Performance Analytics</h3>";
    echo "<p>Phân tích hiệu suất và ROI của chiến dịch</p>";
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
