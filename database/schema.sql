-- Database schema cho Linh2Store
-- Website bán son môi & mỹ phẩm cao cấp

CREATE DATABASE IF NOT EXISTS linh2store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE linh2store;

-- Bảng người dùng
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng thương hiệu
CREATE TABLE brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    logo VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng danh mục
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    parent_id INT DEFAULT NULL,
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Bảng sản phẩm
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    brand_id INT,
    category_id INT,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) DEFAULT NULL,
    sku VARCHAR(100) UNIQUE,
    stock_quantity INT DEFAULT 0,
    weight DECIMAL(8,2) DEFAULT 0,
    dimensions VARCHAR(50),
    ingredients TEXT,
    usage_instructions TEXT,
    status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Bảng màu son
CREATE TABLE product_colors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    color_name VARCHAR(100) NOT NULL,
    color_code VARCHAR(7) NOT NULL, -- Hex color code
    image VARCHAR(255),
    stock_quantity INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Bảng hình ảnh sản phẩm
CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    alt_text VARCHAR(200),
    sort_order INT DEFAULT 0,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Bảng giỏ hàng
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    product_color_id INT DEFAULT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (product_color_id) REFERENCES product_colors(id) ON DELETE SET NULL,
    UNIQUE KEY unique_cart_item (user_id, product_id, product_color_id)
);

-- Bảng đơn hàng
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    shipping_fee DECIMAL(10,2) DEFAULT 0,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    final_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'shipping', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    address TEXT NOT NULL,
    city VARCHAR(50) NOT NULL,
    district VARCHAR(50) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng chi tiết đơn hàng
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_color_id INT DEFAULT NULL,
    product_name VARCHAR(200) NOT NULL,
    product_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (product_color_id) REFERENCES product_colors(id) ON DELETE SET NULL
);

-- Bảng đánh giá
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(200),
    content TEXT,
    images JSON,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product_review (user_id, product_id)
);

-- Bảng yêu thích
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist_item (user_id, product_id)
);

-- Bảng mã giảm giá
CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    type ENUM('percentage', 'fixed') NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    minimum_amount DECIMAL(10,2) DEFAULT 0,
    maximum_discount DECIMAL(10,2) DEFAULT NULL,
    usage_limit INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Chèn dữ liệu mẫu
INSERT INTO brands (name, description, status) VALUES
('MAC', 'Thương hiệu mỹ phẩm cao cấp từ Mỹ', 'active'),
('Chanel', 'Thương hiệu thời trang và mỹ phẩm Pháp', 'active'),
('Dior', 'Thương hiệu mỹ phẩm cao cấp Pháp', 'active'),
('YSL', 'Yves Saint Laurent - Thương hiệu mỹ phẩm Pháp', 'active'),
('Tom Ford', 'Thương hiệu mỹ phẩm cao cấp Mỹ', 'active'),
('NARS', 'Thương hiệu mỹ phẩm chuyên nghiệp', 'active'),
('Urban Decay', 'Thương hiệu mỹ phẩm sáng tạo', 'active'),
('Fenty Beauty', 'Thương hiệu mỹ phẩm đa dạng', 'active'),
('Charlotte Tilbury', 'Thương hiệu mỹ phẩm cao cấp Anh', 'active'),
('Pat McGrath', 'Thương hiệu mỹ phẩm nghệ thuật', 'active');

INSERT INTO categories (name, description, status) VALUES
('Son môi', 'Các loại son môi cao cấp', 'active'),
('Son kem', 'Son kem lì, son kem môi', 'active'),
('Son thỏi', 'Son thỏi truyền thống', 'active'),
('Son nước', 'Son nước, son stain', 'active'),
('Son dưỡng', 'Son dưỡng môi, lip balm', 'active'),
('Son lì', 'Son môi lì, matte', 'active'),
('Son bóng', 'Son môi bóng, glossy', 'active'),
('Son satin', 'Son môi satin, mượt mà', 'active');

-- Tạo user admin mặc định
INSERT INTO users (username, email, password, full_name, role, status) VALUES
('admin', 'admin@linh2store.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', 'active'),
('testuser', 'test@linh2store.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test User', 'user', 'active');
-- Mật khẩu mặc định cho cả admin và testuser: password

-- Chèn 100 sản phẩm mẫu
INSERT INTO products (name, description, short_description, brand_id, category_id, price, sale_price, sku, stock_quantity, status, featured) VALUES
-- MAC Products
('MAC Ruby Woo', 'Son môi đỏ cổ điển với finish matte hoàn hảo', 'Son môi đỏ cổ điển MAC', 1, 1, 650000, 580000, 'MAC-RW-001', 50, 'active', 1),
('MAC Velvet Teddy', 'Son môi màu nude ấm áp, phù hợp mọi tông da', 'Son môi nude MAC', 1, 1, 650000, NULL, 'MAC-VT-002', 45, 'active', 1),
('MAC Chili', 'Son môi màu đỏ cam rực rỡ', 'Son môi đỏ cam MAC', 1, 1, 650000, 600000, 'MAC-CH-003', 40, 'active', 0),
('MAC Twig', 'Son môi màu hồng nâu thanh lịch', 'Son môi hồng nâu MAC', 1, 1, 650000, NULL, 'MAC-TW-004', 35, 'active', 0),
('MAC Russian Red', 'Son môi đỏ sâu, sang trọng', 'Son môi đỏ sâu MAC', 1, 1, 650000, 620000, 'MAC-RR-005', 30, 'active', 1),

