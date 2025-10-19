<?php
/**
 * Setup AI Training Database
 * Linh2Store - Setup AI Training Tables
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/ai-training.php';

echo "<h1>🤖 Setup AI Training Database</h1>";
echo "<p>Đang thiết lập database cho AI training...</p>";

try {
    $training = new AITraining();
    
    // Create training table
    $result = $training->createTrainingTable();
    
    if ($result) {
        echo "<p style='color: green;'>✅ Tạo bảng ai_training_data thành công!</p>";
    } else {
        echo "<p style='color: red;'>❌ Lỗi tạo bảng ai_training_data</p>";
    }
    
    // Insert sample training data
    $sampleData = [
        [
            'user_message' => 'Tìm son môi màu đỏ',
            'bot_response' => 'Tôi có thể giúp bạn tìm son môi màu đỏ. Chúng tôi có nhiều thương hiệu như MAC, Dior, Chanel với các tông màu đỏ đa dạng từ đỏ cổ điển đến đỏ cam.',
            'category' => 'product_search'
        ],
        [
            'user_message' => 'Thương hiệu nào có?',
            'bot_response' => 'Chúng tôi có nhiều thương hiệu mỹ phẩm cao cấp như MAC, Dior, Chanel, YSL, Tom Ford, NARS, Urban Decay và nhiều thương hiệu khác.',
            'category' => 'brand_info'
        ],
        [
            'user_message' => 'Giao hàng như thế nào?',
            'bot_response' => 'Chúng tôi giao hàng toàn quốc với phí ship từ 30.000đ. Miễn phí ship cho đơn hàng từ 500.000đ. Thời gian giao hàng 1-3 ngày làm việc.',
            'category' => 'shipping_info'
        ],
        [
            'user_message' => 'Xin chào',
            'bot_response' => 'Xin chào! Tôi là AI Chatbot của Linh2Store. Tôi có thể giúp bạn tìm sản phẩm, kiểm tra đơn hàng, hoặc trả lời câu hỏi về mỹ phẩm.',
            'category' => 'general'
        ],
        [
            'user_message' => 'Bạn có thể giúp gì?',
            'bot_response' => 'Tôi có thể giúp bạn: 1) Tìm kiếm sản phẩm mỹ phẩm, 2) Kiểm tra thông tin đơn hàng, 3) Tư vấn về thương hiệu, 4) Hướng dẫn thanh toán và giao hàng, 5) Trả lời các câu hỏi khác.',
            'category' => 'general'
        ]
    ];
    
    $successCount = 0;
    foreach ($sampleData as $data) {
        $result = $training->trainWithConversation(
            $data['user_message'],
            $data['bot_response'],
            ['sample_data' => true, 'category' => $data['category']]
        );
        
        if ($result) {
            $successCount++;
        }
    }
    
    echo "<p style='color: green;'>✅ Đã thêm {$successCount}/" . count($sampleData) . " mẫu dữ liệu training!</p>";
    
    // Get training stats
    $stats = $training->getTrainingStats();
    echo "<h2>📊 Thống kê AI Training</h2>";
    echo "<ul>";
    echo "<li><strong>Tổng cuộc hội thoại:</strong> " . ($stats['total_conversations'] ?? 0) . "</li>";
    echo "<li><strong>Độ tin cậy trung bình:</strong> " . round(($stats['avg_confidence'] ?? 0) * 100, 1) . "%</li>";
    echo "<li><strong>Số danh mục:</strong> " . ($stats['categories_covered'] ?? 0) . "</li>";
    echo "</ul>";
    
    echo "<h2>🎉 Setup hoàn tất!</h2>";
    echo "<p><a href='test-chatbot.php' style='background: #EC407A; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Test Chatbot ngay</a></p>";
    echo "<p><a href='ai-training-dashboard.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>📊 Training Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
