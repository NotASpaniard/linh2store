<?php
/**
 * Trang danh sách sản phẩm
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/session.php';
require_once '../config/database.php';

// Lấy tham số từ URL
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$brand_id = intval($_GET['brand'] ?? 0);
$category_id = intval($_GET['category'] ?? 0);
$sort = $_GET['sort'] ?? 'newest';
$min_price = floatval($_GET['min_price'] ?? 0);
$max_price = floatval($_GET['max_price'] ?? 0);

$products = [];
$total_products = 0;
$brands = [];
$categories = [];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Xây dựng query
    $where_conditions = ["p.status = 'active'"];
    $params = [];
    
    if ($search) {
        $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($brand_id) {
        $where_conditions[] = "p.brand_id = ?";
        $params[] = $brand_id;
    }
    
    if ($category_id) {
        $where_conditions[] = "p.category_id = ?";
        $params[] = $category_id;
    }
    
    if ($min_price > 0) {
        $where_conditions[] = "p.price >= ?";
        $params[] = $min_price;
    }
    
    if ($max_price > 0) {
        $where_conditions[] = "p.price <= ?";
        $params[] = $max_price;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Sắp xếp
    $order_by = "p.created_at DESC";
    switch ($sort) {
        case 'price_asc':
            $order_by = "p.price ASC";
            break;
        case 'price_desc':
            $order_by = "p.price DESC";
            break;
        case 'name':
            $order_by = "p.name ASC";
            break;
        case 'popular':
            $order_by = "p.id DESC"; // Tạm thời, có thể thêm field view_count
            break;
    }
    
    // Đếm tổng số sản phẩm
    $count_sql = "
        SELECT COUNT(*) as total 
        FROM products p 
        WHERE $where_clause
    ";
    $stmt = $conn->prepare($count_sql);
    $stmt->execute($params);
    $total_products = $stmt->fetch()['total'];
    
    // Lấy sản phẩm
    $sql = "
        SELECT p.*, b.name as brand_name, c.name as category_name, pi.image_url 
        FROM products p 
        LEFT JOIN brands b ON p.brand_id = b.id 
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE $where_clause 
        ORDER BY $order_by 
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Lấy danh sách thương hiệu
    $stmt = $conn->prepare("SELECT id, name FROM brands WHERE status = 'active' ORDER BY name");
    $stmt->execute();
    $brands = $stmt->fetchAll();
    
    // Lấy danh sách danh mục
    $stmt = $conn->prepare("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll();
    
} catch (Exception $e) {
    $products = [];
    $total_products = 0;
    $brands = [];
    $categories = [];
}

$total_pages = ceil($total_products / $limit);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm - Linh2Store</title>
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
                        <a href="index.php" class="nav-link active">Sản phẩm</a>
                        <a href="../thuong-hieu/" class="nav-link">Thương hiệu</a>
                        <a href="../blog/" class="nav-link">Blog</a>
                        <a href="../lien-he/" class="nav-link">Liên hệ</a>
                    </nav>
                    
                    <div class="search-bar">
                        <input type="text" class="search-input" placeholder="Tìm kiếm sản phẩm..." value="<?php echo htmlspecialchars($search); ?>">
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
                        
                        <a href="../gio-hang/" class="cart-icon" title="Giỏ hàng">
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
            <span>Sản phẩm</span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="products-page">
        <div class="container">
            <div class="row">
                <!-- Sidebar Filters -->
                <div class="col-3">
                    <div class="filters-sidebar">
                        <h3>Bộ lọc</h3>
                        
                        <!-- Search -->
                        <div class="filter-group">
                            <h4>Tìm kiếm</h4>
                            <form method="GET" class="search-form">
                                <input type="text" name="search" placeholder="Tìm kiếm..." value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="btn btn-primary btn-sm">Tìm</button>
                            </form>
                        </div>
                        
                        <!-- Thương hiệu -->
                        <div class="filter-group">
                            <h4>Thương hiệu</h4>
                            <div class="filter-options">
                                <label class="checkbox-label">
                                    <input type="radio" name="brand" value="" <?php echo !$brand_id ? 'checked' : ''; ?>>
                                    <span>Tất cả</span>
                                </label>
                                <?php foreach ($brands as $brand): ?>
                                    <label class="checkbox-label">
                                        <input type="radio" name="brand" value="<?php echo $brand['id']; ?>" 
                                               <?php echo $brand_id == $brand['id'] ? 'checked' : ''; ?>>
                                        <span><?php echo htmlspecialchars($brand['name']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Danh mục -->
                        <div class="filter-group">
                            <h4>Danh mục</h4>
                            <div class="filter-options">
                                <label class="checkbox-label">
                                    <input type="radio" name="category" value="" <?php echo !$category_id ? 'checked' : ''; ?>>
                                    <span>Tất cả</span>
                                </label>
                                <?php foreach ($categories as $category): ?>
                                    <label class="checkbox-label">
                                        <input type="radio" name="category" value="<?php echo $category['id']; ?>" 
                                               <?php echo $category_id == $category['id'] ? 'checked' : ''; ?>>
                                        <span><?php echo htmlspecialchars($category['name']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Giá -->
                        <div class="filter-group">
                            <h4>Khoảng giá</h4>
                            <div class="price-range">
                                <input type="number" name="min_price" placeholder="Từ" value="<?php echo $min_price ?: ''; ?>">
                                <span>-</span>
                                <input type="number" name="min_price" placeholder="Đến" value="<?php echo $max_price ?: ''; ?>">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Áp dụng bộ lọc</button>
                    </div>
                </div>
                
                <!-- Products List -->
                <div class="col-9">
                    <div class="products-header">
                        <div class="products-info">
                            <h2>Sản phẩm</h2>
                            <p><?php echo $total_products; ?> sản phẩm</p>
                        </div>
                        
                        <div class="products-controls">
                            <div class="sort-controls">
                                <label>Sắp xếp:</label>
                                <select name="sort" onchange="this.form.submit()">
                                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                                    <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Giá thấp đến cao</option>
                                    <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Giá cao đến thấp</option>
                                    <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Tên A-Z</option>
                                    <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Phổ biến</option>
                                </select>
                            </div>
                            
                            <div class="view-controls">
                                <button class="view-btn active" data-view="grid">
                                    <i class="fas fa-th"></i>
                                </button>
                                <button class="view-btn" data-view="list">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Products Grid -->
                    <div class="products-grid" id="products-grid">
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <div class="product-card">
                                    <div class="product-image">
                                        <img src="<?php echo $product['image_url'] ?: '../assets/images/no-image.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             data-src="<?php echo $product['image_url'] ?: '../assets/images/no-image.jpg'; ?>">
                                        <div class="product-actions">
                                            <button class="action-btn wishlist-btn" data-product-id="<?php echo $product['id']; ?>">
                                                <i class="fas fa-heart"></i>
                                            </button>
                                            <button class="action-btn quick-view-btn" data-product-id="<?php echo $product['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <h3 class="product-title">
                                            <a href="chi-tiet.php?id=<?php echo $product['id']; ?>">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </a>
                                        </h3>
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
                                <i class="fas fa-search"></i>
                                <h3>Không tìm thấy sản phẩm</h3>
                                <p>Hãy thử thay đổi bộ lọc hoặc từ khóa tìm kiếm</p>
                                <a href="index.php" class="btn btn-primary">Xem tất cả sản phẩm</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-btn">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                                   class="page-btn <?php echo $i === $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-btn">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    
    <style>
        .breadcrumb {
            background: var(--bg-light);
            padding: var(--spacing-md) 0;
            border-bottom: 1px solid var(--primary-color);
        }
        
        .breadcrumb a {
            color: var(--text-light);
            text-decoration: none;
        }
        
        .breadcrumb span {
            color: var(--text-muted);
            margin: 0 var(--spacing-sm);
        }
        
        .products-page {
            padding: var(--spacing-xl) 0;
        }
        
        .filters-sidebar {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--spacing-xl);
        }
        
        .filters-sidebar h3 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-lg);
            padding-bottom: var(--spacing-sm);
            border-bottom: 2px solid var(--primary-color);
        }
        
        .filter-group {
            margin-bottom: var(--spacing-xl);
        }
        
        .filter-group h4 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-md);
            font-size: var(--font-size-base);
        }
        
        .filter-options {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            cursor: pointer;
            font-size: var(--font-size-sm);
            color: var(--text-light);
        }
        
        .checkbox-label input[type="radio"] {
            margin: 0;
        }
        
        .price-range {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }
        
        .price-range input {
            flex: 1;
            padding: var(--spacing-sm);
            border: 1px solid var(--primary-color);
            border-radius: var(--radius-sm);
        }
        
        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-xl);
            padding-bottom: var(--spacing-lg);
            border-bottom: 1px solid var(--primary-color);
        }
        
        .products-info h2 {
            margin: 0;
            color: var(--text-dark);
        }
        
        .products-info p {
            margin: 0;
            color: var(--text-light);
        }
        
        .products-controls {
            display: flex;
            align-items: center;
            gap: var(--spacing-lg);
        }
        
        .sort-controls {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }
        
        .sort-controls select {
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid var(--primary-color);
            border-radius: var(--radius-sm);
        }
        
        .view-controls {
            display: flex;
            gap: var(--spacing-xs);
        }
        
        .view-btn {
            padding: var(--spacing-sm);
            border: 1px solid var(--primary-color);
            background: var(--white);
            color: var(--text-light);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .view-btn.active,
        .view-btn:hover {
            background: var(--cta-color);
            color: var(--white);
            border-color: var(--cta-color);
        }
        
        .product-actions {
            position: absolute;
            top: var(--spacing-sm);
            right: var(--spacing-sm);
            display: flex;
            flex-direction: column;
            gap: var(--spacing-xs);
            opacity: 0;
            transition: opacity var(--transition-fast);
        }
        
        .product-card:hover .product-actions {
            opacity: 1;
        }
        
        .action-btn {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: var(--radius-full);
            background: var(--white);
            color: var(--text-dark);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-sm);
            transition: all var(--transition-fast);
        }
        
        .action-btn:hover {
            background: var(--cta-color);
            color: var(--white);
            transform: scale(1.1);
        }
        
        .no-products {
            text-align: center;
            padding: var(--spacing-3xl);
            color: var(--text-light);
        }
        
        .no-products i {
            font-size: var(--font-size-3xl);
            margin-bottom: var(--spacing-lg);
            color: var(--primary-color);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: var(--spacing-sm);
            margin-top: var(--spacing-xl);
        }
        
        .page-btn {
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid var(--primary-color);
            background: var(--white);
            color: var(--text-dark);
            text-decoration: none;
            border-radius: var(--radius-sm);
            transition: all var(--transition-fast);
        }
        
        .page-btn:hover,
        .page-btn.active {
            background: var(--cta-color);
            color: var(--white);
            border-color: var(--cta-color);
        }
        
        @media (max-width: 768px) {
            .col-3 {
                flex: 0 0 100%;
                margin-bottom: var(--spacing-lg);
            }
            
            .col-9 {
                flex: 0 0 100%;
            }
            
            .products-header {
                flex-direction: column;
                gap: var(--spacing-md);
                align-items: flex-start;
            }
            
            .products-controls {
                flex-direction: column;
                gap: var(--spacing-md);
                width: 100%;
            }
        }
    </style>
</body>
</html>
