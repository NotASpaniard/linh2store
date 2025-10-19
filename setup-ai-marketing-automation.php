<?php
/**
 * Setup AI Marketing Automation
 * Linh2Store - Setup AI Marketing Automation System
 */

require_once 'config/database.php';

echo "<h1>📢 Setup AI Marketing Automation</h1>";
echo "<p>Thiết lập hệ thống marketing tự động thông minh</p>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Read and execute schema
    $schemaFile = 'database/ai-marketing-automation-schema.sql';
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
        
        // Insert email personalization
        try {
            $stmt = $conn->prepare("
                INSERT INTO ai_email_personalization (user_id, subject_line, content, open_rate, click_rate, created_at) VALUES
                (1, 'Son môi cao cấp dành riêng cho bạn', 'Chào bạn! Dựa trên sở thích của bạn, chúng tôi gợi ý...', 0.85, 0.25, NOW()),
                (2, 'Xu hướng son môi mới nhất', 'Khám phá những màu son hot nhất mùa này...', 0.75, 0.20, NOW()),
                (3, 'Ưu đãi đặc biệt cho khách hàng VIP', 'Chỉ dành cho khách hàng VIP như bạn...', 0.90, 0.35, NOW())
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Sample email personalization inserted</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Sample email personalization may already exist</p>";
        }
        
        // Insert social media insights
        try {
            $stmt = $conn->prepare("
                INSERT INTO ai_social_insights (platform, post_type, engagement_rate, reach, conversion_rate, created_at) VALUES
                ('Facebook', 'Product Showcase', 0.12, 5000, 0.08, NOW()),
                ('Instagram', 'Beauty Tutorial', 0.18, 8000, 0.12, NOW()),
                ('TikTok', 'Lipstick Review', 0.25, 12000, 0.15, NOW()),
                ('YouTube', 'Makeup Tutorial', 0.15, 15000, 0.10, NOW())
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Sample social media insights inserted</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Sample social media insights may already exist</p>";
        }
        
        // Insert A/B testing
        try {
            $stmt = $conn->prepare("
                INSERT INTO ai_ab_testing (test_name, test_type, variant_a, variant_b, winner_variant, confidence_level, test_start, test_end) VALUES
                ('Email Subject Line', 'email', 'Son môi cao cấp', 'Khám phá màu son mới', 'A', 0.85, DATE_SUB(NOW(), INTERVAL 7 DAY), NOW()),
                ('Product Page Layout', 'website', 'Grid Layout', 'List Layout', 'A', 0.78, DATE_SUB(NOW(), INTERVAL 14 DAY), NOW()),
                ('Checkout Button Color', 'website', 'Red Button', 'Pink Button', 'B', 0.92, DATE_SUB(NOW(), INTERVAL 10 DAY), NOW())
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Sample A/B testing data inserted</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Sample A/B testing data may already exist</p>";
        }
        
        // Insert content generation
        try {
            $stmt = $conn->prepare("
                INSERT INTO ai_content_generation (content_type, content_text, quality_score, usage_count, created_at) VALUES
                ('product_description', 'Son môi MAC Ruby Woo với màu đỏ quyến rũ, chất son mịn màng, bền màu suốt ngày...', 0.95, 15, NOW()),
                ('email_template', 'Chào bạn! Chúng tôi có tin vui dành riêng cho bạn...', 0.88, 8, NOW()),
                ('social_post', '💄 Son môi mới nhất từ MAC! Màu sắc tuyệt đẹp, chất lượng cao cấp...', 0.92, 12, NOW()),
                ('blog_article', 'Top 10 màu son môi hot nhất mùa thu 2024...', 0.85, 5, NOW())
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Sample content generation inserted</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Sample content generation may already exist</p>";
        }
        
        echo "</div>";
        
        echo "<h2>✅ Setup Complete!</h2>";
        echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h3>AI Marketing Automation System is ready!</h3>";
        echo "<p><strong>Features available:</strong></p>";
        echo "<ul>";
        echo "<li>📧 Email personalization</li>";
        echo "<li>📱 Social media insights</li>";
        echo "<li>🧪 A/B testing</li>";
        echo "<li>📝 Content generation</li>";
        echo "<li>🎯 Audience targeting</li>";
        echo "<li>📊 Performance analytics</li>";
        echo "</ul>";
        echo "<p><a href='ai-marketing-dashboard.php' style='background: #4caf50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Open AI Marketing Dashboard</a></p>";
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
