<?php
/**
 * Quản lý đơn hàng
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/auth-middleware.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
$user = AuthMiddleware::requireAdmin();

// Lấy tham số
$page = max(1, intval($_GET['page'] ?? 1));
$status = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');
$limit = 20;
$offset = ($page - 1) * $limit;

$orders = [];
$total_orders = 0;

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Xây dựng query
    $where_conditions = ["1=1"];
    $params = [];
    
    if ($status) {
        $where_conditions[] = "o.status = ?";
        $params[] = $status;
    }
    
    if ($search) {
        $where_conditions[] = "(o.id LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Đếm tổng số đơn hàng
    $count_sql = "
        SELECT COUNT(*) as total 
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE $where_clause
    ";
    $stmt = $conn->prepare($count_sql);
    $stmt->execute($params);
    $total_orders = $stmt->fetch()['total'];
    
    // Lấy đơn hàng
    $sql = "
        SELECT o.*, u.full_name, u.email, u.phone, COUNT(oi.id) as item_count
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE $where_clause
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    
} catch (Exception $e) {
    $orders = [];
    $total_orders = 0;
}

$total_pages = ceil($total_orders / $limit);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng - Linh2Store</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="header-content">
            <div class="header-left">
                <h1><i class="fas fa-shopping-cart"></i> Quản lý đơn hàng</h1>
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
            <a href="orders.php" class="nav-item active">
                <i class="fas fa-shopping-cart"></i> Đơn hàng
            </a>
            <a href="products.php" class="nav-item">
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
                        <label>Trạng thái:</label>
                        <select name="status">
                            <option value="">Tất cả</option>
                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                            <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                            <option value="shipping" <?php echo $status === 'shipping' ? 'selected' : ''; ?>>Đang giao</option>
                            <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                            <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Tìm kiếm:</label>
                        <input type="text" name="search" placeholder="Mã đơn, tên khách hàng, email..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Lọc
                    </button>
                    
                    <a href="orders.php" class="btn btn-secondary">
                        <i class="fas fa-refresh"></i> Reset
                    </a>
                </form>
            </div>

            <!-- Orders Table -->
            <div class="orders-table-container">
                <div class="table-header">
                    <h3>Danh sách đơn hàng (<?php echo $total_orders; ?> đơn)</h3>
                </div>
                
                <div class="table-content">
                    <?php if (!empty($orders)): ?>
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Số sản phẩm</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>
                                            <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="order-link">
                                                #<?php echo $order['id']; ?>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="customer-info">
                                                <div class="customer-name"><?php echo htmlspecialchars($order['full_name']); ?></div>
                                                <div class="customer-email"><?php echo htmlspecialchars($order['email']); ?></div>
                                            </div>
                                        </td>
                                        <td><?php echo $order['item_count']; ?> sản phẩm</td>
                                        <td class="amount"><?php echo number_format($order['total_amount']); ?>đ</td>
                                        <td>
                                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                                <?php 
                                                $status_labels = [
                                                    'pending' => 'Chờ xác nhận',
                                                    'confirmed' => 'Đã xác nhận',
                                                    'shipping' => 'Đang giao',
                                                    'completed' => 'Hoàn thành',
                                                    'cancelled' => 'Đã hủy'
                                                ];
                                                echo $status_labels[$order['status']] ?? $order['status'];
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($order['status'] === 'pending'): ?>
                                                    <button class="btn btn-sm btn-success" onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'confirmed')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php elseif ($order['status'] === 'confirmed'): ?>
                                                    <button class="btn btn-sm btn-warning" onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'shipping')">
                                                        <i class="fas fa-truck"></i>
                                                    </button>
                                                <?php elseif ($order['status'] === 'shipping'): ?>
                                                    <button class="btn btn-sm btn-info" onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'completed')">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                                                    <button class="btn btn-sm btn-danger" onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'cancelled')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-shopping-cart"></i>
                            <h3>Không có đơn hàng nào</h3>
                            <p>Chưa có đơn hàng nào phù hợp với bộ lọc</p>
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
        function updateOrderStatus(orderId, newStatus) {
            const statusLabels = {
                'confirmed': 'xác nhận',
                'shipping': 'chuyển sang đang giao',
                'completed': 'hoàn thành',
                'cancelled': 'hủy'
            };
            
            if (confirm(`Bạn có chắc muốn ${statusLabels[newStatus]} đơn hàng #${orderId}?`)) {
                fetch('api/update-order-status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: orderId,
                        status: newStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Đã cập nhật trạng thái đơn hàng!');
                        location.reload();
                    } else {
                        alert('Có lỗi xảy ra: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi cập nhật trạng thái!');
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

        .orders-table-container {
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

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th,
        .orders-table td {
            padding: var(--spacing-md);
            text-align: left;
            border-bottom: 1px solid var(--primary-color);
        }

        .orders-table th {
            background: var(--bg-light);
            font-weight: 600;
            color: var(--text-dark);
        }

        .order-link {
            color: var(--cta-color);
            text-decoration: none;
            font-weight: 500;
        }

        .order-link:hover {
            text-decoration: underline;
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

        .customer-email {
            font-size: var(--font-size-sm);
            color: var(--text-light);
        }

        .amount {
            font-weight: 600;
            color: var(--cta-color);
        }

        .status-badge {
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-sm);
            font-size: var(--font-size-xs);
            font-weight: 500;
        }

        .status-pending {
            background: #FFF3CD;
            color: #856404;
        }

        .status-confirmed {
            background: #D1ECF1;
            color: #0C5460;
        }

        .status-shipping {
            background: #D4EDDA;
            color: #155724;
        }

        .status-completed {
            background: #D1ECF1;
            color: #0C5460;
        }

        .status-cancelled {
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

        .btn-success {
            background: #28a745;
            color: var(--white);
        }

        .btn-warning {
            background: #ffc107;
            color: var(--text-dark);
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
            
            .orders-table {
                font-size: var(--font-size-sm);
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>
