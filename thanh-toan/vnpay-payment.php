<?php
/**
 * Trang thanh toán VNPay
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
    <title>Thanh toán VNPay - Linh2Store</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Dancing+Script:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .vnpay-page {
            padding: var(--spacing-xl) 0;
            background: var(--bg-light);
            min-height: 100vh;
        }
        
        .vnpay-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 var(--spacing-lg);
        }
        
        .vnpay-header {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }
        
        .vnpay-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--spacing-lg);
        }
        
        .vnpay-logo i {
            font-size: 40px;
            color: var(--white);
        }
        
        .vnpay-header h1 {
            color: var(--text-dark);
            font-size: var(--font-size-xxl);
            margin-bottom: var(--spacing-sm);
        }
        
        .vnpay-header p {
            color: var(--text-light);
            font-size: var(--font-size-base);
        }
        
        .vnpay-info {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
        }
        
        .vnpay-info h2 {
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
        
        .vnpay-details {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: var(--white);
            border-radius: var(--radius-md);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
        }
        
        .vnpay-details h3 {
            margin: 0 0 var(--spacing-md) 0;
            font-size: var(--font-size-lg);
        }
        
        .vnpay-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--spacing-sm);
            padding: var(--spacing-xs) 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .vnpay-detail:last-child {
            border-bottom: none;
        }
        
        .vnpay-detail-label {
            opacity: 0.9;
        }
        
        .vnpay-detail-value {
            font-weight: 600;
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
        }
        
        .payment-method {
            border: 2px solid var(--primary-color);
            border-radius: var(--radius-md);
            padding: var(--spacing-lg);
            text-align: center;
            cursor: pointer;
            transition: all var(--transition-fast);
            background: var(--white);
        }
        
        .payment-method:hover {
            border-color: var(--accent-color);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .payment-method.selected {
            border-color: var(--accent-color);
            background: var(--accent-color);
            color: var(--white);
        }
        
        .payment-method input[type="radio"] {
            display: none;
        }
        
        .payment-method i {
            font-size: var(--font-size-xl);
            margin-bottom: var(--spacing-sm);
            display: block;
        }
        
        .payment-method h4 {
            margin: 0 0 var(--spacing-xs) 0;
            font-size: var(--font-size-base);
        }
        
        .payment-method p {
            margin: 0;
            font-size: var(--font-size-sm);
            opacity: 0.8;
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
            background: linear-gradient(135deg, #1e3c72, #2a5298);
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
        
        .security-info {
            background: var(--primary-color);
            color: var(--white);
            border-radius: var(--radius-md);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
        }
        
        .security-info h4 {
            margin: 0 0 var(--spacing-sm) 0;
            font-size: var(--font-size-lg);
        }
        
        .security-info p {
            margin: 0;
            font-size: var(--font-size-sm);
            opacity: 0.9;
        }
        
        @media (max-width: 768px) {
            .payment-methods {
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

    <!-- VNPay Payment Page -->
    <div class="vnpay-page">
        <div class="vnpay-container">
            <div class="vnpay-header">
                <div class="vnpay-logo">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h1>Thanh toán VNPay</h1>
                <p>Thanh toán an toàn với VNPay</p>
            </div>
            
            <!-- Order Summary -->
            <div class="vnpay-info">
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
                
                <div class="vnpay-details">
                    <h3><i class="fas fa-shield-alt"></i> Thông tin VNPay</h3>
                    <div class="vnpay-detail">
                        <span class="vnpay-detail-label">Mã đơn hàng:</span>
                        <span class="vnpay-detail-value"><?php echo $order['order_number']; ?></span>
                    </div>
                    <div class="vnpay-detail">
                        <span class="vnpay-detail-label">Số tiền:</span>
                        <span class="vnpay-detail-value"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</span>
                    </div>
                    <div class="vnpay-detail">
                        <span class="vnpay-detail-label">Nội dung:</span>
                        <span class="vnpay-detail-value">Thanh toan don hang <?php echo $order['order_number']; ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Payment Methods -->
            <div class="vnpay-info">
                <h2><i class="fas fa-credit-card"></i> Chọn phương thức thanh toán</h2>
                
                <div class="payment-methods">
                    <label class="payment-method">
                        <input type="radio" name="vnpay_method" value="atm" checked>
                        <i class="fas fa-university"></i>
                        <h4>Thẻ ATM</h4>
                        <p>Thẻ ATM nội địa</p>
                    </label>
                    
                    <label class="payment-method">
                        <input type="radio" name="vnpay_method" value="internet_banking">
                        <i class="fas fa-laptop"></i>
                        <h4>Internet Banking</h4>
                        <p>Ngân hàng trực tuyến</p>
                    </label>
                    
                    <label class="payment-method">
                        <input type="radio" name="vnpay_method" value="credit_card">
                        <i class="fas fa-credit-card"></i>
                        <h4>Thẻ tín dụng</h4>
                        <p>Visa, Mastercard</p>
                    </label>
                    
                    <label class="payment-method">
                        <input type="radio" name="vnpay_method" value="qr_code">
                        <i class="fas fa-qrcode"></i>
                        <h4>QR Code</h4>
                        <p>Quét mã QR</p>
                    </label>
                </div>
            </div>
            
            <!-- Security Info -->
            <div class="security-info">
                <h4><i class="fas fa-shield-alt"></i> Bảo mật thanh toán</h4>
                <p>Giao dịch được bảo mật 100% bởi VNPay. Thông tin thẻ của bạn được mã hóa và không được lưu trữ trên hệ thống.</p>
            </div>
            
            <!-- Payment Steps -->
            <div class="payment-steps">
                <h3><i class="fas fa-list-ol"></i> Hướng dẫn thanh toán</h3>
                <div class="steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <div class="step-title">Chọn phương thức thanh toán</div>
                            <div class="step-description">Chọn thẻ ATM, Internet Banking, thẻ tín dụng hoặc QR Code</div>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <div class="step-title">Nhập thông tin thanh toán</div>
                            <div class="step-description">Nhập thông tin thẻ hoặc đăng nhập Internet Banking</div>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <div class="step-title">Xác thực OTP</div>
                            <div class="step-description">Nhập mã OTP được gửi về điện thoại</div>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <div class="step-title">Hoàn tất thanh toán</div>
                            <div class="step-description">Nhận xác nhận thanh toán thành công</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="action-buttons">
                <button onclick="proceedToVNPay()" class="btn btn-primary">
                    <i class="fas fa-credit-card"></i>
                    Thanh toán với VNPay
                </button>
                <a href="../user/" class="btn btn-secondary">
                    <i class="fas fa-user"></i>
                    Xem đơn hàng của tôi
                </a>
            </div>
        </div>
    </div>

    <script>
        // Payment method selection
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                // Remove selected class from all methods
                document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
                // Add selected class to clicked method
                this.classList.add('selected');
            });
        });
        
        // Set initial selected method
        document.querySelector('input[name="vnpay_method"]:checked').closest('.payment-method').classList.add('selected');
        
        function proceedToVNPay() {
            const selectedMethod = document.querySelector('input[name="vnpay_method"]:checked').value;
            
            // Simulate VNPay payment process
            if (confirm(`Bạn sẽ được chuyển đến trang thanh toán VNPay với phương thức ${selectedMethod}. Tiếp tục?`)) {
                // Here you would typically redirect to VNPay gateway
                // For demo purposes, we'll simulate a successful payment
                alert('Thanh toán thành công! Bạn sẽ được chuyển về trang xác nhận.');
                window.location.href = 'success.php?order_id=<?php echo $order_id; ?>';
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
