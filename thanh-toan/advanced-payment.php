<?php
/**
 * Advanced Payment System
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/auth-middleware.php';
require_once '../config/database.php';
require_once '../config/wallet.php';
require_once '../config/loyalty.php';
require_once '../config/coupon.php';
require_once '../config/installment.php';

// Kiểm tra đăng nhập
$user = AuthMiddleware::requireLogin();

// Lấy thông tin user
$user_id = $user['id'];

// Lấy thông tin ví và điểm thưởng
$wallet = WalletSystem::getWallet($user_id);
$loyalty = LoyaltySystem::getLoyaltyInfo($user_id);
$member_level = LoyaltySystem::getMemberLevel($user_id);
$member_benefits = LoyaltySystem::getMemberBenefits($member_level);

// Lấy danh sách coupon có sẵn
$available_coupons = CouponSystem::getAvailableCoupons($user_id);

// Lấy gói trả góp
$installment_plans = InstallmentSystem::getInstallmentPlans();

// Lấy giỏ hàng
$cart_items = [];
$cart_total = 0;

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("
        SELECT c.*, p.name, p.price, p.image, b.name as brand_name
        FROM cart c
        JOIN products p ON c.product_id = p.id
        LEFT JOIN brands b ON p.brand_id = b.id
        WHERE c.user_id = ? AND c.status = 'active'
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
    
    $cart_total = array_sum(array_map(function($item) {
        return $item['quantity'] * $item['price'];
    }, $cart_items));
    
} catch (Exception $e) {
    $cart_items = [];
    $cart_total = 0;
}

// Tính toán ưu đãi
$discount_amount = 0;
$loyalty_discount = 0;
$coupon_discount = 0;
$free_shipping = false;

// Áp dụng ưu đãi thành viên
if ($member_benefits['discount'] > 0) {
    $loyalty_discount = ($cart_total * $member_benefits['discount']) / 100;
}

// Miễn phí ship cho thành viên
if ($member_benefits['free_shipping']) {
    $free_shipping = true;
}

// Xử lý áp dụng coupon
if ($_POST['action'] === 'apply_coupon') {
    $coupon_code = trim($_POST['coupon_code']);
    
    if ($coupon_code) {
        $coupon_result = CouponSystem::validateCoupon($coupon_code, $user_id, $cart_total);
        
        if ($coupon_result['valid']) {
            $coupon_discount = CouponSystem::calculateDiscount($coupon_result['coupon'], $cart_total);
            $_SESSION['applied_coupon'] = $coupon_result['coupon'];
        } else {
            $_SESSION['coupon_error'] = $coupon_result['message'];
        }
    }
}

// Xử lý sử dụng điểm thưởng
if ($_POST['action'] === 'use_loyalty_points') {
    $points_to_use = intval($_POST['loyalty_points']);
    
    if ($points_to_use > 0 && $loyalty && $points_to_use <= $loyalty['points']) {
        $loyalty_discount = LoyaltySystem::calculateValue($points_to_use);
        $_SESSION['loyalty_points_used'] = $points_to_use;
    }
}

$total_discount = $loyalty_discount + $coupon_discount;
$final_amount = max(0, $cart_total - $total_discount);
$shipping_fee = $free_shipping ? 0 : 50000; // 50k phí ship
$total_amount = $final_amount + $shipping_fee;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán nâng cao - Linh2Store</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .payment-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .payment-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            margin-top: 20px;
        }
        
        .payment-methods {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .payment-method {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .payment-method:hover {
            border-color: #EC407A;
            background: #fce4ec;
        }
        
        .payment-method.selected {
            border-color: #EC407A;
            background: #fce4ec;
        }
        
        .payment-method input[type="radio"] {
            margin-right: 15px;
        }
        
        .payment-method i {
            font-size: 24px;
            margin-right: 15px;
            width: 30px;
            text-align: center;
        }
        
        .wallet-balance {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .loyalty-points {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .member-benefits {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .coupon-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .installment-section {
            background: #fff3cd;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .order-summary {
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
            margin-bottom: 10px;
            padding: 8px 0;
        }
        
        .summary-total {
            border-top: 2px solid #EC407A;
            padding-top: 15px;
            margin-top: 15px;
            font-weight: bold;
            font-size: 18px;
        }
        
        .btn-apply {
            background: #EC407A;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-apply:hover {
            background: #d81b60;
        }
        
        .error-message {
            color: #f44336;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .success-message {
            color: #4caf50;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="../" class="logo">
                    <i class="fas fa-gem"></i>
                    <span>Linh2Store</span>
                </a>
                <nav class="nav">
                    <a href="../">Trang chủ</a>
                    <a href="../san-pham/">Sản phẩm</a>
                    <a href="../user/">Tài khoản</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="payment-container">
        <h1><i class="fas fa-credit-card"></i> Thanh toán nâng cao</h1>
        
        <div class="payment-grid">
            <!-- Payment Methods -->
            <div class="payment-methods">
                <h2><i class="fas fa-wallet"></i> Thông tin tài khoản</h2>
                
                <!-- Wallet Balance -->
                <div class="wallet-balance">
                    <h3><i class="fas fa-wallet"></i> Số dư ví</h3>
                    <p><strong><?php echo number_format($wallet['balance'] ?? 0); ?>đ</strong></p>
                    <a href="../user/wallet.php" class="btn-apply">Nạp tiền vào ví</a>
                </div>
                
                <!-- Loyalty Points -->
                <div class="loyalty-points">
                    <h3><i class="fas fa-star"></i> Điểm thưởng</h3>
                    <p><strong><?php echo number_format($loyalty['points'] ?? 0); ?> điểm</strong></p>
                    <p>Giá trị: <strong><?php echo number_format(LoyaltySystem::calculateValue($loyalty['points'] ?? 0)); ?>đ</strong></p>
                    
                    <form method="POST" style="margin-top: 10px;">
                        <input type="hidden" name="action" value="use_loyalty_points">
                        <input type="number" name="loyalty_points" placeholder="Số điểm sử dụng" min="0" max="<?php echo $loyalty['points'] ?? 0; ?>">
                        <button type="submit" class="btn-apply">Sử dụng điểm</button>
                    </form>
                </div>
                
                <!-- Member Benefits -->
                <div class="member-benefits">
                    <h3><i class="fas fa-crown"></i> <?php echo $member_benefits['name']; ?></h3>
                    <ul>
                        <li>Giảm giá: <?php echo $member_benefits['discount']; ?>%</li>
                        <li>Miễn phí ship: <?php echo $member_benefits['free_shipping'] ? 'Có' : 'Không'; ?></li>
                        <li>Hỗ trợ ưu tiên: <?php echo $member_benefits['priority_support'] ? 'Có' : 'Không'; ?></li>
                    </ul>
                </div>
                
                <!-- Coupon Section -->
                <div class="coupon-section">
                    <h3><i class="fas fa-ticket-alt"></i> Mã giảm giá</h3>
                    
                    <form method="POST" style="display: flex; gap: 10px; margin-bottom: 15px;">
                        <input type="hidden" name="action" value="apply_coupon">
                        <input type="text" name="coupon_code" placeholder="Nhập mã giảm giá" style="flex: 1;">
                        <button type="submit" class="btn-apply">Áp dụng</button>
                    </form>
                    
                    <?php if (isset($_SESSION['coupon_error'])): ?>
                        <div class="error-message"><?php echo $_SESSION['coupon_error']; unset($_SESSION['coupon_error']); ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['applied_coupon'])): ?>
                        <div class="success-message">
                            Đã áp dụng mã: <?php echo $_SESSION['applied_coupon']['name']; ?>
                        </div>
                    <?php endif; ?>
                    
                    <h4>Mã giảm giá có sẵn:</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                        <?php foreach ($available_coupons as $coupon): ?>
                            <div style="border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                <strong><?php echo $coupon['code']; ?></strong><br>
                                <small><?php echo $coupon['name']; ?></small><br>
                                <small>Giảm: <?php echo $coupon['type'] === 'percentage' ? $coupon['value'] . '%' : number_format($coupon['value']) . 'đ'; ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Installment Section -->
                <div class="installment-section">
                    <h3><i class="fas fa-calendar-alt"></i> Trả góp</h3>
                    
                    <?php if (InstallmentSystem::checkEligibility($user_id, $cart_total)): ?>
                        <p>Bạn đủ điều kiện trả góp cho đơn hàng này!</p>
                        
                        <h4>Gói trả góp phù hợp:</h4>
                        <?php 
                        $suitable_plans = InstallmentSystem::getSuitablePlans($cart_total);
                        foreach ($suitable_plans as $plan): 
                            $calculation = InstallmentSystem::calculateInstallment($cart_total, $plan['id']);
                        ?>
                            <div style="border: 1px solid #ddd; padding: 15px; border-radius: 8px; margin-bottom: 10px;">
                                <h5><?php echo $plan['name']; ?></h5>
                                <p>Số tiền gốc: <?php echo number_format($cart_total); ?>đ</p>
                                <p>Lãi suất: <?php echo $plan['interest_rate']; ?>%</p>
                                <p>Tổng tiền: <?php echo number_format($calculation['total_amount']); ?>đ</p>
                                <p>Trả hàng tháng: <?php echo number_format($calculation['monthly_payment']); ?>đ</p>
                                <p>Kỳ hạn: <?php echo $plan['months']; ?> tháng</p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Đơn hàng chưa đủ điều kiện trả góp (tối thiểu 1,000,000đ)</p>
                    <?php endif; ?>
                </div>
                
                <!-- Payment Methods -->
                <h2><i class="fas fa-credit-card"></i> Phương thức thanh toán</h2>
                
                <div class="payment-method">
                    <input type="radio" name="payment_method" value="wallet" id="wallet">
                    <label for="wallet">
                        <i class="fas fa-wallet" style="color: #4CAF50;"></i>
                        <div>
                            <h4>Thanh toán bằng ví</h4>
                            <p>Số dư: <?php echo number_format($wallet['balance'] ?? 0); ?>đ</p>
                        </div>
                    </label>
                </div>
                
                <div class="payment-method">
                    <input type="radio" name="payment_method" value="cod" id="cod" checked>
                    <label for="cod">
                        <i class="fas fa-money-bill-wave" style="color: #FF9800;"></i>
                        <div>
                            <h4>Thanh toán khi nhận hàng</h4>
                            <p>COD - Trả tiền mặt</p>
                        </div>
                    </label>
                </div>
                
                <div class="payment-method">
                    <input type="radio" name="payment_method" value="bank_transfer" id="bank">
                    <label for="bank">
                        <i class="fas fa-university" style="color: #2196F3;"></i>
                        <div>
                            <h4>Chuyển khoản ngân hàng</h4>
                            <p>Chuyển khoản trước</p>
                        </div>
                    </label>
                </div>
                
                <div class="payment-method">
                    <input type="radio" name="payment_method" value="momo" id="momo">
                    <label for="momo">
                        <i class="fas fa-mobile-alt" style="color: #E91E63;"></i>
                        <div>
                            <h4>Ví MoMo</h4>
                            <p>Thanh toán qua MoMo</p>
                        </div>
                    </label>
                </div>
                
                <div class="payment-method">
                    <input type="radio" name="payment_method" value="vnpay" id="vnpay">
                    <label for="vnpay">
                        <i class="fas fa-credit-card" style="color: #9C27B0;"></i>
                        <div>
                            <h4>VNPay</h4>
                            <p>Thẻ ATM/Internet Banking</p>
                        </div>
                    </label>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="order-summary">
                <h2><i class="fas fa-shopping-cart"></i> Tóm tắt đơn hàng</h2>
                
                <div class="summary-item">
                    <span>Tạm tính:</span>
                    <span><?php echo number_format($cart_total); ?>đ</span>
                </div>
                
                <?php if ($loyalty_discount > 0): ?>
                <div class="summary-item" style="color: #4caf50;">
                    <span>Giảm giá thành viên (<?php echo $member_benefits['discount']; ?>%):</span>
                    <span>-<?php echo number_format($loyalty_discount); ?>đ</span>
                </div>
                <?php endif; ?>
                
                <?php if ($coupon_discount > 0): ?>
                <div class="summary-item" style="color: #4caf50;">
                    <span>Mã giảm giá:</span>
                    <span>-<?php echo number_format($coupon_discount); ?>đ</span>
                </div>
                <?php endif; ?>
                
                <div class="summary-item">
                    <span>Phí vận chuyển:</span>
                    <span><?php echo $free_shipping ? 'Miễn phí' : number_format($shipping_fee) . 'đ'; ?></span>
                </div>
                
                <div class="summary-total">
                    <span>Tổng cộng:</span>
                    <span><?php echo number_format($total_amount); ?>đ</span>
                </div>
                
                <button class="btn btn-primary" style="width: 100%; margin-top: 20px; padding: 15px;">
                    <i class="fas fa-check"></i> Hoàn tất đơn hàng
                </button>
            </div>
        </div>
    </div>

    <script>
        // Payment method selection
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
                this.classList.add('selected');
            });
        });
        
        // Set initial selected method
        document.querySelector('input[name="payment_method"]:checked').closest('.payment-method').classList.add('selected');
    </script>
</body>
</html>
