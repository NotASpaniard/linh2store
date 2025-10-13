<?php
/**
 * Trang thanh toán từ giỏ hàng (nút địa chỉ)
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
$cart_items = [];
$total_amount = 0;
$shipping_fee = 0;
$final_amount = 0;

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Lấy giỏ hàng của user
    $stmt = $conn->prepare("
        SELECT c.*, p.name, p.price, p.sale_price, b.name as brand_name, pc.color_name, pc.color_code
        FROM cart c
        LEFT JOIN products p ON c.product_id = p.id
        LEFT JOIN brands b ON p.brand_id = b.id
        LEFT JOIN product_colors pc ON c.product_color_id = pc.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $cart_items = $stmt->fetchAll();
    
    // Tính tổng tiền
    foreach ($cart_items as $item) {
        $price = $item['sale_price'] ?: $item['price'];
        $total_amount += $price * $item['quantity'];
    }
    
    // Phí ship (miễn phí nếu đơn hàng trên 500k)
    $shipping_fee = $total_amount >= 500000 ? 0 : 30000;
    $final_amount = $total_amount + $shipping_fee;
    
} catch (Exception $e) {
    $cart_items = [];
}

// Nếu giỏ hàng trống, chuyển về trang giỏ hàng
if (empty($cart_items)) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - Linh2Store</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Dancing+Script:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .checkout-page {
            padding: var(--spacing-xl) 0;
            background: var(--bg-light);
            min-height: 100vh;
        }
        
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 var(--spacing-lg);
        }
        
        .checkout-header {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }
        
        .checkout-header h1 {
            color: var(--text-dark);
            font-size: var(--font-size-xxl);
            margin-bottom: var(--spacing-sm);
        }
        
        .checkout-header p {
            color: var(--text-light);
            font-size: var(--font-size-base);
        }
        
        .checkout-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: var(--spacing-xl);
            align-items: start;
        }
        
        .checkout-form {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
        }
        
        .checkout-summary {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: var(--spacing-xl);
        }
        
        .form-section {
            margin-bottom: var(--spacing-xl);
        }
        
        .form-section h3 {
            color: var(--text-dark);
            font-size: var(--font-size-lg);
            margin-bottom: var(--spacing-lg);
            padding-bottom: var(--spacing-sm);
            border-bottom: 2px solid var(--primary-color);
        }
        
        .form-group {
            margin-bottom: var(--spacing-lg);
        }
        
        .form-group label {
            display: block;
            color: var(--text-dark);
            font-weight: 500;
            margin-bottom: var(--spacing-xs);
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: var(--spacing-sm);
            border: 1px solid var(--primary-color);
            border-radius: var(--radius-sm);
            font-size: var(--font-size-base);
            transition: border-color var(--transition-fast);
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent-color);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-md);
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-md);
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
        
        .order-item-image {
            width: 60px;
            height: 60px;
            border-radius: var(--radius-sm);
            overflow: hidden;
        }
        
        .order-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
            border-top: 2px solid var(--primary-color);
            padding-top: var(--spacing-lg);
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
            border-top: 1px solid var(--bg-light);
            padding-top: var(--spacing-sm);
            margin-top: var(--spacing-sm);
        }
        
        .checkout-btn {
            width: 100%;
            padding: var(--spacing-lg);
            background: var(--accent-color);
            color: var(--white);
            border: none;
            border-radius: var(--radius-md);
            font-size: var(--font-size-lg);
            font-weight: 600;
            cursor: pointer;
            transition: background-color var(--transition-fast);
        }
        
        .checkout-btn:hover {
            background: #d81b60;
        }
        
        .checkout-btn:disabled {
            background: var(--text-light);
            cursor: not-allowed;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .checkout-content {
                grid-template-columns: 1fr;
                gap: var(--spacing-lg);
            }
            
            .checkout-summary {
                position: static;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .payment-methods {
                grid-template-columns: 1fr;
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
                    <a href="index.php" class="cart-btn">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?php echo count($cart_items); ?></span>
                    </a>
                    <a href="../user/" class="user-btn">
                        <i class="fas fa-user"></i>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Checkout Page -->
    <div class="checkout-page">
        <div class="checkout-container">
            <div class="checkout-header">
                <h1>Thanh toán</h1>
                <p>Hoàn tất đơn hàng của bạn</p>
            </div>
            
            <div class="checkout-content">
                <!-- Checkout Form -->
                <div class="checkout-form">
                    <form id="checkout-form" method="POST" action="../thanh-toan/process.php">
                        <!-- Thông tin giao hàng -->
                        <div class="form-section">
                            <h3><i class="fas fa-truck"></i> Thông tin giao hàng</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="full_name">Họ và tên *</label>
                                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Số điện thoại *</label>
                                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Địa chỉ giao hàng *</label>
                                <textarea id="address" name="address" rows="3" placeholder="Nhập địa chỉ chi tiết..." required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">Thành phố *</label>
                                    <select id="city" name="city" required>
                                        <option value="">Chọn thành phố</option>
                                        <option value="hanoi" <?php echo ($user['city'] ?? '') == 'hanoi' ? 'selected' : ''; ?>>Hà Nội</option>
                                        <option value="hcm" <?php echo ($user['city'] ?? '') == 'hcm' ? 'selected' : ''; ?>>TP. Hồ Chí Minh</option>
                                        <option value="danang" <?php echo ($user['city'] ?? '') == 'danang' ? 'selected' : ''; ?>>Đà Nẵng</option>
                                        <option value="other" <?php echo ($user['city'] ?? '') == 'other' ? 'selected' : ''; ?>>Khác</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="district">Quận/Huyện *</label>
                                    <input type="text" id="district" name="district" value="<?php echo htmlspecialchars($user['district'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Phương thức thanh toán -->
                        <div class="form-section">
                            <h3><i class="fas fa-credit-card"></i> Phương thức thanh toán</h3>
                            
                            <div class="payment-methods">
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="cod" checked>
                                    <i class="fas fa-money-bill-wave"></i>
                                    <h4>Thanh toán khi nhận hàng</h4>
                                    <p>COD - Trả tiền mặt</p>
                                </label>
                                
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="bank_transfer">
                                    <i class="fas fa-university"></i>
                                    <h4>Chuyển khoản ngân hàng</h4>
                                    <p>Chuyển khoản trước</p>
                                </label>
                                
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="momo">
                                    <i class="fas fa-mobile-alt"></i>
                                    <h4>Ví MoMo</h4>
                                    <p>Thanh toán qua MoMo</p>
                                </label>
                                
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="vnpay">
                                    <i class="fas fa-credit-card"></i>
                                    <h4>VNPay</h4>
                                    <p>Thẻ ATM/Internet Banking</p>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Ghi chú -->
                        <div class="form-section">
                            <h3><i class="fas fa-sticky-note"></i> Ghi chú đơn hàng</h3>
                            <div class="form-group">
                                <label for="notes">Ghi chú (tùy chọn)</label>
                                <textarea id="notes" name="notes" rows="3" placeholder="Ghi chú thêm cho đơn hàng..."></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Order Summary -->
                <div class="checkout-summary">
                    <h3><i class="fas fa-shopping-bag"></i> Đơn hàng của bạn</h3>
                    
                    <div class="order-items">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="order-item">
                                <div class="order-item-image">
                                    <img src="../<?php echo getProductImage($item['product_id']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="order-item-info">
                                    <div class="order-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="order-item-details">
                                        <?php if ($item['brand_name']): ?>
                                            <?php echo htmlspecialchars($item['brand_name']); ?>
                                        <?php endif; ?>
                                        <?php if ($item['color_name']): ?>
                                            - <?php echo htmlspecialchars($item['color_name']); ?>
                                        <?php endif; ?>
                                        <br>
                                        Số lượng: <?php echo $item['quantity']; ?>
                                    </div>
                                </div>
                                <div class="order-item-price">
                                    <?php 
                                    $price = $item['sale_price'] ?: $item['price'];
                                    echo number_format($price * $item['quantity'], 0, ',', '.') . 'đ';
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-summary">
                        <div class="summary-row">
                            <span>Tạm tính:</span>
                            <span><?php echo number_format($total_amount, 0, ',', '.'); ?>đ</span>
                        </div>
                        <div class="summary-row">
                            <span>Phí vận chuyển:</span>
                            <span>
                                <?php if ($shipping_fee == 0): ?>
                                    <span style="color: var(--accent-color);">Miễn phí</span>
                                <?php else: ?>
                                    <?php echo number_format($shipping_fee, 0, ',', '.'); ?>đ
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="summary-row total">
                            <span>Tổng cộng:</span>
                            <span><?php echo number_format($final_amount, 0, ',', '.'); ?>đ</span>
                        </div>
                    </div>
                    
                    <button type="submit" form="checkout-form" class="checkout-btn">
                        <i class="fas fa-lock"></i>
                        Hoàn tất đơn hàng
                    </button>
                </div>
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
        document.querySelector('input[name="payment_method"]:checked').closest('.payment-method').classList.add('selected');
        
        // Form validation
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            const requiredFields = ['full_name', 'phone', 'address', 'city', 'district'];
            let isValid = true;
            
            requiredFields.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value.trim()) {
                    input.style.borderColor = '#f44336';
                    isValid = false;
                } else {
                    input.style.borderColor = '';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
            }
        });
        
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
