<?php
require_once '../config/database.php';
require_once '../config/auth-middleware.php';

// Kiểm tra quyền admin
$user = AuthMiddleware::requireAdmin();

$db = new Database();
$conn = $db->getConnection();

// Xử lý cập nhật trạng thái đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    try {
        $stmt = $conn->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        
        $success_message = "Đã cập nhật trạng thái đơn hàng thành công!";
    } catch (Exception $e) {
        $error_message = "Có lỗi xảy ra: " . $e->getMessage();
    }
}

// Lấy danh sách đơn hàng
$stmt = $conn->prepare("
    SELECT 
        o.*,
        u.username,
        u.full_name,
        u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 50
");
$stmt->execute();
$orders = $stmt->fetchAll();

$status_labels = [
    'pending' => 'Chờ xử lý',
    'processing' => 'Đang xử lý', 
    'shipped' => 'Đang giao hàng',
    'delivered' => 'Đã giao hàng',
    'cancelled' => 'Đã hủy'
];

$status_colors = [
    'pending' => '#ffc107',
    'processing' => '#17a2b8',
    'shipped' => '#007bff', 
    'delivered' => '#28a745',
    'cancelled' => '#dc3545'
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật trạng thái đơn hàng - Admin</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .order-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .order-info {
            flex: 1;
        }
        
        .order-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
            color: white;
        }
        
        .status-select {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .btn-update {
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
        }
        
        .btn-update:hover {
            background: #218838;
        }
        
        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-weight: bold;
            color: #666;
            font-size: 0.9em;
        }
        
        .detail-value {
            color: #333;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1>Cập nhật trạng thái đơn hàng</h1>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="orders-list">
            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <p>Không có đơn hàng nào</p>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-info">
                                <h3>Đơn hàng #<?php echo $order['order_number']; ?></h3>
                                <p>Khách hàng: <?php echo htmlspecialchars($order['full_name'] ?: $order['username']); ?></p>
                                <p>Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                            </div>
                            
                            <div class="order-actions">
                                <span class="status-badge" style="background: <?php echo $status_colors[$order['status']]; ?>">
                                    <?php echo $status_labels[$order['status']]; ?>
                                </span>
                                
                                <form method="POST" style="display: flex; gap: 10px; align-items: center;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    
                                    <select name="status" class="status-select">
                                        <?php foreach ($status_labels as $key => $label): ?>
                                            <option value="<?php echo $key; ?>" <?php echo $order['status'] === $key ? 'selected' : ''; ?>>
                                                <?php echo $label; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    
                                    <button type="submit" class="btn-update">
                                        <i class="fas fa-save"></i> Cập nhật
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="order-details">
                            <div class="detail-item">
                                <span class="detail-label">Tổng tiền</span>
                                <span class="detail-value"><?php echo number_format($order['final_amount'], 0, ',', '.'); ?>đ</span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Phương thức thanh toán</span>
                                <span class="detail-value"><?php echo ucfirst($order['payment_method']); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Email</span>
                                <span class="detail-value"><?php echo htmlspecialchars($order['email']); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Số điện thoại</span>
                                <span class="detail-value"><?php echo htmlspecialchars($order['phone']); ?></span>
                            </div>
                        </div>
                        
                        <?php if ($order['full_name'] || $order['address']): ?>
                        <div class="order-details">
                            <div class="detail-item">
                                <span class="detail-label">Tên người nhận</span>
                                <span class="detail-value"><?php echo htmlspecialchars($order['full_name']); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Địa chỉ</span>
                                <span class="detail-value"><?php echo htmlspecialchars($order['address']); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Thành phố</span>
                                <span class="detail-value"><?php echo htmlspecialchars($order['city']); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Quận/Huyện</span>
                                <span class="detail-value"><?php echo htmlspecialchars($order['district']); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="orders.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách đơn hàng
            </a>
        </div>
    </div>
</body>
</html>
