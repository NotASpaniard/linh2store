<?php
/**
 * AI Auto Training Dashboard
 * Linh2Store - Automatic AI Training System
 */

require_once __DIR__ . '/config/ai-auto-training.php';

$autoTraining = new AIAutoTraining();

// Handle auto training request
if (isset($_POST['start_auto_training'])) {
    $results = $autoTraining->autoTrain();
    $totalTrained = array_sum($results);
    $success = $totalTrained > 0;
} else {
    $results = null;
    $success = null;
}

// Get current stats
$stats = $autoTraining->getTrainingStats();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Auto Training - Linh2Store</title>
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
        
        .btn {
            background: #EC407A;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin: 10px 5px;
        }
        
        .btn:hover {
            background: #d81b60;
            transform: translateY(-2px);
        }
        
        .btn-large {
            padding: 20px 40px;
            font-size: 18px;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-info {
            background: #17a2b8;
        }
        
        .btn-info:hover {
            background: #138496;
        }
        
        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .result-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            text-align: center;
        }
        
        .result-card h4 {
            color: #EC407A;
            margin-bottom: 10px;
        }
        
        .result-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #28a745;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
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
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-robot"></i> AI Auto Training</h1>
            <p>Hệ thống tự động huấn luyện AI với dữ liệu toàn diện</p>
        </div>
        
        <div class="content">
            <!-- Current Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $stats['total_conversations'] ?? 0; ?></h3>
                    <p>Cuộc hội thoại đã học</p>
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
            
            <!-- Auto Training Section -->
            <div class="training-section">
                <h2><i class="fas fa-magic"></i> Tự động huấn luyện AI</h2>
                <p>Hệ thống sẽ tự động huấn luyện AI với hơn 100+ câu hỏi và câu trả lời được chuẩn bị sẵn, bao gồm:</p>
                
                <div class="features">
                    <div class="feature-card">
                        <h3><i class="fas fa-palette"></i> Kiến thức sản phẩm</h3>
                        <p>Huấn luyện AI về:</p>
                        <ul class="feature-list">
                            <li>Son môi các màu sắc</li>
                            <li>Kem nền, phấn mắt</li>
                            <li>Mascara, kem che khuyết điểm</li>
                            <li>Son dưỡng môi</li>
                            <li>Hơn 50+ câu hỏi sản phẩm</li>
                        </ul>
                    </div>
                    
                    <div class="feature-card">
                        <h3><i class="fas fa-star"></i> Kiến thức thương hiệu</h3>
                        <p>Huấn luyện AI về:</p>
                        <ul class="feature-list">
                            <li>MAC, Dior, Chanel</li>
                            <li>YSL, NARS, Tom Ford</li>
                            <li>Urban Decay, các thương hiệu khác</li>
                            <li>Đặc điểm từng thương hiệu</li>
                            <li>Hơn 20+ câu hỏi thương hiệu</li>
                        </ul>
                    </div>
                    
                    <div class="feature-card">
                        <h3><i class="fas fa-shipping-fast"></i> Kiến thức giao hàng</h3>
                        <p>Huấn luyện AI về:</p>
                        <ul class="feature-list">
                            <li>Phí ship, thời gian giao hàng</li>
                            <li>Giao hàng nhanh, COD</li>
                            <li>Theo dõi đơn hàng</li>
                            <li>Đổi trả hàng</li>
                            <li>Hơn 15+ câu hỏi giao hàng</li>
                        </ul>
                    </div>
                    
                    <div class="feature-card">
                        <h3><i class="fas fa-credit-card"></i> Kiến thức thanh toán</h3>
                        <p>Huấn luyện AI về:</p>
                        <ul class="feature-list">
                            <li>COD, chuyển khoản</li>
                            <li>Ví điện tử MoMo, ZaloPay</li>
                            <li>Thẻ tín dụng, trả góp</li>
                            <li>Hoàn tiền</li>
                            <li>Hơn 15+ câu hỏi thanh toán</li>
                        </ul>
                    </div>
                    
                    <div class="feature-card">
                        <h3><i class="fas fa-comments"></i> Mẫu hội thoại</h3>
                        <p>Huấn luyện AI về:</p>
                        <ul class="feature-list">
                            <li>Các cách chào hỏi</li>
                            <li>Biến thể câu hỏi sản phẩm</li>
                            <li>Câu hỏi giá cả</li>
                            <li>Câu hỏi hỗ trợ</li>
                            <li>Hơn 30+ mẫu hội thoại</li>
                        </ul>
                    </div>
                    
                    <div class="feature-card">
                        <h3><i class="fas fa-info-circle"></i> Kiến thức chung</h3>
                        <p>Huấn luyện AI về:</p>
                        <ul class="feature-list">
                            <li>Giờ làm việc, liên hệ</li>
                            <li>Địa chỉ, khuyến mãi</li>
                            <li>Cảm ơn, tạm biệt</li>
                            <li>Hỗ trợ khách hàng</li>
                            <li>Hơn 20+ câu hỏi chung</li>
                        </ul>
                    </div>
                </div>
                
                <form method="POST" style="text-align: center; margin-top: 30px;">
                    <button type="submit" name="start_auto_training" class="btn btn-large">
                        <i class="fas fa-rocket"></i> Bắt đầu Auto Training
                    </button>
                </form>
            </div>
            
            <?php if ($results !== null): ?>
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <h3><i class="fas fa-check-circle"></i> Auto Training hoàn tất!</h3>
                        <p>Đã huấn luyện thành công <strong><?php echo array_sum($results); ?> câu hỏi/trả lời</strong> cho AI.</p>
                    </div>
                    
                    <div class="results-grid">
                        <div class="result-card">
                            <h4>Kiến thức sản phẩm</h4>
                            <div class="number"><?php echo $results['product_knowledge']; ?></div>
                            <p>câu hỏi</p>
                        </div>
                        <div class="result-card">
                            <h4>Kiến thức thương hiệu</h4>
                            <div class="number"><?php echo $results['brand_knowledge']; ?></div>
                            <p>câu hỏi</p>
                        </div>
                        <div class="result-card">
                            <h4>Kiến thức giao hàng</h4>
                            <div class="number"><?php echo $results['shipping_knowledge']; ?></div>
                            <p>câu hỏi</p>
                        </div>
                        <div class="result-card">
                            <h4>Kiến thức thanh toán</h4>
                            <div class="number"><?php echo $results['payment_knowledge']; ?></div>
                            <p>câu hỏi</p>
                        </div>
                        <div class="result-card">
                            <h4>Kiến thức chung</h4>
                            <div class="number"><?php echo $results['general_knowledge']; ?></div>
                            <p>câu hỏi</p>
                        </div>
                        <div class="result-card">
                            <h4>Mẫu hội thoại</h4>
                            <div class="number"><?php echo $results['conversation_patterns']; ?></div>
                            <p>câu hỏi</p>
                        </div>
                    </div>
                    
                    <div style="text-align: center; margin-top: 30px;">
                        <a href="test-chatbot.php" class="btn btn-success">
                            <i class="fas fa-comments"></i> Test Chatbot ngay
                        </a>
                        <a href="ai-training-dashboard.php" class="btn btn-info">
                            <i class="fas fa-chart-line"></i> Xem Training Dashboard
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <h3><i class="fas fa-exclamation-triangle"></i> Lỗi Auto Training</h3>
                        <p>Có lỗi xảy ra trong quá trình huấn luyện. Vui lòng thử lại.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
