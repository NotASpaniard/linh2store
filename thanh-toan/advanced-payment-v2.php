<?php
/**
 * Advanced Payment System V2 - Clean Layout
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
$user_id = $user['id'];

// Lấy thông tin user
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
if (isset($_POST['action']) && $_POST['action'] === 'apply_coupon') {
    $coupon_code = trim($_POST['coupon_code']);
    
    if ($coupon_code) {
        $coupon_result = CouponSystem::validateCoupon($coupon_code, $user_id, $cart_total);
        
        if ($coupon_result['valid']) {
            $coupon_discount = CouponSystem::calculateDiscount($coupon_result['coupon'], $cart_total);
            $applied_coupon = $coupon_result['coupon'];
        } else {
            $coupon_error = $coupon_result['message'];
        }
    }
}

// Xử lý sử dụng điểm thưởng
if (isset($_POST['action']) && $_POST['action'] === 'use_loyalty_points') {
    $points_to_use = intval($_POST['loyalty_points']);
    
    if ($points_to_use > 0 && $loyalty && $points_to_use <= $loyalty['points']) {
        $loyalty_discount = LoyaltySystem::calculateValue($points_to_use);
        $loyalty_points_used = $points_to_use;
    }
}

$total_discount = $loyalty_discount + $coupon_discount;
$final_amount = max(0, $cart_total - $total_discount);
$shipping_fee = $free_shipping ? 0 : 50000;
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
        
        .payment-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .payment-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .payment-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .section-title {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #EC407A;
        }
        
        .section-title i {
            margin-right: 10px;
            color: #EC407A;
        }
        
        .account-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #EC407A;
        }
        
        .info-card.wallet {
            border-left-color: #4CAF50;
        }
        
        .info-card.loyalty {
            border-left-color: #FF9800;
        }
        
        .info-card.member {
            border-left-color: #9C27B0;
        }
        
        .info-card h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .info-card .amount {
            font-size: 24px;
            font-weight: bold;
            color: #EC407A;
            margin: 10px 0;
        }
        
        .info-card .points {
            font-size: 20px;
            font-weight: bold;
            color: #FF9800;
            margin: 10px 0;
        }
        
        .coupon-section {
            background: #fff3cd;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .coupon-form {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .coupon-form input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .btn-apply {
            background: #EC407A;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-apply:hover {
            background: #d81b60;
        }
        
        .available-coupons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }
        
        .coupon-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }
        
        .coupon-card .code {
            font-weight: bold;
            color: #EC407A;
            font-size: 18px;
        }
        
        .coupon-card .name {
            font-size: 14px;
            color: #666;
            margin: 5px 0;
        }
        
        .coupon-card .discount {
            font-size: 12px;
            color: #4CAF50;
        }
        
        .installment-section {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 8px;
        }
        
        .installment-plans {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .plan-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        
        .plan-card h4 {
            margin: 0 0 10px 0;
            color: #EC407A;
        }
        
        .plan-details {
            font-size: 14px;
            color: #666;
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .payment-method {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
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
        
        .btn-complete {
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
        
        .btn-complete:hover {
            background: #d81b60;
        }
        
        @media (max-width: 768px) {
            .payment-content {
                grid-template-columns: 1fr;
            }
            
            .account-info {
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
        <div class="payment-header">
            <h1><i class="fas fa-credit-card"></i> Thanh toán nâng cao</h1>
            <p>Trải nghiệm thanh toán hiện đại với ví điện tử, điểm thưởng và trả góp</p>
        </div>
        
        <div class="payment-content">
            <!-- Left Column -->
            <div>
                <!-- Account Information -->
                <div class="payment-section">
                    <div class="section-title">
                        <i class="fas fa-user-circle"></i>
                        <h2>Thông tin tài khoản</h2>
                    </div>
                    
                    <div class="account-info">
                        <!-- Wallet Card -->
                        <div class="info-card wallet">
                            <h3><i class="fas fa-wallet"></i> Ví điện tử</h3>
                            <div class="amount"><?php echo number_format($wallet['balance'] ?? 0); ?>đ</div>
                            <a href="../user/wallet.php" class="btn-apply">Nạp tiền</a>
                        </div>
                        
                        <!-- Loyalty Points Card -->
                        <div class="info-card loyalty">
                            <h3><i class="fas fa-star"></i> Điểm thưởng</h3>
                            <div class="points"><?php echo number_format($loyalty['points'] ?? 0); ?> điểm</div>
                            <div>Giá trị: <?php echo number_format(LoyaltySystem::calculateValue($loyalty['points'] ?? 0)); ?>đ</div>
                            
                            <form method="POST" style="margin-top: 10px;">
                                <input type="hidden" name="action" value="use_loyalty_points">
                                <input type="number" name="loyalty_points" placeholder="Số điểm sử dụng" min="0" max="<?php echo $loyalty['points'] ?? 0; ?>" style="width: 100%; padding: 5px; margin: 5px 0;">
                                <button type="submit" class="btn-apply" style="width: 100%;">Sử dụng điểm</button>
                            </form>
                        </div>
                        
                        <!-- Member Benefits Card -->
                        <div class="info-card member">
                            <h3><i class="fas fa-crown"></i> <?php echo $member_benefits['name']; ?></h3>
                            <div>Giảm giá: <?php echo $member_benefits['discount']; ?>%</div>
                            <div>Miễn phí ship: <?php echo $member_benefits['free_shipping'] ? 'Có' : 'Không'; ?></div>
                            <div>Hỗ trợ ưu tiên: <?php echo $member_benefits['priority_support'] ? 'Có' : 'Không'; ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Coupon Section -->
                <div class="payment-section">
                    <div class="section-title">
                        <i class="fas fa-ticket-alt"></i>
                        <h2>Mã giảm giá</h2>
                    </div>
                    
                    <div class="coupon-section">
                        <form method="POST" class="coupon-form">
                            <input type="hidden" name="action" value="apply_coupon">
                            <input type="text" name="coupon_code" placeholder="Nhập mã giảm giá">
                            <button type="submit" class="btn-apply">Áp dụng</button>
                        </form>
                        
                        <?php if (isset($coupon_error)): ?>
                            <div class="error-message"><?php echo $coupon_error; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($applied_coupon)): ?>
                            <div class="success-message">
                                Đã áp dụng mã: <?php echo $applied_coupon['name']; ?>
                            </div>
                        <?php endif; ?>
                        
                        <h4>Mã giảm giá có sẵn:</h4>
                        <div class="available-coupons">
                            <?php foreach ($available_coupons as $coupon): ?>
                                <div class="coupon-card">
                                    <div class="code"><?php echo $coupon['code']; ?></div>
                                    <div class="name"><?php echo $coupon['name']; ?></div>
                                    <div class="discount">
                                        Giảm: <?php echo $coupon['type'] === 'percentage' ? $coupon['value'] . '%' : number_format($coupon['value']) . 'đ'; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Installment Section -->
                <div class="payment-section">
                    <div class="section-title">
                        <i class="fas fa-calendar-alt"></i>
                        <h2>Trả góp</h2>
                    </div>
                    
                    <div class="installment-section">
                        <?php if (InstallmentSystem::checkEligibility($user_id, $cart_total)): ?>
                            <p>Bạn đủ điều kiện trả góp cho đơn hàng này!</p>
                            
                            <h4>Gói trả góp phù hợp:</h4>
                            <div class="installment-plans">
                                <?php 
                                $suitable_plans = InstallmentSystem::getSuitablePlans($cart_total);
                                foreach ($suitable_plans as $plan): 
                                    $calculation = InstallmentSystem::calculateInstallment($cart_total, $plan['id']);
                                ?>
                                    <div class="plan-card">
                                        <h4><?php echo $plan['name']; ?></h4>
                                        <div class="plan-details">
                                            <p>Số tiền gốc: <?php echo number_format($cart_total); ?>đ</p>
                                            <p>Lãi suất: <?php echo $plan['interest_rate']; ?>%</p>
                                            <p>Tổng tiền: <?php echo number_format($calculation['total_amount']); ?>đ</p>
                                            <p>Trả hàng tháng: <?php echo number_format($calculation['monthly_payment']); ?>đ</p>
                                            <p>Kỳ hạn: <?php echo $plan['months']; ?> tháng</p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>Đơn hàng chưa đủ điều kiện trả góp (tối thiểu 1,000,000đ)</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Payment Methods -->
                <div class="payment-section">
                    <div class="section-title">
                        <i class="fas fa-credit-card"></i>
                        <h2>Phương thức thanh toán</h2>
                    </div>
                    
                    <div class="payment-methods">
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
                </div>
            </div>
            
            <!-- Right Column - Order Summary -->
            <div>
                <div class="order-summary">
                    <div class="section-title">
                        <i class="fas fa-shopping-cart"></i>
                        <h2>Tóm tắt đơn hàng</h2>
                    </div>
                    
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
                    
                    <button class="btn-complete">
                        <i class="fas fa-check"></i> Hoàn tất đơn hàng
                    </button>
                </div>
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
