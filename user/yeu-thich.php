<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/dang-nhap.php');
    exit();
}

$user = $_SESSION['user'];

// Dữ liệu hardcode về sản phẩm yêu thích
$wishlist_items = [
    [
        'id' => 1,
        'name' => 'Chanel Rouge Allure Ink Fusion',
        'image' => 'chanel-rouge-allure.jpg',
        'price' => 1600000,
        'sale_price' => 1200000,
        'brand' => 'Chanel',
        'category' => 'Son môi',
        'rating' => 4.8,
        'reviews' => 156,
        'added_date' => '2025-10-10 14:30:00',
        'in_stock' => true,
        'stock_quantity' => 15
    ],
    [
        'id' => 2,
        'name' => 'MAC Retro Matte Liquid Lipcolour',
        'image' => 'mac-retro-matte.jpg',
        'price' => 650000,
        'sale_price' => null,
        'brand' => 'MAC',
        'category' => 'Son môi',
        'rating' => 4.6,
        'reviews' => 89,
        'added_date' => '2025-10-12 09:15:00',
        'in_stock' => true,
        'stock_quantity' => 8
    ],
    [
        'id' => 3,
        'name' => 'Dior Rouge Dior Forever Liquid',
        'image' => 'dior-rouge-dior.jpg',
        'price' => 1800000,
        'sale_price' => 1400000,
        'brand' => 'Dior',
        'category' => 'Son môi',
        'rating' => 4.9,
        'reviews' => 203,
        'added_date' => '2025-10-13 16:45:00',
        'in_stock' => false,
        'stock_quantity' => 0
    ],
    [
        'id' => 4,
        'name' => 'MAC Ruby Woo',
        'image' => 'mac-ruby-woo.jpg',
        'price' => 580000,
        'sale_price' => null,
        'brand' => 'MAC',
        'category' => 'Son môi',
        'rating' => 4.7,
        'reviews' => 124,
        'added_date' => '2025-10-14 11:20:00',
        'in_stock' => true,
        'stock_quantity' => 12
    ]
];

