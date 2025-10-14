<?php
/**
 * Cập nhật cấu trúc database
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/database.php';

echo "<h1>Cập nhật cấu trúc database</h1>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<h2>1. Kiểm tra cấu trúc hiện tại</h2>";
    
    // Kiểm tra cấu trúc bảng orders
    $stmt = $conn->prepare("DESCRIBE orders");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    echo "<h3>Bảng orders hiện tại:</h3>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li>{$column['Field']} - {$column['Type']}</li>";
    }
    echo "</ul>";
    
    echo "<h2>2. Cập nhật cấu trúc bảng orders</h2>";
    
    // Kiểm tra và thêm các cột cần thiết
    $required_columns = [
        'subtotal' => 'DECIMAL(10,2) NOT NULL DEFAULT 0',
        'final_amount' => 'DECIMAL(10,2) NOT NULL DEFAULT 0',
        'full_name' => 'VARCHAR(100) NOT NULL',
        'phone' => 'VARCHAR(20) NOT NULL',
        'email' => 'VARCHAR(100)',
        'address' => 'TEXT NOT NULL',
        'city' => 'VARCHAR(50) NOT NULL',
        'district' => 'VARCHAR(50) NOT NULL'
    ];
    
    foreach ($required_columns as $column => $definition) {
        // Kiểm tra xem cột đã tồn tại chưa
        $stmt = $conn->prepare("SHOW COLUMNS FROM orders LIKE ?");
        $stmt->execute([$column]);
        
        if (!$stmt->fetch()) {
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
    
    echo "<h2>3. Cập nhật cấu trúc bảng order_items</h2>";
    
    // Kiểm tra cấu trúc bảng order_items
    $stmt = $conn->prepare("DESCRIBE order_items");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    echo "<h3>Bảng order_items hiện tại:</h3>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li>{$column['Field']} - {$column['Type']}</li>";
    }
    echo "</ul>";
    
    // Kiểm tra và thêm các cột cần thiết cho order_items
    $required_order_items_columns = [
        'product_name' => 'VARCHAR(200) NOT NULL',
        'product_price' => 'DECIMAL(10,2) NOT NULL',
        'total_price' => 'DECIMAL(10,2) NOT NULL'
    ];
    
    foreach ($required_order_items_columns as $column => $definition) {
        // Kiểm tra xem cột đã tồn tại chưa
        $stmt = $conn->prepare("SHOW COLUMNS FROM order_items LIKE ?");
        $stmt->execute([$column]);
        
        if (!$stmt->fetch()) {
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
    
    echo "<h2>4. Kiểm tra cấu trúc sau khi cập nhật</h2>";
    
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
    
    // Kiểm tra lại cấu trúc bảng order_items
    $stmt = $conn->prepare("DESCRIBE order_items");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    echo "<h3>Bảng order_items sau khi cập nhật:</h3>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li>{$column['Field']} - {$column['Type']}</li>";
    }
    echo "</ul>";
    
    echo "<h2 style='color: green;'>✓ Cập nhật database hoàn tất!</h2>";
    
    echo "<h2>5. Test luồng thanh toán</h2>";
    echo "<p><a href='gio-hang/index.php' target='_blank' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Giỏ Hàng</a></p>";
    echo "<p><a href='gio-hang/thanh-toan.php' target='_blank' style='background: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Thanh Toán</a></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Lỗi: " . $e->getMessage() . "</h2>";
}
?>
