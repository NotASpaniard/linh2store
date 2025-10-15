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

// Xử lý export
if (isset($_GET['export'])) {
    $report_type = $_GET['export'];
    $start_date = $_GET['start_date'] ?? date('Y-m-01');
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    
    switch ($report_type) {
        case 'sales':
            exportSalesReport($conn, $start_date, $end_date);
            break;
        case 'products':
            exportProductsReport($conn, $start_date, $end_date);
            break;
        case 'customers':
            exportCustomersReport($conn, $start_date, $end_date);
            break;
    }
    exit;
}

// Lấy dữ liệu cho báo cáo
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Sales Report
$sales_report_sql = "
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as total_orders,
        SUM(final_amount) as total_revenue,
        AVG(final_amount) as avg_order_value,
        COUNT(DISTINCT user_id) as unique_customers
    FROM orders 
    WHERE created_at >= ? AND created_at <= ?
    GROUP BY DATE(created_at)
    ORDER BY date DESC
";
$stmt = $conn->prepare($sales_report_sql);
$stmt->execute([$start_date, $end_date]);
$sales_report = $stmt->fetchAll();

// Products Report
$products_report_sql = "
    SELECT 
        p.name,
        b.name as brand,
        p.price,
        SUM(oi.quantity) as total_sold,
        SUM(oi.total_price) as total_revenue,
        COUNT(DISTINCT oi.order_id) as order_count
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN brands b ON p.brand_id = b.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.created_at >= ? AND o.created_at <= ?
    GROUP BY oi.product_id
    ORDER BY total_sold DESC
";
$stmt = $conn->prepare($products_report_sql);
$stmt->execute([$start_date, $end_date]);
$products_report = $stmt->fetchAll();

// Customers Report
$customers_report_sql = "
    SELECT 
        u.username,
        u.full_name,
        u.email,
        COUNT(o.id) as total_orders,
        SUM(o.final_amount) as total_spent,
        MAX(o.created_at) as last_order_date
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id AND o.created_at >= ? AND o.created_at <= ?
    WHERE u.role = 'user'
    GROUP BY u.id
    HAVING total_orders > 0
    ORDER BY total_spent DESC
";
$stmt = $conn->prepare($customers_report_sql);
$stmt->execute([$start_date, $end_date]);
$customers_report = $stmt->fetchAll();

function exportSalesReport($conn, $start_date, $end_date) {
    $sql = "
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as total_orders,
            SUM(final_amount) as total_revenue,
            AVG(final_amount) as avg_order_value
        FROM orders 
        WHERE created_at >= ? AND created_at <= ?
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$start_date, $end_date]);
    $data = $stmt->fetchAll();
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="sales_report_' . $start_date . '_to_' . $end_date . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Ngày', 'Tổng đơn hàng', 'Tổng doanh thu', 'Giá trị đơn hàng TB']);
    
    foreach ($data as $row) {
        fputcsv($output, [
            $row['date'],
            $row['total_orders'],
            number_format($row['total_revenue'], 0, ',', '.'),
            number_format($row['avg_order_value'], 0, ',', '.')
        ]);
    }
    
    fclose($output);
}

function exportProductsReport($conn, $start_date, $end_date) {
    $sql = "
    SELECT 
        p.name,
        b.name as brand,
        p.price,
        SUM(oi.quantity) as total_sold,
        SUM(oi.total_price) as total_revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN brands b ON p.brand_id = b.id
    JOIN orders o ON oi.order_id = o.id
        WHERE o.created_at >= ? AND o.created_at <= ?
        GROUP BY oi.product_id
        ORDER BY total_sold DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$start_date, $end_date]);
    $data = $stmt->fetchAll();
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="products_report_' . $start_date . '_to_' . $end_date . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Tên sản phẩm', 'Thương hiệu', 'Giá', 'Số lượng bán', 'Doanh thu']);
    
    foreach ($data as $row) {
        fputcsv($output, [
            $row['name'],
            $row['brand'],
            number_format($row['price'], 0, ',', '.'),
            $row['total_sold'],
            number_format($row['total_revenue'], 0, ',', '.')
        ]);
    }
    
    fclose($output);
}

