<?php
/**
 * Trang giỏ hàng
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

$cart_items = [];
$total_amount = 0;

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
    $stmt->execute([$_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll();
    
    // Tính tổng tiền
    foreach ($cart_items as $item) {
        $price = $item['sale_price'] ?: $item['price'];
        $total_amount += $price * $item['quantity'];
    }
    
} catch (Exception $e) {
    $cart_items = [];
    $total_amount = 0;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - Linh2Store</title>
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
                        <a href="../user/" class="user-icon" title="Tài khoản">
                            <i class="fas fa-user"></i>
                        </a>
                        
                        <a href="index.php" class="cart-icon active" title="Giỏ hàng">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count"><?php echo count($cart_items); ?></span>
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
            <span>Giỏ hàng</span>
        </div>
    </div>

    <!-- Cart Content -->
    <div class="cart-page">
        <div class="container">
            <h1>Giỏ hàng của bạn</h1>
            
            <?php if (!empty($cart_items)): ?>
                <div class="row">
                    <div class="col-8">
                        <!-- Cart Items -->
                        <div class="cart-items">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="cart-item" data-cart-id="<?php echo $item['id']; ?>">
                                    <div class="item-image">
                                        <img src="../<?php echo getProductImage($item['product_id']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>"
                                             loading="lazy">
                                    </div>
                                    
                                    <div class="item-info">
                                        <h3 class="item-name">
                                            <a href="../san-pham/chi-tiet.php?id=<?php echo $item['product_id']; ?>">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </a>
                                        </h3>
                                        <p class="item-brand"><?php echo htmlspecialchars($item['brand_name']); ?></p>
                                        
                                        <?php if ($item['color_name']): ?>
                                            <div class="item-color">
                                                <span>Màu:</span>
                                                <div class="color-swatch" style="background-color: <?php echo $item['color_code']; ?>;"></div>
                                                <span><?php echo htmlspecialchars($item['color_name']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="item-price">
                                        <span class="current-price">
                                            <?php 
                                            $price = $item['sale_price'] ?: $item['price'];
                                            echo number_format($price); 
                                            ?>đ
                                        </span>
                                        <?php if ($item['sale_price']): ?>
                                            <span class="old-price"><?php echo number_format($item['price']); ?>đ</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="item-quantity">
                                        <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">-</button>
                                        <input type="number" class="quantity-input" 
                                               value="<?php echo $item['quantity']; ?>" 
                                               min="1" 
                                               onchange="updateQuantity(<?php echo $item['id']; ?>, 0, this.value)">
                                        <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">+</button>
                                    </div>
                                    
                                    <div class="item-total">
                                        <span class="total-price">
                                            <?php echo number_format(($item['sale_price'] ?: $item['price']) * $item['quantity']); ?>đ
                                        </span>
                                    </div>
                                    
                                    <div class="item-actions">
                                        <button class="remove-btn" onclick="removeFromCart(<?php echo $item['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Cart Actions -->
                        <div class="cart-actions">
                            <a href="../san-pham/" class="btn btn-outline">
                                <i class="fas fa-arrow-left"></i>
                                Tiếp tục mua sắm
                            </a>
                            
                            <button class="btn btn-secondary" onclick="clearCart()">
                                <i class="fas fa-trash"></i>
                                Xóa tất cả
                            </button>
                            
                            <?php if (!empty($cart_items)): ?>
                                <a href="../thanh-toan/" class="btn btn-primary">
                                    <i class="fas fa-credit-card"></i>
                                    Thanh toán
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-4">
                        <!-- Cart Summary -->
                        <div class="cart-summary">
                            <h3>Tóm tắt đơn hàng</h3>
                            
                            <div class="summary-row">
                                <span>Tạm tính:</span>
                                <span id="subtotal"><?php echo number_format($total_amount); ?>đ</span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Phí vận chuyển:</span>
                                <span id="shipping-fee">
                                    <?php echo $total_amount >= 500000 ? 'Miễn phí' : '30.000đ'; ?>
                                </span>
                            </div>
                            
                            <div class="summary-row total">
                                <span>Tổng cộng:</span>
                                <span id="total-amount">
                                    <?php 
                                    $shipping_fee = $total_amount >= 500000 ? 0 : 30000;
                                    echo number_format($total_amount + $shipping_fee); 
                                    ?>đ
                                </span>
                            </div>
                            
                            <?php if ($total_amount < 500000): ?>
                                <div class="shipping-notice">
                                    <i class="fas fa-info-circle"></i>
                                    Mua thêm <?php echo number_format(500000 - $total_amount); ?>đ để được miễn phí ship
                                </div>
                            <?php endif; ?>
                            
                            <a href="thanh-toan.php" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-credit-card"></i>
                                Thanh toán
                            </a>
                            
                            <div class="payment-methods">
                                <h4>Phương thức thanh toán</h4>
                                <div class="payment-icons">
                                    <i class="fab fa-cc-visa"></i>
                                    <i class="fab fa-cc-mastercard"></i>
                                    <i class="fas fa-mobile-alt"></i>
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Empty Cart -->
                <div class="empty-cart">
                    <div class="empty-cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h2>Giỏ hàng trống</h2>
                    <p>Bạn chưa có sản phẩm nào trong giỏ hàng</p>
                    <a href="../san-pham/" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag"></i>
                        Mua sắm ngay
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    
    <script>
        // Cập nhật số lượng sản phẩm
        function updateQuantity(cartId, change, newValue = null) {
            const quantityInput = document.querySelector(`[data-cart-id="${cartId}"] .quantity-input`);
            let newQuantity = newValue ? parseInt(newValue) : parseInt(quantityInput.value) + change;
            
            if (newQuantity < 1) newQuantity = 1;
            
            // Gửi request cập nhật
            fetch('../api/cart.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cart_id: cartId,
                    quantity: newQuantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    quantityInput.value = newQuantity;
                    updateCartTotals();
                } else {
                    showAlert(data.message || 'Có lỗi xảy ra', 'error');
                }
            })
            .catch(error => {
                console.error('Lỗi cập nhật giỏ hàng:', error);
                showAlert('Có lỗi xảy ra khi cập nhật giỏ hàng', 'error');
            });
        }
        
        // Xóa sản phẩm khỏi giỏ hàng
        function removeFromCart(cartId) {
            if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                fetch(`../api/cart.php?id=${cartId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector(`[data-cart-id="${cartId}"]`).remove();
                        updateCartTotals();
                        updateCartCount();
                    } else {
                        showAlert(data.message || 'Có lỗi xảy ra', 'error');
                    }
                })
                .catch(error => {
                    console.error('Lỗi xóa khỏi giỏ hàng:', error);
                    showAlert('Có lỗi xảy ra khi xóa khỏi giỏ hàng', 'error');
                });
            }
        }
        
        // Xóa tất cả giỏ hàng
        function clearCart() {
            if (confirm('Bạn có chắc muốn xóa tất cả sản phẩm khỏi giỏ hàng?')) {
                fetch('../api/cart.php', {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        showAlert(data.message || 'Có lỗi xảy ra', 'error');
                    }
                })
                .catch(error => {
                    console.error('Lỗi xóa giỏ hàng:', error);
                    showAlert('Có lỗi xảy ra khi xóa giỏ hàng', 'error');
                });
            }
        }
        
        // Cập nhật tổng tiền
        function updateCartTotals() {
            let subtotal = 0;
            
            document.querySelectorAll('.cart-item').forEach(item => {
                const price = parseFloat(item.querySelector('.current-price').textContent.replace(/[^\d]/g, ''));
                const quantity = parseInt(item.querySelector('.quantity-input').value);
                const total = price * quantity;
                
                item.querySelector('.total-price').textContent = total.toLocaleString() + 'đ';
                subtotal += total;
            });
            
            document.getElementById('subtotal').textContent = subtotal.toLocaleString() + 'đ';
            
            const shippingFee = subtotal >= 500000 ? 0 : 30000;
            const total = subtotal + shippingFee;
            
            document.getElementById('shipping-fee').textContent = shippingFee === 0 ? 'Miễn phí' : '30.000đ';
            document.getElementById('total-amount').textContent = total.toLocaleString() + 'đ';
            
            // Cập nhật thông báo miễn phí ship
            const shippingNotice = document.querySelector('.shipping-notice');
            if (shippingNotice) {
                if (subtotal >= 500000) {
                    shippingNotice.style.display = 'none';
                } else {
                    const remaining = 500000 - subtotal;
                    shippingNotice.innerHTML = `<i class="fas fa-info-circle"></i> Mua thêm ${remaining.toLocaleString()}đ để được miễn phí ship`;
                }
            }
        }
        
        // Cập nhật số lượng giỏ hàng trong header
        function updateCartCount() {
            fetch('../api/cart.php?action=count')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.count;
                        cartCount.style.display = data.count > 0 ? 'block' : 'none';
                    }
                }
            })
            .catch(error => {
                console.error('Error updating cart count:', error);
            });
        }
        
        // Hiển thị thông báo
        function showAlert(message, type = 'info') {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = `
                <div class="alert-content">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                    <span>${message}</span>
                </div>
            `;
            
            alert.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#2196F3'};
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 10000;
                transform: translateX(100%);
                transition: transform 0.3s ease;
            `;
            
            document.body.appendChild(alert);
            
            setTimeout(() => {
                alert.style.transform = 'translateX(0)';
            }, 100);
            
            setTimeout(() => {
                alert.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(alert);
                }, 300);
            }, 3000);
        }
    </script>
    
    <style>
        .cart-page {
            padding: var(--spacing-xl) 0;
        }
        
        .cart-page h1 {
            margin-bottom: var(--spacing-xl);
            color: var(--text-dark);
        }
        
        .cart-items {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-lg);
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto auto auto auto;
            gap: var(--spacing-lg);
            align-items: center;
            padding: var(--spacing-lg);
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            transition: all var(--transition-fast);
        }
        
        .cart-item:hover {
            box-shadow: var(--shadow-md);
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
        
        .item-info {
            min-width: 0;
        }
        
        .item-name {
            margin: 0 0 var(--spacing-sm) 0;
            font-size: var(--font-size-lg);
        }
        
        .item-name a {
            color: var(--text-dark);
            text-decoration: none;
        }
        
        .item-name a:hover {
            color: var(--cta-color);
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
        
        .item-price {
            text-align: center;
            min-width: 120px;
        }
        
        .current-price {
            display: block;
            font-size: var(--font-size-lg);
            font-weight: 600;
            color: var(--cta-color);
        }
        
        .old-price {
            display: block;
            font-size: var(--font-size-sm);
            color: var(--text-muted);
            text-decoration: line-through;
        }
        
        .item-quantity {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }
        
        .quantity-btn {
            width: 32px;
            height: 32px;
            border: 1px solid var(--primary-color);
            background: var(--white);
            color: var(--text-dark);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .quantity-btn:hover {
            background: var(--cta-color);
            color: var(--white);
            border-color: var(--cta-color);
        }
        
        .quantity-input {
            width: 60px;
            text-align: center;
            padding: var(--spacing-sm);
            border: 1px solid var(--primary-color);
            border-radius: var(--radius-sm);
        }
        
        .item-total {
            text-align: center;
            min-width: 120px;
        }
        
        .total-price {
            font-size: var(--font-size-lg);
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .item-actions {
            text-align: center;
        }
        
        .remove-btn {
            width: 36px;
            height: 36px;
            border: none;
            background: var(--error-color);
            color: var(--white);
            border-radius: var(--radius-full);
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .remove-btn:hover {
            background: #d32f2f;
            transform: scale(1.1);
        }
        
        .cart-actions {
            display: flex;
            justify-content: space-between;
            margin-top: var(--spacing-xl);
            padding-top: var(--spacing-lg);
            border-top: 1px solid var(--primary-color);
        }
        
        .cart-summary {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: var(--spacing-xl);
        }
        
        .cart-summary h3 {
            margin-bottom: var(--spacing-lg);
            color: var(--text-dark);
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--spacing-md);
            padding: var(--spacing-sm) 0;
        }
        
        .summary-row.total {
            border-top: 1px solid var(--primary-color);
            font-weight: 600;
            font-size: var(--font-size-lg);
            color: var(--text-dark);
        }
        
        .shipping-notice {
            background: var(--accent-color);
            color: var(--cta-color);
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius-sm);
            margin: var(--spacing-md) 0;
            font-size: var(--font-size-sm);
        }
        
        .payment-methods {
            margin-top: var(--spacing-xl);
            padding-top: var(--spacing-lg);
            border-top: 1px solid var(--primary-color);
        }
        
        .payment-methods h4 {
            margin-bottom: var(--spacing-md);
            color: var(--text-dark);
            font-size: var(--font-size-base);
        }
        
        .payment-icons {
            display: flex;
            gap: var(--spacing-md);
        }
        
        .payment-icons i {
            font-size: var(--font-size-xl);
            color: var(--text-light);
        }
        
        .empty-cart {
            text-align: center;
            padding: var(--spacing-3xl);
        }
        
        .empty-cart-icon {
            font-size: var(--font-size-3xl);
            color: var(--primary-color);
            margin-bottom: var(--spacing-lg);
        }
        
        .empty-cart h2 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-md);
        }
        
        .empty-cart p {
            color: var(--text-light);
            margin-bottom: var(--spacing-xl);
        }
        
        @media (max-width: 768px) {
            .col-8, .col-4 {
                flex: 0 0 100%;
            }
            
            .cart-item {
                grid-template-columns: 80px 1fr;
                gap: var(--spacing-md);
            }
            
            .item-price,
            .item-quantity,
            .item-total,
            .item-actions {
                grid-column: 1 / -1;
                justify-self: start;
                margin-top: var(--spacing-sm);
            }
            
            .cart-actions {
                flex-direction: column;
                gap: var(--spacing-md);
            }
        }
    </style>
</body>
</html>
