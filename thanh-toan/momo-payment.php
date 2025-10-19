<?php
/**
 * Trang thanh toán MoMo
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/auth-middleware.php';
require_once '../config/database.php';

// Kiểm tra đăng nhập
$user = AuthMiddleware::requireLogin();

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
        
    } catch (Exception $e) {
        $order = null;
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
    <title>Thanh toán MoMo - Linh2Store</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Dancing+Script:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .momo-page {
            padding: var(--spacing-xl) 0;
            background: var(--bg-light);
            min-height: 100vh;
        }
        
        .momo-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 0 var(--spacing-lg);
        }
        
        .momo-header {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }
        
        .momo-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #d81b60, #ff6b9d);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--spacing-lg);
        }
        
        .momo-logo i {
            font-size: 40px;
            color: var(--white);
        }
        
        .momo-header h1 {
            color: var(--text-dark);
            font-size: var(--font-size-xxl);
            margin-bottom: var(--spacing-sm);
        }
        
        .momo-header p {
            color: var(--text-light);
            font-size: var(--font-size-base);
        }
        
        .momo-info {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
        }
        
        .momo-info h2 {
            color: var(--text-dark);
            font-size: var(--font-size-xl);
            margin-bottom: var(--spacing-lg);
            padding-bottom: var(--spacing-sm);
            border-bottom: 2px solid var(--primary-color);
        }
        
        .order-summary {
            background: var(--bg-light);
            border-radius: var(--radius-md);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
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
            border-top: 2px solid var(--primary-color);
            padding-top: var(--spacing-sm);
            margin-top: var(--spacing-sm);
        }
        
        .momo-details {
            background: linear-gradient(135deg, #d81b60, #ff6b9d);
            color: var(--white);
            border-radius: var(--radius-md);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
        }
        
        .momo-details h3 {
            margin: 0 0 var(--spacing-md) 0;
            font-size: var(--font-size-lg);
        }
        
        .momo-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--spacing-sm);
            padding: var(--spacing-xs) 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .momo-detail:last-child {
            border-bottom: none;
        }
        
        .momo-detail-label {
            opacity: 0.9;
        }
        
        .momo-detail-value {
            font-weight: 600;
        }
        
        .qr-code {
            text-align: center;
            margin: var(--spacing-xl) 0;
        }
        
        .qr-code img {
            width: 200px;
            height: 200px;
            border: 3px solid var(--primary-color);
            border-radius: var(--radius-md);
        }
        
        .qr-code p {
            margin-top: var(--spacing-md);
            color: var(--text-light);
            font-size: var(--font-size-sm);
        }
        
        .payment-steps {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
        }
        
        .payment-steps h3 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-lg);
        }
        
        .steps {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
        }
        
        .step {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            padding: var(--spacing-md);
            background: var(--bg-light);
            border-radius: var(--radius-sm);
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            background: var(--accent-color);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            flex-shrink: 0;
        }
        
        .step-content {
            flex: 1;
        }
        
        .step-title {
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: var(--spacing-xs);
        }
        
        .step-description {
            color: var(--text-light);
            font-size: var(--font-size-sm);
        }
        
        .action-buttons {
            display: flex;
            gap: var(--spacing-md);
            justify-content: center;
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
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #d81b60, #ff6b9d);
            color: var(--white);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
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
        
        .momo-app-link {
            background: var(--white);
            border: 2px solid var(--accent-color);
            border-radius: var(--radius-md);
            padding: var(--spacing-lg);
            text-align: center;
            margin-bottom: var(--spacing-lg);
        }
        
        .momo-app-link h4 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-sm);
        }
        
        .momo-app-link p {
            color: var(--text-light);
            margin-bottom: var(--spacing-md);
            font-size: var(--font-size-sm);
        }
        
        @media (max-width: 768px) {
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
                    <a href="../gio-hang/" class="cart-btn">
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

    <!-- MoMo Payment Page -->
    <div class="momo-page">
        <div class="momo-container">
            <div class="momo-header">
                <div class="momo-logo">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h1>Thanh toán MoMo</h1>
                <p>Quét mã QR hoặc chuyển khoản qua MoMo</p>
            </div>
            
            <!-- Order Summary -->
            <div class="momo-info">
                <h2><i class="fas fa-receipt"></i> Thông tin thanh toán</h2>
                
                <div class="order-summary">
                    <div class="summary-row">
                        <span>Mã đơn hàng:</span>
                        <span><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></span>
                    </div>
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
                        <span>Số tiền cần thanh toán:</span>
                        <span style="color: var(--accent-color);"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</span>
                    </div>
                </div>
                
                <div class="momo-details">
                    <h3><i class="fas fa-mobile-alt"></i> Thông tin MoMo</h3>
                    <div class="momo-detail">
                        <span class="momo-detail-label">Số điện thoại MoMo:</span>
                        <span class="momo-detail-value">090JQKA567</span>
                    </div>
                    <div class="momo-detail">
                        <span class="momo-detail-label">Tên người nhận:</span>
                        <span class="momo-detail-value">Linh2Store</span>
                    </div>
                    <div class="momo-detail">
                        <span class="momo-detail-label">Nội dung chuyển khoản:</span>
                        <span class="momo-detail-value"><?php echo $order['order_number']; ?></span>
                    </div>
                </div>
            </div>
            
            <!-- QR Code -->
            <div class="qr-code">
                <h3>Quét mã QR để thanh toán</h3>
                <img src="https://via.placeholder.com/200x200/d81b60/ffffff?text=MoMo+QR" alt="MoMo QR Code">
                <p>Mở ứng dụng MoMo và quét mã QR này để thanh toán</p>
            </div>
            
            <!-- MoMo App Link -->
            <div class="momo-app-link">
                <h4><i class="fas fa-mobile-alt"></i> Thanh toán qua ứng dụng MoMo</h4>
                <p>Nếu bạn chưa có ứng dụng MoMo, hãy tải về và đăng ký tài khoản</p>
                <a href="https://momo.vn" target="_blank" class="btn btn-primary">
                    <i class="fas fa-download"></i>
                    Tải ứng dụng MoMo
                </a>
            </div>
            
            <!-- Payment Steps -->
            <div class="payment-steps">
                <h3><i class="fas fa-list-ol"></i> Hướng dẫn thanh toán</h3>
                <div class="steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <div class="step-title">Mở ứng dụng MoMo</div>
                            <div class="step-description">Mở ứng dụng MoMo trên điện thoại của bạn</div>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <div class="step-title">Quét mã QR hoặc chuyển khoản</div>
                            <div class="step-description">Quét mã QR hoặc chuyển khoản đến số 090JQKA567</div>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <div class="step-title">Nhập nội dung</div>
                            <div class="step-description">Nhập nội dung: <?php echo $order['order_number']; ?></div>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <div class="step-title">Xác nhận thanh toán</div>
                            <div class="step-description">Nhập mật khẩu và xác nhận thanh toán</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="action-buttons">
                <button onclick="confirmMoMoPayment()" class="btn btn-primary">
                    <i class="fas fa-check"></i>
                    Tôi đã thanh toán MoMo
                </button>
                <a href="../user/" class="btn btn-secondary">
                    <i class="fas fa-user"></i>
                    Xem đơn hàng của tôi
                </a>
            </div>
        </div>
    </div>

    <script>
        function confirmMoMoPayment() {
            if (confirm('Bạn đã thanh toán thành công qua MoMo? Chúng tôi sẽ xác nhận và xử lý đơn hàng trong vòng 24h.')) {
                // Here you would typically send a notification to admin
                alert('Cảm ơn bạn! Chúng tôi đã nhận được thông tin thanh toán và sẽ xử lý đơn hàng sớm nhất.');
                window.location.href = '../user/';
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
</body>
</html>
