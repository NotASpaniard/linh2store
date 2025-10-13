<?php
/**
 * Trang quản lý đơn hàng của user
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

$user = getCurrentUser();
$orders = [];
$page = intval($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;
$total_orders = 0;

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Đếm tổng số đơn hàng
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $total_orders = $stmt->fetch()['total'];
    
    // Lấy đơn hàng với phân trang
    $stmt = $conn->prepare("
        SELECT o.*, COUNT(oi.id) as item_count
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$user['id'], $limit, $offset]);
    $orders = $stmt->fetchAll();
    
} catch (Exception $e) {
    $orders = [];
}

$total_pages = ceil($total_orders / $limit);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng của tôi - Linh2Store</title>
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
                            <i class="fas fa-user"></i>
                        </a>
                        
                        <a href="../gio-hang/" class="cart-icon" title="Giỏ hàng">
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
            <span>Đơn hàng</span>
        </div>
    </div>

    <!-- User Dashboard -->
    <div class="user-dashboard">
        <div class="container">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-3">
                    <div class="user-sidebar">
                        <div class="user-info">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                            <p><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        
                        <nav class="user-nav">
                            <a href="index.php" class="nav-item">
                                <i class="fas fa-tachometer-alt"></i>
                                Tổng quan
                            </a>
                            <a href="don-hang.php" class="nav-item active">
                                <i class="fas fa-shopping-bag"></i>
                                Đơn hàng
                            </a>
                            <a href="yeu-thich.php" class="nav-item">
                                <i class="fas fa-heart"></i>
                                Yêu thích
                            </a>
                            <a href="dia-chi.php" class="nav-item">
                                <i class="fas fa-map-marker-alt"></i>
                                Địa chỉ
                            </a>
                            <a href="thong-tin.php" class="nav-item">
                                <i class="fas fa-user-edit"></i>
                                Thông tin cá nhân
                            </a>
                            <a href="doi-mat-khau.php" class="nav-item">
                                <i class="fas fa-lock"></i>
                                Đổi mật khẩu
                            </a>
                            <a href="../auth/dang-xuat.php" class="nav-item logout">
                                <i class="fas fa-sign-out-alt"></i>
                                Đăng xuất
                            </a>
                        </nav>
                    </div>
                </div>
                
                <!-- Main Content -->
                <div class="col-9">
                    <div class="dashboard-content">
                        <div class="section-header">
                            <h1>Đơn hàng của tôi</h1>
                            <p>Tổng cộng: <?php echo $total_orders; ?> đơn hàng</p>
                        </div>
                        
                        <?php if (!empty($orders)): ?>
                            <div class="orders-list">
                                <?php foreach ($orders as $order): ?>
                                    <div class="order-card">
                                        <div class="order-header">
                                            <div class="order-info">
                                                <h3>Đơn hàng #<?php echo htmlspecialchars($order['order_number']); ?></h3>
                                                <p class="order-date">
                                                    <i class="fas fa-calendar"></i>
                                                    <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                                </p>
                                                <p class="order-items-count">
                                                    <i class="fas fa-box"></i>
                                                    <?php echo $order['item_count']; ?> sản phẩm
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
                                        
                                        <div class="order-details">
                                            <div class="order-address">
                                                <h4><i class="fas fa-map-marker-alt"></i> Địa chỉ giao hàng</h4>
                                                <p><strong><?php echo htmlspecialchars($order['full_name']); ?></strong></p>
                                                <p><?php echo htmlspecialchars($order['phone']); ?></p>
                                                <p><?php echo htmlspecialchars($order['address'] . ', ' . $order['district'] . ', ' . $order['city']); ?></p>
                                            </div>
                                            
                                            <div class="order-payment">
                                                <h4><i class="fas fa-credit-card"></i> Phương thức thanh toán</h4>
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
                                            </div>
                                            
                                            <div class="order-total">
                                                <h4><i class="fas fa-receipt"></i> Tổng tiền</h4>
                                                <div class="total-breakdown">
                                                    <div class="total-row">
                                                        <span>Tạm tính:</span>
                                                        <span><?php echo number_format($order['subtotal'], 0, ',', '.'); ?>đ</span>
                                                    </div>
                                                    <div class="total-row">
                                                        <span>Phí vận chuyển:</span>
                                                        <span>
                                                            <?php if ($order['shipping_fee'] == 0): ?>
                                                                <span style="color: var(--accent-color);">Miễn phí</span>
                                                            <?php else: ?>
                                                                <?php echo number_format($order['shipping_fee'], 0, ',', '.'); ?>đ
                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                    <div class="total-row final">
                                                        <span>Tổng cộng:</span>
                                                        <span><?php echo number_format($order['final_amount'], 0, ',', '.'); ?>đ</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="order-actions">
                                            <a href="chi-tiet-don-hang.php?id=<?php echo $order['id']; ?>" class="btn btn-primary">
                                                <i class="fas fa-eye"></i>
                                                Xem chi tiết
                                            </a>
                                            
                                            <?php if ($order['status'] === 'pending'): ?>
                                                <button class="btn btn-outline" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                                    <i class="fas fa-times"></i>
                                                    Hủy đơn hàng
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($order['status'] === 'delivered'): ?>
                                                <button class="btn btn-success" onclick="reorder(<?php echo $order['id']; ?>)">
                                                    <i class="fas fa-redo"></i>
                                                    Đặt lại
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <div class="pagination">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn">
                                            <i class="fas fa-chevron-left"></i>
                                            Trước
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <a href="?page=<?php echo $i; ?>" class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn">
                                            Sau
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-shopping-bag"></i>
                                <h3>Chưa có đơn hàng nào</h3>
                                <p>Bắt đầu mua sắm để xem đơn hàng ở đây</p>
                                <a href="../san-pham/" class="btn btn-primary">Mua sắm ngay</a>
                            </div>
                        <?php endif; ?>
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
                        window.location.href = '../gio-hang/';
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
        .user-dashboard {
            padding: var(--spacing-xl) 0;
        }
        
        .user-sidebar {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: var(--spacing-xl);
        }
        
        .user-info {
            text-align: center;
            margin-bottom: var(--spacing-xl);
            padding-bottom: var(--spacing-lg);
            border-bottom: 1px solid var(--primary-color);
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            background: var(--primary-color);
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--spacing-md);
            font-size: var(--font-size-2xl);
            color: var(--white);
        }
        
        .user-info h3 {
            margin: 0 0 var(--spacing-sm) 0;
            color: var(--text-dark);
        }
        
        .user-info p {
            margin: 0;
            color: var(--text-light);
            font-size: var(--font-size-sm);
        }
        
        .user-nav {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-xs);
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            padding: var(--spacing-md);
            color: var(--text-light);
            text-decoration: none;
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
            position: relative;
        }
        
        .nav-item:hover {
            background: var(--primary-color);
            color: var(--text-dark);
        }
        
        .nav-item.active {
            background: var(--cta-color);
            color: var(--white);
        }
        
        .nav-item.logout {
            color: var(--error-color);
            margin-top: var(--spacing-lg);
            border-top: 1px solid var(--primary-color);
            padding-top: var(--spacing-md);
        }
        
        .nav-item.logout:hover {
            background: var(--error-color);
            color: var(--white);
        }
        
        .dashboard-content h1 {
            margin-bottom: var(--spacing-lg);
            color: var(--text-dark);
        }
        
        .section-header {
            margin-bottom: var(--spacing-xl);
        }
        
        .section-header p {
            color: var(--text-light);
            margin: 0;
        }
        
        .orders-list {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-lg);
        }
        
        .order-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
            transition: all var(--transition-fast);
        }
        
        .order-card:hover {
            box-shadow: var(--shadow-md);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: var(--spacing-lg);
            padding-bottom: var(--spacing-lg);
            border-bottom: 1px solid var(--bg-light);
        }
        
        .order-info h3 {
            margin: 0 0 var(--spacing-sm) 0;
            color: var(--text-dark);
        }
        
        .order-info p {
            margin: 0 0 var(--spacing-xs) 0;
            color: var(--text-light);
            font-size: var(--font-size-sm);
        }
        
        .order-info p i {
            margin-right: var(--spacing-xs);
            color: var(--accent-color);
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
        
        .order-details {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
        }
        
        .order-address h4,
        .order-payment h4,
        .order-total h4 {
            margin: 0 0 var(--spacing-sm) 0;
            color: var(--text-dark);
            font-size: var(--font-size-base);
        }
        
        .order-address h4 i,
        .order-payment h4 i,
        .order-total h4 i {
            margin-right: var(--spacing-xs);
            color: var(--accent-color);
        }
        
        .order-address p,
        .order-payment p {
            margin: 0 0 var(--spacing-xs) 0;
            color: var(--text-light);
            font-size: var(--font-size-sm);
        }
        
        .total-breakdown {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-xs);
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: var(--font-size-sm);
        }
        
        .total-row.final {
            font-weight: 600;
            color: var(--text-dark);
            border-top: 1px solid var(--bg-light);
            padding-top: var(--spacing-xs);
            margin-top: var(--spacing-xs);
        }
        
        .order-actions {
            display: flex;
            gap: var(--spacing-md);
            justify-content: flex-end;
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
        
        .btn-success {
            background: var(--success-color);
            color: var(--white);
        }
        
        .btn-success:hover {
            background: #2E7D32;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: var(--spacing-sm);
            margin-top: var(--spacing-xl);
        }
        
        .pagination-btn {
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid var(--primary-color);
            border-radius: var(--radius-sm);
            text-decoration: none;
            color: var(--text-dark);
            transition: all var(--transition-fast);
        }
        
        .pagination-btn:hover,
        .pagination-btn.active {
            background: var(--accent-color);
            color: var(--white);
            border-color: var(--accent-color);
        }
        
        .empty-state {
            text-align: center;
            padding: var(--spacing-3xl);
            color: var(--text-light);
        }
        
        .empty-state i {
            font-size: var(--font-size-3xl);
            color: var(--primary-color);
            margin-bottom: var(--spacing-lg);
        }
        
        .empty-state h3 {
            margin-bottom: var(--spacing-md);
            color: var(--text-dark);
        }
        
        @media (max-width: 768px) {
            .col-3, .col-9 {
                flex: 0 0 100%;
            }
            
            .user-sidebar {
                position: static;
                margin-bottom: var(--spacing-xl);
            }
            
            .order-details {
                grid-template-columns: 1fr;
            }
            
            .order-actions {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>
