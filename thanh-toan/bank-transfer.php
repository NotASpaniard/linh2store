<?php
/**
 * Trang thanh toán chuyển khoản ngân hàng
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
    <title>Thanh toán chuyển khoản - Linh2Store</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Dancing+Script:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .bank-transfer-page {
            padding: var(--spacing-xl) 0;
            background: var(--bg-light);
            min-height: 100vh;
        }
        
        .bank-transfer-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 var(--spacing-lg);
        }
        
        .bank-transfer-header {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }
        
        .bank-transfer-header h1 {
            color: var(--text-dark);
            font-size: var(--font-size-xxl);
            margin-bottom: var(--spacing-sm);
        }
        
        .bank-transfer-header p {
            color: var(--text-light);
            font-size: var(--font-size-base);
        }
        
        .bank-info {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
        }
        
        .bank-info h2 {
            color: var(--text-dark);
            font-size: var(--font-size-xl);
            margin-bottom: var(--spacing-lg);
            padding-bottom: var(--spacing-sm);
            border-bottom: 2px solid var(--primary-color);
        }
        
        .bank-accounts {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-xl);
        }
        
        .bank-account {
            border: 2px solid var(--primary-color);
            border-radius: var(--radius-md);
            padding: var(--spacing-lg);
            text-align: center;
            transition: all var(--transition-fast);
        }
        
        .bank-account:hover {
            border-color: var(--accent-color);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .bank-account h3 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-md);
            font-size: var(--font-size-lg);
        }
        
        .bank-details {
            text-align: left;
        }
        
        .bank-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--spacing-sm);
            padding: var(--spacing-xs) 0;
            border-bottom: 1px solid var(--bg-light);
        }
        
        .bank-detail:last-child {
            border-bottom: none;
        }
        
        .bank-detail-label {
            color: var(--text-light);
            font-weight: 500;
        }
        
        .bank-detail-value {
            color: var(--text-dark);
            font-weight: 600;
        }
        
        .order-summary {
            background: var(--bg-light);
            border-radius: var(--radius-md);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-xl);
        }
        
        .order-summary h3 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-md);
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
        
        .copy-btn {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: var(--font-size-sm);
            margin-left: var(--spacing-xs);
        }
        
        .copy-btn:hover {
            background: var(--accent-color);
        }
        
        @media (max-width: 768px) {
            .bank-accounts {
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

    <!-- Bank Transfer Page -->
    <div class="bank-transfer-page">
        <div class="bank-transfer-container">
            <div class="bank-transfer-header">
                <h1><i class="fas fa-university"></i> Thanh toán chuyển khoản</h1>
                <p>Vui lòng chuyển khoản theo thông tin bên dưới</p>
            </div>
            
            <!-- Order Summary -->
            <div class="order-summary">
                <h3><i class="fas fa-receipt"></i> Tóm tắt đơn hàng</h3>
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
                    <span>Số tiền cần chuyển:</span>
                    <span style="color: var(--accent-color);"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</span>
                </div>
            </div>
            
            <!-- Bank Information -->
            <div class="bank-info">
                <h2><i class="fas fa-credit-card"></i> Thông tin chuyển khoản</h2>
                
                <div class="bank-accounts">
                    <div class="bank-account">
                        <h3><i class="fas fa-university"></i> Vietcombank</h3>
                        <div class="bank-details">
                            <div class="bank-detail">
                                <span class="bank-detail-label">Số tài khoản:</span>
                                <span class="bank-detail-value" id="account1">JQKA567890</span>
                                <button class="copy-btn" onclick="copyToClipboard('account1')">Copy</button>
                            </div>
                            <div class="bank-detail">
                                <span class="bank-detail-label">Chủ tài khoản:</span>
                                <span class="bank-detail-value">Linh2Store</span>
                            </div>
                            <div class="bank-detail">
                                <span class="bank-detail-label">Chi nhánh:</span>
                                <span class="bank-detail-value">Hà Nội</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bank-account">
                        <h3><i class="fas fa-university"></i> BIDV</h3>
                        <div class="bank-details">
                            <div class="bank-detail">
                                <span class="bank-detail-label">Số tài khoản:</span>
                                <span class="bank-detail-value" id="account2">9876543210</span>
                                <button class="copy-btn" onclick="copyToClipboard('account2')">Copy</button>
                            </div>
                            <div class="bank-detail">
                                <span class="bank-detail-label">Chủ tài khoản:</span>
                                <span class="bank-detail-value">Linh2Store</span>
                            </div>
                            <div class="bank-detail">
                                <span class="bank-detail-label">Chi nhánh:</span>
                                <span class="bank-detail-value">TP.HCM</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="background: #e3f2fd; color: #1565c0; padding: var(--spacing-lg); border-radius: var(--radius-md); margin-top: var(--spacing-lg); border: 1px solid #bbdefb;">
                    <h4 style="margin: 0 0 var(--spacing-sm) 0;"><i class="fas fa-info-circle"></i> Lưu ý quan trọng</h4>
                    <p style="margin: 0; font-size: var(--font-size-sm);">
                        <strong>Nội dung chuyển khoản:</strong> <?php echo $order['order_number']; ?><br>
                        <strong>Số tiền:</strong> <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ<br>
                        Sau khi chuyển khoản, đơn hàng sẽ được xử lý trong vòng 24h.
                    </p>
                </div>
            </div>
            
            <!-- Payment Steps -->
            <div class="payment-steps">
                <h3><i class="fas fa-list-ol"></i> Hướng dẫn thanh toán</h3>
                <div class="steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <div class="step-title">Chuyển khoản</div>
                            <div class="step-description">Chuyển khoản số tiền <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ đến một trong các tài khoản trên</div>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <div class="step-title">Ghi nội dung</div>
                            <div class="step-description">Ghi nội dung chuyển khoản: <?php echo $order['order_number']; ?></div>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <div class="step-title">Xác nhận</div>
                            <div class="step-description">Chúng tôi sẽ xác nhận và xử lý đơn hàng trong vòng 24h</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="action-buttons">
                <button onclick="confirmPayment()" class="btn btn-primary">
                    <i class="fas fa-check"></i>
                    Tôi đã chuyển khoản
                </button>
                <a href="../user/" class="btn btn-secondary">
                    <i class="fas fa-user"></i>
                    Xem đơn hàng của tôi
                </a>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const text = element.textContent;
            
            navigator.clipboard.writeText(text).then(function() {
                // Show success message
                const button = element.nextElementSibling;
                const originalText = button.textContent;
                button.textContent = 'Copied!';
                button.style.background = 'var(--accent-color)';
                
                setTimeout(() => {
                    button.textContent = originalText;
                    button.style.background = 'var(--primary-color)';
                }, 2000);
            });
        }
        
        function confirmPayment() {
            if (confirm('Bạn đã chuyển khoản thành công? Chúng tôi sẽ xác nhận và xử lý đơn hàng trong vòng 24h.')) {
                // Here you would typically send a notification to admin
                alert('Cảm ơn bạn! Chúng tôi đã nhận được thông tin và sẽ xử lý đơn hàng sớm nhất.');
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
