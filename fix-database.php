<?php
/**
 * Sửa lỗi database - Thêm các cột cần thiết
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/database.php';

echo "<h1>Sửa lỗi database</h1>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<h2>1. Kiểm tra cấu trúc hiện tại</h2>";
    
    // Kiểm tra cấu trúc bảng orders
    $stmt = $conn->prepare("DESCRIBE orders");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    $existing_columns = array_column($columns, 'Field');
    echo "<p>Các cột hiện tại trong bảng orders: " . implode(', ', $existing_columns) . "</p>";
    
    echo "<h2>2. Thêm các cột cần thiết</h2>";
    
    // Danh sách các cột cần thêm
    $columns_to_add = [
        'subtotal' => 'DECIMAL(10,2) NOT NULL DEFAULT 0',
        'final_amount' => 'DECIMAL(10,2) NOT NULL DEFAULT 0',
        'full_name' => 'VARCHAR(100) NOT NULL DEFAULT \'\'',
        'phone' => 'VARCHAR(20) NOT NULL DEFAULT \'\'',
        'email' => 'VARCHAR(100) DEFAULT NULL',
        'address' => 'TEXT NOT NULL DEFAULT \'\'',
        'city' => 'VARCHAR(50) NOT NULL DEFAULT \'\'',
        'district' => 'VARCHAR(50) NOT NULL DEFAULT \'\''
    ];
    
    foreach ($columns_to_add as $column => $definition) {
        if (!in_array($column, $existing_columns)) {
            try {
                $sql = "ALTER TABLE orders ADD COLUMN {$column} {$definition}";
                $conn->exec($sql);
                echo "<p style='color: green;'>✓ Đã thêm cột {$column}</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>✗ Lỗi khi thêm cột {$column}: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>- Cột {$column} đã tồn tại</p>";
        }
    }
    
    echo "<h2>3. Cập nhật bảng order_items</h2>";
    
    // Kiểm tra cấu trúc bảng order_items
    $stmt = $conn->prepare("DESCRIBE order_items");
    $stmt->execute();
    $order_items_columns = $stmt->fetchAll();
    $existing_order_items_columns = array_column($order_items_columns, 'Field');
    
    echo "<p>Các cột hiện tại trong bảng order_items: " . implode(', ', $existing_order_items_columns) . "</p>";
    
    // Danh sách các cột cần thêm cho order_items
    $order_items_columns_to_add = [
        'product_name' => 'VARCHAR(200) NOT NULL DEFAULT \'\'',
        'product_price' => 'DECIMAL(10,2) NOT NULL DEFAULT 0',
        'total_price' => 'DECIMAL(10,2) NOT NULL DEFAULT 0'
    ];
    
    foreach ($order_items_columns_to_add as $column => $definition) {
        if (!in_array($column, $existing_order_items_columns)) {
            try {
                $sql = "ALTER TABLE order_items ADD COLUMN {$column} {$definition}";
                $conn->exec($sql);
                echo "<p style='color: green;'>✓ Đã thêm cột {$column} vào order_items</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>✗ Lỗi khi thêm cột {$column}: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>- Cột {$column} đã tồn tại trong order_items</p>";
        }
    }
    
    echo "<h2>4. Cập nhật dữ liệu hiện tại</h2>";
    
    // Cập nhật subtotal và final_amount cho các đơn hàng hiện tại
    try {
        $sql = "UPDATE orders SET subtotal = total_amount - shipping_fee, final_amount = total_amount WHERE subtotal = 0 AND final_amount = 0";
        $conn->exec($sql);
        echo "<p style='color: green;'>✓ Đã cập nhật subtotal và final_amount</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠ Lỗi khi cập nhật dữ liệu: " . $e->getMessage() . "</p>";
    }
    
    // Cập nhật product_name cho các order_items hiện tại
    try {
        $sql = "UPDATE order_items oi 
                JOIN products p ON oi.product_id = p.id 
                SET oi.product_name = p.name, 
                    oi.product_price = oi.price, 
                    oi.total_price = oi.price * oi.quantity 
                WHERE oi.product_name = ''";
        $conn->exec($sql);
        echo "<p style='color: green;'>✓ Đã cập nhật product_name và giá cho order_items</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠ Lỗi khi cập nhật order_items: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>5. Tạo các bảng bổ sung</h2>";
    
    // Tạo bảng inventory_logs
    try {
        $sql = "CREATE TABLE IF NOT EXISTS inventory_logs (
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
        )";
        $conn->exec($sql);
        echo "<p style='color: green;'>✓ Đã tạo bảng inventory_logs</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠ Lỗi khi tạo inventory_logs: " . $e->getMessage() . "</p>";
    }
    
    // Tạo bảng order_status_logs
    try {
        $sql = "CREATE TABLE IF NOT EXISTS order_status_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            old_status VARCHAR(50),
            new_status VARCHAR(50) NOT NULL,
            changed_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE
        )";
        $conn->exec($sql);
        echo "<p style='color: green;'>✓ Đã tạo bảng order_status_logs</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠ Lỗi khi tạo order_status_logs: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>6. Kiểm tra cấu trúc sau khi cập nhật</h2>";
    
    // Kiểm tra lại cấu trúc bảng orders
    $stmt = $conn->prepare("DESCRIBE orders");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    echo "<h3>Bảng orders sau khi cập nhật:</h3>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li>{$column['Field']} - {$column['Type']}</li>";
    }
    echo "</ul>";
    
    echo "<h2 style='color: green;'>✓ Sửa lỗi database hoàn tất!</h2>";
    
    echo "<h2>7. Test luồng thanh toán</h2>";
    echo "<p><a href='gio-hang/index.php' target='_blank' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Giỏ Hàng</a></p>";
    echo "<p><a href='gio-hang/thanh-toan.php' target='_blank' style='background: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Thanh Toán</a></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Lỗi: " . $e->getMessage() . "</h2>";
}
?>
