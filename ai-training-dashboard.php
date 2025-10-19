<?php
/**
 * AI Training Dashboard
 * Linh2Store - AI Chatbot Training Management
 */

require_once __DIR__ . '/config/ai-training.php';
require_once __DIR__ . '/config/ai-chatbot.php';

$training = new AITraining();
$chatbot = new AIChatbot();

// Create training table if not exists
$training->createTrainingTable();

// Get training statistics
$stats = $training->getTrainingStats();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Training Dashboard - Linh2Store</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #EC407A, #E91E63);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            border-left: 4px solid #EC407A;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            color: #EC407A;
            margin-bottom: 10px;
        }
        
        .stat-card p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .training-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .training-section h2 {
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .training-form {
            display: grid;
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group label {
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #EC407A;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .btn {
            background: #EC407A;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn:hover {
            background: #d81b60;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .ai-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .feature-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            transition: all 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .feature-card h3 {
            color: #EC407A;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .feature-card p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .feature-list {
            list-style: none;
        }
        
        .feature-list li {
            padding: 5px 0;
            color: #555;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .feature-list li::before {
            content: "✓";
            color: #28a745;
            font-weight: bold;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-robot"></i> AI Training Dashboard</h1>
            <p>Quản lý và huấn luyện AI Chatbot thông minh</p>
        </div>
        
        <div class="content">
            <!-- Training Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $stats['total_conversations'] ?? 0; ?></h3>
                    <p>Cuộc hội thoại đã huấn luyện</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo round(($stats['avg_confidence'] ?? 0) * 100, 1); ?>%</h3>
                    <p>Độ tin cậy trung bình</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['categories_covered'] ?? 0; ?></h3>
                    <p>Danh mục đã học</p>
                </div>
            </div>
            
            <!-- Manual Training -->
            <div class="training-section">
                <h2><i class="fas fa-graduation-cap"></i> Huấn luyện thủ công</h2>
                <form method="POST" class="training-form">
                    <div class="form-group">
                        <label for="user_message">Câu hỏi của người dùng:</label>
                        <input type="text" id="user_message" name="user_message" 
                               placeholder="Ví dụ: Tìm son môi màu đỏ" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="bot_response">Câu trả lời của AI:</label>
                        <textarea id="bot_response" name="bot_response" 
                                  placeholder="Ví dụ: Tôi có thể giúp bạn tìm son môi màu đỏ. Chúng tôi có nhiều thương hiệu như MAC, Dior, Chanel..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Danh mục:</label>
                        <select id="category" name="category">
                            <option value="product_search">Tìm kiếm sản phẩm</option>
                            <option value="order_tracking">Theo dõi đơn hàng</option>
                            <option value="brand_info">Thông tin thương hiệu</option>
                            <option value="shipping_info">Thông tin giao hàng</option>
                            <option value="payment_info">Thông tin thanh toán</option>
                            <option value="contact_info">Liên hệ hỗ trợ</option>
                            <option value="general">Chung</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="train_ai" class="btn">
                        <i class="fas fa-brain"></i> Huấn luyện AI
                    </button>
                </form>
            </div>
            
            <!-- AI Features -->
            <div class="ai-features">
                <div class="feature-card">
                    <h3><i class="fas fa-search"></i> Tìm kiếm thông minh</h3>
                    <p>AI có thể hiểu và tìm kiếm sản phẩm theo:</p>
                    <ul class="feature-list">
                        <li>Tên sản phẩm</li>
                        <li>Màu sắc</li>
                        <li>Thương hiệu</li>
                        <li>Giá cả</li>
                        <li>Mô tả</li>
                    </ul>
                </div>
                
                <div class="feature-card">
                    <h3><i class="fas fa-shopping-cart"></i> Hỗ trợ mua sắm</h3>
                    <p>AI có thể giúp khách hàng:</p>
                    <ul class="feature-list">
                        <li>Kiểm tra đơn hàng</li>
                        <li>Hướng dẫn thanh toán</li>
                        <li>Thông tin giao hàng</li>
                        <li>Chính sách đổi trả</li>
                        <li>Khuyến mãi</li>
                    </ul>
                </div>
                
                <div class="feature-card">
                    <h3><i class="fas fa-comments"></i> Giao tiếp tự nhiên</h3>
                    <p>AI hiểu ngôn ngữ tự nhiên:</p>
                    <ul class="feature-list">
                        <li>Tiếng Việt</li>
                        <li>Tiếng lóng</li>
                        <li>Emoji</li>
                        <li>Ngữ cảnh</li>
                        <li>Cảm xúc</li>
                    </ul>
                </div>
                
                <div class="feature-card">
                    <h3><i class="fas fa-chart-line"></i> Học hỏi liên tục</h3>
                    <p>AI tự động cải thiện:</p>
                    <ul class="feature-list">
                        <li>Từ mỗi cuộc hội thoại</li>
                        <li>Phân tích phản hồi</li>
                        <li>Cập nhật kiến thức</li>
                        <li>Tối ưu hóa câu trả lời</li>
                        <li>Học từ lỗi</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    // Handle manual training
    if (isset($_POST['train_ai'])) {
        $userMessage = $_POST['user_message'];
        $botResponse = $_POST['bot_response'];
        $category = $_POST['category'];
        
        $context = [
            'manual_training' => true,
            'category' => $category,
            'trained_by' => 'admin'
        ];
        
        $result = $training->trainWithConversation($userMessage, $botResponse, $context);
        
        if ($result) {
            echo '<div class="alert alert-success">✅ Huấn luyện thành công! AI đã học được câu trả lời mới.</div>';
        } else {
            echo '<div class="alert alert-danger">❌ Có lỗi xảy ra khi huấn luyện AI.</div>';
        }
    }
    ?>
    
    <script>
        // Auto-refresh stats every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