-- Chanel Products
('Chanel Rouge Allure Velvet', 'Son môi lì với độ bền cao', 'Son môi lì Chanel', 2, 2, 1200000, 1100000, 'CHN-RAV-001', 25, 'active', 1),
('Chanel Rouge Coco', 'Son môi satin mượt mà', 'Son môi satin Chanel', 2, 2, 1000000, NULL, 'CHN-RC-002', 30, 'active', 0),
('Chanel Le Rouge Duo', 'Son môi 2 đầu đa năng', 'Son môi 2 đầu Chanel', 2, 2, 1500000, 1400000, 'CHN-LRD-003', 20, 'active', 1),
('Chanel Rouge Allure Ink', 'Son môi nước bền màu', 'Son môi nước Chanel', 2, 4, 1300000, NULL, 'CHN-RAI-004', 28, 'active', 0),
('Chanel Rouge Allure Luminous', 'Son môi bóng sang trọng', 'Son môi bóng Chanel', 2, 7, 1100000, 1050000, 'CHN-RAL-005', 22, 'active', 0),

-- Dior Products
('Dior Rouge Dior', 'Son môi cao cấp với bao bì sang trọng', 'Son môi cao cấp Dior', 3, 1, 1400000, 1300000, 'DIO-RD-001', 18, 'active', 1),
('Dior Addict Lip Glow', 'Son dưỡng môi có màu tự nhiên', 'Son dưỡng môi Dior', 3, 5, 800000, NULL, 'DIO-ALG-002', 40, 'active', 0),
('Dior Rouge Dior Forever', 'Son môi lì bền màu 24h', 'Son môi lì bền Dior', 3, 2, 1600000, 1500000, 'DIO-RDF-003', 15, 'active', 1),
('Dior Addict Stellar Shine', 'Son môi bóng với ánh kim', 'Son môi bóng kim Dior', 3, 7, 1200000, NULL, 'DIO-ASS-004', 25, 'active', 0),
('Dior Rouge Dior Ultra Care', 'Son môi dưỡng ẩm cao cấp', 'Son môi dưỡng Dior', 3, 1, 1300000, 1250000, 'DIO-RDUC-005', 20, 'active', 0),

-- YSL Products
('YSL Rouge Pur Couture', 'Son môi cao cấp với thiết kế vàng', 'Son môi cao cấp YSL', 4, 1, 1500000, 1400000, 'YSL-RPC-001', 12, 'active', 1),
('YSL Rouge Volupté Shine', 'Son môi bóng với độ ẩm cao', 'Son môi bóng YSL', 4, 7, 1300000, NULL, 'YSL-RVS-002', 35, 'active', 0),
('YSL Tatouage Couture', 'Son môi nước bền màu', 'Son môi nước YSL', 4, 4, 1600000, 1550000, 'YSL-TC-003', 18, 'active', 1),
('YSL Rouge Pur Couture The Slim', 'Son môi dạng thỏi nhỏ gọn', 'Son môi thỏi YSL', 4, 3, 1200000, NULL, 'YSL-RPCTS-004', 30, 'active', 0),
('YSL Rouge Volupté', 'Son môi satin mượt mà', 'Son môi satin YSL', 4, 8, 1400000, 1350000, 'YSL-RV-005', 22, 'active', 0),

-- Tom Ford Products
('Tom Ford Lip Color', 'Son môi cao cấp với bao bì đen sang trọng', 'Son môi cao cấp Tom Ford', 5, 1, 2000000, 1900000, 'TF-LC-001', 8, 'active', 1),
('Tom Ford Lip Color Matte', 'Son môi lì với độ bền cao', 'Son môi lì Tom Ford', 5, 2, 2100000, NULL, 'TF-LCM-002', 10, 'active', 0),
('Tom Ford Lip Color Shine', 'Son môi bóng với ánh kim', 'Son môi bóng Tom Ford', 5, 7, 1950000, 1850000, 'TF-LCS-003', 12, 'active', 1),
('Tom Ford Ultra-Rich Lip Color', 'Son môi dưỡng ẩm cao cấp', 'Son môi dưỡng Tom Ford', 5, 1, 2200000, NULL, 'TF-URL-004', 6, 'active', 0),
('Tom Ford Boys & Girls', 'Son môi mini với nhiều màu sắc', 'Son môi mini Tom Ford', 5, 1, 1500000, 1450000, 'TF-BG-005', 25, 'active', 0),

-- NARS Products
('NARS Powermatte Lip Pigment', 'Son môi nước bền màu', 'Son môi nước NARS', 6, 4, 900000, 850000, 'NAR-PLP-001', 40, 'active', 1),
('NARS Audacious Lipstick', 'Son môi với độ bền cao', 'Son môi bền NARS', 6, 1, 1100000, NULL, 'NAR-AL-002', 35, 'active', 0),
('NARS Lip Gloss', 'Son môi bóng với nhiều màu sắc', 'Son môi bóng NARS', 6, 7, 800000, 750000, 'NAR-LG-003', 50, 'active', 0),
('NARS Velvet Matte Lip Pencil', 'Son môi dạng bút chì', 'Son môi bút NARS', 6, 2, 700000, NULL, 'NAR-VMLP-004', 45, 'active', 0),
('NARS Afterglow Lip Balm', 'Son dưỡng môi với màu sắc', 'Son dưỡng NARS', 6, 5, 600000, 550000, 'NAR-ALB-005', 60, 'active', 0),

