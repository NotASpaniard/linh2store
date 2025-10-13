<?php
/**
 * Báo cáo & Thống kê
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
$period = $_GET['period'] ?? 'month';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$reports = [];
$chart_data = [];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Xác định khoảng thời gian
    $date_condition = "1=1";
    if ($start_date && $end_date) {
        $date_condition = "DATE(created_at) BETWEEN ? AND ?";
    } elseif ($period === 'today') {
        $date_condition = "DATE(created_at) = CURDATE()";
    } elseif ($period === 'week') {
        $date_condition = "YEARWEEK(created_at) = YEARWEEK(NOW())";
    } elseif ($period === 'month') {
        $date_condition = "YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())";
    } elseif ($period === 'year') {
        $date_condition = "YEAR(created_at) = YEAR(NOW())";
    }
    
    // Báo cáo tổng quan
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_orders,
            SUM(total_amount) as total_revenue,
            AVG(total_amount) as avg_order_value,
            COUNT(DISTINCT user_id) as unique_customers
        FROM orders 
        WHERE status != 'cancelled' AND $date_condition
    ");
    
    if ($start_date && $end_date) {
        $stmt->execute([$start_date, $end_date]);
    } else {
        $stmt->execute();
    }
    $reports['overview'] = $stmt->fetch();
    
    // Top sản phẩm bán chạy
    $stmt = $conn->prepare("
        SELECT p.name, p.sku, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price) as revenue
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        LEFT JOIN orders o ON oi.order_id = o.id
        WHERE o.status = 'completed' AND $date_condition
        GROUP BY p.id
        ORDER BY total_sold DESC
        LIMIT 10
    ");
    
    if ($start_date && $end_date) {
        $stmt->execute([$start_date, $end_date]);
    } else {
        $stmt->execute();
    }
    $reports['top_products'] = $stmt->fetchAll();
    
    // Doanh thu theo ngày (7 ngày gần nhất)
    $stmt = $conn->prepare("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as orders,
            SUM(total_amount) as revenue
        FROM orders 
        WHERE status = 'completed' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $stmt->execute();
    $reports['daily_revenue'] = $stmt->fetchAll();
    
    // Thống kê theo trạng thái đơn hàng
    $stmt = $conn->prepare("
        SELECT 
            status,
            COUNT(*) as count,
            SUM(total_amount) as total_amount
        FROM orders 
        WHERE $date_condition
        GROUP BY status
    ");
    
    if ($start_date && $end_date) {
        $stmt->execute([$start_date, $end_date]);
    } else {
        $stmt->execute();
    }
    $reports['order_status'] = $stmt->fetchAll();
    
} catch (Exception $e) {
    $reports = [
        'overview' => ['total_orders' => 0, 'total_revenue' => 0, 'avg_order_value' => 0, 'unique_customers' => 0],
        'top_products' => [],
        'daily_revenue' => [],
        'order_status' => []
    ];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo & Thống kê - Linh2Store</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="header-content">
            <div class="header-left">
                <h1><i class="fas fa-chart-bar"></i> Báo cáo & Thống kê</h1>
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
            <a href="customers.php" class="nav-item">
                <i class="fas fa-users"></i> Khách hàng
            </a>
            <a href="inventory.php" class="nav-item">
                <i class="fas fa-warehouse"></i> Kho hàng
            </a>
            <a href="reports.php" class="nav-item active">
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
                        <label>Khoảng thời gian:</label>
                        <select name="period">
                            <option value="today" <?php echo $period === 'today' ? 'selected' : ''; ?>>Hôm nay</option>
                            <option value="week" <?php echo $period === 'week' ? 'selected' : ''; ?>>Tuần này</option>
                            <option value="month" <?php echo $period === 'month' ? 'selected' : ''; ?>>Tháng này</option>
                            <option value="year" <?php echo $period === 'year' ? 'selected' : ''; ?>>Năm nay</option>
                            <option value="custom" <?php echo $start_date && $end_date ? 'selected' : ''; ?>>Tùy chọn</option>
                        </select>
                    </div>
                    
                    <div class="filter-group" id="custom-dates" style="<?php echo $start_date && $end_date ? '' : 'display: none;'; ?>">
                        <label>Từ ngày:</label>
                        <input type="date" name="start_date" value="<?php echo $start_date; ?>">
                    </div>
                    
                    <div class="filter-group" id="custom-dates-end" style="<?php echo $start_date && $end_date ? '' : 'display: none;'; ?>">
                        <label>Đến ngày:</label>
                        <input type="date" name="end_date" value="<?php echo $end_date; ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-chart-line"></i> Xem báo cáo
                    </button>
                    
                    <button type="button" class="btn btn-success" onclick="exportReport()">
                        <i class="fas fa-download"></i> Xuất Excel
                    </button>
                </form>
            </div>

            <!-- Overview Cards -->
            <div class="overview-cards">
                <div class="overview-card">
                    <div class="card-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="card-content">
                        <h3>Tổng đơn hàng</h3>
                        <div class="card-value"><?php echo number_format($reports['overview']['total_orders']); ?></div>
                    </div>
                </div>
                
                <div class="overview-card">
                    <div class="card-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="card-content">
                        <h3>Tổng doanh thu</h3>
                        <div class="card-value"><?php echo number_format($reports['overview']['total_revenue']); ?>đ</div>
                    </div>
                </div>
                
                <div class="overview-card">
                    <div class="card-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="card-content">
                        <h3>Giá trị đơn hàng TB</h3>
                        <div class="card-value"><?php echo number_format($reports['overview']['avg_order_value']); ?>đ</div>
                    </div>
                </div>
                
                <div class="overview-card">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-content">
                        <h3>Khách hàng</h3>
                        <div class="card-value"><?php echo number_format($reports['overview']['unique_customers']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="charts-row">
                <div class="chart-container">
                    <h3>Doanh thu 7 ngày gần nhất</h3>
                    <canvas id="revenueChart"></canvas>
                </div>
                
                <div class="chart-container">
                    <h3>Trạng thái đơn hàng</h3>
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <!-- Top Products -->
            <div class="top-products-section">
                <div class="section-header">
                    <h3><i class="fas fa-trophy"></i> Sản phẩm bán chạy</h3>
                </div>
                
                <div class="top-products-table">
                    <?php if (!empty($reports['top_products'])): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Sản phẩm</th>
                                    <th>SKU</th>
                                    <th>Số lượng bán</th>
                                    <th>Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports['top_products'] as $index => $product): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['sku']); ?></td>
                                        <td>
                                            <span class="sales-badge"><?php echo $product['total_sold']; ?></span>
                                        </td>
                                        <td><?php echo number_format($product['revenue']); ?>đ</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-chart-bar"></i>
                            <h3>Chưa có dữ liệu</h3>
                            <p>Chưa có sản phẩm nào được bán trong khoảng thời gian này</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueData = <?php echo json_encode($reports['daily_revenue']); ?>;
        
        const labels = revenueData.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('vi-VN', { weekday: 'short', month: 'short', day: 'numeric' });
        });
        
        const revenueValues = revenueData.map(item => item.revenue);
        
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Doanh thu (đ)',
                    data: revenueValues,
                    borderColor: '#EC407A',
                    backgroundColor: 'rgba(236, 64, 122, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + 'đ';
                            }
                        }
                    }
                }
            }
        });

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusData = <?php echo json_encode($reports['order_status']); ?>;
        
        const statusLabels = statusData.map(item => {
            const labels = {
                'pending': 'Chờ xác nhận',
                'confirmed': 'Đã xác nhận',
                'shipping': 'Đang giao',
                'completed': 'Hoàn thành',
                'cancelled': 'Đã hủy'
            };
            return labels[item.status] || item.status;
        });
        
        const statusValues = statusData.map(item => item.count);
        const statusColors = ['#FFC107', '#17A2B8', '#28A745', '#007BFF', '#DC3545'];
        
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: statusColors
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

        // Toggle custom dates
        document.querySelector('select[name="period"]').addEventListener('change', function() {
            const customDates = document.getElementById('custom-dates');
            const customDatesEnd = document.getElementById('custom-dates-end');
            
            if (this.value === 'custom') {
                customDates.style.display = 'flex';
                customDatesEnd.style.display = 'flex';
            } else {
                customDates.style.display = 'none';
                customDatesEnd.style.display = 'none';
            }
        });

        function exportReport() {
            // Tạo URL export
            const params = new URLSearchParams(window.location.search);
            params.set('export', 'excel');
            window.open('api/export-report.php?' + params.toString(), '_blank');
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

        .overview-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-xl);
        }

        .overview-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: var(--spacing-lg);
        }

        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--font-size-xl);
            color: var(--white);
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        }

        .card-content h3 {
            margin: 0 0 var(--spacing-xs) 0;
            color: var(--text-light);
            font-size: var(--font-size-sm);
        }

        .card-value {
            font-size: var(--font-size-2xl);
            font-weight: 700;
            color: var(--text-dark);
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

        .sales-badge {
            background: var(--cta-color);
            color: var(--white);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-sm);
            font-size: var(--font-size-xs);
            font-weight: 500;
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

        .btn {
            padding: var(--spacing-sm) var(--spacing-md);
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

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-success {
            background: #28a745;
            color: var(--white);
        }

        .btn:hover {
            opacity: 0.8;
            transform: translateY(-1px);
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
            
            .charts-row {
                grid-template-columns: 1fr;
            }
            
            .overview-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
