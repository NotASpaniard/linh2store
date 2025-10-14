<?php
/**
 * Trang thành công thanh toán
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/session.php';
require_once '../config/database.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    header('Location: ../auth/dang-nhap.php');
    exit();
}

$order_id = intval($_GET['order_id'] ?? 0);
$order = null;

if ($order_id) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Lấy thông tin đơn hàng
        $stmt = $conn->prepare("
            SELECT o.*, u.username, u.email as user_email
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE o.id = ? AND o.user_id = ?
        ");
        $stmt->execute([$order_id, $_SESSION['user_id']]);
        $order = $stmt->fetch();
        
        if (!$order) {
            header('Location: ../user/');
            exit();
        }
        
        // Lấy chi tiết đơn hàng
        $stmt = $conn->prepare("
            SELECT oi.*, p.sku, b.name as brand_name, pc.color_name, pc.color_code
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN product_colors pc ON oi.product_color_id = pc.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $order_items = $stmt->fetchAll();
        
    } catch (Exception $e) {
        $order = null;
        $order_items = [];
    }
}

if (!$order) {
    header('Location: ../user/');
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng thành công - Linh2Store</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Dancing+Script:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .success-page {
            padding: var(--spacing-xl) 0;
            background: var(--bg-light);
            min-height: 100vh;
        }
        
        .success-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 var(--spacing-lg);
        }
        
        .success-header {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: var(--accent-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--spacing-lg);
        }
        
        .success-icon i {
            font-size: 40px;
            color: var(--white);
        }
        
        .success-header h1 {
            color: var(--text-dark);
            font-size: var(--font-size-xxl);
            margin-bottom: var(--spacing-sm);
        }
        
        .success-header p {
            color: var(--text-light);
            font-size: var(--font-size-lg);
        }
        
        .order-info {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
        }
        
        .order-info h2 {
            color: var(--text-dark);
            font-size: var(--font-size-xl);
            margin-bottom: var(--spacing-lg);
            padding-bottom: var(--spacing-sm);
            border-bottom: 2px solid var(--primary-color);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            color: var(--text-light);
            font-size: var(--font-size-sm);
            margin-bottom: var(--spacing-xs);
        }
        
        .info-value {
            color: var(--text-dark);
            font-weight: 500;
        }
        
        .order-items {
            margin-bottom: var(--spacing-lg);
        }
        
        .order-item {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            padding: var(--spacing-md);
            border-bottom: 1px solid var(--bg-light);
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-item-info {
            flex: 1;
        }
        
        .order-item-name {
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: var(--spacing-xs);
        }
        
        .order-item-details {
            font-size: var(--font-size-sm);
            color: var(--text-light);
        }
        
        .order-item-price {
            font-weight: 600;
            color: var(--accent-color);
        }
        
        .order-summary {
            background: var(--bg-light);
            border-radius: var(--radius-md);
            padding: var(--spacing-lg);
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--spacing-sm);
        }
        
        .summary-row.total {
            font-weight: 600;
            font-size: var(--font-size-lg);
            color: var(--text-dark);
            border-top: 1px solid var(--primary-color);
            padding-top: var(--spacing-sm);
            margin-top: var(--spacing-sm);
        }
        
        .action-buttons {
            display: flex;
            gap: var(--spacing-md);
            justify-content: center;
            margin-top: var(--spacing-xl);
        }
        
        .btn {
            padding: var(--spacing-md) var(--spacing-lg);
            border-radius: var(--radius-md);
            text-decoration: none;
            font-weight: 500;
            transition: all var(--transition-fast);
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
        }
        
        .btn-primary {
            background: var(--accent-color);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background: #d81b60;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: var(--white);
            color: var(--text-dark);
            border: 2px solid var(--primary-color);
        }
        
        .btn-secondary:hover {
            background: var(--primary-color);
            color: var(--white);
        }
        
        .payment-info {
            background: var(--primary-color);
            color: var(--white);
            border-radius: var(--radius-md);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
        }
        
        .payment-info h3 {
            margin: 0 0 var(--spacing-md) 0;
            font-size: var(--font-size-lg);
        }
        
        .payment-info p {
            margin: 0;
            opacity: 0.9;
        }
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="../index.php" class="logo">Linh2Store</a>
                <nav class="nav">
                    <a href="../index.php" class="nav-link">Trang chủ</a>
                    <a href="../san-pham/" class="nav-link">Sản phẩm</a>
                    <a href="../thuong-hieu/" class="nav-link">Thương hiệu</a>
                    <a href="../blog/" class="nav-link">Blog</a>
                    <a href="../lien-he/" class="nav-link">Liên hệ</a>
                </nav>
                <div class="header-actions">
                    <a href="../" class="cart-btn">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </a>
                    <a href="../user/" class="user-btn">
                        <i class="fas fa-user"></i>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Success Page -->
    <div class="success-page">
        <div class="success-container">
            <div class="success-header">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h1>Đặt hàng thành công!</h1>
                <p>Cảm ơn bạn đã mua sắm tại Linh2Store</p>
            </div>
            
            <div class="order-info">
                <h2><i class="fas fa-receipt"></i> Thông tin đơn hàng</h2>
                
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Mã đơn hàng</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['order_number']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ngày đặt hàng</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Trạng thái</span>
                        <span class="info-value" style="color: var(--accent-color); font-weight: 600;">
                            <?php 
                            $status_text = [
                                'pending' => 'Chờ xử lý',
                                'processing' => 'Đang xử lý',
                                'shipped' => 'Đang giao hàng',
                                'delivered' => 'Đã giao hàng',
                                'cancelled' => 'Đã hủy'
                            ];
                            echo $status_text[$order['status']] ?? 'Chờ xử lý';
                            ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Phương thức thanh toán</span>
                        <span class="info-value">
                            <?php 
                            $payment_text = [
                                'cod' => 'Thanh toán khi nhận hàng',
                                'bank_transfer' => 'Chuyển khoản ngân hàng',
                                'momo' => 'Ví MoMo',
                                'vnpay' => 'VNPay'
                            ];
                            echo $payment_text[$order['payment_method']] ?? 'Thanh toán khi nhận hàng';
                            ?>
                        </span>
                    </div>
                </div>
                
                <!-- Thông tin giao hàng -->
                <div style="margin-top: var(--spacing-lg);">
                    <h3 style="color: var(--text-dark); margin-bottom: var(--spacing-md);">Thông tin giao hàng</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Người nhận</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['full_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Số điện thoại</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['phone']); ?></span>
                        </div>
                        <div class="info-item" style="grid-column: 1 / -1;">
                            <span class="info-label">Địa chỉ</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['address'] . ', ' . $order['district'] . ', ' . $order['city']); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Sản phẩm đã đặt -->
                <div class="order-items">
                    <h3 style="color: var(--text-dark); margin-bottom: var(--spacing-md);">Sản phẩm đã đặt</h3>
                    <?php foreach ($order_items as $item): ?>
                        <div class="order-item">
                            <div class="order-item-info">
                                <div class="order-item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <div class="order-item-details">
                                    <?php if ($item['brand_name']): ?>
                                        <?php echo htmlspecialchars($item['brand_name']); ?>
                                    <?php endif; ?>
                                    <?php if ($item['color_name']): ?>
                                        - <?php echo htmlspecialchars($item['color_name']); ?>
                                    <?php endif; ?>
                                    <?php if ($item['sku']): ?>
                                        - SKU: <?php echo htmlspecialchars($item['sku']); ?>
                                    <?php endif; ?>
                                    <br>
                                    Số lượng: <?php echo $item['quantity']; ?>
                                </div>
                            </div>
                            <div class="order-item-price">
                                <?php echo number_format($item['total_price'], 0, ',', '.'); ?>đ
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Tổng tiền -->
                <div class="order-summary">
                    <div class="summary-row">
                        <span>Tạm tính:</span>
                        <span><?php echo number_format($order['subtotal'], 0, ',', '.'); ?>đ</span>
                    </div>
                    <div class="summary-row">
                        <span>Phí vận chuyển:</span>
                        <span>
                            <?php if ($order['shipping_fee'] == 0): ?>
                                <span style="color: var(--accent-color);">Miễn phí</span>
                            <?php else: ?>
                                <?php echo number_format($order['shipping_fee'], 0, ',', '.'); ?>đ
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="summary-row total">
                        <span>Tổng cộng:</span>
                        <span><?php echo number_format($order['final_amount'], 0, ',', '.'); ?>đ</span>
                    </div>
                </div>
                
                <?php if ($order['notes']): ?>
                    <div style="margin-top: var(--spacing-lg);">
                        <h3 style="color: var(--text-dark); margin-bottom: var(--spacing-sm);">Ghi chú</h3>
                        <p style="color: var(--text-light); font-style: italic;"><?php echo htmlspecialchars($order['notes']); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($order['payment_method'] === 'bank_transfer'): ?>
                <div class="payment-info">
                    <h3><i class="fas fa-university"></i> Thông tin chuyển khoản</h3>
                    <p>Vui lòng chuyển khoản số tiền <strong><?php echo number_format($order['final_amount'], 0, ',', '.'); ?>đ</strong> đến tài khoản:</p>
                    <p><strong>Ngân hàng:</strong> Vietcombank<br>
                    <strong>STK:</strong> 1234567890<br>
                    <strong>Chủ TK:</strong> Linh2Store<br>
                    <strong>Nội dung:</strong> <?php echo $order['order_number']; ?></p>
                </div>
            <?php endif; ?>
            
            <div class="action-buttons">
                <a href="../user/" class="btn btn-primary">
                    <i class="fas fa-user"></i>
                    Xem đơn hàng của tôi
                </a>
                <a href="../san-pham/" class="btn btn-secondary">
                    <i class="fas fa-shopping-bag"></i>
                    Tiếp tục mua sắm
                </a>
            </div>
        </div>
    </div>

    <script>
        // Update cart count
        function updateCartCount() {
            fetch('../api/cart.php?action=count')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector('.cart-count').textContent = data.count;
                    }
                });
        }
        
        // Load cart count on page load
        document.addEventListener('DOMContentLoaded', updateCartCount);
    </script>
</body>
</html>
