<?php
/**
 * Quản lý sản phẩm
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/session.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
if (!isLoggedIn()) {
    header('Location: ../auth/dang-nhap.php');
    exit();
}

$user = getCurrentUser();
if (!$user || $user['role'] !== 'admin') {
    header('Location: ../');
    exit();
}

// Lấy tham số
$page = max(1, intval($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');
$brand_id = intval($_GET['brand'] ?? 0);
$category_id = intval($_GET['category'] ?? 0);
$status = $_GET['status'] ?? '';
$limit = 20;
$offset = ($page - 1) * $limit;

$products = [];
$total_products = 0;
$brands = [];
$categories = [];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Xây dựng query
    $where_conditions = ["1=1"];
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
    
    if ($status) {
        $where_conditions[] = "p.status = ?";
        $params[] = $status;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
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
        ORDER BY p.created_at DESC 
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
    <title>Quản lý sản phẩm - Linh2Store</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="header-content">
            <div class="header-left">
                <h1><i class="fas fa-box"></i> Quản lý sản phẩm</h1>
            </div>
            <div class="header-right">
                <a href="product-add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Thêm sản phẩm
                </a>
                <span class="admin-name">Xin chào, <?php echo htmlspecialchars($user['full_name']); ?></span>
                <a href="../auth/dang-xuat.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="admin-nav">
        <div class="nav-content">
            <a href="index.php" class="nav-item">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="orders.php" class="nav-item">
                <i class="fas fa-shopping-cart"></i> Đơn hàng
            </a>
            <a href="products.php" class="nav-item active">
                <i class="fas fa-box"></i> Sản phẩm
            </a>
            <a href="customers.php" class="nav-item">
                <i class="fas fa-users"></i> Khách hàng
            </a>
            <a href="inventory.php" class="nav-item">
                <i class="fas fa-warehouse"></i> Kho hàng
            </a>
            <a href="reports.php" class="nav-item">
                <i class="fas fa-chart-bar"></i> Báo cáo
            </a>
            <a href="../" class="nav-item">
                <i class="fas fa-external-link-alt"></i> Xem website
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="admin-content">
        <div class="container">
            <!-- Filters -->
            <div class="filters-section">
                <form method="GET" class="filters-form">
                    <div class="filter-group">
                        <label>Tìm kiếm:</label>
                        <input type="text" name="search" placeholder="Tên sản phẩm, mô tả..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label>Thương hiệu:</label>
                        <select name="brand">
                            <option value="">Tất cả</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['id']; ?>" <?php echo $brand_id == $brand['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($brand['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Danh mục:</label>
                        <select name="category">
                            <option value="">Tất cả</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Trạng thái:</label>
                        <select name="status">
                            <option value="">Tất cả</option>
                            <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                            <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Không hoạt động</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Lọc
                    </button>
                    
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-refresh"></i> Reset
                    </a>
                </form>
            </div>

            <!-- Products Table -->
            <div class="products-table-container">
                <div class="table-header">
                    <h3>Danh sách sản phẩm (<?php echo $total_products; ?> sản phẩm)</h3>
                </div>
                
                <div class="table-content">
                    <?php if (!empty($products)): ?>
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>Hình ảnh</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Thương hiệu</th>
                                    <th>Danh mục</th>
                                    <th>Giá</th>
                                    <th>Tồn kho</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <div class="product-image">
                                                <img src="<?php echo $product['image_url'] ?: 'https://via.placeholder.com/60x60/E3F2FD/EC407A?text=No+Image'; ?>" 
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="product-info">
                                                <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                                <div class="product-sku">SKU: <?php echo htmlspecialchars($product['sku']); ?></div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['brand_name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                        <td>
                                            <div class="price-info">
                                                <div class="current-price"><?php echo number_format($product['price']); ?>đ</div>
                                                <?php if ($product['sale_price']): ?>
                                                    <div class="sale-price"><?php echo number_format($product['sale_price']); ?>đ</div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="stock-badge <?php echo $product['stock_quantity'] <= 10 ? 'low' : 'good'; ?>">
                                                <?php echo $product['stock_quantity']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $product['status']; ?>">
                                                <?php echo $product['status'] === 'active' ? 'Hoạt động' : 'Không hoạt động'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="product-edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="../san-pham/chi-tiet.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-info" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-box"></i>
                            <h3>Không có sản phẩm nào</h3>
                            <p>Chưa có sản phẩm nào phù hợp với bộ lọc</p>
                            <a href="product-add.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Thêm sản phẩm đầu tiên
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
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

    <script>
        function deleteProduct(productId) {
            if (confirm('Bạn có chắc muốn xóa sản phẩm này? Hành động này không thể hoàn tác!')) {
                fetch('api/delete-product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: productId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Đã xóa sản phẩm!');
                        location.reload();
                    } else {
                        alert('Có lỗi xảy ra: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi xóa sản phẩm!');
                });
            }
        }
    </script>

    <style>
        /* Admin Dashboard Styles */
        .admin-header {
            background: var(--white);
            box-shadow: var(--shadow-sm);
            padding: var(--spacing-lg) 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 var(--spacing-lg);
        }

        .header-left h1 {
            margin: 0;
            color: var(--text-dark);
            font-size: var(--font-size-xl);
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: var(--spacing-lg);
        }

        .admin-name {
            color: var(--text-light);
        }

        .logout-btn {
            background: var(--cta-color);
            color: var(--white);
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius-sm);
            text-decoration: none;
            transition: all var(--transition-fast);
        }

        .logout-btn:hover {
            background: #d32f2f;
        }

        .admin-nav {
            background: var(--primary-color);
            padding: var(--spacing-md) 0;
        }

        .nav-content {
            display: flex;
            gap: var(--spacing-lg);
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 var(--spacing-lg);
        }

        .nav-item {
            color: var(--white);
            text-decoration: none;
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius-sm);
            transition: all var(--transition-fast);
        }

        .nav-item:hover,
        .nav-item.active {
            background: var(--cta-color);
        }

        .admin-content {
            padding: var(--spacing-xl) 0;
            background: var(--bg-light);
            min-height: calc(100vh - 200px);
        }

        .filters-section {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
        }

        .filters-form {
            display: flex;
            gap: var(--spacing-lg);
            align-items: end;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-xs);
        }

        .filter-group label {
            font-weight: 500;
            color: var(--text-dark);
        }

        .filter-group select,
        .filter-group input {
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid var(--primary-color);
            border-radius: var(--radius-sm);
            min-width: 200px;
        }

        .products-table-container {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .table-header {
            padding: var(--spacing-lg);
            border-bottom: 1px solid var(--primary-color);
            background: var(--bg-light);
        }

        .table-header h3 {
            margin: 0;
            color: var(--text-dark);
        }

        .table-content {
            overflow-x: auto;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
        }

        .products-table th,
        .products-table td {
            padding: var(--spacing-md);
            text-align: left;
            border-bottom: 1px solid var(--primary-color);
        }

        .products-table th {
            background: var(--bg-light);
            font-weight: 600;
            color: var(--text-dark);
        }

        .product-image {
            width: 60px;
            height: 60px;
            border-radius: var(--radius-sm);
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-xs);
        }

        .product-name {
            font-weight: 500;
            color: var(--text-dark);
        }

        .product-sku {
            font-size: var(--font-size-sm);
            color: var(--text-light);
        }

        .price-info {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-xs);
        }

        .current-price {
            font-weight: 600;
            color: var(--text-dark);
        }

        .sale-price {
            font-size: var(--font-size-sm);
            color: var(--cta-color);
            text-decoration: line-through;
        }

        .stock-badge {
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-sm);
            font-size: var(--font-size-xs);
            font-weight: 500;
        }

        .stock-badge.good {
            background: #D4EDDA;
            color: #155724;
        }

        .stock-badge.low {
            background: #F8D7DA;
            color: #721C24;
        }

        .status-badge {
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-sm);
            font-size: var(--font-size-xs);
            font-weight: 500;
        }

        .status-active {
            background: #D4EDDA;
            color: #155724;
        }

        .status-inactive {
            background: #F8D7DA;
            color: #721C24;
        }

        .action-buttons {
            display: flex;
            gap: var(--spacing-xs);
        }

        .btn {
            padding: var(--spacing-xs) var(--spacing-sm);
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-xs);
            font-size: var(--font-size-sm);
            transition: all var(--transition-fast);
        }

        .btn-sm {
            padding: var(--spacing-xs);
            font-size: var(--font-size-xs);
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-info {
            background: #17a2b8;
            color: var(--white);
        }

        .btn-danger {
            background: #dc3545;
            color: var(--white);
        }

        .btn-secondary {
            background: var(--text-light);
            color: var(--white);
        }

        .btn:hover {
            opacity: 0.8;
            transform: translateY(-1px);
        }

        .no-data {
            text-align: center;
            padding: var(--spacing-3xl);
            color: var(--text-light);
        }

        .no-data i {
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
            .filters-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group select,
            .filter-group input {
                min-width: auto;
            }
            
            .products-table {
                font-size: var(--font-size-sm);
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>