-- Urban Decay Products
('Urban Decay Vice Lipstick', 'Son môi với nhiều finish khác nhau', 'Son môi đa dạng Urban Decay', 7, 1, 800000, 750000, 'UD-VL-001', 55, 'active', 1),
('Urban Decay Vice Liquid Lipstick', 'Son môi nước bền màu', 'Son môi nước Urban Decay', 7, 4, 900000, NULL, 'UD-VLL-002', 48, 'active', 0),
('Urban Decay Hi-Fi Shine Ultra Cushion Lip Gloss', 'Son môi bóng với độ ẩm cao', 'Son môi bóng Urban Decay', 7, 7, 700000, 650000, 'UD-HFS-003', 42, 'active', 0),
('Urban Decay 24/7 Glide-On Lip Pencil', 'Bút kẻ môi dưỡng ẩm', 'Bút kẻ môi Urban Decay', 7, 1, 500000, NULL, 'UD-24GOLP-004', 38, 'active', 0),
('Urban Decay Vice Special Effects', 'Son môi với hiệu ứng đặc biệt', 'Son môi hiệu ứng Urban Decay', 7, 1, 850000, 800000, 'UD-VSE-005', 30, 'active', 0),

-- Fenty Beauty Products
('Fenty Beauty Stunna Lip Paint', 'Son môi nước với độ bền cao', 'Son môi nước Fenty', 8, 4, 700000, 650000, 'FB-SLP-001', 65, 'active', 1),
('Fenty Beauty Mattemoiselle Plush Matte Lipstick', 'Son môi lì mềm mại', 'Son môi lì Fenty', 8, 2, 600000, NULL, 'FB-MPL-002', 70, 'active', 0),
('Fenty Beauty Gloss Bomb Universal Lip Luminizer', 'Son môi bóng với ánh kim', 'Son môi bóng Fenty', 8, 7, 500000, 450000, 'FB-GB-003', 80, 'active', 1),
('Fenty Beauty Slip Shine Sheer Shiny Lipstick', 'Son môi bóng trong suốt', 'Son môi bóng Fenty', 8, 7, 550000, NULL, 'FB-SSS-004', 75, 'active', 0),
('Fenty Beauty Pro Kissr Lip Balm', 'Son dưỡng môi chuyên nghiệp', 'Son dưỡng Fenty', 8, 5, 400000, 350000, 'FB-PKLB-005', 90, 'active', 0),

-- Charlotte Tilbury Products
('Charlotte Tilbury Matte Revolution', 'Son môi lì với độ bền cao', 'Son môi lì Charlotte Tilbury', 9, 2, 1200000, 1150000, 'CT-MR-001', 25, 'active', 1),
('Charlotte Tilbury K.I.S.S.I.N.G', 'Son môi satin mượt mà', 'Son môi satin Charlotte Tilbury', 9, 8, 1100000, NULL, 'CT-KISS-002', 30, 'active', 0),
('Charlotte Tilbury Hot Lips 2', 'Son môi với nhiều màu sắc', 'Son môi đa dạng Charlotte Tilbury', 9, 1, 1300000, 1250000, 'CT-HL2-003', 20, 'active', 1),
('Charlotte Tilbury Lip Cheat', 'Bút kẻ môi dưỡng ẩm', 'Bút kẻ môi Charlotte Tilbury', 9, 1, 600000, NULL, 'CT-LC-004', 40, 'active', 0),
('Charlotte Tilbury Collagen Lip Bath', 'Son dưỡng môi collagen', 'Son dưỡng collagen Charlotte Tilbury', 9, 5, 800000, 750000, 'CT-CLB-005', 35, 'active', 0),

-- Pat McGrath Products
('Pat McGrath Labs MatteTrance Lipstick', 'Son môi lì cao cấp', 'Son môi lì Pat McGrath', 10, 2, 1800000, 1700000, 'PM-MTL-001', 15, 'active', 1),
('Pat McGrath Labs Lust Gloss', 'Son môi bóng với ánh kim', 'Son môi bóng Pat McGrath', 10, 7, 1600000, NULL, 'PM-LG-002', 20, 'active', 0),
('Pat McGrath Labs Permagel Ultra Lip Pencil', 'Bút kẻ môi bền màu', 'Bút kẻ môi Pat McGrath', 10, 1, 1200000, 1150000, 'PM-PULP-003', 25, 'active', 1),
('Pat McGrath Labs Divine Blush + Glow', 'Son môi với hiệu ứng đặc biệt', 'Son môi hiệu ứng Pat McGrath', 10, 1, 2000000, NULL, 'PM-DBG-004', 10, 'active', 0),
('Pat McGrath Labs Permagel Ultra Lip Pencil', 'Son môi dưỡng ẩm cao cấp', 'Son môi dưỡng Pat McGrath', 10, 1, 1400000, 1350000, 'PM-PULP2-005', 18, 'active', 0);

-- Thêm 75 sản phẩm nữa để đủ 100 sản phẩm
INSERT INTO products (name, description, short_description, brand_id, category_id, price, sale_price, sku, stock_quantity, status, featured) VALUES
-- MAC Products (tiếp tục)
('MAC Retro Matte Liquid Lipcolour', 'Son môi nước lì bền màu', 'Son môi nước MAC', 1, 4, 700000, 650000, 'MAC-RML-006', 45, 'active', 0),
('MAC Powder Kiss Liquid Lipcolour', 'Son môi nước với finish powder', 'Son môi powder MAC', 1, 4, 750000, NULL, 'MAC-PKL-007', 40, 'active', 0),
('MAC Cremesheen Lipstick', 'Son môi với độ ẩm cao', 'Son môi ẩm MAC', 1, 1, 600000, 550000, 'MAC-CL-008', 50, 'active', 0),
('MAC Amplified Lipstick', 'Son môi với độ bền cao', 'Son môi bền MAC', 1, 1, 650000, NULL, 'MAC-AL-009', 35, 'active', 0),
('MAC Satin Lipstick', 'Son môi satin mượt mà', 'Son môi satin MAC', 1, 8, 620000, 580000, 'MAC-SL-010', 42, 'active', 0),

