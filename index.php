<?php
/**
 * Trang chủ Linh2Store
 * Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/session.php';
require_once 'config/database.php';

// Lấy sản phẩm nổi bật
$featured_products = [];
$new_products = [];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Sản phẩm nổi bật
    $stmt = $conn->prepare("
        SELECT p.*, b.name as brand_name, pi.image_url 
        FROM products p 
        LEFT JOIN brands b ON p.brand_id = b.id 
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE p.status = 'active' AND p.featured = 1 
        ORDER BY p.created_at DESC 
        LIMIT 8
    ");
    $stmt->execute();
    $featured_products = $stmt->fetchAll();
    
    // Sản phẩm mới
    $stmt = $conn->prepare("
        SELECT p.*, b.name as brand_name, pi.image_url 
        FROM products p 
        LEFT JOIN brands b ON p.brand_id = b.id 
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
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
                        <p><i class="fas fa-phone"></i> Hotline: 1900 1234</p>
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
                        <input type="text" class="search-input" placeholder="Tìm kiếm son môi, mỹ phẩm...">
                        <button class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    
                    <!-- User Actions -->
                    <div class="user-actions">
                        <?php if (isLoggedIn()): ?>
                            <a href="user/" class="user-icon" title="Tài khoản">
                                <i class="fas fa-user"></i>
                            </a>
                        <?php else: ?>
                            <a href="auth/dang-nhap.php" class="user-icon" title="Đăng nhập">
                                <i class="fas fa-user"></i>
                            </a>
                        <?php endif; ?>
                        
                        <a href="gio-hang/" class="cart-icon" title="Giỏ hàng">
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
                                <img src="assets/images/hero-lipstick.jpg" alt="Son môi cao cấp" class="hero-image">
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
                                <img src="<?php echo $product['image_url'] ?: 'https://via.placeholder.com/300x300/E3F2FD/EC407A?text=No+Image'; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     data-src="<?php echo $product['image_url'] ?: 'https://via.placeholder.com/300x300/E3F2FD/EC407A?text=No+Image'; ?>"
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
                                <img src="<?php echo $product['image_url'] ?: 'https://via.placeholder.com/300x300/E3F2FD/EC407A?text=No+Image'; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     data-src="<?php echo $product['image_url'] ?: 'https://via.placeholder.com/300x300/E3F2FD/EC407A?text=No+Image'; ?>"
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
                        <li><i class="fas fa-map-marker-alt"></i> 123 Đường ABC, Quận 1, TP.HCM</li>
                        <li><i class="fas fa-phone"></i> 1900 1234</li>
                        <li><i class="fas fa-envelope"></i> info@linh2store.com</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Linh2Store. Tất cả quyền được bảo lưu.</p>
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
            <?php if (isLoggedIn()): ?>
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
</body>
</html>
