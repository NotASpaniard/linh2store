<?php
/**
 * Trang chủ Linh2Store
 * Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/auth-middleware.php';
require_once 'config/database.php';
require_once 'config/image-helper.php';

// Lấy sản phẩm nổi bật
$featured_products = [];
$new_products = [];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Sản phẩm nổi bật
    $stmt = $conn->prepare("
        SELECT p.*, b.name as brand_name
        FROM products p 
        LEFT JOIN brands b ON p.brand_id = b.id 
        WHERE p.status = 'active' AND p.featured = 1 
        ORDER BY p.created_at DESC 
        LIMIT 8
    ");
    $stmt->execute();
    $featured_products = $stmt->fetchAll();
    
    // Sản phẩm mới
    $stmt = $conn->prepare("
        SELECT p.*, b.name as brand_name
        FROM products p 
        LEFT JOIN brands b ON p.brand_id = b.id 
        WHERE p.status = 'active' 
        ORDER BY p.created_at DESC 
        LIMIT 8
    ");
    $stmt->execute();
    $new_products = $stmt->fetchAll();
    
} catch (Exception $e) {
    // Xử lý lỗi
    $featured_products = [];
    $new_products = [];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Linh2Store - Son môi & Mỹ phẩm cao cấp</title>
    <meta name="description" content="Linh2Store - Cửa hàng son môi và mỹ phẩm cao cấp dành cho phụ nữ. Khám phá bộ sưu tập son môi đa dạng từ các thương hiệu nổi tiếng.">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-top">
            <div class="container">
                <div class="row justify-between align-center">
                    <div class="col">
                        <p><i class="fas fa-phone"></i> Hotline: 1900 JQKA</p>
                    </div>
                    <div class="col">
                        <p><i class="fas fa-truck"></i> Miễn phí ship đơn từ 500k</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="header-main">
            <div class="container">
                <div class="header-content">
                    <!-- Logo -->
                    <a href="index.php" class="logo">Linh2Store</a>
                    
                    <!-- Navigation -->
                    <nav class="nav">
                        <a href="index.php" class="nav-link">Trang chủ</a>
                        <a href="san-pham/" class="nav-link">Sản phẩm</a>
                        <a href="thuong-hieu/" class="nav-link">Thương hiệu</a>
                        <a href="blog/" class="nav-link">Blog</a>
                        <a href="lien-he/" class="nav-link">Liên hệ</a>
                    </nav>
                    
                    <!-- Search Bar -->
                    <div class="search-bar">
                        <form method="GET" action="san-pham/search.php">
                            <input type="text" name="q" class="search-input" placeholder="Tìm kiếm son môi, mỹ phẩm..." required>
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <!-- User Actions -->
                    <div class="user-actions">
                        <?php if (AuthMiddleware::isLoggedIn()): ?>
                            <a href="user/" class="user-icon" title="Tài khoản">
                                <i class="fas fa-user"></i>
                            </a>
                        <?php else: ?>
                            <a href="auth/dang-nhap.php" class="user-icon" title="Đăng nhập">
                                <i class="fas fa-user"></i>
                            </a>
                        <?php endif; ?>
                        
                        <a href="gio-hang.php" class="cart-icon" title="Giỏ hàng">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count" id="header-cart-count">0</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-slider">
            <div class="hero-slide active">
                <div class="hero-content">
                    <div class="container">
                        <div class="row align-center">
                            <div class="col-6">
                                <h1>Son môi cao cấp</h1>
                                <p>Khám phá bộ sưu tập son môi đa dạng từ các thương hiệu nổi tiếng thế giới</p>
                                <a href="san-pham/" class="btn btn-primary btn-lg">
                                    <i class="fas fa-shopping-bag"></i>
                                    Mua ngay
                                </a>
                            </div>
                            <div class="col-6">
                                <img src="https://via.placeholder.com/600x400/FFB6C1/FF69B4?text=💄+Son+Môi+Cao+Cấp" 
                                     alt="Son môi cao cấp" 
                                     class="hero-image"
                                     style="width: 100%; height: 400px; object-fit: cover; border-radius: 10px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products">
        <div class="container">
            <div class="section-header">
                <h2>Sản phẩm nổi bật</h2>
                <p>Những sản phẩm được yêu thích nhất</p>
            </div>
            
            <div class="products-grid">
                <?php if (!empty($featured_products)): ?>
                    <?php foreach ($featured_products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?php echo getProductImage($product['id']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     data-src="<?php echo getProductImage($product['id']); ?>"
                                     loading="lazy">
                                <div class="product-badge">Nổi bật</div>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="product-brand"><?php echo htmlspecialchars($product['brand_name']); ?></p>
                                <div class="product-price">
                                    <span class="price-current"><?php echo number_format($product['price']); ?>đ</span>
                                    <?php if ($product['sale_price']): ?>
                                        <span class="price-old"><?php echo number_format($product['sale_price']); ?>đ</span>
                                    <?php endif; ?>
                                </div>
                                <button class="btn btn-primary add-to-cart" 
                                        data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-cart-plus"></i>
                                    Thêm vào giỏ
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">
                        <p>Chưa có sản phẩm nào</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- New Products -->
    <section class="new-products">
        <div class="container">
            <div class="section-header">
                <h2>Sản phẩm mới</h2>
                <p>Những sản phẩm mới nhất từ các thương hiệu</p>
            </div>
            
            <div class="products-grid">
                <?php if (!empty($new_products)): ?>
                    <?php foreach ($new_products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?php echo getProductImage($product['id']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     data-src="<?php echo getProductImage($product['id']); ?>"
                                     loading="lazy">
                                <div class="product-badge">Mới</div>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="product-brand"><?php echo htmlspecialchars($product['brand_name']); ?></p>
                                <div class="product-price">
                                    <span class="price-current"><?php echo number_format($product['price']); ?>đ</span>
                                    <?php if ($product['sale_price']): ?>
                                        <span class="price-old"><?php echo number_format($product['sale_price']); ?>đ</span>
                                    <?php endif; ?>
                                </div>
                                <button class="btn btn-primary add-to-cart" 
                                        data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-cart-plus"></i>
                                    Thêm vào giỏ
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">
                        <p>Chưa có sản phẩm nào</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h3>Giao hàng nhanh</h3>
                    <p>Giao hàng trong 24h tại TP.HCM</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Chính hãng 100%</h3>
                    <p>Cam kết sản phẩm chính hãng</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-undo"></i>
                    </div>
                    <h3>Đổi trả dễ dàng</h3>
                    <p>Đổi trả trong 7 ngày</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>Hỗ trợ 24/7</h3>
                    <p>Hotline hỗ trợ khách hàng</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Linh2Store</h3>
                    <p>Website bán son môi & mỹ phẩm cao cấp dành cho phụ nữ hiện đại.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Danh mục</h3>
                    <ul>
                        <li><a href="san-pham/">Tất cả sản phẩm</a></li>
                        <li><a href="san-pham/son-moi/">Son môi</a></li>
                        <li><a href="san-pham/son-kem/">Son kem</a></li>
                        <li><a href="san-pham/son-thoi/">Son thỏi</a></li>
                        <li><a href="san-pham/son-nuoc/">Son nước</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Hỗ trợ</h3>
                    <ul>
                        <li><a href="lien-he/">Liên hệ</a></li>
                        <li><a href="huong-dan/">Hướng dẫn mua hàng</a></li>
                        <li><a href="doi-tra/">Chính sách đổi trả</a></li>
                        <li><a href="bao-mat/">Bảo mật thông tin</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Liên hệ</h3>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i> 8910 Đường JQK, Quận A, Sảnh Rồng</li>
                        <li><i class="fas fa-phone"></i> 1900 JQKA</li>
                        <li><i class="fas fa-envelope"></i> info@linh2store.com</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Linh2Store. ...</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    
    <script>
        // Cập nhật số lượng giỏ hàng khi trang load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });
        
        function updateCartCount() {
            <?php if (AuthMiddleware::isLoggedIn()): ?>
            fetch('api/cart.php?action=count')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const cartCount = document.getElementById('header-cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.count;
                        cartCount.style.display = data.count > 0 ? 'block' : 'none';
                    }
                }
            })
            .catch(error => {
                console.error('Error updating cart count:', error);
            });
            <?php endif; ?>
        }
    </script>
    
    <style>
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            padding: var(--spacing-3xl) 0;
            position: relative;
            overflow: hidden;
        }
        
        .hero-slide {
            position: relative;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero h1 {
            font-size: var(--font-size-3xl);
            color: var(--text-dark);
            margin-bottom: var(--spacing-lg);
        }
        
        .hero p {
            font-size: var(--font-size-lg);
            color: var(--text-light);
            margin-bottom: var(--spacing-xl);
        }
        
        .hero-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
        }
        
        /* Sections */
        .featured-products, .new-products {
            padding: var(--spacing-3xl) 0;
        }
        
        .featured-products {
            background: var(--white);
        }
        
        .new-products {
            background: var(--bg-light);
        }
        
        .section-header {
            text-align: center;
            margin-bottom: var(--spacing-3xl);
        }
        
        .section-header h2 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-sm);
        }
        
        .section-header p {
            color: var(--text-light);
            font-size: var(--font-size-lg);
        }
        
        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: var(--spacing-xl);
        }
        
        .no-products {
            grid-column: 1 / -1;
            text-align: center;
            padding: var(--spacing-3xl);
            color: var(--text-light);
        }
        
        /* Features */
        .features {
            background: var(--white);
            padding: var(--spacing-3xl) 0;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-xl);
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .feature-card {
            text-align: center;
            padding: var(--spacing-xl);
            background: var(--white);
            border-radius: var(--radius-lg);
            transition: all var(--transition-fast);
            border: 1px solid var(--primary-color);
            min-height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--cta-color);
        }
        
        .feature-icon {
            font-size: var(--font-size-3xl);
            color: var(--cta-color);
            margin-bottom: var(--spacing-lg);
        }
        
        .feature-card h3 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-sm);
            font-size: var(--font-size-lg);
        }
        
        .feature-card p {
            color: var(--text-light);
            margin: 0;
            font-size: var(--font-size-sm);
        }
        
        /* Social Links */
        .social-links {
            display: flex;
            gap: var(--spacing-md);
            margin-top: var(--spacing-lg);
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            color: var(--text-dark);
            border-radius: var(--radius-full);
            transition: all var(--transition-fast);
        }
        
        .social-links a:hover {
            background: var(--cta-color);
            color: var(--white);
            transform: translateY(-2px);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: var(--font-size-2xl);
            }
            
            .hero p {
                font-size: var(--font-size-base);
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: var(--spacing-lg);
            }
            
            .feature-card {
                padding: var(--spacing-lg);
            }
        }
        
        @media (max-width: 480px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .col-3 {
                flex: 0 0 100%;
                margin-bottom: var(--spacing-lg);
            }
        }
    </style>
    
    <!-- Floating AI Hub -->
    <div id="ai-hub" class="ai-hub">
        <!-- AI Hub Toggle Button -->
        <button class="ai-hub-toggle" onclick="toggleAIHub()">
            <i class="fas fa-robot"></i>
        </button>
        
        <!-- AI Hub Panel -->
        <div id="ai-hub-panel" class="ai-hub-panel" style="display: none;">
            <div class="ai-hub-header">
                <h4>🤖 AI Assistant Hub</h4>
                <button onclick="toggleAIHub()" class="ai-hub-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="ai-hub-content">
                <!-- AI Chatbot Chat -->
                <div id="ai-chatbot-chat" class="ai-chat-section">
                    <div class="ai-chat-header">
                        <div class="ai-chat-avatar">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="ai-chat-info">
                            <h5>Linh2Store AI Assistant</h5>
                            <span class="ai-chat-status">Online</span>
                        </div>
                    </div>
                    <div class="ai-chat-messages" id="ai-chatbot-messages">
                        <div class="ai-message bot">
                            <div class="message-content">
                                <div class="message-text">👋 Xin chào! Tôi có thể giúp bạn:<br>• 💰 Thông tin giá sản phẩm<br>• 🚚 Thông tin giao hàng<br>• 🔄 Chính sách đổi trả<br>• 🎉 Khuyến mãi hiện tại<br>• 🛒 Hướng dẫn đặt hàng<br>• 📞 Thông tin liên hệ</div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="quick-actions" id="quick-actions">
                            <div class="quick-actions-title">💡 Tôi có thể giúp bạn:</div>
                            <div class="quick-actions-buttons">
                                <button class="quick-action-btn" onclick="sendQuickMessage('giá son môi')">
                                    💰 Giá sản phẩm
                                </button>
                                <button class="quick-action-btn" onclick="sendQuickMessage('ship hàng')">
                                    🚚 Giao hàng
                                </button>
                                <button class="quick-action-btn" onclick="sendQuickMessage('đổi trả')">
                                    🔄 Đổi trả
                                </button>
                                <button class="quick-action-btn" onclick="sendQuickMessage('khuyến mãi')">
                                    🎉 Khuyến mãi
                                </button>
                                <button class="quick-action-btn" onclick="sendQuickMessage('đặt hàng')">
                                    🛒 Đặt hàng
                                </button>
                                <button class="quick-action-btn" onclick="sendQuickMessage('liên hệ')">
                                    📞 Liên hệ
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Scroll to bottom button -->
                    <button class="scroll-to-bottom" id="scroll-to-bottom" onclick="scrollToBottom()" style="display: none;">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="ai-chat-input">
                        <input type="text" id="ai-chatbot-input" placeholder="Nhập tin nhắn..." maxlength="500">
                        <button onclick="sendAIChatbotMessage()" id="ai-chatbot-send">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
                
                <!-- AI Features Menu -->
                <div class="ai-features-menu">
                    <div class="ai-feature-item" onclick="openAIRecommendations()">
                        <div class="ai-feature-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div class="ai-feature-info">
                            <h6>AI Recommendations</h6>
                            <p>Gợi ý sản phẩm thông minh</p>
                        </div>
                        <div class="ai-feature-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                    
                        <div class="ai-feature-item" onclick="openAISentiment()">
                            <div class="ai-feature-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="ai-feature-info">
                                <h6>AI Sentiment Analysis</h6>
                                <p>Phân tích cảm xúc đánh giá</p>
                            </div>
                            <div class="ai-feature-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </div>
                        
                        <div class="ai-feature-item" onclick="openAITraining()">
                            <div class="ai-feature-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="ai-feature-info">
                                <h6>AI Training Dashboard</h6>
                                <p>Huấn luyện AI thông minh</p>
                            </div>
                            <div class="ai-feature-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </div>
                        
                        <div class="ai-feature-item" onclick="openAIAutoTraining()">
                            <div class="ai-feature-icon">
                                <i class="fas fa-magic"></i>
                            </div>
                            <div class="ai-feature-info">
                                <h6>AI Auto Training</h6>
                                <p>Tự động huấn luyện AI</p>
                            </div>
                            <div class="ai-feature-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // AI Hub Functions
        
        function sendAIChatbotMessage() {
            const input = document.getElementById('ai-chatbot-input');
            const message = input.value.trim();
            
            if (!message) return;
            
            // Add user message
            addAIChatbotMessage('user', message);
            input.value = '';
            
            // Show typing indicator
            showAIChatbotTyping();
            
            // Send to Linh2Store Chatbot API
            fetch('api/Linh2Store-chatbot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                hideAIChatbotTyping();
                if (data.success) {
                    addAIChatbotMessage('bot', data.response);
                } else {
                    addAIChatbotMessage('bot', 'Xin lỗi, có lỗi xảy ra. Vui lòng thử lại.');
                }
            })
            .catch(error => {
                hideAIChatbotTyping();
                addAIChatbotMessage('bot', 'Xin lỗi, có lỗi kết nối. Vui lòng thử lại.');
            });
        }
        
        function addAIChatbotMessage(sender, text) {
            const messagesDiv = document.getElementById('ai-chatbot-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `ai-message ${sender}`;
            messageDiv.innerHTML = `
                <div class="message-content">
                    <div class="message-text">${text}</div>
                </div>
            `;
            messagesDiv.appendChild(messageDiv);
            
            // Hide quick actions after first user message
            if (sender === 'user') {
                const quickActions = document.getElementById('quick-actions');
                if (quickActions) {
                    quickActions.style.display = 'none';
                }
            }
            
            // Smooth scroll to bottom
            setTimeout(() => {
                messagesDiv.scrollTo({
                    top: messagesDiv.scrollHeight,
                    behavior: 'smooth'
                });
            }, 100);
        }
        
        function showAIChatbotTyping() {
            const messagesDiv = document.getElementById('ai-chatbot-messages');
            const typingDiv = document.createElement('div');
            typingDiv.className = 'ai-message bot typing';
            typingDiv.id = 'ai-chatbot-typing';
            typingDiv.innerHTML = `
                <div class="message-content">
                    <div class="message-text">
                        <i class="fas fa-circle"></i>
                        <i class="fas fa-circle"></i>
                        <i class="fas fa-circle"></i>
                        AI đang trả lời...
                    </div>
                </div>
            `;
            messagesDiv.appendChild(typingDiv);
            
            // Smooth scroll to bottom
            setTimeout(() => {
                messagesDiv.scrollTo({
                    top: messagesDiv.scrollHeight,
                    behavior: 'smooth'
                });
            }, 100);
        }
        
        function hideAIChatbotTyping() {
            const typing = document.getElementById('ai-chatbot-typing');
            if (typing) typing.remove();
        }
        
        function getOrCreateConversationId() {
            let conversationId = localStorage.getItem('chatbot_conversation_id');
            if (!conversationId) {
                // Start new conversation
                fetch('api/ai-chatbot.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'start_conversation'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        localStorage.setItem('chatbot_conversation_id', data.conversation.conversation_id);
                    }
                });
            }
            return conversationId;
        }
        
        function openAIRecommendations() {
            window.open('ai-demo.php', '_blank');
        }
        
        function openAISentiment() {
            window.open('ai-sentiment-demo.php', '_blank');
        }
        
        function openAITraining() {
            window.open('ai-training-dashboard.php', '_blank');
        }
        
        function openAIAutoTraining() {
            window.open('ai-auto-training.php', '_blank');
        }
        
        function scrollToBottom() {
            const messagesDiv = document.getElementById('ai-chatbot-messages');
            messagesDiv.scrollTo({
                top: messagesDiv.scrollHeight,
                behavior: 'smooth'
            });
        }
        
        function sendQuickMessage(message) {
            // Hide quick actions after first interaction
            const quickActions = document.getElementById('quick-actions');
            if (quickActions) {
                quickActions.style.display = 'none';
            }
            
            // Set input value and send message
            const input = document.getElementById('ai-chatbot-input');
            input.value = message;
            sendAIChatbotMessage();
        }
        
        // Check if user is at bottom of chat
        function checkScrollPosition() {
            const messagesDiv = document.getElementById('ai-chatbot-messages');
            const scrollButton = document.getElementById('scroll-to-bottom');
            
            if (messagesDiv.scrollTop + messagesDiv.clientHeight >= messagesDiv.scrollHeight - 10) {
                scrollButton.style.display = 'none';
            } else {
                scrollButton.style.display = 'block';
            }
        }
        
        // Handle Enter key in AI chatbot input
        document.getElementById('ai-chatbot-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendAIChatbotMessage();
            }
        });
        
        // Add scroll event listener when AI hub is opened
        function toggleAIHub() {
            const panel = document.getElementById('ai-hub-panel');
            if (panel.style.display === 'none') {
                panel.style.display = 'block';
                document.getElementById('ai-chatbot-input').focus();
                
                // Add scroll event listener
                const messagesDiv = document.getElementById('ai-chatbot-messages');
                messagesDiv.addEventListener('scroll', checkScrollPosition);
                
                // Initial check
                setTimeout(checkScrollPosition, 100);
            } else {
                panel.style.display = 'none';
            }
        }
    </script>
    
    <style>
        /* Floating AI Hub Styles */
        .ai-hub {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .ai-hub-toggle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #EC407A, #E91E63);
            color: white;
            border: none;
            cursor: pointer;
            font-size: 24px;
            box-shadow: 0 4px 20px rgba(236, 64, 122, 0.4);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .ai-hub-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(236, 64, 122, 0.6);
        }
        
        .ai-hub-panel {
            position: absolute;
            bottom: 80px;
            right: 0;
            width: 380px;
            height: 600px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .ai-hub-header {
            background: linear-gradient(135deg, #EC407A, #E91E63);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .ai-hub-header h4 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }
        
        .ai-hub-close {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: background-color 0.3s;
        }
        
        .ai-hub-close:hover {
            background-color: rgba(255,255,255,0.2);
        }
        
        .ai-hub-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        /* AI Chat Section */
        .ai-chat-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            border-bottom: 1px solid #eee;
        }
        
        .ai-chat-header {
            padding: 15px 20px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .ai-chat-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #EC407A, #E91E63);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        
        .ai-chat-info h5 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }
        
        .ai-chat-status {
            font-size: 12px;
            color: #28a745;
            font-weight: 500;
        }
        
        .ai-chat-messages {
            flex: 1;
            padding: 15px 20px;
            overflow-y: auto;
            background: #f8f9fa;
            scroll-behavior: smooth;
            max-height: 400px;
            /* Custom scrollbar */
            scrollbar-width: thin;
            scrollbar-color: #EC407A #f8f9fa;
        }
        
        .ai-chat-messages::-webkit-scrollbar {
            width: 6px;
        }
        
        .ai-chat-messages::-webkit-scrollbar-track {
            background: #f8f9fa;
            border-radius: 3px;
        }
        
        .ai-chat-messages::-webkit-scrollbar-thumb {
            background: #EC407A;
            border-radius: 3px;
        }
        
        .ai-chat-messages::-webkit-scrollbar-thumb:hover {
            background: #d81b60;
        }
        
        /* Scroll to bottom button */
        .scroll-to-bottom {
            position: absolute;
            bottom: 60px;
            right: 20px;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #EC407A;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(236, 64, 122, 0.3);
            transition: all 0.3s ease;
            z-index: 10;
        }
        
        .scroll-to-bottom:hover {
            background: #d81b60;
            transform: scale(1.1);
        }
        
        /* Quick Actions */
        .quick-actions {
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #e9ecef;
        }
        
        .quick-actions-title {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .quick-actions-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .quick-action-btn {
            background: white;
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 8px 12px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #333;
            white-space: nowrap;
        }
        
        .quick-action-btn:hover {
            background: #EC407A;
            color: white;
            border-color: #EC407A;
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(236, 64, 122, 0.3);
        }
        
        .ai-message {
            margin-bottom: 15px;
            display: flex;
        }
        
        .ai-message.user {
            justify-content: flex-end;
        }
        
        .ai-message.bot {
            justify-content: flex-start;
        }
        
        .ai-message .message-content {
            max-width: 80%;
            padding: 12px 16px;
            border-radius: 18px;
        }
        
        .ai-message.user .message-content {
            background: #EC407A;
            color: white;
        }
        
        .ai-message.bot .message-content {
            background: white;
            color: #333;
            border: 1px solid #e9ecef;
        }
        
        .ai-message.typing .message-content {
            background: #e9ecef;
            color: #666;
        }
        
        .ai-chat-input {
            padding: 15px 20px;
            background: white;
            display: flex;
            gap: 10px;
            border-top: 1px solid #eee;
        }
        
        .ai-chat-input input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 25px;
            outline: none;
            font-size: 14px;
        }
        
        .ai-chat-input button {
            padding: 12px 16px;
            background: #EC407A;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .ai-chat-input button:hover {
            background: #d81b60;
        }
        
        /* AI Features Menu */
        .ai-features-menu {
            padding: 20px;
            background: white;
        }
        
        .ai-feature-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 10px;
            border: 1px solid #e9ecef;
        }
        
        .ai-feature-item:hover {
            background: #f8f9fa;
            border-color: #EC407A;
            transform: translateX(5px);
        }
        
        .ai-feature-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #EC407A, #E91E63);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-right: 15px;
        }
        
        .ai-feature-info {
            flex: 1;
        }
        
        .ai-feature-info h6 {
            margin: 0 0 5px 0;
            font-size: 16px;
            color: #333;
            font-weight: 600;
        }
        
        .ai-feature-info p {
            margin: 0;
            font-size: 13px;
            color: #666;
        }
        
        .ai-feature-arrow {
            color: #999;
            font-size: 14px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .ai-hub-panel {
                width: 320px;
                height: 500px;
            }
        }
    </style>
</body>
</html>