$wishlist_count = count($wishlist_items);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu thích - Linh2Store</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .wishlist-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--spacing-xl);
        }
        
        .wishlist-header {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }
        
        .wishlist-header h1 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-md);
        }
        
        .wishlist-header p {
            color: var(--text-light);
            font-size: var(--font-size-lg);
        }
        
        .wishlist-stats {
            display: flex;
            justify-content: center;
            gap: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
        }
        
        .stat-item {
            text-align: center;
            padding: var(--spacing-lg);
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            min-width: 150px;
        }
        
        .stat-item h3 {
            margin: 0 0 var(--spacing-xs) 0;
            color: var(--accent-color);
            font-size: var(--font-size-2xl);
        }
        
        .stat-item p {
            margin: 0;
            color: var(--text-light);
            font-size: var(--font-size-sm);
        }
        
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: var(--spacing-xl);
        }
        
        .wishlist-item {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: all var(--transition-fast);
            position: relative;
        }
        
        .wishlist-item:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-4px);
        }
        
        .product-image {
            position: relative;
            height: 250px;
            overflow: hidden;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform var(--transition-fast);
        }
        
        .wishlist-item:hover .product-image img {
            transform: scale(1.05);
        }
        
        .product-badge {
            position: absolute;
            top: var(--spacing-sm);
            left: var(--spacing-sm);
            background: var(--accent-color);
            color: var(--white);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-sm);
            font-size: var(--font-size-xs);
            font-weight: 600;
        }
        
        .product-badge.out-of-stock {
            background: var(--error-color);
        }
        
        .wishlist-actions {
            position: absolute;
            top: var(--spacing-sm);
            right: var(--spacing-sm);
            display: flex;
            gap: var(--spacing-xs);
        }
        
        .action-btn {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: var(--radius-full);
            background: rgba(255, 255, 255, 0.9);
            color: var(--text-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all var(--transition-fast);
            backdrop-filter: blur(10px);
        }
        
        .action-btn:hover {
            background: var(--white);
            transform: scale(1.1);
        }
        
        .action-btn.remove {
            color: var(--error-color);
        }
        
        .action-btn.remove:hover {
            background: var(--error-color);
            color: var(--white);
        }
        
        .product-info {
            padding: var(--spacing-lg);
        }
        
        .product-brand {
            color: var(--text-light);
            font-size: var(--font-size-sm);
            margin-bottom: var(--spacing-xs);
        }
        
        .product-name {
            font-size: var(--font-size-lg);
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: var(--spacing-sm);
            line-height: 1.4;
        }
        
        .product-rating {
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
            margin-bottom: var(--spacing-sm);
        }
        
        .rating-stars {
            color: #ffc107;
        }
        
        .rating-text {
            color: var(--text-light);
            font-size: var(--font-size-sm);
        }
        
        .product-price {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-lg);
        }
        
        .current-price {
            font-size: var(--font-size-xl);
            font-weight: 700;
            color: var(--accent-color);
        }
        
        .original-price {
            font-size: var(--font-size-base);
            color: var(--text-light);
            text-decoration: line-through;
        }
        
        .product-actions {
            display: flex;
            gap: var(--spacing-sm);
        }
        
        .btn {
            flex: 1;
            padding: var(--spacing-sm) var(--spacing-md);
            border: none;
            border-radius: var(--radius-sm);
            text-decoration: none;
            font-size: var(--font-size-sm);
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-xs);
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background: var(--accent-color);
        }
        
        .btn-primary:disabled {
            background: var(--text-light);
            cursor: not-allowed;
        }
        
        .btn-outline {
            background: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }
        
        .btn-outline:hover {
            background: var(--primary-color);
            color: var(--white);
        }
        
        .empty-state {
            text-align: center;
            padding: var(--spacing-xxl);
            color: var(--text-light);
        }
        
        .empty-state i {
            font-size: var(--font-size-4xl);
            margin-bottom: var(--spacing-lg);
            color: var(--text-light);
        }
        
        .empty-state h3 {
            margin: 0 0 var(--spacing-md) 0;
            color: var(--text-dark);
        }
        
        .empty-state p {
            margin: 0 0 var(--spacing-lg) 0;
        }
        
        .bulk-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-xl);
            padding: var(--spacing-lg);
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }
        
        .bulk-actions-left {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }
        
        .bulk-actions-right {
            display: flex;
            gap: var(--spacing-md);
        }
        
        .checkbox {
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .wishlist-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .bulk-actions {
                flex-direction: column;
                gap: var(--spacing-md);
            }
            
            .bulk-actions-left,
            .bulk-actions-right {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="../">
                        <h1>Linh2Store</h1>
                    </a>
                </div>
                
                <nav class="main-nav">
                    <a href="../">Trang chủ</a>
                    <a href="../san-pham/">Sản phẩm</a>
                    <a href="../thuong-hieu/">Thương hiệu</a>
                    <a href="../blog/">Blog</a>
                    <a href="../lien-he/">Liên hệ</a>
                </nav>
                
                <div class="header-actions">
                    <a href="../thanh-toan/" class="cart-icon" title="Thanh toán">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </a>
                    <a href="index.php" class="user-icon" title="Tài khoản">
                        <i class="fas fa-user"></i>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="../">Trang chủ</a>
            <span>/</span>
            <a href="index.php">Tài khoản</a>
            <span>/</span>
            <span>Yêu thích</span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="wishlist-container">
        <div class="wishlist-header">
            <h1>Danh sách yêu thích</h1>
            <p>Sản phẩm bạn đã lưu để mua sau</p>
        </div>
        
        <?php if (!empty($wishlist_items)): ?>
            <div class="wishlist-stats">
                <div class="stat-item">
                    <h3><?php echo $wishlist_count; ?></h3>
                    <p>Tổng sản phẩm</p>
                </div>
                <div class="stat-item">
                    <h3><?php echo count(array_filter($wishlist_items, function($item) { return $item['in_stock']; })); ?></h3>
                    <p>Còn hàng</p>
                </div>
                <div class="stat-item">
                    <h3><?php echo count(array_filter($wishlist_items, function($item) { return !$item['in_stock']; })); ?></h3>
                    <p>Hết hàng</p>
                </div>
            </div>
            
            <div class="bulk-actions">
                <div class="bulk-actions-left">
                    <input type="checkbox" id="select-all" class="checkbox">
                    <label for="select-all">Chọn tất cả</label>
                    <span id="selected-count">0 sản phẩm được chọn</span>
                </div>
                <div class="bulk-actions-right">
                    <button class="btn btn-outline" onclick="addSelectedToCart()">
                        <i class="fas fa-shopping-cart"></i>
                        Thêm vào giỏ hàng
                    </button>
                    <button class="btn btn-outline" onclick="removeSelected()">
                        <i class="fas fa-trash"></i>
                        Xóa đã chọn
                    </button>
                </div>
            </div>
            
            <div class="wishlist-grid">
                <?php foreach ($wishlist_items as $item): ?>
                    <div class="wishlist-item" data-product-id="<?php echo $item['id']; ?>">
                        <div class="product-image">
                            <img src="../images/<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="product-badge <?php echo !$item['in_stock'] ? 'out-of-stock' : ''; ?>">
                                <?php echo $item['in_stock'] ? 'Còn hàng' : 'Hết hàng'; ?>
                            </div>
                            <div class="wishlist-actions">
                                <button class="action-btn remove" onclick="removeFromWishlist(<?php echo $item['id']; ?>)">
                                    <i class="fas fa-heart-broken"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="product-info">
                            <div class="product-brand"><?php echo htmlspecialchars($item['brand']); ?></div>
                            <h3 class="product-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                            
                            <div class="product-rating">
                                <div class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= floor($item['rating']) ? '' : 'far'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-text"><?php echo $item['rating']; ?> (<?php echo $item['reviews']; ?> đánh giá)</span>
                            </div>
                            
                            <div class="product-price">
                                <span class="current-price">
                                    <?php echo number_format($item['sale_price'] ?? $item['price'], 0, ',', '.'); ?>đ
                                </span>
                                <?php if ($item['sale_price']): ?>
                                    <span class="original-price">
                                        <?php echo number_format($item['price'], 0, ',', '.'); ?>đ
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-actions">
                                <input type="checkbox" class="checkbox product-checkbox" data-product-id="<?php echo $item['id']; ?>">
                                <a href="../san-pham/chi-tiet.php?id=<?php echo $item['id']; ?>" class="btn btn-outline">
                                    <i class="fas fa-eye"></i>
                                    Xem chi tiết
                                </a>
                                <button class="btn btn-primary" onclick="addToCart(<?php echo $item['id']; ?>)" <?php echo !$item['in_stock'] ? 'disabled' : ''; ?>>
                                    <i class="fas fa-shopping-cart"></i>
                                    <?php echo $item['in_stock'] ? 'Thêm vào giỏ' : 'Hết hàng'; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-heart"></i>
                <h3>Chưa có sản phẩm yêu thích</h3>
                <p>Bạn chưa lưu sản phẩm nào vào danh sách yêu thích</p>
                <a href="../san-pham/" class="btn btn-primary">Khám phá sản phẩm</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Select all functionality
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.product-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });
        
        // Individual checkbox change
        document.querySelectorAll('.product-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectedCount();
                updateSelectAllState();
            });
        });
        
        function updateSelectedCount() {
            const selected = document.querySelectorAll('.product-checkbox:checked');
            document.getElementById('selected-count').textContent = `${selected.length} sản phẩm được chọn`;
        }
        
        function updateSelectAllState() {
            const checkboxes = document.querySelectorAll('.product-checkbox');
            const checked = document.querySelectorAll('.product-checkbox:checked');
            const selectAll = document.getElementById('select-all');
            
            if (checked.length === 0) {
                selectAll.indeterminate = false;
                selectAll.checked = false;
            } else if (checked.length === checkboxes.length) {
                selectAll.indeterminate = false;
                selectAll.checked = true;
            } else {
                selectAll.indeterminate = true;
            }
        }
        
        function removeFromWishlist(productId) {
            if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi danh sách yêu thích?')) {
                // Simulate API call
                const item = document.querySelector(`[data-product-id="${productId}"]`);
                item.style.opacity = '0.5';
                item.style.transform = 'scale(0.95)';
                
                setTimeout(() => {
                    item.remove();
                    updateStats();
                }, 300);
            }
        }
        
        function addToCart(productId) {
            // Simulate API call
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thêm...';
            button.disabled = true;
            
            setTimeout(() => {
                button.innerHTML = '<i class="fas fa-check"></i> Đã thêm';
                button.style.background = '#28a745';
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                    button.style.background = '';
                }, 2000);
            }, 1000);
        }
        
        function addSelectedToCart() {
            const selected = document.querySelectorAll('.product-checkbox:checked');
            if (selected.length === 0) {
                alert('Vui lòng chọn ít nhất một sản phẩm');
                return;
            }
            
            // Simulate API call
            alert(`Đã thêm ${selected.length} sản phẩm vào giỏ hàng`);
        }
        
        function removeSelected() {
            const selected = document.querySelectorAll('.product-checkbox:checked');
            if (selected.length === 0) {
                alert('Vui lòng chọn ít nhất một sản phẩm');
                return;
            }
            
            if (confirm(`Bạn có chắc muốn xóa ${selected.length} sản phẩm khỏi danh sách yêu thích?`)) {
                selected.forEach(checkbox => {
                    const item = checkbox.closest('.wishlist-item');
                    item.style.opacity = '0.5';
                    item.style.transform = 'scale(0.95)';
                    
                    setTimeout(() => {
                        item.remove();
                        updateStats();
                    }, 300);
                });
            }
        }
        
        function updateStats() {
            // Update stats after removing items
            const remainingItems = document.querySelectorAll('.wishlist-item');
            const inStockItems = document.querySelectorAll('.wishlist-item:not(.out-of-stock)');
            
            // Update stat numbers
            const statItems = document.querySelectorAll('.stat-item h3');
            if (statItems.length >= 3) {
                statItems[0].textContent = remainingItems.length;
                statItems[1].textContent = inStockItems.length;
                statItems[2].textContent = remainingItems.length - inStockItems.length;
            }
        }
    </script>
</body>
</html>
