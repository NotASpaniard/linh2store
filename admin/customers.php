<?php
/**
 * Quản lý khách hàng
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/auth-middleware.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
$user = AuthMiddleware::requireAdmin();

// Lấy tham số
$page = max(1, intval($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');
$limit = 20;
$offset = ($page - 1) * $limit;

$customers = [];
$total_customers = 0;

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Xây dựng query
    $where_conditions = ["role = 'user'"];
    $params = [];
    
    if ($search) {
        $where_conditions[] = "(full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Đếm tổng số khách hàng
    $count_sql = "SELECT COUNT(*) as total FROM users WHERE $where_clause";
    $stmt = $conn->prepare($count_sql);
    $stmt->execute($params);
    $total_customers = $stmt->fetch()['total'];
    
    // Lấy khách hàng
    $sql = "
        SELECT u.*, 
               COUNT(o.id) as total_orders,
               SUM(o.total_amount) as total_spent
        FROM users u
        LEFT JOIN orders o ON u.id = o.user_id AND o.status = 'completed'
        WHERE $where_clause
        GROUP BY u.id
        ORDER BY u.created_at DESC 
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll();
    
} catch (Exception $e) {
    $customers = [];
    $total_customers = 0;
}

$total_pages = ceil($total_customers / $limit);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý khách hàng - Linh2Store</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="header-content">
            <div class="header-left">
                <h1><i class="fas fa-users"></i> Quản lý khách hàng</h1>
            </div>
            <div class="header-right">
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
            <a href="products.php" class="nav-item">
                <i class="fas fa-box"></i> Sản phẩm
            </a>
            <a href="customers.php" class="nav-item active">
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
                        <input type="text" name="search" placeholder="Tên, email, số điện thoại..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Lọc
                    </button>
                    
                    <a href="customers.php" class="btn btn-secondary">
                        <i class="fas fa-refresh"></i> Reset
                    </a>
                </form>
            </div>

            <!-- Customers Table -->
            <div class="customers-table-container">
                <div class="table-header">
                    <h3>Danh sách khách hàng (<?php echo $total_customers; ?> khách hàng)</h3>
                </div>
                
                <div class="table-content">
                    <?php if (!empty($customers)): ?>
                        <table class="customers-table">
                            <thead>
                                <tr>
                                    <th>Khách hàng</th>
                                    <th>Email</th>
                                    <th>Số điện thoại</th>
                                    <th>Tổng đơn hàng</th>
                                    <th>Tổng chi tiêu</th>
                                    <th>Ngày đăng ký</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td>
                                            <div class="customer-info">
                                                <div class="customer-name"><?php echo htmlspecialchars($customer['full_name']); ?></div>
                                                <div class="customer-id">ID: <?php echo $customer['id']; ?></div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['phone'] ?: 'Chưa cập nhật'); ?></td>
                                        <td>
                                            <span class="order-count"><?php echo $customer['total_orders']; ?> đơn</span>
                                        </td>
                                        <td>
                                            <span class="total-spent"><?php echo number_format($customer['total_spent'] ?: 0); ?>đ</span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($customer['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="customer-detail.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="btn btn-sm btn-info" onclick="viewOrders(<?php echo $customer['id']; ?>)">
                                                    <i class="fas fa-shopping-cart"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-users"></i>
                            <h3>Không có khách hàng nào</h3>
                            <p>Chưa có khách hàng nào phù hợp với bộ lọc</p>
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
        function viewOrders(customerId) {
            window.location.href = `orders.php?customer_id=${customerId}`;
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

        .filter-group input {
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid var(--primary-color);
            border-radius: var(--radius-sm);
            min-width: 300px;
        }

        .customers-table-container {
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

        .customers-table {
            width: 100%;
            border-collapse: collapse;
        }

        .customers-table th,
        .customers-table td {
            padding: var(--spacing-md);
            text-align: left;
            border-bottom: 1px solid var(--primary-color);
        }

        .customers-table th {
            background: var(--bg-light);
            font-weight: 600;
            color: var(--text-dark);
        }

        .customer-info {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-xs);
        }

        .customer-name {
            font-weight: 500;
            color: var(--text-dark);
        }

        .customer-id {
            font-size: var(--font-size-sm);
            color: var(--text-light);
        }

        .order-count {
            background: var(--primary-color);
            color: var(--white);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-sm);
            font-size: var(--font-size-xs);
        }

        .total-spent {
            font-weight: 600;
            color: var(--cta-color);
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
            
            .filter-group input {
                min-width: auto;
            }
            
            .customers-table {
                font-size: var(--font-size-sm);
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>