-- Chanel Products (tiếp tục)
('Chanel Rouge Allure Intense', 'Son môi với độ bền cao', 'Son môi bền Chanel', 2, 1, 1300000, NULL, 'CHN-RAI2-006', 28, 'active', 0),
('Chanel Rouge Allure Luminous Intense', 'Son môi bóng với độ bền cao', 'Son môi bóng bền Chanel', 2, 7, 1400000, 1350000, 'CHN-RALI-007', 25, 'active', 0),
('Chanel Rouge Allure Velvet Extreme', 'Son môi lì cực bền', 'Son môi lì cực bền Chanel', 2, 2, 1500000, NULL, 'CHN-RAVE-008', 20, 'active', 0),
('Chanel Rouge Coco Flash', 'Son môi bóng với độ ẩm cao', 'Son môi bóng ẩm Chanel', 2, 7, 1200000, 1150000, 'CHN-RCF-009', 32, 'active', 0),
('Chanel Rouge Allure Ink Fusion', 'Son môi nước với hiệu ứng đặc biệt', 'Son môi nước đặc biệt Chanel', 2, 4, 1600000, NULL, 'CHN-RAIF-010', 18, 'active', 0),

-- Dior Products (tiếp tục)
('Dior Rouge Dior Forever Liquid', 'Son môi nước bền màu 24h', 'Son môi nước bền Dior', 3, 4, 1700000, 1600000, 'DIO-RDFL-006', 15, 'active', 0),
('Dior Addict Lacquer Stick', 'Son môi bóng dạng thỏi', 'Son môi bóng thỏi Dior', 3, 7, 1300000, NULL, 'DIO-ALS-007', 25, 'active', 0),
('Dior Rouge Dior Ultra Rouge', 'Son môi với độ bền cực cao', 'Son môi cực bền Dior', 3, 1, 1800000, 1750000, 'DIO-RDUR-008', 12, 'active', 0),
('Dior Addict Ultra-Gloss', 'Son môi bóng với độ ẩm cao', 'Son môi bóng ẩm Dior', 3, 7, 1100000, NULL, 'DIO-AUG-009', 30, 'active', 0),
('Dior Rouge Dior Couture Colour', 'Son môi cao cấp với bao bì sang trọng', 'Son môi cao cấp Dior', 3, 1, 1900000, 1850000, 'DIO-RDCC-010', 10, 'active', 0),

-- YSL Products (tiếp tục)
('YSL Rouge Pur Couture The Slim Matte', 'Son môi lì dạng thỏi nhỏ', 'Son môi lì thỏi YSL', 4, 2, 1400000, NULL, 'YSL-RPCTSM-006', 25, 'active', 0),
('YSL Rouge Volupté Shine Oil-In-Stick', 'Son môi bóng với dầu dưỡng', 'Son môi bóng dầu YSL', 4, 7, 1500000, 1450000, 'YSL-RVSOIS-007', 20, 'active', 0),
('YSL Tatouage Couture Matte Stain', 'Son môi nước lì bền màu', 'Son môi nước lì YSL', 4, 4, 1700000, NULL, 'YSL-TCM-008', 18, 'active', 0),
('YSL Rouge Pur Couture Vernis À Lèvres', 'Son môi bóng với độ bền cao', 'Son môi bóng bền YSL', 4, 7, 1600000, 1550000, 'YSL-RPCVAL-009', 22, 'active', 0),
('YSL Rouge Volupté Plump-In-Colour', 'Son môi với hiệu ứng căng môi', 'Son môi căng môi YSL', 4, 1, 1800000, NULL, 'YSL-RVPIC-010', 15, 'active', 0),

-- Tom Ford Products (tiếp tục)
('Tom Ford Lip Color Matte', 'Son môi lì cao cấp', 'Son môi lì Tom Ford', 5, 2, 2200000, 2100000, 'TF-LCM2-006', 8, 'active', 0),
('Tom Ford Ultra-Rich Lip Color', 'Son môi dưỡng ẩm cao cấp', 'Son môi dưỡng Tom Ford', 5, 1, 2300000, NULL, 'TF-URL2-007', 6, 'active', 0),
('Tom Ford Boys & Girls Lip Color', 'Son môi mini với nhiều màu', 'Son môi mini Tom Ford', 5, 1, 1600000, 1550000, 'TF-BG2-008', 20, 'active', 0),
('Tom Ford Lip Color Shine', 'Son môi bóng với ánh kim', 'Son môi bóng Tom Ford', 5, 7, 2100000, NULL, 'TF-LCS2-009', 10, 'active', 0),
('Tom Ford Ultra-Rich Lip Color Matte', 'Son môi lì dưỡng ẩm', 'Son môi lì dưỡng Tom Ford', 5, 2, 2400000, 2300000, 'TF-URLM-010', 5, 'active', 0),

