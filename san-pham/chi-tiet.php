<?php
/**
 * Trang chi tiết sản phẩm
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/image-helper.php';

$product_id = intval($_GET['id'] ?? 0);
$product = null;
$product_images = [];
$product_colors = [];
$related_products = [];
$reviews = [];

if (!$product_id) {
    header('Location: index.php');
    exit();
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Lấy thông tin sản phẩm
    $stmt = $conn->prepare("
        SELECT p.*, b.name as brand_name, c.name as category_name 
        FROM products p 
        LEFT JOIN brands b ON p.brand_id = b.id 
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ? AND p.status = 'active'
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header('Location: index.php');
        exit();
    }
    
    // Lấy hình ảnh sản phẩm
    $stmt = $conn->prepare("
        SELECT * FROM product_images 
        WHERE product_id = ? 
        ORDER BY is_primary DESC, sort_order ASC
    ");
    $stmt->execute([$product_id]);
    $product_images = $stmt->fetchAll();
    
    // Lấy màu sắc sản phẩm
    $stmt = $conn->prepare("
        SELECT * FROM product_colors 
        WHERE product_id = ? AND status = 'active'
        ORDER BY color_name
    ");
    $stmt->execute([$product_id]);
    $product_colors = $stmt->fetchAll();
    
    // Lấy sản phẩm liên quan
    $stmt = $conn->prepare("
        SELECT p.*, b.name as brand_name
        FROM products p 
        LEFT JOIN brands b ON p.brand_id = b.id
        WHERE p.category_id = ? AND p.id != ? AND p.status = 'active'
        ORDER BY p.created_at DESC 
        LIMIT 4
    ");
    $stmt->execute([$product['category_id'], $product_id]);
    $related_products = $stmt->fetchAll();
    
    // Lấy đánh giá
    $stmt = $conn->prepare("
        SELECT r.*, u.full_name, u.username 
        FROM reviews r 
        LEFT JOIN users u ON r.user_id = u.id 
        WHERE r.product_id = ? AND r.status = 'approved'
        ORDER BY r.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$product_id]);
    $reviews = $stmt->fetchAll();
    
} catch (Exception $e) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Linh2Store</title>
    <meta name="description" content="<?php echo htmlspecialchars($product['short_description']); ?>">
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
                        <a href="index.php" class="nav-link">Sản phẩm</a>
                        <a href="../thuong-hieu/" class="nav-link">Thương hiệu</a>
                        <a href="../blog/" class="nav-link">Blog</a>
                        <a href="../lien-he/" class="nav-link">Liên hệ</a>
                    </nav>
                    
                    <div class="search-bar">
                        <input type="text" class="search-input" placeholder="Tìm kiếm sản phẩm...">
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
            <a href="index.php">Sản phẩm</a>
            <span>/</span>
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </div>
    </div>

    <!-- Product Detail -->
    <div class="product-detail">
        <div class="container">
            <div class="row">
                <div class="col-6">
                    <!-- Product Images -->
                    <div class="product-images">
                        <div class="main-image">
                            <img src="../<?php echo getProductImage($product['id']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 id="main-product-image">
                        </div>
                        
                        <?php if (count($product_images) > 1): ?>
                            <div class="thumbnail-images">
                                <?php foreach ($product_images as $index => $image): ?>
                                    <img src="../<?php echo getProductImage($product['id']); ?>" 
                                         alt="Hình ảnh <?php echo $index + 1; ?>"
                                         class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                                         onclick="changeMainImage('../<?php echo getProductImage($product['id']); ?>')">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-6">
                    <!-- Product Info -->
                    <div class="product-info">
                        <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                        
                        <div class="product-meta">
                            <p class="product-brand">
                                <strong>Thương hiệu:</strong> 
                                <a href="../thuong-hieu/chi-tiet.php?id=<?php echo $product['brand_id']; ?>">
                                    <?php echo htmlspecialchars($product['brand_name']); ?>
                                </a>
                            </p>
                            <p class="product-category">
                                <strong>Danh mục:</strong> 
                                <a href="index.php?category=<?php echo $product['category_id']; ?>">
                                    <?php echo htmlspecialchars($product['category_name']); ?>
                                </a>
                            </p>
                        </div>
                        
                        <div class="product-price">
                            <span class="price-current"><?php echo number_format($product['price']); ?>đ</span>
                            <?php if ($product['sale_price']): ?>
                                <span class="price-old"><?php echo number_format($product['sale_price']); ?>đ</span>
                                <span class="discount-badge">-<?php echo round((($product['sale_price'] - $product['price']) / $product['sale_price']) * 100); ?>%</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($product['short_description']): ?>
                            <div class="product-short-desc">
                                <p><?php echo htmlspecialchars($product['short_description']); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Color Selection -->
                        <?php if (!empty($product_colors)): ?>
                            <div class="color-selection">
                                <h4>Màu sắc:</h4>
                                <div class="color-options">
                                    <?php foreach ($product_colors as $index => $color): ?>
                                        <div class="color-option <?php echo $index === 0 ? 'selected' : ''; ?>" 
                                             data-color-id="<?php echo $color['id']; ?>"
                                             data-color-name="<?php echo htmlspecialchars($color['color_name']); ?>"
                                             style="background-color: <?php echo $color['color_code']; ?>;"
                                             title="<?php echo htmlspecialchars($color['color_name']); ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Quantity & Add to Cart -->
                        <div class="product-actions">
                            <div class="quantity-selector">
                                <label for="quantity">Số lượng:</label>
                                <div class="quantity-controls">
                                    <button type="button" class="quantity-btn" onclick="decreaseQuantity()">-</button>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                                    <button type="button" class="quantity-btn" onclick="increaseQuantity()">+</button>
                                </div>
                            </div>
                            
                            <div class="action-buttons">
                                <button class="btn btn-primary btn-lg add-to-cart" 
                                        data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-cart-plus"></i>
                                    Thêm vào giỏ hàng
                                </button>
                                
                                <button class="btn btn-outline wishlist-btn" 
                                        data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-heart"></i>
                                    Yêu thích
                                </button>
                            </div>
                        </div>
                        
                        <!-- Product Features -->
                        <div class="product-features">
                            <div class="feature">
                                <i class="fas fa-shipping-fast"></i>
                                <span>Giao hàng miễn phí từ 500k</span>
                            </div>
                            <div class="feature">
                                <i class="fas fa-shield-alt"></i>
                                <span>Chính hãng 100%</span>
                            </div>
                            <div class="feature">
                                <i class="fas fa-undo"></i>
                                <span>Đổi trả trong 7 ngày</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Tabs -->
    <div class="product-tabs">
        <div class="container">
            <div class="tabs-nav">
                <button class="tab-btn active" data-tab="description">Mô tả sản phẩm</button>
                <button class="tab-btn" data-tab="ingredients">Thành phần</button>
                <button class="tab-btn" data-tab="usage">Cách sử dụng</button>
                <button class="tab-btn" data-tab="reviews">Đánh giá (<?php echo count($reviews); ?>)</button>
            </div>
            
            <div class="tabs-content">
                <div class="tab-panel active" id="description">
                    <div class="tab-content">
                        <?php if ($product['description']): ?>
                            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                        <?php else: ?>
                            <p>Chưa có mô tả chi tiết cho sản phẩm này.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="tab-panel" id="ingredients">
                    <div class="tab-content">
                        <?php if ($product['ingredients']): ?>
                            <?php echo nl2br(htmlspecialchars($product['ingredients'])); ?>
                        <?php else: ?>
                            <p>Thông tin thành phần đang được cập nhật.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="tab-panel" id="usage">
                    <div class="tab-content">
                        <?php if ($product['usage_instructions']): ?>
                            <?php echo nl2br(htmlspecialchars($product['usage_instructions'])); ?>
                        <?php else: ?>
                            <p>Hướng dẫn sử dụng đang được cập nhật.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="tab-panel" id="reviews">
                    <div class="tab-content">
                        <?php if (!empty($reviews)): ?>
                            <div class="reviews-list">
                                <?php foreach ($reviews as $review): ?>
                                    <div class="review-item">
                                        <div class="review-header">
                                            <div class="reviewer-info">
                                                <h4><?php echo htmlspecialchars($review['full_name'] ?: $review['username']); ?></h4>
                                                <div class="review-rating">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'active' : ''; ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <span class="review-date"><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></span>
                                        </div>
                                        <?php if ($review['title']): ?>
                                            <h5><?php echo htmlspecialchars($review['title']); ?></h5>
                                        <?php endif; ?>
                                        <p><?php echo htmlspecialchars($review['content']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>Chưa có đánh giá nào cho sản phẩm này.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
        <div class="related-products">
            <div class="container">
                <h2>Sản phẩm liên quan</h2>
                <div class="products-grid">
                    <?php foreach ($related_products as $related): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="../<?php echo getProductImage($related['id']); ?>" 
                                     alt="<?php echo htmlspecialchars($related['name']); ?>">
                            </div>
                            <div class="product-info">
                                <h3 class="product-title">
                                    <a href="chi-tiet.php?id=<?php echo $related['id']; ?>">
                                        <?php echo htmlspecialchars($related['name']); ?>
                                    </a>
                                </h3>
                                <p class="product-brand"><?php echo htmlspecialchars($related['brand_name']); ?></p>
                                <div class="product-price">
                                    <span class="price-current"><?php echo number_format($related['price']); ?>đ</span>
                                </div>
                                <a href="chi-tiet.php?id=<?php echo $related['id']; ?>" class="btn btn-primary">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="../assets/js/main.js"></script>
    
    <script>
        // Thay đổi hình ảnh chính
        function changeMainImage(imageUrl) {
            document.getElementById('main-product-image').src = imageUrl;
            
            // Cập nhật active thumbnail
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            event.target.classList.add('active');
        }
        
        // Chọn màu sắc
        document.querySelectorAll('.color-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.color-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                this.classList.add('selected');
            });
        });
        
        // Điều chỉnh số lượng
        function increaseQuantity() {
            const quantityInput = document.getElementById('quantity');
            const max = parseInt(quantityInput.getAttribute('max'));
            const current = parseInt(quantityInput.value);
            if (current < max) {
                quantityInput.value = current + 1;
            }
        }
        
        function decreaseQuantity() {
            const quantityInput = document.getElementById('quantity');
            const current = parseInt(quantityInput.value);
            if (current > 1) {
                quantityInput.value = current - 1;
            }
        }
        
        // Tabs
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Xóa active class
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
                
                // Thêm active class
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });
    </script>
    
    <style>
        .product-detail {
            padding: var(--spacing-xl) 0;
        }
        
        .product-images {
            position: sticky;
            top: var(--spacing-xl);
        }
        
        .main-image {
            margin-bottom: var(--spacing-lg);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }
        
        .main-image img {
            width: 100%;
            height: 500px;
            object-fit: cover;
            transition: transform var(--transition-slow);
        }
        
        .main-image:hover img {
            transform: scale(1.05);
        }
        
        .thumbnail-images {
            display: flex;
            gap: var(--spacing-sm);
            overflow-x: auto;
        }
        
        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: var(--radius-md);
            cursor: pointer;
            border: 2px solid transparent;
            transition: all var(--transition-fast);
        }
        
        .thumbnail:hover,
        .thumbnail.active {
            border-color: var(--cta-color);
            transform: scale(1.05);
        }
        
        .product-info {
            padding-left: var(--spacing-xl);
        }
        
        .product-title {
            font-size: var(--font-size-2xl);
            color: var(--text-dark);
            margin-bottom: var(--spacing-lg);
        }
        
        .product-meta {
            margin-bottom: var(--spacing-lg);
        }
        
        .product-meta p {
            margin-bottom: var(--spacing-sm);
            color: var(--text-light);
        }
        
        .product-meta a {
            color: var(--cta-color);
            text-decoration: none;
        }
        
        .product-price {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
        }
        
        .price-current {
            font-size: var(--font-size-2xl);
            font-weight: 700;
            color: var(--cta-color);
        }
        
        .price-old {
            font-size: var(--font-size-lg);
            color: var(--text-muted);
            text-decoration: line-through;
        }
        
        .discount-badge {
            background: var(--cta-color);
            color: var(--white);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-sm);
            font-size: var(--font-size-sm);
            font-weight: 600;
        }
        
        .color-selection {
            margin-bottom: var(--spacing-xl);
        }
        
        .color-selection h4 {
            margin-bottom: var(--spacing-md);
            color: var(--text-dark);
        }
        
        .color-options {
            display: flex;
            gap: var(--spacing-sm);
        }
        
        .color-option {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-full);
            border: 3px solid var(--white);
            cursor: pointer;
            transition: all var(--transition-fast);
            box-shadow: var(--shadow-sm);
        }
        
        .color-option:hover,
        .color-option.selected {
            transform: scale(1.2);
            border-color: var(--cta-color);
        }
        
        .product-actions {
            margin-bottom: var(--spacing-xl);
        }
        
        .quantity-selector {
            margin-bottom: var(--spacing-lg);
        }
        
        .quantity-selector label {
            display: block;
            margin-bottom: var(--spacing-sm);
            font-weight: 500;
            color: var(--text-dark);
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }
        
        .quantity-btn {
            width: 36px;
            height: 36px;
            border: 1px solid var(--primary-color);
            background: var(--white);
            color: var(--text-dark);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .quantity-btn:hover {
            background: var(--cta-color);
            color: var(--white);
            border-color: var(--cta-color);
        }
        
        #quantity {
            width: 80px;
            text-align: center;
            padding: var(--spacing-sm);
            border: 1px solid var(--primary-color);
            border-radius: var(--radius-sm);
        }
        
        .action-buttons {
            display: flex;
            gap: var(--spacing-md);
        }
        
        .product-features {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
        }
        
        .feature {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            color: var(--text-light);
        }
        
        .feature i {
            color: var(--cta-color);
            width: 20px;
        }
        
        .product-tabs {
            background: var(--bg-light);
            padding: var(--spacing-xl) 0;
        }
        
        .tabs-nav {
            display: flex;
            border-bottom: 1px solid var(--primary-color);
            margin-bottom: var(--spacing-xl);
        }
        
        .tab-btn {
            padding: var(--spacing-md) var(--spacing-lg);
            border: none;
            background: transparent;
            color: var(--text-light);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all var(--transition-fast);
        }
        
        .tab-btn.active,
        .tab-btn:hover {
            color: var(--cta-color);
            border-bottom-color: var(--cta-color);
        }
        
        .tab-panel {
            display: none;
        }
        
        .tab-panel.active {
            display: block;
        }
        
        .tab-content {
            background: var(--white);
            padding: var(--spacing-xl);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }
        
        .reviews-list {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-xl);
        }
        
        .review-item {
            padding: var(--spacing-lg);
            background: var(--primary-color);
            border-radius: var(--radius-md);
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-md);
        }
        
        .reviewer-info h4 {
            margin: 0 0 var(--spacing-xs) 0;
            color: var(--text-dark);
        }
        
        .review-rating {
            color: #FFD700;
        }
        
        .review-date {
            color: var(--text-muted);
            font-size: var(--font-size-sm);
        }
        
        .related-products {
            padding: var(--spacing-xl) 0;
        }
        
        .related-products h2 {
            text-align: center;
            margin-bottom: var(--spacing-xl);
            color: var(--text-dark);
        }
        
        @media (max-width: 768px) {
            .col-6 {
                flex: 0 0 100%;
            }
            
            .product-info {
                padding-left: 0;
                margin-top: var(--spacing-xl);
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .tabs-nav {
                flex-wrap: wrap;
            }
            
            .tab-btn {
                flex: 1;
                min-width: 120px;
            }
        }
    </style>
</body>
</html>
