<?php
/**
 * Admin Dashboard
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

// Lấy dữ liệu thống kê
$stats = [];
$recent_orders = [];
$low_stock_products = [];
$top_products = [];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Thống kê doanh thu
    $stmt = $conn->prepare("
        SELECT 
            SUM(CASE WHEN DATE(created_at) = CURDATE() THEN total_amount ELSE 0 END) as today_revenue,
            SUM(CASE WHEN YEARWEEK(created_at) = YEARWEEK(NOW()) THEN total_amount ELSE 0 END) as week_revenue,
            SUM(CASE WHEN YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW()) THEN total_amount ELSE 0 END) as month_revenue,
            COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_orders,
            COUNT(CASE WHEN YEARWEEK(created_at) = YEARWEEK(NOW()) THEN 1 END) as week_orders,
            COUNT(CASE WHEN YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW()) THEN 1 END) as month_orders
        FROM orders 
        WHERE status != 'cancelled'
    ");
    $stmt->execute();
    $revenue_stats = $stmt->fetch();
    
    // Thống kê khách hàng
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_customers,
            COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as new_customers_today,
            COUNT(CASE WHEN DATE(created_at) >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_customers_week
        FROM users 
        WHERE role = 'user'
    ");
    $stmt->execute();
    $customer_stats = $stmt->fetch();
    
    // Sản phẩm tồn kho thấp
    $stmt = $conn->prepare("
        SELECT id, name, stock_quantity, price
        FROM products 
        WHERE stock_quantity <= 10 AND status = 'active'
        ORDER BY stock_quantity ASC
        LIMIT 10
    ");
    $stmt->execute();
    $low_stock_products = $stmt->fetchAll();
    
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
    
    // Sản phẩm bán chạy
    $stmt = $conn->prepare("
        SELECT p.id, p.name, p.price, SUM(oi.quantity) as total_sold, pi.image_url
        FROM products p
        LEFT JOIN order_items oi ON p.id = oi.product_id
        LEFT JOIN orders o ON oi.order_id = o.id
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE o.status = 'completed'
        GROUP BY p.id
        ORDER BY total_sold DESC
        LIMIT 5
    ");
    $stmt->execute();
    $top_products = $stmt->fetchAll();
    
    // Tổng hợp thống kê
    $stats = [
        'today_revenue' => $revenue_stats['today_revenue'] ?: 0,
        'week_revenue' => $revenue_stats['week_revenue'] ?: 0,
        'month_revenue' => $revenue_stats['month_revenue'] ?: 0,
        'today_orders' => $revenue_stats['today_orders'] ?: 0,
        'week_orders' => $revenue_stats['week_orders'] ?: 0,
        'month_orders' => $revenue_stats['month_orders'] ?: 0,
        'total_customers' => $customer_stats['total_customers'] ?: 0,
        'new_customers_today' => $customer_stats['new_customers_today'] ?: 0,
        'new_customers_week' => $customer_stats['new_customers_week'] ?: 0
    ];
    
} catch (Exception $e) {
    $stats = [
        'today_revenue' => 0, 'week_revenue' => 0, 'month_revenue' => 0,
        'today_orders' => 0, 'week_orders' => 0, 'month_orders' => 0,
        'total_customers' => 0, 'new_customers_today' => 0, 'new_customers_week' => 0
    ];
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="header-content">
            <div class="header-left">
                <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
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
            <a href="index.php" class="nav-item active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="orders.php" class="nav-item">
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
            <!-- Key Metrics -->
            <div class="metrics-grid">
                <div class="metric-card revenue">
                    <div class="metric-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="metric-content">
                        <h3>Doanh thu hôm nay</h3>
                        <div class="metric-value"><?php echo number_format($stats['today_revenue']); ?>đ</div>
                        <div class="metric-subtitle">
                            Tuần: <?php echo number_format($stats['week_revenue']); ?>đ | 
                            Tháng: <?php echo number_format($stats['month_revenue']); ?>đ
                        </div>
                    </div>
                </div>

                <div class="metric-card orders">
                    <div class="metric-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="metric-content">
                        <h3>Đơn hàng hôm nay</h3>
                        <div class="metric-value"><?php echo $stats['today_orders']; ?></div>
                        <div class="metric-subtitle">
                            Tuần: <?php echo $stats['week_orders']; ?> | 
                            Tháng: <?php echo $stats['month_orders']; ?>
                        </div>
                    </div>
                </div>

                <div class="metric-card customers">
                    <div class="metric-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="metric-content">
                        <h3>Khách hàng mới</h3>
                        <div class="metric-value"><?php echo $stats['new_customers_today']; ?></div>
                        <div class="metric-subtitle">
                            Tổng: <?php echo $stats['total_customers']; ?> | 
                            Tuần: <?php echo $stats['new_customers_week']; ?>
                        </div>
                    </div>
                </div>

                <div class="metric-card conversion">
                    <div class="metric-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="metric-content">
                        <h3>Tỷ lệ chuyển đổi</h3>
                        <div class="metric-value">
                            <?php 
                            $conversion_rate = $stats['total_customers'] > 0 ? 
                                round(($stats['month_orders'] / $stats['total_customers']) * 100, 1) : 0;
                            echo $conversion_rate . '%';
                            ?>
                        </div>
                        <div class="metric-subtitle">Tháng này</div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="charts-row">
                <div class="chart-container">
                    <h3>Doanh thu theo thời gian</h3>
                    <canvas id="revenueChart"></canvas>
                </div>
                <div class="chart-container">
                    <h3>Sản phẩm bán chạy</h3>
                    <canvas id="productsChart"></canvas>
                </div>
            </div>

            <!-- Data Tables Row -->
            <div class="data-tables-row">
                <!-- Recent Orders -->
                <div class="data-table-container">
                    <div class="table-header">
                        <h3><i class="fas fa-clock"></i> Đơn hàng gần đây</h3>
                        <a href="orders.php" class="view-all">Xem tất cả</a>
                    </div>
                    <div class="table-content">
                        <?php if (!empty($recent_orders)): ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Khách hàng</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày tạo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                            <td><?php echo number_format($order['total_amount']); ?>đ</td>
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
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="no-data">Chưa có đơn hàng nào</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Low Stock Products -->
                <div class="data-table-container">
                    <div class="table-header">
                        <h3><i class="fas fa-exclamation-triangle"></i> Sản phẩm sắp hết</h3>
                        <a href="inventory.php" class="view-all">Xem tất cả</a>
                    </div>
                    <div class="table-content">
                        <?php if (!empty($low_stock_products)): ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Tồn kho</th>
                                        <th>Giá</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($low_stock_products as $product): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td>
                                                <span class="stock-badge <?php echo $product['stock_quantity'] <= 5 ? 'critical' : 'low'; ?>">
                                                    <?php echo $product['stock_quantity']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo number_format($product['price']); ?>đ</td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" onclick="restockProduct(<?php echo $product['id']; ?>)">
                                                    Nhập hàng
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="no-data">Tất cả sản phẩm đều đủ hàng</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="top-products-section">
                <div class="section-header">
                    <h3><i class="fas fa-trophy"></i> Sản phẩm bán chạy</h3>
                </div>
                <div class="top-products-grid">
                    <?php foreach ($top_products as $product): ?>
                        <div class="top-product-card">
                            <div class="product-image">
                                <img src="<?php echo $product['image_url'] ?: 'https://via.placeholder.com/80x80/E3F2FD/EC407A?text=No+Image'; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                            <div class="product-info">
                                <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                <p class="product-price"><?php echo number_format($product['price']); ?>đ</p>
                                <p class="sales-count">Đã bán: <?php echo $product['total_sold']; ?> sản phẩm</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'],
                datasets: [{
                    label: 'Doanh thu (đ)',
                    data: [<?php echo $stats['week_revenue'] / 7; ?>, <?php echo $stats['week_revenue'] / 7; ?>, <?php echo $stats['week_revenue'] / 7; ?>, <?php echo $stats['week_revenue'] / 7; ?>, <?php echo $stats['week_revenue'] / 7; ?>, <?php echo $stats['week_revenue'] / 7; ?>, <?php echo $stats['week_revenue'] / 7; ?>],
                    borderColor: '#EC407A',
                    backgroundColor: 'rgba(236, 64, 122, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Products Chart
        const productsCtx = document.getElementById('productsChart').getContext('2d');
        new Chart(productsCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php foreach($top_products as $product): ?>'<?php echo htmlspecialchars($product['name']); ?>',<?php endforeach; ?>],
                datasets: [{
                    data: [<?php foreach($top_products as $product): ?><?php echo $product['total_sold']; ?>,<?php endforeach; ?>],
                    backgroundColor: [
                        '#E3F2FD',
                        '#BBDEFB', 
                        '#FCE4EC',
                        '#EC407A',
                        '#F5F5F5'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        function restockProduct(productId) {
            if (confirm('Bạn có muốn nhập hàng cho sản phẩm này?')) {
                // AJAX call để nhập hàng
                fetch('api/restock.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: 50
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Đã nhập hàng thành công!');
                        location.reload();
                    } else {
                        alert('Có lỗi xảy ra: ' + data.message);
                    }
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

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-xl);
        }

        .metric-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: var(--spacing-lg);
        }

        .metric-icon {
            width: 60px;
            height: 60px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--font-size-xl);
            color: var(--white);
        }

        .metric-card.revenue .metric-icon {
            background: linear-gradient(135deg, #4CAF50, #8BC34A);
        }

        .metric-card.orders .metric-icon {
            background: linear-gradient(135deg, #2196F3, #03A9F4);
        }

        .metric-card.customers .metric-icon {
            background: linear-gradient(135deg, #FF9800, #FFC107);
        }

        .metric-card.conversion .metric-icon {
            background: linear-gradient(135deg, #9C27B0, #E91E63);
        }

        .metric-content h3 {
            margin: 0 0 var(--spacing-xs) 0;
            color: var(--text-light);
            font-size: var(--font-size-sm);
        }

        .metric-value {
            font-size: var(--font-size-2xl);
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: var(--spacing-xs);
        }

        .metric-subtitle {
            font-size: var(--font-size-xs);
            color: var(--text-muted);
        }

        .charts-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
        }

        .chart-container {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
        }

        .chart-container h3 {
            margin: 0 0 var(--spacing-lg) 0;
            color: var(--text-dark);
        }

        .data-tables-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
        }

        .data-table-container {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-lg);
            border-bottom: 1px solid var(--primary-color);
            background: var(--bg-light);
        }

        .table-header h3 {
            margin: 0;
            color: var(--text-dark);
        }

        .view-all {
            color: var(--cta-color);
            text-decoration: none;
            font-size: var(--font-size-sm);
        }

        .table-content {
            max-height: 400px;
            overflow-y: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: var(--spacing-md);
            text-align: left;
            border-bottom: 1px solid var(--primary-color);
        }

        .data-table th {
            background: var(--bg-light);
            font-weight: 600;
            color: var(--text-dark);
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

        .stock-badge {
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-sm);
            font-size: var(--font-size-xs);
            font-weight: 500;
        }

        .stock-badge.critical {
            background: #F8D7DA;
            color: #721C24;
        }

        .stock-badge.low {
            background: #FFF3CD;
            color: #856404;
        }

        .no-data {
            text-align: center;
            padding: var(--spacing-xl);
            color: var(--text-light);
        }

        .top-products-section {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
        }

        .section-header h3 {
            margin: 0 0 var(--spacing-lg) 0;
            color: var(--text-dark);
        }

        .top-products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-lg);
        }

        .top-product-card {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            padding: var(--spacing-md);
            border: 1px solid var(--primary-color);
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
        }

        .top-product-card:hover {
            box-shadow: var(--shadow-sm);
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

        .product-info h4 {
            margin: 0 0 var(--spacing-xs) 0;
            font-size: var(--font-size-sm);
            color: var(--text-dark);
        }

        .product-price {
            margin: 0 0 var(--spacing-xs) 0;
            font-size: var(--font-size-sm);
            font-weight: 600;
            color: var(--cta-color);
        }

        .sales-count {
            margin: 0;
            font-size: var(--font-size-xs);
            color: var(--text-light);
        }

        @media (max-width: 768px) {
            .charts-row,
            .data-tables-row {
                grid-template-columns: 1fr;
            }
            
            .nav-content {
                flex-wrap: wrap;
            }
            
            .metric-card {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</body>
</html>