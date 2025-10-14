<?php
/**
 * Trang chi tiết đơn hàng
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/image-helper.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    header('Location: ../auth/dang-nhap.php');
    exit();
}

$order_id = intval($_GET['id'] ?? 0);
$order = null;
$order_items = [];

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
            header('Location: index.php');
            exit();
        }
        
        // Lấy chi tiết đơn hàng
        $stmt = $conn->prepare("
            SELECT oi.*, p.name, p.sku, b.name as brand_name, pc.color_name, pc.color_code
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
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng - Linh2Store</title>
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
                        <p><i class="fas fa-phone"></i> Hotline: 1900 1234</p>
                    </div>
                    <div class="col">
                        <p><i class="fas fa-truck"></i> Miễn phí ship đơn từ 500k</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="header-main">
            <div class="container">
                <div class="header-content">
                    <a href="../" class="logo">Linh2Store</a>
                    
                    <nav class="nav">
                        <a href="../" class="nav-link">Trang chủ</a>
                        <a href="../san-pham/" class="nav-link">Sản phẩm</a>
                        <a href="../thuong-hieu/" class="nav-link">Thương hiệu</a>
                        <a href="../blog/" class="nav-link">Blog</a>
                        <a href="../lien-he/" class="nav-link">Liên hệ</a>
                    </nav>
                    
                    <div class="search-bar">
                        <input type="text" class="search-input" placeholder="Tìm kiếm sản phẩm...">
                        <button class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    
                    <div class="user-actions">
                    <a href="index.php" class="user-icon" title="Tài khoản">
                        <?php if (!empty($user['avatar']) && file_exists("../images/avatars/" . $user['avatar'])): ?>
                            <img src="../images/avatars/<?php echo $user['avatar']; ?>" alt="Avatar" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </a>
                        
                        <a href="../thanh-toan/" class="cart-icon" title="Thanh toán">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count">0</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="../">Trang chủ</a>
            <span>/</span>
            <a href="index.php">Tài khoản</a>
            <span>/</span>
            <a href="index.php">Tổng quan</a>
            <span>/</span>
            <span>Chi tiết</span>
        </div>
    </div>

    <!-- Order Detail Page -->
    <div class="order-detail-page">
        <div class="container">
            <div class="order-header">
                <div class="order-info">
                    <h1>Đơn hàng #<?php echo htmlspecialchars($order['order_number']); ?></h1>
                    <p class="order-date">
                        <i class="fas fa-calendar"></i>
                        Đặt hàng ngày <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                    </p>
                </div>
                <div class="order-status">
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
                </div>
            </div>
            
            <div class="order-content">
                <div class="row">
                    <!-- Thông tin đơn hàng -->
                    <div class="col-8">
                        <div class="order-section">
                            <h2><i class="fas fa-shopping-bag"></i> Sản phẩm đã đặt</h2>
                            
                            <div class="order-items">
                                <?php foreach ($order_items as $item): ?>
                                    <div class="order-item">
                                        <div class="item-image">
                                            <img src="../<?php echo getProductImage($item['product_id']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['product_name'] ?? $item['name'] ?? 'Sản phẩm'); ?>">
                                        </div>
                                        
                                        <div class="item-info">
                                            <h3><?php echo htmlspecialchars($item['product_name'] ?? $item['name'] ?? 'Sản phẩm'); ?></h3>
                                            <p class="item-brand"><?php echo htmlspecialchars($item['brand_name'] ?? ''); ?></p>
                                            
                                            <?php if (!empty($item['color_name'])): ?>
                                                <div class="item-color">
                                                    <span>Màu:</span>
                                                    <div class="color-swatch" style="background-color: <?php echo $item['color_code'] ?? '#ccc'; ?>;"></div>
                                                    <span><?php echo htmlspecialchars($item['color_name']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($item['sku'])): ?>
                                                <p class="item-sku">SKU: <?php echo htmlspecialchars($item['sku']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="item-quantity">
                                            <span>Số lượng: <?php echo $item['quantity']; ?></span>
                                        </div>
                                        
                                        <div class="item-price">
                                            <span class="unit-price"><?php echo number_format($item['product_price'] ?? 0, 0, ',', '.'); ?>đ</span>
                                            <span class="total-price"><?php echo number_format(($item['product_price'] ?? 0) * $item['quantity'], 0, ',', '.'); ?>đ</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Thông tin giao hàng -->
                        <div class="order-section">
                            <h2><i class="fas fa-truck"></i> Thông tin giao hàng</h2>
                            
                            <div class="shipping-info">
                                <div class="info-row">
                                    <span class="label">Người nhận:</span>
                                    <span class="value"><?php echo htmlspecialchars($order['full_name']); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Số điện thoại:</span>
                                    <span class="value"><?php echo htmlspecialchars($order['phone']); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Email:</span>
                                    <span class="value"><?php echo htmlspecialchars($order['email']); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Địa chỉ:</span>
                                    <span class="value"><?php echo htmlspecialchars($order['address'] . ', ' . $order['district'] . ', ' . $order['city']); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Ghi chú -->
                        <?php if ($order['notes']): ?>
                            <div class="order-section">
                                <h2><i class="fas fa-sticky-note"></i> Ghi chú</h2>
                                <p class="order-notes"><?php echo htmlspecialchars($order['notes']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Tóm tắt đơn hàng -->
                    <div class="col-4">
                        <div class="order-summary">
                            <h2><i class="fas fa-receipt"></i> Tóm tắt đơn hàng</h2>
                            
                            <div class="summary-details">
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
                                <?php if ($order['discount_amount'] > 0): ?>
                                    <div class="summary-row">
                                        <span>Giảm giá:</span>
                                        <span style="color: var(--success-color);">-<?php echo number_format($order['discount_amount'], 0, ',', '.'); ?>đ</span>
                                    </div>
                                <?php endif; ?>
                                <div class="summary-row total">
                                    <span>Tổng cộng:</span>
                                    <span><?php echo number_format($order['final_amount'], 0, ',', '.'); ?>đ</span>
                                </div>
                            </div>
                            
                            <div class="payment-info">
                                <h3><i class="fas fa-credit-card"></i> Phương thức thanh toán</h3>
                                <p>
                                    <?php 
                                    $payment_text = [
                                        'cod' => 'Thanh toán khi nhận hàng',
                                        'bank_transfer' => 'Chuyển khoản ngân hàng',
                                        'momo' => 'Ví MoMo',
                                        'vnpay' => 'VNPay'
                                    ];
                                    echo $payment_text[$order['payment_method']] ?? 'Thanh toán khi nhận hàng';
                                    ?>
                                </p>
                                
                                <?php if ($order['payment_method'] === 'bank_transfer'): ?>
                                    <div class="bank-info">
                                        <h4>Thông tin chuyển khoản:</h4>
                                        <p><strong>Ngân hàng:</strong> Vietcombank</p>
                                        <p><strong>STK:</strong> 1234567890</p>
                                        <p><strong>Chủ TK:</strong> Linh2Store</p>
                                        <p><strong>Nội dung:</strong> <?php echo $order['order_number']; ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="order-actions">
                                <?php if ($order['status'] === 'pending'): ?>
                                    <button class="btn btn-outline" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                        <i class="fas fa-times"></i>
                                        Hủy đơn hàng
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($order['status'] === 'delivered'): ?>
                                    <button class="btn btn-primary" onclick="reorder(<?php echo $order['id']; ?>)">
                                        <i class="fas fa-redo"></i>
                                        Đặt lại
                                    </button>
                                <?php endif; ?>
                                
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i>
                                    Quay lại
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    
    <script>
        // Hủy đơn hàng
        function cancelOrder(orderId) {
            if (confirm('Bạn có chắc muốn hủy đơn hàng này?')) {
                fetch('../api/orders.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'cancel',
                        order_id: orderId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Có lỗi xảy ra');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi hủy đơn hàng');
                });
            }
        }
        
        // Đặt lại đơn hàng
        function reorder(orderId) {
            if (confirm('Bạn có muốn đặt lại đơn hàng này?')) {
                fetch('../api/orders.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'reorder',
                        order_id: orderId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '../thanh-toan/';
                    } else {
                        alert(data.message || 'Có lỗi xảy ra');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi đặt lại đơn hàng');
                });
            }
        }
        
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
    
    <style>
        .order-detail-page {
            padding: var(--spacing-xl) 0;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-xl);
            padding-bottom: var(--spacing-lg);
            border-bottom: 2px solid var(--primary-color);
        }
        
        .order-info h1 {
            margin: 0 0 var(--spacing-sm) 0;
            color: var(--text-dark);
        }
        
        .order-info p {
            margin: 0;
            color: var(--text-light);
        }
        
        .order-info p i {
            margin-right: var(--spacing-xs);
            color: var(--accent-color);
        }
        
        .status-badge {
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius-sm);
            font-size: var(--font-size-base);
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
        
        .order-section {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
        }
        
        .order-section h2 {
            margin: 0 0 var(--spacing-lg) 0;
            color: var(--text-dark);
            font-size: var(--font-size-lg);
            padding-bottom: var(--spacing-sm);
            border-bottom: 1px solid var(--bg-light);
        }
        
        .order-section h2 i {
            margin-right: var(--spacing-sm);
            color: var(--accent-color);
        }
        
        .order-items {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-lg);
        }
        
        .order-item {
            display: grid;
            grid-template-columns: 100px 1fr auto auto;
            gap: var(--spacing-lg);
            align-items: center;
            padding: var(--spacing-lg);
            background: var(--bg-light);
            border-radius: var(--radius-md);
        }
        
        .item-image {
            width: 100px;
            height: 100px;
            border-radius: var(--radius-md);
            overflow: hidden;
        }
        
        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .item-info h3 {
            margin: 0 0 var(--spacing-sm) 0;
            color: var(--text-dark);
        }
        
        .item-brand {
            margin: 0 0 var(--spacing-sm) 0;
            color: var(--text-light);
            font-size: var(--font-size-sm);
        }
        
        .item-color {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            font-size: var(--font-size-sm);
            color: var(--text-light);
        }
        
        .color-swatch {
            width: 20px;
            height: 20px;
            border-radius: var(--radius-full);
            border: 1px solid var(--primary-color);
        }
        
        .item-sku {
            margin: 0;
            color: var(--text-light);
            font-size: var(--font-size-sm);
        }
        
        .item-quantity {
            text-align: center;
            color: var(--text-light);
        }
        
        .item-price {
            text-align: right;
        }
        
        .unit-price {
            display: block;
            color: var(--text-light);
            font-size: var(--font-size-sm);
        }
        
        .total-price {
            display: block;
            color: var(--text-dark);
            font-weight: 600;
            font-size: var(--font-size-lg);
        }
        
        .shipping-info {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .info-row .label {
            font-weight: 500;
            color: var(--text-dark);
        }
        
        .info-row .value {
            color: var(--text-light);
        }
        
        .order-notes {
            background: var(--bg-light);
            padding: var(--spacing-md);
            border-radius: var(--radius-sm);
            font-style: italic;
            color: var(--text-light);
        }
        
        .order-summary {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: var(--spacing-xl);
        }
        
        .order-summary h2 {
            margin: 0 0 var(--spacing-lg) 0;
            color: var(--text-dark);
            font-size: var(--font-size-lg);
            padding-bottom: var(--spacing-sm);
            border-bottom: 1px solid var(--bg-light);
        }
        
        .order-summary h2 i {
            margin-right: var(--spacing-sm);
            color: var(--accent-color);
        }
        
        .summary-details {
            margin-bottom: var(--spacing-xl);
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--spacing-sm);
            padding: var(--spacing-sm) 0;
        }
        
        .summary-row.total {
            font-weight: 600;
            font-size: var(--font-size-lg);
            color: var(--text-dark);
            border-top: 1px solid var(--bg-light);
            margin-top: var(--spacing-sm);
        }
        
        .payment-info {
            margin-bottom: var(--spacing-xl);
            padding-bottom: var(--spacing-lg);
            border-bottom: 1px solid var(--bg-light);
        }
        
        .payment-info h3 {
            margin: 0 0 var(--spacing-sm) 0;
            color: var(--text-dark);
            font-size: var(--font-size-base);
        }
        
        .payment-info h3 i {
            margin-right: var(--spacing-xs);
            color: var(--accent-color);
        }
        
        .payment-info p {
            margin: 0 0 var(--spacing-md) 0;
            color: var(--text-light);
        }
        
        .bank-info {
            background: var(--bg-light);
            padding: var(--spacing-md);
            border-radius: var(--radius-sm);
        }
        
        .bank-info h4 {
            margin: 0 0 var(--spacing-sm) 0;
            color: var(--text-dark);
            font-size: var(--font-size-sm);
        }
        
        .bank-info p {
            margin: 0 0 var(--spacing-xs) 0;
            font-size: var(--font-size-sm);
            color: var(--text-light);
        }
        
        .order-actions {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
        }
        
        .btn {
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius-sm);
            text-decoration: none;
            font-weight: 500;
            transition: all var(--transition-fast);
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-xs);
            border: none;
            cursor: pointer;
            font-size: var(--font-size-sm);
            text-align: center;
            justify-content: center;
        }
        
        .btn-primary {
            background: var(--accent-color);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background: #d81b60;
        }
        
        .btn-outline {
            background: var(--white);
            color: var(--text-dark);
            border: 1px solid var(--primary-color);
        }
        
        .btn-outline:hover {
            background: var(--primary-color);
            color: var(--white);
        }
        
        .btn-secondary {
            background: var(--bg-light);
            color: var(--text-dark);
            border: 1px solid var(--primary-color);
        }
        
        .btn-secondary:hover {
            background: var(--primary-color);
            color: var(--white);
        }
        
        @media (max-width: 768px) {
            .col-8, .col-4 {
                flex: 0 0 100%;
            }
            
            .order-summary {
                position: static;
            }
            
            .order-item {
                grid-template-columns: 80px 1fr;
                gap: var(--spacing-md);
            }
            
            .item-quantity,
            .item-price {
                grid-column: 1 / -1;
                justify-self: start;
                margin-top: var(--spacing-sm);
            }
        }
    </style>
</body>
</html>
