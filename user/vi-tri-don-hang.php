<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/dang-nhap.php');
    exit();
}

$user = $_SESSION['user'];

// Dữ liệu hardcode về vị trí đơn hàng trong Việt Nam
$order_locations = [
    [
        'order_id' => 1,
        'order_number' => 'L2S202510139744',
        'status' => 'shipping',
        'current_location' => 'Hà Nội',
        'destination' => 'TP. Hồ Chí Minh',
        'progress' => 65,
        'estimated_delivery' => '2025-10-15 14:00',
        'tracking_steps' => [
            ['step' => 'Đơn hàng đã được xác nhận', 'time' => '2025-10-13 20:12', 'status' => 'completed'],
            ['step' => 'Đơn hàng đang được đóng gói', 'time' => '2025-10-13 21:30', 'status' => 'completed'],
            ['step' => 'Đơn hàng đã rời kho Hà Nội', 'time' => '2025-10-14 08:00', 'status' => 'completed'],
            ['step' => 'Đơn hàng đang vận chuyển', 'time' => '2025-10-14 10:30', 'status' => 'current'],
            ['step' => 'Đơn hàng sẽ được giao tại TP. Hồ Chí Minh', 'time' => '2025-10-15 14:00', 'status' => 'pending']
        ],
        'carrier' => 'Viettel Post',
        'tracking_number' => 'VT123456789'
    ],
    [
        'order_id' => 2,
        'order_number' => 'L2S202510133482',
        'status' => 'delivered',
        'current_location' => 'TP. Hồ Chí Minh',
        'destination' => 'Hà Nội',
        'progress' => 100,
        'estimated_delivery' => '2025-10-13 16:30',
        'tracking_steps' => [
            ['step' => 'Đơn hàng đã được xác nhận', 'time' => '2025-10-13 20:42', 'status' => 'completed'],
            ['step' => 'Đơn hàng đang được đóng gói', 'time' => '2025-10-13 21:15', 'status' => 'completed'],
            ['step' => 'Đơn hàng đã rời kho TP. Hồ Chí Minh', 'time' => '2025-10-13 22:00', 'status' => 'completed'],
            ['step' => 'Đơn hàng đang vận chuyển', 'time' => '2025-10-14 06:00', 'status' => 'completed'],
            ['step' => 'Đơn hàng đã được giao thành công', 'time' => '2025-10-13 16:30', 'status' => 'completed']
        ],
        'carrier' => 'Vietnam Post',
        'tracking_number' => 'VN987654321'
    ],
    [
        'order_id' => 3,
        'order_number' => 'L2S202510149292',
        'status' => 'pending',
        'current_location' => 'Đà Nẵng',
        'destination' => 'Cần Thơ',
        'progress' => 25,
        'estimated_delivery' => '2025-10-16 10:00',
        'tracking_steps' => [
            ['step' => 'Đơn hàng đã được xác nhận', 'time' => '2025-10-14 10:42', 'status' => 'completed'],
            ['step' => 'Đơn hàng đang được đóng gói', 'time' => '2025-10-14 11:00', 'status' => 'current'],
            ['step' => 'Đơn hàng sẽ rời kho Đà Nẵng', 'time' => '2025-10-15 08:00', 'status' => 'pending'],
            ['step' => 'Đơn hàng đang vận chuyển', 'time' => '2025-10-15 12:00', 'status' => 'pending'],
            ['step' => 'Đơn hàng sẽ được giao tại Cần Thơ', 'time' => '2025-10-16 10:00', 'status' => 'pending']
        ],
        'carrier' => 'Giao Hàng Nhanh',
        'tracking_number' => 'GHN456789123'
    ]
];