-- NARS Products (tiếp tục)
('NARS Powermatte Lip Pigment', 'Son môi nước bền màu', 'Son môi nước NARS', 6, 4, 950000, 900000, 'NAR-PLP2-006', 35, 'active', 0),
('NARS Audacious Lipstick', 'Son môi với độ bền cao', 'Son môi bền NARS', 6, 1, 1150000, NULL, 'NAR-AL2-007', 30, 'active', 0),
('NARS Lip Gloss', 'Son môi bóng với nhiều màu', 'Son môi bóng NARS', 6, 7, 850000, 800000, 'NAR-LG2-008', 45, 'active', 0),
('NARS Velvet Matte Lip Pencil', 'Son môi dạng bút chì', 'Son môi bút NARS', 6, 2, 750000, NULL, 'NAR-VMLP2-009', 40, 'active', 0),
('NARS Afterglow Lip Balm', 'Son dưỡng môi với màu sắc', 'Son dưỡng NARS', 6, 5, 650000, 600000, 'NAR-ALB2-010', 55, 'active', 0),

-- Urban Decay Products (tiếp tục)
('Urban Decay Vice Lipstick', 'Son môi với nhiều finish', 'Son môi đa dạng Urban Decay', 7, 1, 850000, 800000, 'UD-VL2-006', 50, 'active', 0),
('Urban Decay Vice Liquid Lipstick', 'Son môi nước bền màu', 'Son môi nước Urban Decay', 7, 4, 950000, NULL, 'UD-VLL2-007', 45, 'active', 0),
('Urban Decay Hi-Fi Shine Ultra Cushion Lip Gloss', 'Son môi bóng với độ ẩm cao', 'Son môi bóng Urban Decay', 7, 7, 750000, 700000, 'UD-HFS2-008', 40, 'active', 0),
('Urban Decay 24/7 Glide-On Lip Pencil', 'Bút kẻ môi dưỡng ẩm', 'Bút kẻ môi Urban Decay', 7, 1, 550000, NULL, 'UD-24GOLP2-009', 35, 'active', 0),
('Urban Decay Vice Special Effects', 'Son môi với hiệu ứng đặc biệt', 'Son môi hiệu ứng Urban Decay', 7, 1, 900000, 850000, 'UD-VSE2-010', 28, 'active', 0),

-- Fenty Beauty Products (tiếp tục)
('Fenty Beauty Stunna Lip Paint', 'Son môi nước với độ bền cao', 'Son môi nước Fenty', 8, 4, 750000, 700000, 'FB-SLP2-006', 60, 'active', 0),
('Fenty Beauty Mattemoiselle Plush Matte Lipstick', 'Son môi lì mềm mại', 'Son môi lì Fenty', 8, 2, 650000, NULL, 'FB-MPL2-007', 65, 'active', 0),
('Fenty Beauty Gloss Bomb Universal Lip Luminizer', 'Son môi bóng với ánh kim', 'Son môi bóng Fenty', 8, 7, 550000, 500000, 'FB-GB2-008', 75, 'active', 0),
('Fenty Beauty Slip Shine Sheer Shiny Lipstick', 'Son môi bóng trong suốt', 'Son môi bóng Fenty', 8, 7, 600000, NULL, 'FB-SSS2-009', 70, 'active', 0),
('Fenty Beauty Pro Kissr Lip Balm', 'Son dưỡng môi chuyên nghiệp', 'Son dưỡng Fenty', 8, 5, 450000, 400000, 'FB-PKLB2-010', 85, 'active', 0),

-- Charlotte Tilbury Products (tiếp tục)
('Charlotte Tilbury Matte Revolution', 'Son môi lì với độ bền cao', 'Son môi lì Charlotte Tilbury', 9, 2, 1250000, 1200000, 'CT-MR2-006', 22, 'active', 0),
('Charlotte Tilbury K.I.S.S.I.N.G', 'Son môi satin mượt mà', 'Son môi satin Charlotte Tilbury', 9, 8, 1150000, NULL, 'CT-KISS2-007', 28, 'active', 0),
('Charlotte Tilbury Hot Lips 2', 'Son môi với nhiều màu sắc', 'Son môi đa dạng Charlotte Tilbury', 9, 1, 1350000, 1300000, 'CT-HL22-008', 18, 'active', 0),
('Charlotte Tilbury Lip Cheat', 'Bút kẻ môi dưỡng ẩm', 'Bút kẻ môi Charlotte Tilbury', 9, 1, 650000, NULL, 'CT-LC2-009', 35, 'active', 0),
('Charlotte Tilbury Collagen Lip Bath', 'Son dưỡng môi collagen', 'Son dưỡng collagen Charlotte Tilbury', 9, 5, 850000, 800000, 'CT-CLB2-010', 32, 'active', 0),

-- Pat McGrath Products (tiếp tục)
('Pat McGrath Labs MatteTrance Lipstick', 'Son môi lì cao cấp', 'Son môi lì Pat McGrath', 10, 2, 1850000, 1750000, 'PM-MTL2-006', 12, 'active', 0),
('Pat McGrath Labs Lust Gloss', 'Son môi bóng với ánh kim', 'Son môi bóng Pat McGrath', 10, 7, 1650000, NULL, 'PM-LG2-007', 18, 'active', 0),
('Pat McGrath Labs Permagel Ultra Lip Pencil', 'Bút kẻ môi bền màu', 'Bút kẻ môi Pat McGrath', 10, 1, 1250000, 1200000, 'PM-PULP2-008', 22, 'active', 0),
('Pat McGrath Labs Divine Blush + Glow', 'Son môi với hiệu ứng đặc biệt', 'Son môi hiệu ứng Pat McGrath', 10, 1, 2050000, NULL, 'PM-DBG2-009', 8, 'active', 0),
('Pat McGrath Labs Permagel Ultra Lip Pencil', 'Son môi dưỡng ẩm cao cấp', 'Son môi dưỡng Pat McGrath', 10, 1, 1450000, 1400000, 'PM-PULP22-010', 15, 'active', 0);

