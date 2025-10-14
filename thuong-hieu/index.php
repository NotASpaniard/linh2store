<?php
/**
 * Trang thương hiệu
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/image-helper.php';

$brands = [];
$featured_brands = [];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Lấy tất cả thương hiệu
    $stmt = $conn->prepare("
        SELECT b.*, COUNT(p.id) as product_count 
        FROM brands b 
        LEFT JOIN products p ON b.id = p.brand_id AND p.status = 'active'
        WHERE b.status = 'active' 
        GROUP BY b.id 
        ORDER BY b.name
    ");
    $stmt->execute();
    $brands = $stmt->fetchAll();
    
    // Lấy thương hiệu nổi bật (có nhiều sản phẩm nhất)
    $stmt = $conn->prepare("
        SELECT b.*, COUNT(p.id) as product_count 
        FROM brands b 
        LEFT JOIN products p ON b.id = p.brand_id AND p.status = 'active'
        WHERE b.status = 'active' 
        GROUP BY b.id 
        ORDER BY product_count DESC 
        LIMIT 6
    ");
    $stmt->execute();
    $featured_brands = $stmt->fetchAll();
    
} catch (Exception $e) {
    $brands = [];
    $featured_brands = [];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thương hiệu - Linh2Store</title>
    <meta name="description" content="Khám phá các thương hiệu mỹ phẩm cao cấp tại Linh2Store. Từ MAC, Chanel, Dior đến YSL và nhiều thương hiệu nổi tiếng khác.">
    <link rel="stylesheet" href="../assets/css/main.css">
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
                    <a href="../" class="logo">Linh2Store</a>
                    
                    <nav class="nav">
                        <a href="../" class="nav-link">Trang chủ</a>
                        <a href="../san-pham/" class="nav-link">Sản phẩm</a>
                        <a href="index.php" class="nav-link active">Thương hiệu</a>
                        <a href="../blog/" class="nav-link">Blog</a>
                        <a href="../lien-he/" class="nav-link">Liên hệ</a>
                    </nav>
                    
                    <div class="search-bar">
                        <input type="text" class="search-input" placeholder="Tìm kiếm thương hiệu...">
                        <button class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    
                    <div class="user-actions">
                        <?php if (isLoggedIn()): ?>
                            <a href="../user/" class="user-icon" title="Tài khoản">
                                <i class="fas fa-user"></i>
                            </a>
                        <?php else: ?>
                            <a href="../auth/dang-nhap.php" class="user-icon" title="Đăng nhập">
                                <i class="fas fa-user"></i>
                            </a>
                        <?php endif; ?>
                        
                        <a href="../thanh-toan/" class="cart-icon" title="Thanh toán">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count">0</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="../">Trang chủ</a>
            <span>/</span>
            <span>Thương hiệu</span>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="brands-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Thương hiệu cao cấp</h1>
                <p>Khám phá những thương hiệu mỹ phẩm nổi tiếng thế giới</p>
            </div>
        </div>
    </section>

    <!-- Featured Brands -->
    <section class="featured-brands">
        <div class="container">
            <div class="section-header">
                <h2>Thương hiệu nổi bật</h2>
                <p>Những thương hiệu được yêu thích nhất</p>
            </div>
            
            <div class="brands-grid">
                <?php if (!empty($featured_brands)): ?>
                    <?php foreach ($featured_brands as $brand): ?>
                        <div class="brand-card">
                            <div class="brand-logo">
                                <img src="../<?php echo getBrandImage($brand['id']); ?>" 
                                     alt="<?php echo htmlspecialchars($brand['name']); ?>">
                            </div>
                            <div class="brand-info">
                                <h3><?php echo htmlspecialchars($brand['name']); ?></h3>
                                <p><?php echo htmlspecialchars($brand['description']); ?></p>
                                <div class="brand-stats">
                                    <span class="product-count"><?php echo $brand['product_count']; ?> sản phẩm</span>
                                </div>
                                <a href="chi-tiet.php?id=<?php echo $brand['id']; ?>" class="btn btn-outline">
                                    Xem sản phẩm
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-brands">
                        <p>Chưa có thương hiệu nào</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- All Brands -->
    <section class="all-brands">
        <div class="container">
            <div class="section-header">
                <h2>Tất cả thương hiệu</h2>
                <p>Khám phá toàn bộ thương hiệu có tại Linh2Store</p>
            </div>
            
            <div class="brands-alphabetical">
                <?php if (!empty($brands)): ?>
                    <?php 
                    $current_letter = '';
                    foreach ($brands as $brand): 
                        $first_letter = strtoupper(substr($brand['name'], 0, 1));
                        if ($first_letter !== $current_letter):
                            $current_letter = $first_letter;
                    ?>
                        <div class="alphabet-section">
                            <h3 class="alphabet-letter"><?php echo $current_letter; ?></h3>
                            <div class="brands-list">
                    <?php endif; ?>
                    
                    <div class="brand-item">
                        <div class="brand-item-logo">
                            <img src="../<?php echo getBrandImage($brand['id']); ?>" 
                                 alt="<?php echo htmlspecialchars($brand['name']); ?>">
                        </div>
                        <div class="brand-item-info">
                            <h4><?php echo htmlspecialchars($brand['name']); ?></h4>
                            <p><?php echo htmlspecialchars($brand['description']); ?></p>
                            <span class="product-count"><?php echo $brand['product_count']; ?> sản phẩm</span>
                        </div>
                        <div class="brand-item-actions">
                            <a href="chi-tiet.php?id=<?php echo $brand['id']; ?>" class="btn btn-sm btn-primary">
                                Xem sản phẩm
                            </a>
                        </div>
                    </div>
                    
                    <?php 
                    // Kiểm tra xem có phải thương hiệu cuối cùng của chữ cái này không
                    $next_brand = next($brands);
                    $next_letter = $next_brand ? strtoupper(substr($next_brand['name'], 0, 1)) : '';
                    if ($next_letter !== $current_letter || !$next_brand):
                        prev($brands); // Reset pointer
                    ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-brands">
                        <p>Chưa có thương hiệu nào</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Brand Benefits -->
    <section class="brand-benefits">
        <div class="container">
            <div class="row">
                <div class="col-4">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-crown"></i>
                        </div>
                        <h3>Thương hiệu cao cấp</h3>
                        <p>Chỉ bán những thương hiệu mỹ phẩm nổi tiếng thế giới</p>
                    </div>
                </div>
                <div class="col-4">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Chính hãng 100%</h3>
                        <p>Cam kết tất cả sản phẩm đều chính hãng</p>
                    </div>
                </div>
                <div class="col-4">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3>Chất lượng cao</h3>
                        <p>Đảm bảo chất lượng sản phẩm tốt nhất</p>
                    </div>
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
                        <li><a href="../san-pham/">Tất cả sản phẩm</a></li>
                        <li><a href="../san-pham/son-moi/">Son môi</a></li>
                        <li><a href="../san-pham/son-kem/">Son kem</a></li>
                        <li><a href="../san-pham/son-thoi/">Son thỏi</a></li>
                        <li><a href="../san-pham/son-nuoc/">Son nước</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Hỗ trợ</h3>
                    <ul>
                        <li><a href="../lien-he/">Liên hệ</a></li>
                        <li><a href="../huong-dan/">Hướng dẫn mua hàng</a></li>
                        <li><a href="../doi-tra/">Chính sách đổi trả</a></li>
                        <li><a href="../bao-mat/">Bảo mật thông tin</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Liên hệ</h3>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i> 8910 Đường JQK, Quận A, Sảnh Rồng</li>
                        <li><i class="fas fa-phone"></i> 1900 1234</li>
                        <li><i class="fas fa-envelope"></i> info@linh2store.com</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Linh2Store. ...</p>
            </div>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
    
    <style>
        .brands-hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            padding: var(--spacing-3xl) 0;
            text-align: center;
        }
        
        .hero-content h1 {
            font-size: var(--font-size-3xl);
            color: var(--text-dark);
            margin-bottom: var(--spacing-lg);
        }
        
        .hero-content p {
            font-size: var(--font-size-lg);
            color: var(--text-light);
        }
        
        .featured-brands {
            padding: var(--spacing-3xl) 0;
            background: var(--white);
        }
        
        .all-brands {
            padding: var(--spacing-3xl) 0;
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
        
        .brands-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--spacing-xl);
        }
        
        .brand-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
            text-align: center;
            transition: all var(--transition-normal);
        }
        
        .brand-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }
        
        .brand-logo {
            margin-bottom: var(--spacing-lg);
        }
        
        .brand-logo img {
            width: 200px;
            height: 100px;
            object-fit: contain;
            border-radius: var(--radius-md);
        }
        
        .brand-info h3 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-md);
        }
        
        .brand-info p {
            color: var(--text-light);
            margin-bottom: var(--spacing-lg);
        }
        
        .brand-stats {
            margin-bottom: var(--spacing-lg);
        }
        
        .product-count {
            background: var(--primary-color);
            color: var(--text-dark);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-sm);
            font-size: var(--font-size-sm);
            font-weight: 500;
        }
        
        .brands-alphabetical {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .alphabet-section {
            margin-bottom: var(--spacing-3xl);
        }
        
        .alphabet-letter {
            font-size: var(--font-size-2xl);
            color: var(--cta-color);
            margin-bottom: var(--spacing-lg);
            padding-bottom: var(--spacing-sm);
            border-bottom: 2px solid var(--primary-color);
        }
        
        .brands-list {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
        }
        
        .brand-item {
            display: flex;
            align-items: center;
            gap: var(--spacing-lg);
            padding: var(--spacing-lg);
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            transition: all var(--transition-fast);
        }
        
        .brand-item:hover {
            box-shadow: var(--shadow-md);
        }
        
        .brand-item-logo {
            flex-shrink: 0;
        }
        
        .brand-item-logo img {
            width: 80px;
            height: 40px;
            object-fit: contain;
            border-radius: var(--radius-sm);
        }
        
        .brand-item-info {
            flex: 1;
        }
        
        .brand-item-info h4 {
            margin: 0 0 var(--spacing-xs) 0;
            color: var(--text-dark);
        }
        
        .brand-item-info p {
            margin: 0 0 var(--spacing-xs) 0;
            color: var(--text-light);
            font-size: var(--font-size-sm);
        }
        
        .brand-item-actions {
            flex-shrink: 0;
        }
        
        .no-brands {
            text-align: center;
            padding: var(--spacing-3xl);
            color: var(--text-light);
        }
        
        .brand-benefits {
            padding: var(--spacing-3xl) 0;
            background: var(--white);
        }
        
        .benefit-card {
            text-align: center;
            padding: var(--spacing-xl);
            background: var(--primary-color);
            border-radius: var(--radius-lg);
            transition: transform var(--transition-normal);
        }
        
        .benefit-card:hover {
            transform: translateY(-4px);
        }
        
        .benefit-icon {
            font-size: var(--font-size-3xl);
            color: var(--cta-color);
            margin-bottom: var(--spacing-lg);
        }
        
        .benefit-card h3 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-sm);
        }
        
        .benefit-card p {
            color: var(--text-light);
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .brands-grid {
                grid-template-columns: 1fr;
            }
            
            .brand-item {
                flex-direction: column;
                text-align: center;
            }
            
            .brand-item-logo,
            .brand-item-info,
            .brand-item-actions {
                width: 100%;
            }
            
            .col-4 {
                flex: 0 0 100%;
                margin-bottom: var(--spacing-lg);
            }
        }
    </style>
</body>
</html>