// Lọc đơn hàng theo user
$user_orders = array_filter($order_locations, function($order) use ($user) {
    return $order['order_id'] <= 3; // Giả sử user có 3 đơn hàng
});
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vị trí đơn hàng - Linh2Store</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .tracking-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--spacing-xl);
        }
        
        .tracking-header {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }
        
        .tracking-header h1 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-md);
        }
        
        .tracking-header p {
            color: var(--text-light);
            font-size: var(--font-size-lg);
        }
        
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: var(--spacing-xl);
        }
        
        .order-tracking-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: all var(--transition-fast);
        }
        
        .order-tracking-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }
        
        .order-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--white);
            padding: var(--spacing-lg);
        }
        
        .order-number {
            font-size: var(--font-size-lg);
            font-weight: 600;
            margin-bottom: var(--spacing-xs);
        }
        
        .order-status {
            font-size: var(--font-size-sm);
            opacity: 0.9;
        }
        
        .tracking-info {
            padding: var(--spacing-lg);
        }
        
        .location-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-lg);
            padding: var(--spacing-md);
            background: #f8f9fa;
            border-radius: var(--radius-md);
        }
        
        .location-item {
            text-align: center;
            flex: 1;
        }
        
        .location-item h4 {
            margin: 0 0 var(--spacing-xs) 0;
            color: var(--text-dark);
            font-size: var(--font-size-base);
        }
        
        .location-item p {
            margin: 0;
            color: var(--text-light);
            font-size: var(--font-size-sm);
        }
        
        .progress-container {
            margin-bottom: var(--spacing-lg);
        }
        
        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--spacing-sm);
        }
        
        .progress-label span {
            font-size: var(--font-size-sm);
            color: var(--text-light);
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: var(--radius-sm);
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
            border-radius: var(--radius-sm);
            transition: width 0.3s ease;
        }
        
        .tracking-steps {
            margin-bottom: var(--spacing-lg);
        }
        
        .tracking-steps h4 {
            margin: 0 0 var(--spacing-md) 0;
            color: var(--text-dark);
            font-size: var(--font-size-base);
        }
        
        .step-item {
            display: flex;
            align-items: flex-start;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-md);
            padding: var(--spacing-sm);
            border-radius: var(--radius-sm);
            transition: background-color var(--transition-fast);
        }
        
        .step-item:hover {
            background: #f8f9fa;
        }
        
        .step-icon {
            width: 24px;
            height: 24px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--font-size-xs);
            flex-shrink: 0;
            margin-top: 2px;
        }
        
        .step-icon.completed {
            background: #28a745;
            color: var(--white);
        }
        
        .step-icon.current {
            background: #007bff;
            color: var(--white);
        }
        
        .step-icon.pending {
            background: #e9ecef;
            color: var(--text-light);
        }
        
        .step-content {
            flex: 1;
        }
        
        .step-content h5 {
            margin: 0 0 var(--spacing-xs) 0;
            color: var(--text-dark);
            font-size: var(--font-size-sm);
            font-weight: 500;
        }
        
        .step-content p {
            margin: 0;
            color: var(--text-light);
            font-size: var(--font-size-xs);
        }
        
        .carrier-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-md);
            background: #f8f9fa;
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-lg);
        }
        
        .carrier-info h4 {
            margin: 0;
            color: var(--text-dark);
            font-size: var(--font-size-sm);
        }
        
        .carrier-info p {
            margin: 0;
            color: var(--text-light);
            font-size: var(--font-size-xs);
        }
        
        .tracking-actions {
            display: flex;
            gap: var(--spacing-md);
        }
        
        .btn {
            padding: var(--spacing-sm) var(--spacing-md);
            border: none;
            border-radius: var(--radius-sm);
            text-decoration: none;
            font-size: var(--font-size-sm);
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-xs);
        }
        
        .btn-primary {
            background: var(--primary-color); color: white;
        }
        
        .btn-primary:hover {
            background: var(--accent-color);
        }
        
        .btn-outline {
            background: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }
        
        .btn-outline:hover {
            background: var(--primary-color); color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: var(--spacing-xxl);
            color: var(--text-light);
        }
        
        .empty-state i {
            font-size: var(--font-size-4xl);
            margin-bottom: var(--spacing-lg);
            color: var(--text-light);
        }
        
        .empty-state h3 {
            margin: 0 0 var(--spacing-md) 0;
            color: var(--text-dark);
        }
        
        .empty-state p {
            margin: 0 0 var(--spacing-lg) 0;
        }
        
        @media (max-width: 768px) {
            .orders-grid {
                grid-template-columns: 1fr;
            }
            
            .location-info {
                flex-direction: column;
                gap: var(--spacing-md);
            }
            
            .tracking-actions {
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
                <div class="logo">
                    <a href="../">
                        <h1>Linh2Store</h1>
                    </a>
                </div>
                
                <nav class="main-nav">
                    <a href="../">Trang chủ</a>
                    <a href="../san-pham/">Sản phẩm</a>
                    <a href="../thuong-hieu/">Thương hiệu</a>
                    <a href="../blog/">Blog</a>
                    <a href="../lien-he/">Liên hệ</a>
                </nav>
                
                <div class="header-actions">
                    <a href="../thanh-toan/" class="cart-icon" title="Thanh toán">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </a>
                    <a href="index.php" class="user-icon" title="Tài khoản">
                        <?php if (!empty($user['avatar']) && file_exists("../images/avatars/" . $user['avatar'])): ?>
                            <img src="../images/avatars/<?php echo $user['avatar']; ?>" alt="Avatar" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </a>
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
            <span>Vị trí đơn hàng</span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="tracking-container">
        <div class="tracking-header">
            <h1>Vị trí đơn hàng</h1>
            <p>Theo dõi trạng thái vận chuyển của đơn hàng</p>
        </div>
        
        <?php if (!empty($user_orders)): ?>
            <div class="orders-grid">
                <?php foreach ($user_orders as $order): ?>
                    <div class="order-tracking-card">
                        <div class="order-header">
                            <div class="order-number">Đơn hàng #<?php echo $order['order_number']; ?></div>
                            <div class="order-status">
                                <?php 
                                $status_labels = [
                                    'pending' => 'Chờ xử lý',
                                    'shipping' => 'Đang giao hàng',
                                    'delivered' => 'Đã giao hàng'
                                ];
                                echo $status_labels[$order['status']] ?? $order['status'];
                                ?>
                            </div>
                        </div>
                        
                        <div class="tracking-info">
                            <div class="location-info">
                                <div class="location-item">
                                    <h4>Điểm xuất phát</h4>
                                    <p><?php echo $order['current_location']; ?></p>
                                </div>
                                <div class="location-item">
                                    <h4>Điểm đến</h4>
                                    <p><?php echo $order['destination']; ?></p>
                                </div>
                            </div>
                            
                            <div class="progress-container">
                                <div class="progress-label">
                                    <span>Tiến độ giao hàng</span>
                                    <span><?php echo $order['progress']; ?>%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $order['progress']; ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="tracking-steps">
                                <h4>Lịch trình vận chuyển</h4>
                                <?php foreach ($order['tracking_steps'] as $step): ?>
                                    <div class="step-item">
                                        <div class="step-icon <?php echo $step['status']; ?>">
                                            <i class="fas fa-<?php echo $step['status'] === 'completed' ? 'check' : ($step['status'] === 'current' ? 'clock' : 'circle'); ?>"></i>
                                        </div>
                                        <div class="step-content">
                                            <h5><?php echo $step['step']; ?></h5>
                                            <p><?php echo $step['time']; ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="carrier-info">
                                <div>
                                    <h4><?php echo $order['carrier']; ?></h4>
                                    <p>Mã vận đơn: <?php echo $order['tracking_number']; ?></p>
                                </div>
                                <div>
                                    <h4>Dự kiến giao hàng</h4>
                                    <p><?php echo date('d/m/Y H:i', strtotime($order['estimated_delivery'])); ?></p>
                                </div>
                            </div>
                            
                            <div class="tracking-actions">
                                <a href="chi-tiet-don-hang.php?id=<?php echo $order['order_id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i>
                                    Xem chi tiết
                                </a>
                                <button class="btn btn-outline" onclick="refreshTracking('<?php echo $order['order_number']; ?>')">
                                    <i class="fas fa-sync-alt"></i>
                                    Làm mới
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-truck"></i>
                <h3>Chưa có đơn hàng nào</h3>
                <p>Bạn chưa có đơn hàng nào để theo dõi</p>
                <a href="../san-pham/" class="btn btn-primary">Mua sắm ngay</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function refreshTracking(orderNumber) {
            // Simulate refresh
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang cập nhật...';
            button.disabled = true;
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
                alert('Thông tin vị trí đã được cập nhật!');
            }, 2000);
        }
        
        // Auto refresh every 30 seconds
        setInterval(() => {
            // In a real application, this would fetch new tracking data
            console.log('Auto-refreshing tracking data...');
        }, 30000);
    </script>
</body>
</html>
