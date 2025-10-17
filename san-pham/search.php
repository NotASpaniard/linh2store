<?php
/**
 * Trang tìm kiếm sản phẩm - ĐƠN GIẢN
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/image-helper.php';

// Lấy từ khóa tìm kiếm
$search = trim($_GET['q'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

$products = [];
$total_products = 0;
$error = '';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($search) {
        // Chỉ tìm theo tên sản phẩm
        $sql = "
            SELECT p.*, b.name as brand_name, c.name as category_name
            FROM products p 
            LEFT JOIN brands b ON p.brand_id = b.id 
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'active' 
            AND p.name LIKE ?
            ORDER BY p.created_at DESC
            LIMIT $limit OFFSET $offset
        ";
        
        $count_sql = "
            SELECT COUNT(*) as total 
            FROM products p 
            WHERE p.status = 'active' 
            AND p.name LIKE ?
        ";
        
        $searchTerm = "%$search%";
        $stmt = $conn->prepare($count_sql);
        $stmt->execute([$searchTerm]);
        $total_products = $stmt->fetch()['total'];
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$searchTerm]);
        $products = $stmt->fetchAll();
    }
    
} catch (Exception $e) {
    $error = "Lỗi kết nối database: " . $e->getMessage();
}

$total_pages = ceil($total_products / $limit);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm kiếm sản phẩm - Linh2Store</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="../">
                        <img src="../assets/images/logo.png" alt="Linh2Store">
                    </a>
                </div>
                
                <nav class="nav">
                    <a href="../" class="nav-link">Trang chủ</a>
                    <a href="index.php" class="nav-link">Sản phẩm</a>
                    <a href="../thuong-hieu/" class="nav-link">Thương hiệu</a>
                    <a href="../blog/" class="nav-link">Blog</a>
                    <a href="../lien-he/" class="nav-link">Liên hệ</a>
                </nav>
                
                <div class="user-actions">
                    <a href="../auth/dang-nhap.php" class="user-icon" title="Đăng nhập">
                        <i class="fas fa-user"></i>
                    </a>
                    
                    <a href="../gio-hang.php" class="cart-icon" title="Giỏ hàng">
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
            <div class="search-results">
                <h1>Tìm kiếm sản phẩm</h1>
                
                <!-- Search Form -->
                <div class="search-form">
                    <form method="GET">
                        <input type="text" name="q" placeholder="Nhập từ khóa tìm kiếm..." 
                               value="<?php echo htmlspecialchars($search); ?>" required>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                    </form>
                </div>
                
                <?php if ($search): ?>
                    <div class="search-info">
                        <p>Tìm kiếm cho: "<strong><?php echo htmlspecialchars($search); ?></strong>"</p>
                        <p>Tìm thấy <strong><?php echo $total_products; ?></strong> sản phẩm</p>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php elseif (!empty($products)): ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="<?php echo getProductImage($product['id']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         loading="lazy">
                                </div>
                                
                                <div class="product-info">
                                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p class="product-brand"><?php echo htmlspecialchars($product['brand_name']); ?></p>
                                    <p class="product-price"><?php echo number_format($product['price']); ?>đ</p>
                                    
                                    <div class="product-actions">
                                        <a href="chi-tiet.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-primary">Xem chi tiết</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?q=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>" 
                                   class="btn btn-outline">Trước</a>
                            <?php endif; ?>
                            
                            <span>Trang <?php echo $page; ?> / <?php echo $total_pages; ?></span>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?q=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>" 
                                   class="btn btn-outline">Sau</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                <?php elseif ($search): ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h3>Không tìm thấy sản phẩm</h3>
                        <p>Không có sản phẩm nào phù hợp với từ khóa "<?php echo htmlspecialchars($search); ?>"</p>
                        <a href="index.php" class="btn btn-primary">Xem tất cả sản phẩm</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Linh2Store. Tất cả quyền được bảo lưu.</p>
        </div>
    </footer>

    <style>
        .search-results {
            padding: 2rem 0;
        }
        
        .search-results h1 {
            color: #EC407A;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .search-form {
            text-align: center;
            margin: 2rem 0;
        }
        
        .search-form form {
            display: flex;
            gap: 1rem;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .search-form input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .search-form button {
            padding: 0.75rem 1.5rem;
        }
        
        .search-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            margin: 1rem 0;
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
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .product-image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .product-info {
            padding: 1rem;
        }
        
        .product-info h3 {
            margin: 0 0 0.5rem 0;
            color: #333;
            font-size: 1.1rem;
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
        
        .no-results {
            text-align: center;
            padding: 3rem 0;
        }
        
        .no-results i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 1rem;
        }
        
        .pagination {
            text-align: center;
            margin: 2rem 0;
        }
        
        .pagination a {
            margin: 0 0.5rem;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 4px;
            margin: 1rem 0;
        }
    </style>
</body>
</html>