<?php
/**
 * User Dashboard
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/session.php';
require_once '../config/database.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    header('Location: ../auth/dang-nhap.php');
    exit();
}

$user = getCurrentUser();
$recent_orders = [];
$wishlist_count = 0;
$cart_count = 0;

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Lấy đơn hàng gần đây
    $stmt = $conn->prepare("
        SELECT o.*, COUNT(oi.id) as item_count
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user['id']]);
    $recent_orders = $stmt->fetchAll();
    
    // Đếm wishlist
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $wishlist_count = $stmt->fetch()['count'];
    
    // Đếm giỏ hàng
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $cart_count = $stmt->fetch()['count'];
    
} catch (Exception $e) {
    $recent_orders = [];
    $wishlist_count = 0;
    $cart_count = 0;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài khoản của tôi - Linh2Store</title>
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
                        <a href="index.php" class="user-icon active" title="Tài khoản">
                            <i class="fas fa-user"></i>
                        </a>
                        
                        <a href="../gio-hang/" class="cart-icon" title="Giỏ hàng">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
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
            <span>Tài khoản</span>
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
                            <a href="index.php" class="nav-item active">
                                <i class="fas fa-tachometer-alt"></i>
                                Tổng quan
                            </a>
                            <a href="don-hang.php" class="nav-item">
                                <i class="fas fa-shopping-bag"></i>
                                Đơn hàng
                            </a>
                            <a href="yeu-thich.php" class="nav-item">
                                <i class="fas fa-heart"></i>
                                Yêu thích
                                <span class="badge"><?php echo $wishlist_count; ?></span>
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
                        <h1>Chào mừng trở lại, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
                        
                        <!-- Stats Cards -->
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo count($recent_orders); ?></h3>
                                    <p>Đơn hàng</p>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $wishlist_count; ?></h3>
                                    <p>Sản phẩm yêu thích</p>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $cart_count; ?></h3>
                                    <p>Sản phẩm trong giỏ</p>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="stat-info">
                                    <h3>4.8</h3>
                                    <p>Đánh giá trung bình</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recent Orders -->
                        <div class="dashboard-section">
                            <div class="section-header">
                                <h2>Đơn hàng gần đây</h2>
                                <a href="don-hang.php" class="btn btn-outline">Xem tất cả</a>
                            </div>
                            
                            <?php if (!empty($recent_orders)): ?>
                                <div class="orders-list">
                                    <?php foreach ($recent_orders as $order): ?>
                                        <div class="order-item">
                                            <div class="order-info">
                                                <h4>Đơn hàng #<?php echo $order['order_number']; ?></h4>
                                                <p><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                                                <p><?php echo $order['item_count']; ?> sản phẩm</p>
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
                                            <div class="order-total">
                                                <span><?php echo number_format($order['final_amount']); ?>đ</span>
                                            </div>
                                            <div class="order-actions">
                                                <a href="chi-tiet-don-hang.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">
                                                    Chi tiết
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-shopping-bag"></i>
                                    <h3>Chưa có đơn hàng nào</h3>
                                    <p>Bắt đầu mua sắm để xem đơn hàng ở đây</p>
                                    <a href="../san-pham/" class="btn btn-primary">Mua sắm ngay</a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="dashboard-section">
                            <h2>Thao tác nhanh</h2>
                            <div class="quick-actions">
                                <a href="../san-pham/" class="action-card">
                                    <i class="fas fa-shopping-bag"></i>
                                    <h4>Mua sắm</h4>
                                    <p>Khám phá sản phẩm mới</p>
                                </a>
                                
                                <a href="yeu-thich.php" class="action-card">
                                    <i class="fas fa-heart"></i>
                                    <h4>Yêu thích</h4>
                                    <p>Xem sản phẩm đã lưu</p>
                                </a>
                                
                                <a href="../gio-hang/" class="action-card">
                                    <i class="fas fa-shopping-cart"></i>
                                    <h4>Giỏ hàng</h4>
                                    <p>Hoàn tất đơn hàng</p>
                                </a>
                                
                                <a href="thong-tin.php" class="action-card">
                                    <i class="fas fa-user-edit"></i>
                                    <h4>Cập nhật thông tin</h4>
                                    <p>Chỉnh sửa thông tin cá nhân</p>
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
        
        .badge {
            background: var(--cta-color);
            color: var(--white);
            font-size: var(--font-size-xs);
            padding: 2px 6px;
            border-radius: var(--radius-full);
            margin-left: auto;
        }
        
        .dashboard-content h1 {
            margin-bottom: var(--spacing-xl);
            color: var(--text-dark);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
            background: var(--primary-color);
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--font-size-xl);
            color: var(--white);
        }
        
        .stat-info h3 {
            margin: 0 0 var(--spacing-xs) 0;
            font-size: var(--font-size-2xl);
            color: var(--text-dark);
        }
        
        .stat-info p {
            margin: 0;
            color: var(--text-light);
            font-size: var(--font-size-sm);
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
        
        .orders-list {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
        }
        
        .order-item {
            display: grid;
            grid-template-columns: 1fr auto auto auto;
            gap: var(--spacing-lg);
            align-items: center;
            padding: var(--spacing-lg);
            background: var(--bg-light);
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
        }
        
        .order-item:hover {
            background: var(--primary-color);
        }
        
        .order-info h4 {
            margin: 0 0 var(--spacing-xs) 0;
            color: var(--text-dark);
        }
        
        .order-info p {
            margin: 0;
            color: var(--text-light);
            font-size: var(--font-size-sm);
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
        
        .order-total {
            font-weight: 600;
            color: var(--text-dark);
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
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-lg);
        }
        
        .action-card {
            background: var(--primary-color);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            text-align: center;
            text-decoration: none;
            color: var(--text-dark);
            transition: all var(--transition-fast);
        }
        
        .action-card:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .action-card i {
            font-size: var(--font-size-2xl);
            color: var(--cta-color);
            margin-bottom: var(--spacing-md);
        }
        
        .action-card h4 {
            margin: 0 0 var(--spacing-sm) 0;
            color: var(--text-dark);
        }
        
        .action-card p {
            margin: 0;
            color: var(--text-light);
            font-size: var(--font-size-sm);
        }
        
        @media (max-width: 768px) {
            .col-3, .col-9 {
                flex: 0 0 100%;
            }
            
            .user-sidebar {
                position: static;
                margin-bottom: var(--spacing-xl);
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .order-item {
                grid-template-columns: 1fr;
                gap: var(--spacing-md);
            }
            
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid,
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
