-- Advanced E-commerce Features Schema
-- Linh2Store - Website bán son môi & mỹ phẩm cao cấp

-- Bảng biến thể sản phẩm (màu sắc, kích thước, v.v.)
CREATE TABLE IF NOT EXISTS product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    variant_name VARCHAR(100) NOT NULL,
    variant_type ENUM('color', 'size', 'finish', 'shade', 'texture') NOT NULL,
    variant_value VARCHAR(100) NOT NULL,
    variant_code VARCHAR(50),
    price_adjustment DECIMAL(10,2) DEFAULT 0.00,
    stock_quantity INT DEFAULT 0,
    sku VARCHAR(100),
    image_url VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_variant (product_id, variant_type, variant_value)
);

-- Bảng combo sản phẩm
CREATE TABLE IF NOT EXISTS product_bundles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    bundle_type ENUM('fixed', 'customizable', 'seasonal') DEFAULT 'fixed',
    original_price DECIMAL(10,2) NOT NULL,
    bundle_price DECIMAL(10,2) NOT NULL,
    discount_percentage DECIMAL(5,2) DEFAULT 0.00,
    image_url VARCHAR(255),
    status ENUM('active', 'inactive', 'draft') DEFAULT 'active',
    start_date DATETIME,
    end_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng sản phẩm trong combo
CREATE TABLE IF NOT EXISTS bundle_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bundle_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    is_required BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bundle_id) REFERENCES product_bundles(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_bundle_product (bundle_id, product_id)
);

-- Bảng gợi ý sản phẩm liên quan
CREATE TABLE IF NOT EXISTS product_relations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    related_product_id INT NOT NULL,
    relation_type ENUM('cross_sell', 'upsell', 'accessory', 'alternative', 'complement') NOT NULL,
    priority INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (related_product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_relation (product_id, related_product_id, relation_type)
);

-- Bảng sản phẩm đã xem gần đây
CREATE TABLE IF NOT EXISTS recently_viewed (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Bảng so sánh sản phẩm
CREATE TABLE IF NOT EXISTS product_comparisons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product_comparison (user_id, product_id)
);

-- Bảng wishlist nâng cao
CREATE TABLE IF NOT EXISTS advanced_wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    variant_id INT NULL,
    notes TEXT,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_product_variant (user_id, product_id, variant_id)
);

-- Bảng đánh giá sản phẩm nâng cao
CREATE TABLE IF NOT EXISTS product_reviews_advanced (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(200),
    review_text TEXT,
    pros TEXT,
    cons TEXT,
    verified_purchase BOOLEAN DEFAULT FALSE,
    helpful_count INT DEFAULT 0,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
);

-- Bảng hình ảnh đánh giá
CREATE TABLE IF NOT EXISTS review_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    alt_text VARCHAR(200),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (review_id) REFERENCES product_reviews_advanced(id) ON DELETE CASCADE
);

-- Bảng lượt thích đánh giá
CREATE TABLE IF NOT EXISTS review_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (review_id) REFERENCES product_reviews_advanced(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_review_like (user_id, review_id)
);

-- Bảng tìm kiếm nâng cao
CREATE TABLE IF NOT EXISTS search_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    search_query VARCHAR(255) NOT NULL,
    filters JSON,
    results_count INT DEFAULT 0,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Bảng gợi ý sản phẩm AI
CREATE TABLE IF NOT EXISTS ai_recommendations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    recommendation_type ENUM('collaborative', 'content_based', 'hybrid', 'trending', 'similar') NOT NULL,
    confidence_score DECIMAL(3,2) DEFAULT 0.00,
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product_recommendation (user_id, product_id, recommendation_type)
);

-- Thêm cột vào bảng products
ALTER TABLE products ADD COLUMN IF NOT EXISTS has_variants BOOLEAN DEFAULT FALSE AFTER status;
ALTER TABLE products ADD COLUMN IF NOT EXISTS is_bundle BOOLEAN DEFAULT FALSE AFTER has_variants;
ALTER TABLE products ADD COLUMN IF NOT EXISTS comparison_enabled BOOLEAN DEFAULT TRUE AFTER is_bundle;
ALTER TABLE products ADD COLUMN IF NOT EXISTS review_count INT DEFAULT 0 AFTER comparison_enabled;
ALTER TABLE products ADD COLUMN IF NOT EXISTS average_rating DECIMAL(3,2) DEFAULT 0.00 AFTER review_count;

-- Insert dữ liệu mẫu cho product variants
INSERT INTO product_variants (product_id, variant_name, variant_type, variant_value, variant_code, price_adjustment, stock_quantity, sku) VALUES
(1, 'Màu đỏ', 'color', 'Red', 'RED001', 0.00, 50, 'LIPSTICK-RED-001'),
(1, 'Màu hồng', 'color', 'Pink', 'PINK001', 0.00, 30, 'LIPSTICK-PINK-001'),
(1, 'Màu nude', 'color', 'Nude', 'NUDE001', 0.00, 25, 'LIPSTICK-NUDE-001'),
(2, 'Màu đỏ', 'color', 'Red', 'RED002', 0.00, 40, 'LIPSTICK-RED-002'),
(2, 'Màu hồng', 'color', 'Pink', 'PINK002', 0.00, 35, 'LIPSTICK-PINK-002'),
(3, 'Màu đỏ', 'color', 'Red', 'RED003', 0.00, 45, 'LIPSTICK-RED-003'),
(3, 'Màu hồng', 'color', 'Pink', 'PINK003', 0.00, 20, 'LIPSTICK-PINK-003');

-- Insert dữ liệu mẫu cho product bundles
INSERT INTO product_bundles (name, description, bundle_type, original_price, bundle_price, discount_percentage, status) VALUES
('Combo Son Môi Cao Cấp', 'Bộ sưu tập son môi cao cấp từ các thương hiệu nổi tiếng', 'fixed', 1500000, 1200000, 20.00, 'active'),
('Combo Mỹ Phẩm Mùa Hè', 'Bộ sưu tập mỹ phẩm phù hợp cho mùa hè', 'seasonal', 2000000, 1600000, 20.00, 'active'),
('Combo Son Môi Nude', 'Bộ sưu tập son môi màu nude cho văn phòng', 'fixed', 800000, 600000, 25.00, 'active');

-- Insert dữ liệu mẫu cho bundle items
INSERT INTO bundle_items (bundle_id, product_id, quantity, is_required) VALUES
(1, 1, 1, TRUE),
(1, 2, 1, TRUE),
(1, 3, 1, TRUE),
(2, 4, 1, TRUE),
(2, 5, 1, TRUE),
(2, 6, 1, TRUE),
(3, 7, 1, TRUE),
(3, 8, 1, TRUE);

-- Insert dữ liệu mẫu cho product relations
INSERT INTO product_relations (product_id, related_product_id, relation_type, priority) VALUES
(1, 2, 'cross_sell', 1),
(1, 3, 'cross_sell', 2),
(2, 1, 'cross_sell', 1),
(2, 3, 'cross_sell', 2),
(3, 1, 'cross_sell', 1),
(3, 2, 'cross_sell', 2),
(1, 4, 'upsell', 1),
(2, 5, 'upsell', 1),
(3, 6, 'upsell', 1);
