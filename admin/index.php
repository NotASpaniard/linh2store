<?php
/**
 * Admin Dashboard
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/session.php';
require_once '../config/database.php';

// Kiểm tra đăng nhập và quyền admin
if (!isLoggedIn() || getCurrentUser()['role'] !== 'admin') {
    header('Location: ../auth/dang-nhap.php');
    exit();
}

$stats = [];
$recent_orders = [];
$low_stock_products = [];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Thống kê tổng quan
    $queries = [
        'total_users' => "SELECT COUNT(*) as count FROM users WHERE status = 'active'",
        'total_products' => "SELECT COUNT(*) as count FROM products WHERE status = 'active'",
        'total_orders' => "SELECT COUNT(*) as count FROM orders",
        'total_revenue' => "SELECT SUM(final_amount) as total FROM orders WHERE status = 'delivered'",
        'pending_orders' => "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'",
        'low_stock' => "SELECT COUNT(*) as count FROM products WHERE stock_quantity < 10 AND status = 'active'"
    ];
    
    foreach ($queries as $key => $query) {
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        $stats[$key] = $result['count'] ?? $result['total'] ?? 0;
    }
    
    // Đơn hàng gần đây
    $stmt = $conn->prepare("
        SELECT o.*, u.full_name, u.email, COUNT(oi.id) as item_count
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recent_orders = $stmt->fetchAll();
    
    // Sản phẩm sắp hết hàng
    $stmt = $conn->prepare("
        SELECT p.*, b.name as brand_name
        FROM products p
        LEFT JOIN brands b ON p.brand_id = b.id
        WHERE p.stock_quantity < 10 AND p.status = 'active'
        ORDER BY p.stock_quantity ASC
        LIMIT 10
    ");
    $stmt->execute();
    $low_stock_products = $stmt->fetchAll();
    
} catch (Exception $e) {
    $stats = array_fill_keys(['total_users', 'total_products', 'total_orders', 'total_revenue', 'pending_orders', 'low_stock'], 0);
    $recent_orders = [];
    $low_stock_products = [];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Linh2Store</title>
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
                        <p><i class="fas fa-crown"></i> Admin Dashboard</p>
                    </div>
                    <div class="col">
                        <p><i class="fas fa-user"></i> <?php echo htmlspecialchars(getCurrentUser()['full_name']); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="header-main">
            <div class="container">
                <div class="header-content">
                    <a href="../" class="logo">Linh2Store Admin</a>
                    
                    <nav class="nav">
                        <a href="index.php" class="nav-link active">Tổng quan</a>
                        <a href="san-pham/" class="nav-link">Sản phẩm</a>
                        <a href="don-hang/" class="nav-link">Đơn hàng</a>
                        <a href="khach-hang/" class="nav-link">Khách hàng</a>
                        <a href="thong-ke/" class="nav-link">Thống kê</a>
                    </nav>
                    
                    <div class="user-actions">
                        <a href="../" class="user-icon" title="Về trang chủ">
                            <i class="fas fa-home"></i>
                        </a>
                        
                        <a href="../auth/dang-xuat.php" class="user-icon" title="Đăng xuất">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Dashboard Content -->
    <div class="admin-dashboard">
        <div class="container">
            <div class="dashboard-header">
                <h1>Dashboard</h1>
                <p>Chào mừng trở lại, <?php echo htmlspecialchars(getCurrentUser()['full_name']); ?>!</p>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_users']); ?></h3>
                        <p>Người dùng</p>
                        <span class="stat-change positive">+12%</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon products">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_products']); ?></h3>
                        <p>Sản phẩm</p>
                        <span class="stat-change positive">+5%</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orders">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_orders']); ?></h3>
                        <p>Đơn hàng</p>
                        <span class="stat-change positive">+8%</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon revenue">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_revenue']); ?>đ</h3>
                        <p>Doanh thu</p>
                        <span class="stat-change positive">+15%</span>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Recent Orders -->
                <div class="col-8">
                    <div class="dashboard-section">
                        <div class="section-header">
                            <h2>Đơn hàng gần đây</h2>
                            <a href="don-hang/" class="btn btn-outline">Xem tất cả</a>
                        </div>
                        
                        <div class="orders-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Khách hàng</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recent_orders)): ?>
                                        <?php foreach ($recent_orders as $order): ?>
                                            <tr>
                                                <td>#<?php echo $order['order_number']; ?></td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($order['full_name']); ?></strong>
                                                        <br>
                                                        <small><?php echo htmlspecialchars($order['email']); ?></small>
                                                    </div>
                                                </td>
                                                <td><?php echo number_format($order['final_amount']); ?>đ</td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                                        <?php
                                                        $status_labels = [
                                                            'pending' => 'Chờ xử lý',
                                                            'confirmed' => 'Đã xác nhận',
                                                            'shipping' => 'Đang giao',
                                                            'delivered' => 'Đã giao',
                                                            'cancelled' => 'Đã hủy'
                                                        ];
                                                        echo $status_labels[$order['status']] ?? $order['status'];
                                                        ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                                <td>
                                                    <a href="don-hang/chi-tiet.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">
                                                        Chi tiết
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Chưa có đơn hàng nào</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="col-4">
                    <!-- Pending Orders -->
                    <div class="dashboard-widget">
                        <div class="widget-header">
                            <h3>Đơn hàng chờ xử lý</h3>
                            <span class="badge"><?php echo $stats['pending_orders']; ?></span>
                        </div>
                        <div class="widget-content">
                            <p>Có <?php echo $stats['pending_orders']; ?> đơn hàng đang chờ xử lý</p>
                            <a href="don-hang/?status=pending" class="btn btn-primary btn-sm">Xem chi tiết</a>
                        </div>
                    </div>
                    
                    <!-- Low Stock -->
                    <div class="dashboard-widget">
                        <div class="widget-header">
                            <h3>Sản phẩm sắp hết hàng</h3>
                            <span class="badge warning"><?php echo $stats['low_stock']; ?></span>
                        </div>
                        <div class="widget-content">
                            <?php if (!empty($low_stock_products)): ?>
                                <div class="low-stock-list">
                                    <?php foreach (array_slice($low_stock_products, 0, 5) as $product): ?>
                                        <div class="low-stock-item">
                                            <div class="product-info">
                                                <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                                <p><?php echo htmlspecialchars($product['brand_name']); ?></p>
                                            </div>
                                            <div class="stock-info">
                                                <span class="stock-quantity"><?php echo $product['stock_quantity']; ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <a href="san-pham/?filter=low_stock" class="btn btn-warning btn-sm">Xem tất cả</a>
                            <?php else: ?>
                                <p>Tất cả sản phẩm đều có đủ hàng</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="dashboard-widget">
                        <div class="widget-header">
                            <h3>Thao tác nhanh</h3>
                        </div>
                        <div class="widget-content">
                            <div class="quick-actions">
                                <a href="san-pham/them.php" class="action-btn">
                                    <i class="fas fa-plus"></i>
                                    Thêm sản phẩm
                                </a>
                                <a href="don-hang/" class="action-btn">
                                    <i class="fas fa-list"></i>
                                    Quản lý đơn hàng
                                </a>
                                <a href="thong-ke/" class="action-btn">
                                    <i class="fas fa-chart-bar"></i>
                                    Xem báo cáo
                                </a>
                                <a href="cai-dat/" class="action-btn">
                                    <i class="fas fa-cog"></i>
                                    Cài đặt
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    
    <style>
        .admin-dashboard {
            padding: var(--spacing-xl) 0;
            background: var(--bg-light);
            min-height: 100vh;
        }
        
        .dashboard-header {
            margin-bottom: var(--spacing-xl);
        }
        
        .dashboard-header h1 {
            margin: 0 0 var(--spacing-sm) 0;
            color: var(--text-dark);
        }
        
        .dashboard-header p {
            margin: 0;
            color: var(--text-light);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-xl);
        }
        
        .stat-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: var(--spacing-lg);
            transition: transform var(--transition-fast);
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--font-size-xl);
            color: var(--white);
        }
        
        .stat-icon.users {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .stat-icon.products {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stat-icon.orders {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stat-icon.revenue {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        .stat-content h3 {
            margin: 0 0 var(--spacing-xs) 0;
            font-size: var(--font-size-2xl);
            color: var(--text-dark);
        }
        
        .stat-content p {
            margin: 0 0 var(--spacing-xs) 0;
            color: var(--text-light);
            font-size: var(--font-size-sm);
        }
        
        .stat-change {
            font-size: var(--font-size-sm);
            font-weight: 600;
        }
        
        .stat-change.positive {
            color: var(--success-color);
        }
        
        .stat-change.negative {
            color: var(--error-color);
        }
        
        .dashboard-section {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--spacing-xl);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-lg);
        }
        
        .section-header h2 {
            margin: 0;
            color: var(--text-dark);
        }
        
        .orders-table {
            overflow-x: auto;
        }
        
        .orders-table table {
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
            background: var(--primary-color);
            color: var(--text-dark);
            font-weight: 600;
        }
        
        .orders-table tr:hover {
            background: var(--bg-light);
        }
        
        .status-badge {
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-sm);
            font-size: var(--font-size-sm);
            font-weight: 500;
        }
        
        .status-pending {
            background: #FFF3E0;
            color: #E65100;
        }
        
        .status-confirmed {
            background: #E3F2FD;
            color: #1565C0;
        }
        
        .status-shipping {
            background: #E8F5E8;
            color: #2E7D32;
        }
        
        .status-delivered {
            background: #E8F5E8;
            color: #2E7D32;
        }
        
        .status-cancelled {
            background: #FFEBEE;
            color: #C62828;
        }
        
        .dashboard-widget {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--spacing-lg);
        }
        
        .widget-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-lg);
        }
        
        .widget-header h3 {
            margin: 0;
            color: var(--text-dark);
        }
        
        .badge {
            background: var(--cta-color);
            color: var(--white);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-full);
            font-size: var(--font-size-sm);
            font-weight: 600;
        }
        
        .badge.warning {
            background: var(--warning-color);
        }
        
        .low-stock-list {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
        }
        
        .low-stock-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-sm);
            background: var(--bg-light);
            border-radius: var(--radius-sm);
        }
        
        .product-info h4 {
            margin: 0 0 var(--spacing-xs) 0;
            font-size: var(--font-size-sm);
            color: var(--text-dark);
        }
        
        .product-info p {
            margin: 0;
            font-size: var(--font-size-xs);
            color: var(--text-light);
        }
        
        .stock-quantity {
            background: var(--warning-color);
            color: var(--white);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-sm);
            font-size: var(--font-size-sm);
            font-weight: 600;
        }
        
        .quick-actions {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            padding: var(--spacing-md);
            background: var(--primary-color);
            color: var(--text-dark);
            text-decoration: none;
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
        }
        
        .action-btn:hover {
            background: var(--secondary-color);
            transform: translateX(4px);
        }
        
        .action-btn i {
            width: 20px;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .col-8, .col-4 {
                flex: 0 0 100%;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .orders-table {
                font-size: var(--font-size-sm);
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .stat-card {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</body>
</html>
