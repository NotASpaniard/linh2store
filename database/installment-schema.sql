-- Installment Payment System
-- Linh2Store - Website bán son môi & mỹ phẩm cao cấp

-- Bảng lưu thông tin trả góp
CREATE TABLE IF NOT EXISTS installment_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    months INT NOT NULL,
    interest_rate DECIMAL(5,2) DEFAULT 0.00,
    min_amount DECIMAL(10,2) DEFAULT 0.00,
    max_amount DECIMAL(10,2) DEFAULT 999999999.99,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng lưu thông tin đơn hàng trả góp
CREATE TABLE IF NOT EXISTS installment_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    installment_plan_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    monthly_payment DECIMAL(10,2) NOT NULL,
    interest_amount DECIMAL(10,2) DEFAULT 0.00,
    remaining_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'active', 'completed', 'defaulted') DEFAULT 'pending',
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (installment_plan_id) REFERENCES installment_plans(id)
);

-- Bảng lưu lịch sử thanh toán trả góp
CREATE TABLE IF NOT EXISTS installment_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    installment_order_id INT NOT NULL,
    payment_number INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    due_date DATE NOT NULL,
    paid_date DATE NULL,
    status ENUM('pending', 'paid', 'overdue') DEFAULT 'pending',
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (installment_order_id) REFERENCES installment_orders(id) ON DELETE CASCADE
);

-- Bảng ví điện tử
CREATE TABLE IF NOT EXISTS user_wallets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    balance DECIMAL(10,2) DEFAULT 0.00,
    frozen_balance DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('active', 'frozen', 'closed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_wallet (user_id)
);

-- Bảng lịch sử giao dịch ví
CREATE TABLE IF NOT EXISTS wallet_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wallet_id INT NOT NULL,
    type ENUM('deposit', 'withdraw', 'payment', 'refund', 'bonus', 'penalty') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    balance_before DECIMAL(10,2) NOT NULL,
    balance_after DECIMAL(10,2) NOT NULL,
    description TEXT,
    reference_id VARCHAR(100),
    status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wallet_id) REFERENCES user_wallets(id) ON DELETE CASCADE
);

-- Bảng hệ thống điểm thưởng
CREATE TABLE IF NOT EXISTS loyalty_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    points INT DEFAULT 0,
    total_earned INT DEFAULT 0,
    total_redeemed INT DEFAULT 0,
    status ENUM('active', 'frozen', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_points (user_id)
);

-- Bảng lịch sử điểm thưởng
CREATE TABLE IF NOT EXISTS loyalty_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('earn', 'redeem', 'expire', 'adjust') NOT NULL,
    points INT NOT NULL,
    balance_before INT NOT NULL,
    balance_after INT NOT NULL,
    description TEXT,
    reference_id VARCHAR(100),
    expires_at DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng mã giảm giá
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    type ENUM('percentage', 'fixed', 'free_shipping') NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    min_order_amount DECIMAL(10,2) DEFAULT 0.00,
    max_discount DECIMAL(10,2) DEFAULT NULL,
    usage_limit INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    user_limit INT DEFAULT 1,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng sử dụng mã giảm giá
CREATE TABLE IF NOT EXISTS coupon_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    discount_amount DECIMAL(10,2) NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    UNIQUE KEY unique_coupon_user_order (coupon_id, user_id, order_id)
);

-- Thêm cột vào bảng orders
ALTER TABLE orders ADD COLUMN IF NOT EXISTS installment_order_id INT NULL AFTER payment_method;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS loyalty_points_earned INT DEFAULT 0 AFTER installment_order_id;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS loyalty_points_used INT DEFAULT 0 AFTER loyalty_points_earned;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS coupon_id INT NULL AFTER loyalty_points_used;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(10,2) DEFAULT 0.00 AFTER coupon_id;

-- Thêm foreign keys
ALTER TABLE orders ADD FOREIGN KEY (installment_order_id) REFERENCES installment_orders(id);
ALTER TABLE orders ADD FOREIGN KEY (coupon_id) REFERENCES coupons(id);

-- Insert dữ liệu mẫu
INSERT INTO installment_plans (name, months, interest_rate, min_amount, max_amount) VALUES
('Trả góp 0% - 3 tháng', 3, 0.00, 1000000, 5000000),
('Trả góp 0% - 6 tháng', 6, 0.00, 2000000, 10000000),
('Trả góp 0% - 12 tháng', 12, 0.00, 5000000, 20000000),
('Trả góp 1.5% - 6 tháng', 6, 1.50, 1000000, 50000000),
('Trả góp 2% - 12 tháng', 12, 2.00, 2000000, 100000000);

INSERT INTO coupons (code, name, description, type, value, min_order_amount, usage_limit, start_date, end_date) VALUES
('WELCOME10', 'Chào mừng 10%', 'Giảm 10% cho khách hàng mới', 'percentage', 10.00, 500000, 100, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
('FREESHIP', 'Miễn phí ship', 'Miễn phí vận chuyển', 'free_shipping', 0.00, 300000, 1000, NOW(), DATE_ADD(NOW(), INTERVAL 60 DAY)),
('SAVE50K', 'Tiết kiệm 50k', 'Giảm 50,000đ cho đơn hàng từ 1 triệu', 'fixed', 50000.00, 1000000, 200, NOW(), DATE_ADD(NOW(), INTERVAL 45 DAY)),
('VIP20', 'VIP 20%', 'Giảm 20% cho khách hàng VIP', 'percentage', 20.00, 2000000, 50, NOW(), DATE_ADD(NOW(), INTERVAL 90 DAY));
