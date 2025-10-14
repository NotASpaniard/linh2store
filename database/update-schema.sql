-- Cập nhật cấu trúc database cho Linh2Store
-- Thêm các cột cần thiết cho bảng orders và order_items

-- Cập nhật bảng orders
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS final_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS full_name VARCHAR(100) NOT NULL DEFAULT '',
ADD COLUMN IF NOT EXISTS phone VARCHAR(20) NOT NULL DEFAULT '',
ADD COLUMN IF NOT EXISTS email VARCHAR(100) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS address TEXT NOT NULL DEFAULT '',
ADD COLUMN IF NOT EXISTS city VARCHAR(50) NOT NULL DEFAULT '',
ADD COLUMN IF NOT EXISTS district VARCHAR(50) NOT NULL DEFAULT '';

-- Cập nhật bảng order_items
ALTER TABLE order_items 
ADD COLUMN IF NOT EXISTS product_name VARCHAR(200) NOT NULL DEFAULT '',
ADD COLUMN IF NOT EXISTS product_price DECIMAL(10,2) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS total_price DECIMAL(10,2) NOT NULL DEFAULT 0;

-- Cập nhật dữ liệu hiện tại (nếu có)
-- Cập nhật subtotal và final_amount cho các đơn hàng hiện tại
UPDATE orders 
SET subtotal = total_amount - shipping_fee,
    final_amount = total_amount
WHERE subtotal = 0 AND final_amount = 0;

-- Cập nhật product_name cho các order_items hiện tại
UPDATE order_items oi
JOIN products p ON oi.product_id = p.id
SET oi.product_name = p.name,
    oi.product_price = oi.price,
    oi.total_price = oi.price * oi.quantity
WHERE oi.product_name = '';

-- Tạo bảng inventory_logs nếu chưa tồn tại
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

-- Tạo bảng order_status_logs nếu chưa tồn tại
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
