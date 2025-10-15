<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../auth/dang-nhap.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Lấy thời gian filter
$period = $_GET['period'] ?? '30'; // 7, 30, 90, 365
$start_date = date('Y-m-d', strtotime("-$period days"));
$end_date = date('Y-m-d');

// Sales Analytics
$sales_sql = "
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as total_orders,
        SUM(final_amount) as total_revenue,
        AVG(final_amount) as avg_order_value
    FROM orders 
    WHERE created_at >= ? AND created_at <= ?
    GROUP BY DATE(created_at)
    ORDER BY date DESC
";
$stmt = $conn->prepare($sales_sql);
$stmt->execute([$start_date, $end_date]);
$sales_data = $stmt->fetchAll();

// Tổng doanh thu
$total_revenue_sql = "SELECT SUM(final_amount) as total FROM orders WHERE created_at >= ? AND created_at <= ?";
$stmt = $conn->prepare($total_revenue_sql);
$stmt->execute([$start_date, $end_date]);
$total_revenue = $stmt->fetch()['total'] ?? 0;

// Tổng đơn hàng
$total_orders_sql = "SELECT COUNT(*) as total FROM orders WHERE created_at >= ? AND created_at <= ?";
$stmt = $conn->prepare($total_orders_sql);
$stmt->execute([$start_date, $end_date]);
$total_orders = $stmt->fetch()['total'] ?? 0;

// Giá trị đơn hàng trung bình
$avg_order_value = $total_orders > 0 ? $total_revenue / $total_orders : 0;

// Top sản phẩm bán chạy
$top_products_sql = "
    SELECT 
        p.name,
        SUM(oi.quantity) as total_sold,
        SUM(oi.total_price) as total_revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.created_at >= ? AND o.created_at <= ?
    GROUP BY oi.product_id
    ORDER BY total_sold DESC
    LIMIT 10
";
$stmt = $conn->prepare($top_products_sql);
$stmt->execute([$start_date, $end_date]);
$top_products = $stmt->fetchAll();

// Thống kê đơn hàng theo trạng thái
$order_status_sql = "
    SELECT 
        status,
        COUNT(*) as count,
        SUM(final_amount) as revenue
    FROM orders 
    WHERE created_at >= ? AND created_at <= ?
    GROUP BY status
";
$stmt = $conn->prepare($order_status_sql);
$stmt->execute([$start_date, $end_date]);
$order_status = $stmt->fetchAll();

// Thống kê người dùng
$user_stats_sql = "
    SELECT 
        COUNT(*) as total_users,
        COUNT(CASE WHEN created_at >= ? THEN 1 END) as new_users,
        COUNT(CASE WHEN role = 'admin' THEN 1 END) as admin_users
    FROM users
";
$stmt = $conn->prepare($user_stats_sql);
$stmt->execute([$start_date]);
$user_stats = $stmt->fetch();

// Thống kê đánh giá
$review_stats_sql = "
    SELECT 
        COUNT(*) as total_reviews,
        COUNT(CASE WHEN is_approved = 1 THEN 1 END) as approved_reviews,
        COUNT(CASE WHEN is_approved = 0 THEN 1 END) as pending_reviews,
        ROUND(AVG(rating), 2) as avg_rating
    FROM product_reviews
    WHERE created_at >= ? AND created_at <= ?
";
$stmt = $conn->prepare($review_stats_sql);
$stmt->execute([$start_date, $end_date]);
$review_stats = $stmt->fetch();

// Doanh thu theo ngày (cho chart)
$revenue_chart_sql = "
    SELECT 
        DATE(created_at) as date,
        SUM(final_amount) as revenue
    FROM orders 
    WHERE created_at >= ? AND created_at <= ?
    GROUP BY DATE(created_at)
    ORDER BY date ASC
