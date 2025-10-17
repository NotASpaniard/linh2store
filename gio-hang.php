<?php
/**
 * Trang giỏ hàng
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/auth-middleware.php';
require_once 'config/database.php';
require_once 'config/image-helper.php';

// Kiểm tra đăng nhập
$user = AuthMiddleware::requireLogin();
$user_id = $user['id'];

// Lấy giỏ hàng
$cart_items = [];
$cart_total = 0;
$cart_count = 0;

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("
        SELECT c.*, p.name, p.price, p.image, p.stock_quantity, b.name as brand_name
        FROM cart c
        JOIN products p ON c.product_id = p.id
        LEFT JOIN brands b ON p.brand_id = b.id
        WHERE c.user_id = ? AND c.status = 'active'
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
    
    $cart_total = array_sum(array_map(function($item) {
        return $item['quantity'] * $item['price'];
    }, $cart_items));
    
    $cart_count = count($cart_items);
    
} catch (Exception $e) {
    $cart_items = [];
    $cart_total = 0;
    $cart_count = 0;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - Linh2Store</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .cart-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .cart-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .cart-items {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 20px;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .item-brand {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .item-price {
            font-size: 16px;
            font-weight: bold;
            color: #EC407A;
        }
        
        .item-controls {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .quantity-btn:hover {
            background: #f5f5f5;
        }
        
        .quantity-input {
            width: 50px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
        
        .remove-btn {
            background: #f44336;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .remove-btn:hover {
            background: #d32f2f;
        }
        
        .cart-summary {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 10px 0;
        }
        
        .summary-total {
            border-top: 2px solid #EC407A;
            padding-top: 15px;
            margin-top: 15px;
            font-weight: bold;
            font-size: 18px;
        }
        
        .checkout-btn {
            background: #EC407A;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
        }
        
        .checkout-btn:hover {
            background: #d81b60;
        }
        
        .empty-cart {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        
        .empty-cart i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .cart-content {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .item-image {
                margin-bottom: 15px;
            }
            
            .item-controls {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="/" class="logo">
                    <i class="fas fa-gem"></i>
                    <span>Linh2Store</span>
                </a>
                <nav class="nav">
                    <a href="/">Trang chủ</a>
                    <a href="san-pham/">Sản phẩm</a>
                    <a href="user/">Tài khoản</a>
                </nav>
                <div class="header-actions">
                    <a href="api/cart.php" class="cart-icon" title="Giỏ hàng">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="cart-container">
        <div class="cart-header">
            <h1><i class="fas fa-shopping-cart"></i> Giỏ hàng của bạn</h1>
            <p>Kiểm tra và thanh toán các sản phẩm đã chọn</p>
        </div>
        
        <div class="cart-content">
            <!-- Cart Items -->
            <div class="cart-items">
                <?php if (empty($cart_items)): ?>
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>Giỏ hàng trống</h3>
                        <p>Bạn chưa có sản phẩm nào trong giỏ hàng</p>
                        <a href="san-pham/" class="btn btn-primary">Tiếp tục mua sắm</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item" data-item-id="<?php echo $item['id']; ?>">
                            <img src="<?php echo getImageUrl($item['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="item-image">
                            
                            <div class="item-info">
                                <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="item-brand"><?php echo htmlspecialchars($item['brand_name']); ?></div>
                                <div class="item-price"><?php echo number_format($item['price']); ?>đ</div>
                            </div>
                            
                            <div class="item-controls">
                                <div class="quantity-controls">
                                    <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" 
                                           class="quantity-input" 
                                           value="<?php echo $item['quantity']; ?>" 
                                           min="1" 
                                           max="<?php echo $item['stock_quantity']; ?>"
                                           onchange="updateQuantity(<?php echo $item['id']; ?>, 0, this.value)">
                                    <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                
                                <button class="remove-btn" onclick="removeItem(<?php echo $item['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Cart Summary -->
            <div class="cart-summary">
                <h3>Tóm tắt đơn hàng</h3>
                
                <div class="summary-item">
                    <span>Tạm tính:</span>
                    <span id="subtotal"><?php echo number_format($cart_total); ?>đ</span>
                </div>
                
                <div class="summary-item">
                    <span>Phí vận chuyển:</span>
                    <span>50,000đ</span>
                </div>
                
                <div class="summary-total">
                    <span>Tổng cộng:</span>
                    <span id="total"><?php echo number_format($cart_total + 50000); ?>đ</span>
                </div>
                
                <button class="checkout-btn" onclick="proceedToCheckout()">
                    <i class="fas fa-credit-card"></i> Thanh toán
                </button>
            </div>
        </div>
    </div>

    <script>
        function updateQuantity(itemId, change, newValue = null) {
            const quantityInput = document.querySelector(`[data-item-id="${itemId}"] .quantity-input`);
            let newQuantity = newValue ? parseInt(newValue) : parseInt(quantityInput.value) + change;
            
            if (newQuantity < 1) newQuantity = 1;
            
            // Gửi request cập nhật
            fetch('api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update_quantity',
                    item_id: itemId,
                    quantity: newQuantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    quantityInput.value = newQuantity;
                    updateCartSummary();
                } else {
                    alert('Có lỗi xảy ra: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi cập nhật giỏ hàng');
            });
        }
        
        function removeItem(itemId) {
            if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                fetch('api/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'remove_item',
                        item_id: itemId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector(`[data-item-id="${itemId}"]`).remove();
                        updateCartSummary();
                    } else {
                        alert('Có lỗi xảy ra: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi xóa sản phẩm');
                });
            }
        }
        
        function updateCartSummary() {
            // Tính lại tổng tiền
            let subtotal = 0;
            document.querySelectorAll('.cart-item').forEach(item => {
                const price = parseFloat(item.querySelector('.item-price').textContent.replace(/[^\d]/g, ''));
                const quantity = parseInt(item.querySelector('.quantity-input').value);
                subtotal += price * quantity;
            });
            
            document.getElementById('subtotal').textContent = subtotal.toLocaleString() + 'đ';
            document.getElementById('total').textContent = (subtotal + 50000).toLocaleString() + 'đ';
        }
        
        function proceedToCheckout() {
            window.location.href = 'thanh-toan/';
        }
    </script>
</body>
</html>