function exportCustomersReport($conn, $start_date, $end_date) {
    $sql = "
        SELECT 
            u.username,
            u.full_name,
            u.email,
            COUNT(o.id) as total_orders,
            SUM(o.final_amount) as total_spent,
            MAX(o.created_at) as last_order_date
        FROM users u
        LEFT JOIN orders o ON u.id = o.user_id AND o.created_at >= ? AND o.created_at <= ?
        WHERE u.role = 'user'
        GROUP BY u.id
        HAVING total_orders > 0
        ORDER BY total_spent DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$start_date, $end_date]);
    $data = $stmt->fetchAll();
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="customers_report_' . $start_date . '_to_' . $end_date . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Username', 'Họ tên', 'Email', 'Tổng đơn hàng', 'Tổng chi tiêu', 'Đơn hàng cuối']);
    
    foreach ($data as $row) {
        fputcsv($output, [
            $row['username'],
            $row['full_name'],
            $row['email'],
            $row['total_orders'],
            number_format($row['total_spent'], 0, ',', '.'),
            $row['last_order_date']
        ]);
    }
    
    fclose($output);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo - Admin</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        .reports-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .report-filters {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: end;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .report-section {
            background: white;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .report-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .report-content {
            padding: 0;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th,
        .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .data-table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        .data-table tr:hover {
            background: #f8f9fa;
        }
        
        .export-btn {
            background: #28a745;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9em;
        }
        
        .export-btn:hover {
            background: #218838;
        }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .summary-number {
            font-size: 2em;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .summary-label {
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="reports-container">
        <h1>Báo cáo & Phân tích</h1>
        
        <!-- Filters -->
        <div class="report-filters">
            <h3>Bộ lọc báo cáo</h3>
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label for="start_date">Từ ngày:</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="end_date">Đến ngày:</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Cập nhật báo cáo</button>
                </div>
            </form>
        </div>
        
        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-number"><?php echo count($sales_report); ?></div>
                <div class="summary-label">Ngày có đơn hàng</div>
            </div>
            <div class="summary-card">
                <div class="summary-number"><?php echo count($products_report); ?></div>
                <div class="summary-label">Sản phẩm bán được</div>
            </div>
            <div class="summary-card">
                <div class="summary-number"><?php echo count($customers_report); ?></div>
                <div class="summary-label">Khách hàng mua hàng</div>
            </div>
        </div>
        
        <!-- Sales Report -->
        <div class="report-section">
            <div class="report-header">
                <h3>Báo cáo doanh số</h3>
                <a href="?export=sales&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="export-btn">
                    <i class="fas fa-download"></i> Export CSV
                </a>
            </div>
            <div class="report-content">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Ngày</th>
                            <th>Tổng đơn hàng</th>
                            <th>Tổng doanh thu</th>
                            <th>Giá trị đơn hàng TB</th>
                            <th>Khách hàng duy nhất</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sales_report)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px;">Không có dữ liệu</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sales_report as $row): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($row['date'])); ?></td>
                                    <td><?php echo $row['total_orders']; ?></td>
                                    <td><?php echo number_format($row['total_revenue'], 0, ',', '.'); ?>đ</td>
                                    <td><?php echo number_format($row['avg_order_value'], 0, ',', '.'); ?>đ</td>
                                    <td><?php echo $row['unique_customers']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Products Report -->
        <div class="report-section">
            <div class="report-header">
                <h3>Báo cáo sản phẩm</h3>
                <a href="?export=products&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="export-btn">
                    <i class="fas fa-download"></i> Export CSV
                </a>
            </div>
            <div class="report-content">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tên sản phẩm</th>
                            <th>Thương hiệu</th>
                            <th>Giá</th>
                            <th>Số lượng bán</th>
                            <th>Doanh thu</th>
                            <th>Số đơn hàng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products_report)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px;">Không có dữ liệu</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products_report as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['brand']); ?></td>
                                    <td><?php echo number_format($row['price'], 0, ',', '.'); ?>đ</td>
                                    <td><?php echo $row['total_sold']; ?></td>
                                    <td><?php echo number_format($row['total_revenue'], 0, ',', '.'); ?>đ</td>
                                    <td><?php echo $row['order_count']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Customers Report -->
        <div class="report-section">
            <div class="report-header">
                <h3>Báo cáo khách hàng</h3>
                <a href="?export=customers&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="export-btn">
                    <i class="fas fa-download"></i> Export CSV
                </a>
            </div>
            <div class="report-content">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Tổng đơn hàng</th>
                            <th>Tổng chi tiêu</th>
                            <th>Đơn hàng cuối</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customers_report)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px;">Không có dữ liệu</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($customers_report as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo $row['total_orders']; ?></td>
                                    <td><?php echo number_format($row['total_spent'], 0, ',', '.'); ?>đ</td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['last_order_date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>