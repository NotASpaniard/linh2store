<?php
/**
 * AI Recommendations Demo Page
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/auth-middleware.php';
require_once 'config/ai-recommendations.php';

// Check if user is logged in
if (!AuthMiddleware::isLoggedIn()) {
    header('Location: auth/dang-nhap.php');
    exit;
}

$user = AuthMiddleware::getCurrentUser();
$ai = new AIRecommendations();

// Get recommendations
$recommendations = $ai->getRecommendations($user['id'], 12);
$stats = $ai->getRecommendationStats();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Recommendations Demo - Linh2Store</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">
                        <img src="assets/images/logo.png" alt="Linh2Store">
                    </a>
                </div>
                
                <nav class="nav">
                    <a href="index.php" class="nav-link">Trang chủ</a>
                    <a href="san-pham/" class="nav-link">Sản phẩm</a>
                    <a href="thuong-hieu/" class="nav-link">Thương hiệu</a>
                    <a href="blog/" class="nav-link">Blog</a>
                    <a href="lien-he/" class="nav-link">Liên hệ</a>
                </nav>
                
                <div class="user-actions">
                    <a href="user/" class="user-icon" title="Tài khoản">
                        <i class="fas fa-user"></i>
                    </a>
                    <a href="gio-hang.php" class="cart-icon" title="Giỏ hàng">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            <div class="ai-demo">
                <h1>🤖 AI Recommendations Demo</h1>
                <p>Xin chào <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>! Đây là những sản phẩm AI gợi ý cho bạn:</p>
                
                <!-- AI Stats -->
                <div class="ai-stats">
                    <h3>📊 AI System Statistics</h3>
                    <div class="stats-grid">
                        <?php foreach ($stats as $stat): ?>
                        <div class="stat-card">
                            <h4><?php echo ucfirst($stat['recommendation_type']); ?></h4>
                            <p><?php echo $stat['count']; ?> recommendations</p>
                            <p>Avg Score: <?php echo number_format($stat['avg_score'], 2); ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Recommendations -->
                <div class="recommendations-section">
                    <h3>🎯 Personalized Recommendations</h3>
                    <?php if (!empty($recommendations)): ?>
                        <div class="products-grid">
                            <?php foreach ($recommendations as $product): ?>
                                <div class="product-card" data-product-id="<?php echo $product['id']; ?>">
                                    <div class="product-image">
                                        <img src="<?php echo $product['image'] ?? 'assets/images/no-image.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                                        <div class="ai-badge">
                                            <i class="fas fa-robot"></i>
                                            <?php echo ucfirst($product['recommendation_type']); ?>
                                        </div>
                                        <div class="ai-score">
                                            Score: <?php echo number_format($product['score'], 2); ?>
                                        </div>
                                    </div>
                                    
                                    <div class="product-info">
                                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                        <p class="product-brand"><?php echo htmlspecialchars($product['brand_name']); ?></p>
                                        <p class="product-price"><?php echo number_format($product['price']); ?>đ</p>
                                        
                                        <div class="product-actions">
                                            <button class="btn btn-primary add-to-cart" 
                                                    data-product-id="<?php echo $product['id']; ?>">
                                                <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                                            </button>
                                            <a href="san-pham/chi-tiet.php?id=<?php echo $product['id']; ?>" 
                                               class="btn btn-outline">
                                                <i class="fas fa-eye"></i> Xem chi tiết
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-recommendations">
                            <i class="fas fa-robot"></i>
                            <h3>Chưa có gợi ý nào</h3>
                            <p>Hãy xem một số sản phẩm để AI có thể gợi ý cho bạn!</p>
                            <a href="san-pham/" class="btn btn-primary">Xem sản phẩm</a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- AI Controls -->
                <div class="ai-controls">
                    <h3>🎛️ AI Controls</h3>
                    <div class="controls-grid">
                        <button class="btn btn-primary" onclick="refreshRecommendations()">
                            <i class="fas fa-sync"></i> Refresh Recommendations
                        </button>
                        <button class="btn btn-outline" onclick="trackBehavior('view', 'demo')">
                            <i class="fas fa-eye"></i> Track View
                        </button>
                        <button class="btn btn-outline" onclick="getSimilarProducts()">
                            <i class="fas fa-search"></i> Find Similar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Linh2Store. Tất cả quyền được bảo lưu.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        // AI Demo Functions
        function refreshRecommendations() {
            showLoading();
            fetch('api/ai-recommendations.php?action=get_recommendations')
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        location.reload();
                    } else {
                        showAlert('Lỗi khi tải gợi ý: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    hideLoading();
                    showAlert('Lỗi kết nối: ' + error.message, 'error');
                });
        }
        
        function trackBehavior(actionType, productId) {
            fetch('api/ai-recommendations.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'track_behavior',
                    product_id: productId,
                    action_type: actionType
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Đã ghi nhận hành vi: ' + actionType, 'success');
                } else {
                    showAlert('Lỗi ghi nhận hành vi: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showAlert('Lỗi kết nối: ' + error.message, 'error');
            });
        }
        
        function getSimilarProducts() {
            const productId = prompt('Nhập ID sản phẩm để tìm sản phẩm tương tự:');
            if (productId) {
                fetch(`api/ai-recommendations.php?action=get_similar&product_id=${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert(`Tìm thấy ${data.count} sản phẩm tương tự`, 'success');
                        } else {
                            showAlert('Lỗi tìm sản phẩm tương tự: ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        showAlert('Lỗi kết nối: ' + error.message, 'error');
                    });
            }
        }
        
        // Track page view
        document.addEventListener('DOMContentLoaded', function() {
            trackBehavior('view', 'ai-demo');
        });
    </script>
    
    <style>
        .ai-demo {
            padding: 2rem 0;
        }
        
        .ai-demo h1 {
            color: #EC407A;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .ai-stats {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 2rem 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .stat-card {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .recommendations-section {
            margin: 2rem 0;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }
        
        .product-card {
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s ease;
            background: white;
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .product-image {
            position: relative;
        }
        
        .product-image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .ai-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #EC407A;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .ai-score {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        
        .product-info {
            padding: 1rem;
        }
        
        .product-info h4 {
            margin: 0 0 0.5rem 0;
            color: #333;
        }
        
        .product-brand {
            color: #666;
            font-size: 0.9rem;
            margin: 0 0 0.5rem 0;
        }
        
        .product-price {
            color: #EC407A;
            font-weight: bold;
            font-size: 1.1rem;
            margin: 0 0 1rem 0;
        }
        
        .product-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .product-actions .btn {
            flex: 1;
            padding: 0.5rem;
            font-size: 0.9rem;
        }
        
        .no-recommendations {
            text-align: center;
            padding: 3rem 0;
        }
        
        .no-recommendations i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 1rem;
        }
        
        .ai-controls {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 2rem 0;
        }
        
        .controls-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
    </style>
</body>
</html>