-- Thêm hình ảnh mẫu cho tất cả 100 sản phẩm
INSERT INTO product_images (product_id, image_url, alt_text, is_primary) VALUES
-- MAC Products (1-10)
(1, 'https://images.unsplash.com/photo-1586495777744-4413f21062fa?w=400&h=400&fit=crop&crop=center&auto=format', 'MAC Ruby Woo', 1),
(2, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0c0?w=400&h=400&fit=crop&crop=center', 'MAC Velvet Teddy', 1),
(3, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0c1?w=400&h=400&fit=crop&crop=center', 'MAC Chili', 1),
(4, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0c2?w=400&h=400&fit=crop&crop=center', 'MAC Twig', 1),
(5, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0c3?w=400&h=400&fit=crop&crop=center', 'MAC Russian Red', 1),
(6, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0c4?w=400&h=400&fit=crop&crop=center', 'MAC Retro Matte Liquid Lipcolour', 1),
(7, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0c5?w=400&h=400&fit=crop&crop=center', 'MAC Powder Kiss Liquid Lipcolour', 1),
(8, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0c6?w=400&h=400&fit=crop&crop=center', 'MAC Cremesheen Lipstick', 1),
(9, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0c7?w=400&h=400&fit=crop&crop=center', 'MAC Amplified Lipstick', 1),
(10, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0c8?w=400&h=400&fit=crop&crop=center', 'MAC Satin Lipstick', 1),

-- Chanel Products (11-20)
(11, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0c9?w=400&h=400&fit=crop&crop=center', 'Chanel Rouge Allure Velvet', 1),
(12, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0d0?w=400&h=400&fit=crop&crop=center', 'Chanel Rouge Coco', 1),
(13, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0d1?w=400&h=400&fit=crop&crop=center', 'Chanel Le Rouge Duo', 1),
(14, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0d2?w=400&h=400&fit=crop&crop=center', 'Chanel Rouge Allure Ink', 1),
(15, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0d3?w=400&h=400&fit=crop&crop=center', 'Chanel Rouge Allure Luminous', 1),
(16, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0d4?w=400&h=400&fit=crop&crop=center', 'Chanel Rouge Allure Intense', 1),
(17, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0d5?w=400&h=400&fit=crop&crop=center', 'Chanel Rouge Allure Luminous Intense', 1),
(18, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0d6?w=400&h=400&fit=crop&crop=center', 'Chanel Rouge Allure Velvet Extreme', 1),
(19, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0d7?w=400&h=400&fit=crop&crop=center', 'Chanel Rouge Coco Flash', 1),
(20, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0d8?w=400&h=400&fit=crop&crop=center', 'Chanel Rouge Allure Ink Fusion', 1),

-- Dior Products (21-30)
(21, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0d9?w=400&h=400&fit=crop&crop=center', 'Dior Rouge Dior', 1),
(22, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0e0?w=400&h=400&fit=crop&crop=center', 'Dior Addict Lip Glow', 1),
(23, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0e1?w=400&h=400&fit=crop&crop=center', 'Dior Rouge Dior Forever', 1),
(24, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0e2?w=400&h=400&fit=crop&crop=center', 'Dior Addict Stellar Shine', 1),
(25, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0e3?w=400&h=400&fit=crop&crop=center', 'Dior Rouge Dior Ultra Care', 1),
(26, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0e4?w=400&h=400&fit=crop&crop=center', 'Dior Rouge Dior Forever Liquid', 1),
(27, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0e5?w=400&h=400&fit=crop&crop=center', 'Dior Addict Lacquer Stick', 1),
(28, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0e6?w=400&h=400&fit=crop&crop=center', 'Dior Rouge Dior Ultra Rouge', 1),
(29, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0e7?w=400&h=400&fit=crop&crop=center', 'Dior Addict Ultra-Gloss', 1),
(30, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0e8?w=400&h=400&fit=crop&crop=center', 'Dior Rouge Dior Couture Colour', 1),

-- YSL Products (31-40)
(31, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0e9?w=400&h=400&fit=crop&crop=center', 'YSL Rouge Pur Couture', 1),
(32, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0f0?w=400&h=400&fit=crop&crop=center', 'YSL Rouge Volupté Shine', 1),
(33, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0f1?w=400&h=400&fit=crop&crop=center', 'YSL Tatouage Couture', 1),
(34, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0f2?w=400&h=400&fit=crop&crop=center', 'YSL Rouge Pur Couture The Slim', 1),
(35, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0f3?w=400&h=400&fit=crop&crop=center', 'YSL Rouge Volupté', 1),
(36, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0f4?w=400&h=400&fit=crop&crop=center', 'YSL Rouge Pur Couture The Slim Matte', 1),
(37, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0f5?w=400&h=400&fit=crop&crop=center', 'YSL Rouge Volupté Shine Oil-In-Stick', 1),
(38, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0f6?w=400&h=400&fit=crop&crop=center', 'YSL Tatouage Couture Matte Stain', 1),
(39, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0f7?w=400&h=400&fit=crop&crop=center', 'YSL Rouge Pur Couture Vernis À Lèvres', 1),
(40, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0f8?w=400&h=400&fit=crop&crop=center', 'YSL Rouge Volupté Plump-In-Colour', 1),

-- Tom Ford Products (41-50)
(41, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0f9?w=400&h=400&fit=crop&crop=center', 'Tom Ford Lip Color', 1),
(42, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1a0?w=400&h=400&fit=crop&crop=center', 'Tom Ford Lip Color Matte', 1),
(43, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1a1?w=400&h=400&fit=crop&crop=center', 'Tom Ford Lip Color Shine', 1),
(44, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1a2?w=400&h=400&fit=crop&crop=center', 'Tom Ford Ultra-Rich Lip Color', 1),
(45, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1a3?w=400&h=400&fit=crop&crop=center', 'Tom Ford Boys & Girls', 1),
(46, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1a4?w=400&h=400&fit=crop&crop=center', 'Tom Ford Lip Color Matte 2', 1),
(47, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1a5?w=400&h=400&fit=crop&crop=center', 'Tom Ford Ultra-Rich Lip Color 2', 1),
(48, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1a6?w=400&h=400&fit=crop&crop=center', 'Tom Ford Boys & Girls Lip Color', 1),
(49, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1a7?w=400&h=400&fit=crop&crop=center', 'Tom Ford Lip Color Shine 2', 1),
(50, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1a8?w=400&h=400&fit=crop&crop=center', 'Tom Ford Ultra-Rich Lip Color Matte', 1),

-- NARS Products (51-60)
(51, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1a9?w=400&h=400&fit=crop&crop=center', 'NARS Powermatte Lip Pigment', 1),
(52, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1b0?w=400&h=400&fit=crop&crop=center', 'NARS Audacious Lipstick', 1),
(53, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1b1?w=400&h=400&fit=crop&crop=center', 'NARS Lip Gloss', 1),
(54, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1b2?w=400&h=400&fit=crop&crop=center', 'NARS Velvet Matte Lip Pencil', 1),
(55, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1b3?w=400&h=400&fit=crop&crop=center', 'NARS Afterglow Lip Balm', 1),
(56, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1b4?w=400&h=400&fit=crop&crop=center', 'NARS Powermatte Lip Pigment 2', 1),
(57, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1b5?w=400&h=400&fit=crop&crop=center', 'NARS Audacious Lipstick 2', 1),
(58, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1b6?w=400&h=400&fit=crop&crop=center', 'NARS Lip Gloss 2', 1),
(59, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1b7?w=400&h=400&fit=crop&crop=center', 'NARS Velvet Matte Lip Pencil 2', 1),
(60, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1b8?w=400&h=400&fit=crop&crop=center', 'NARS Afterglow Lip Balm 2', 1),

-- Urban Decay Products (61-70)
(61, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1b9?w=400&h=400&fit=crop&crop=center', 'Urban Decay Vice Lipstick', 1),
(62, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1c0?w=400&h=400&fit=crop&crop=center', 'Urban Decay Vice Liquid Lipstick', 1),
(63, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1c1?w=400&h=400&fit=crop&crop=center', 'Urban Decay Hi-Fi Shine Ultra Cushion Lip Gloss', 1),
(64, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1c2?w=400&h=400&fit=crop&crop=center', 'Urban Decay 24/7 Glide-On Lip Pencil', 1),
(65, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1c3?w=400&h=400&fit=crop&crop=center', 'Urban Decay Vice Special Effects', 1),
(66, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1c4?w=400&h=400&fit=crop&crop=center', 'Urban Decay Vice Lipstick 2', 1),
(67, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1c5?w=400&h=400&fit=crop&crop=center', 'Urban Decay Vice Liquid Lipstick 2', 1),
(68, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1c6?w=400&h=400&fit=crop&crop=center', 'Urban Decay Hi-Fi Shine Ultra Cushion Lip Gloss 2', 1),
(69, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1c7?w=400&h=400&fit=crop&crop=center', 'Urban Decay 24/7 Glide-On Lip Pencil 2', 1),
(70, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1c8?w=400&h=400&fit=crop&crop=center', 'Urban Decay Vice Special Effects 2', 1),

-- Fenty Beauty Products (71-80)
(71, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1c9?w=400&h=400&fit=crop&crop=center', 'Fenty Beauty Stunna Lip Paint', 1),
(72, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1d0?w=400&h=400&fit=crop&crop=center', 'Fenty Beauty Mattemoiselle Plush Matte Lipstick', 1),
(73, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1d1?w=400&h=400&fit=crop&crop=center', 'Fenty Beauty Gloss Bomb Universal Lip Luminizer', 1),
(74, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1d2?w=400&h=400&fit=crop&crop=center', 'Fenty Beauty Slip Shine Sheer Shiny Lipstick', 1),
(75, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1d3?w=400&h=400&fit=crop&crop=center', 'Fenty Beauty Pro Kissr Lip Balm', 1),
(76, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1d4?w=400&h=400&fit=crop&crop=center', 'Fenty Beauty Stunna Lip Paint 2', 1),
(77, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1d5?w=400&h=400&fit=crop&crop=center', 'Fenty Beauty Mattemoiselle Plush Matte Lipstick 2', 1),
(78, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1d6?w=400&h=400&fit=crop&crop=center', 'Fenty Beauty Gloss Bomb Universal Lip Luminizer 2', 1),
(79, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1d7?w=400&h=400&fit=crop&crop=center', 'Fenty Beauty Slip Shine Sheer Shiny Lipstick 2', 1),
(80, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1d8?w=400&h=400&fit=crop&crop=center', 'Fenty Beauty Pro Kissr Lip Balm 2', 1),

-- Charlotte Tilbury Products (81-90)
(81, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1d9?w=400&h=400&fit=crop&crop=center', 'Charlotte Tilbury Matte Revolution', 1),
(82, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1e0?w=400&h=400&fit=crop&crop=center', 'Charlotte Tilbury K.I.S.S.I.N.G', 1),
(83, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1e1?w=400&h=400&fit=crop&crop=center', 'Charlotte Tilbury Hot Lips 2', 1),
(84, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1e2?w=400&h=400&fit=crop&crop=center', 'Charlotte Tilbury Lip Cheat', 1),
(85, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1e3?w=400&h=400&fit=crop&crop=center', 'Charlotte Tilbury Collagen Lip Bath', 1),
(86, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1e4?w=400&h=400&fit=crop&crop=center', 'Charlotte Tilbury Matte Revolution 2', 1),
(87, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1e5?w=400&h=400&fit=crop&crop=center', 'Charlotte Tilbury K.I.S.S.I.N.G 2', 1),
(88, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1e6?w=400&h=400&fit=crop&crop=center', 'Charlotte Tilbury Hot Lips 2 2', 1),
(89, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1e7?w=400&h=400&fit=crop&crop=center', 'Charlotte Tilbury Lip Cheat 2', 1),
(90, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1e8?w=400&h=400&fit=crop&crop=center', 'Charlotte Tilbury Collagen Lip Bath 2', 1),

-- Pat McGrath Products (91-100)
(91, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1e9?w=400&h=400&fit=crop&crop=center', 'Pat McGrath Labs MatteTrance Lipstick', 1),
(92, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1f0?w=400&h=400&fit=crop&crop=center', 'Pat McGrath Labs Lust Gloss', 1),
(93, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1f1?w=400&h=400&fit=crop&crop=center', 'Pat McGrath Labs Permagel Ultra Lip Pencil', 1),
(94, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1f2?w=400&h=400&fit=crop&crop=center', 'Pat McGrath Labs Divine Blush + Glow', 1),
(95, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1f3?w=400&h=400&fit=crop&crop=center', 'Pat McGrath Labs Permagel Ultra Lip Pencil 2', 1),
(96, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1f4?w=400&h=400&fit=crop&crop=center', 'Pat McGrath Labs MatteTrance Lipstick 2', 1),
(97, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1f5?w=400&h=400&fit=crop&crop=center', 'Pat McGrath Labs Lust Gloss 2', 1),
(98, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1f6?w=400&h=400&fit=crop&crop=center', 'Pat McGrath Labs Permagel Ultra Lip Pencil 3', 1),
(99, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1f7?w=400&h=400&fit=crop&crop=center', 'Pat McGrath Labs Divine Blush + Glow 2', 1),
(100, 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c1f8?w=400&h=400&fit=crop&crop=center', 'Pat McGrath Labs Permagel Ultra Lip Pencil 4', 1);

-- Thêm logo cho thương hiệu
UPDATE brands SET logo = 'https://via.placeholder.com/200x100/E3F2FD/EC407A?text=MAC' WHERE id = 1;
UPDATE brands SET logo = 'https://via.placeholder.com/200x100/E3F2FD/EC407A?text=Chanel' WHERE id = 2;
UPDATE brands SET logo = 'https://via.placeholder.com/200x100/E3F2FD/EC407A?text=Dior' WHERE id = 3;
UPDATE brands SET logo = 'https://via.placeholder.com/200x100/E3F2FD/EC407A?text=YSL' WHERE id = 4;
UPDATE brands SET logo = 'https://via.placeholder.com/200x100/E3F2FD/EC407A?text=Tom+Ford' WHERE id = 5;
UPDATE brands SET logo = 'https://via.placeholder.com/200x100/E3F2FD/EC407A?text=NARS' WHERE id = 6;
UPDATE brands SET logo = 'https://via.placeholder.com/200x100/E3F2FD/EC407A?text=Urban+Decay' WHERE id = 7;
UPDATE brands SET logo = 'https://via.placeholder.com/200x100/E3F2FD/EC407A?text=Fenty+Beauty' WHERE id = 8;
UPDATE brands SET logo = 'https://via.placeholder.com/200x100/E3F2FD/EC407A?text=Charlotte+Tilbury' WHERE id = 9;
UPDATE brands SET logo = 'https://via.placeholder.com/200x100/E3F2FD/EC407A?text=Pat+McGrath' WHERE id = 10;

-- Tạo bảng inventory_logs
CREATE TABLE IF NOT EXISTS inventory_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    type ENUM('restock', 'adjust', 'sale', 'return') NOT NULL,
    quantity INT NOT NULL,
    old_quantity INT NOT NULL,
    new_quantity INT NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Tạo bảng order_status_logs
CREATE TABLE IF NOT EXISTS order_status_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    old_status VARCHAR(50),
    new_status VARCHAR(50) NOT NULL,
    changed_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Thêm màu sắc cho sản phẩm
INSERT INTO product_colors (product_id, color_name, color_code, stock_quantity, status) VALUES
-- MAC Ruby Woo
(1, 'Ruby Woo', '#DC143C', 25, 'active'),
(1, 'Russian Red', '#B22222', 25, 'active'),

-- MAC Velvet Teddy
(2, 'Velvet Teddy', '#D2B48C', 30, 'active'),
(2, 'Whirl', '#8B7355', 15, 'active'),

-- MAC Chili
(3, 'Chili', '#CD5C5C', 20, 'active'),
(3, 'Spice It Up', '#A0522D', 20, 'active'),

-- Chanel Rouge Allure Velvet
(6, 'Rouge Allure', '#DC143C', 15, 'active'),
(6, 'Luminous Intense', '#B22222', 10, 'active'),

-- Chanel Rouge Coco
(7, 'Rouge Coco', '#CD5C5C', 20, 'active'),
(7, 'Flash', '#A0522D', 10, 'active');