";
$stmt = $conn->prepare($revenue_chart_sql);
$stmt->execute([$start_date, $end_date]);
$revenue_chart = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - Admin</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .analytics-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .period-filter {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filter-group {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-icon {
            font-size: 2.5em;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 1.1em;
        }
        
        .stat-change {
            font-size: 0.9em;
            margin-top: 5px;
        }
        
        .stat-change.positive {
            color: #28a745;
        }
        
        .stat-change.negative {
            color: #dc3545;
        }
        
        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .chart-title {
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        .data-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .data-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .table-header {
            background: var(--primary-color);
            color: white;
            padding: 15px 20px;
            font-weight: bold;
        }
        
        .table-content {
            padding: 0;
        }
        
        .table-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
        }
        
        .table-row:last-child {
            border-bottom: none;
        }
        
        .table-row:hover {
            background: #f8f9fa;
        }
        
        .product-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .product-image {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-shipped {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-delivered {
            background: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="analytics-container">
        <h1>Analytics Dashboard</h1>
        
        <!-- Period Filter -->
        <div class="period-filter">
            <div class="filter-group">
                <label>Thời gian:</label>
                <select onchange="window.location.href='?period=' + this.value">
                    <option value="7" <?php echo $period == '7' ? 'selected' : ''; ?>>7 ngày qua</option>
                    <option value="30" <?php echo $period == '30' ? 'selected' : ''; ?>>30 ngày qua</option>
                    <option value="90" <?php echo $period == '90' ? 'selected' : ''; ?>>90 ngày qua</option>
                    <option value="365" <?php echo $period == '365' ? 'selected' : ''; ?>>1 năm qua</option>
                </select>
                <span>Từ <?php echo date('d/m/Y', strtotime($start_date)); ?> đến <?php echo date('d/m/Y', strtotime($end_date)); ?></span>
            </div>
        </div>
        
        <!-- Key Metrics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="color: #28a745;">💰</div>
                <div class="stat-number" style="color: #28a745;"><?php echo number_format($total_revenue, 0, ',', '.'); ?>đ</div>
                <div class="stat-label">Tổng doanh thu</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: #007bff;">📦</div>
                <div class="stat-number" style="color: #007bff;"><?php echo $total_orders; ?></div>
                <div class="stat-label">Tổng đơn hàng</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: #ffc107;">📊</div>
                <div class="stat-number" style="color: #ffc107;"><?php echo number_format($avg_order_value, 0, ',', '.'); ?>đ</div>
                <div class="stat-label">Giá trị đơn hàng TB</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: #17a2b8;">👥</div>
                <div class="stat-number" style="color: #17a2b8;"><?php echo $user_stats['total_users']; ?></div>
                <div class="stat-label">Tổng người dùng</div>
            </div>
        </div>
        
        <!-- Revenue Chart -->
        <div class="chart-container">
            <h3 class="chart-title">Biểu đồ doanh thu</h3>
            <canvas id="revenueChart" width="400" height="100"></canvas>
        </div>
        
        <!-- Data Tables -->
        <div class="data-grid">
            <!-- Top Products -->
            <div class="data-table">
                <div class="table-header">
                    <h3>Top sản phẩm bán chạy</h3>
                </div>
                <div class="table-content">
                    <?php if (empty($top_products)): ?>
                        <div class="table-row">
                            <span>Không có dữ liệu</span>
                        </div>
                    <?php else: ?>
                        <?php foreach ($top_products as $product): ?>
                            <div class="table-row">
                                <div class="product-info">
                                    <div class="product-image" style="width: 40px; height: 40px; background: #f0f0f0; border-radius: 5px; display: flex; align-items: center; justify-content: center; color: #999;">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: bold;"><?php echo htmlspecialchars($product['name']); ?></div>
                                        <div style="font-size: 0.9em; color: #666;"><?php echo number_format($product['total_revenue'], 0, ',', '.'); ?>đ</div>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-weight: bold;"><?php echo $product['total_sold']; ?> sản phẩm</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Order Status -->
            <div class="data-table">
                <div class="table-header">
                    <h3>Trạng thái đơn hàng</h3>
                </div>
                <div class="table-content">
                    <?php if (empty($order_status)): ?>
                        <div class="table-row">
                            <span>Không có dữ liệu</span>
                        </div>
                    <?php else: ?>
                        <?php foreach ($order_status as $status): ?>
                            <div class="table-row">
                                <div>
                                    <span class="status-badge status-<?php echo $status['status']; ?>">
                                        <?php 
                                        $status_labels = [
                                            'pending' => 'Chờ xử lý',
                                            'processing' => 'Đang xử lý',
                                            'shipped' => 'Đã giao hàng',
                                            'delivered' => 'Đã nhận hàng',
                                            'cancelled' => 'Đã hủy'
                                        ];
                                        echo $status_labels[$status['status']] ?? $status['status'];
                                        ?>
                                    </span>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-weight: bold;"><?php echo $status['count']; ?> đơn</div>
                                    <div style="font-size: 0.9em; color: #666;"><?php echo number_format($status['revenue'], 0, ',', '.'); ?>đ</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Review Stats -->
        <div class="chart-container">
            <h3 class="chart-title">Thống kê đánh giá</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $review_stats['total_reviews']; ?></div>
                    <div class="stat-label">Tổng đánh giá</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $review_stats['approved_reviews']; ?></div>
                    <div class="stat-label">Đã duyệt</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $review_stats['pending_reviews']; ?></div>
                    <div class="stat-label">Chờ duyệt</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $review_stats['avg_rating']; ?></div>
                    <div class="stat-label">Đánh giá TB</div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueData = <?php echo json_encode($revenue_chart); ?>;
        
        const labels = revenueData.map(item => item.date);
        const data = revenueData.map(item => parseFloat(item.revenue));
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: data,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + 'đ';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
