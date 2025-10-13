<?php
/**
 * Script lấy ảnh từ thư mục images/ và gắn vào database
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/database.php';

echo "<h1>📁 Lấy ảnh từ thư mục images/</h1>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Xóa tất cả hình ảnh cũ
    $conn->exec("DELETE FROM product_images");
    echo "<p>✅ Đã xóa hình ảnh cũ</p>";
    
    // Kiểm tra thư mục images
    if (!file_exists('images')) {
        echo "<p>❌ Thư mục images/ không tồn tại</p>";
        echo "<p>Vui lòng tạo thư mục images/ và đặt ảnh vào đó</p>";
        exit;
    }
    
    // Lấy danh sách file ảnh
    $image_files = [];
    $files = scandir('images');
    
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $image_files[] = $file;
            }
        }
    }
    
    echo "<p>📊 Tìm thấy " . count($image_files) . " file ảnh trong thư mục images/</p>";
    
    if (empty($image_files)) {
        echo "<p>❌ Không có file ảnh nào trong thư mục images/</p>";
        echo "<p>Vui lòng đặt ảnh vào thư mục images/</p>";
        exit;
    }
    
    // Lấy danh sách sản phẩm
    $stmt = $conn->query("SELECT id, name FROM products ORDER BY id");
    $products = $stmt->fetchAll();
    
    echo "<p>📦 Có " . count($products) . " sản phẩm trong database</p>";
    
    $success_count = 0;
    $error_count = 0;
    
    // Gắn ảnh cho từng sản phẩm
    foreach ($products as $index => $product) {
        $product_id = $product['id'];
        $product_name = $product['name'];
        
        // Lấy ảnh theo thứ tự (lặp lại nếu ảnh ít hơn sản phẩm)
        $image_file = $image_files[$index % count($image_files)];
        $image_path = "images/" . $image_file;
        
        try {
            $stmt = $conn->prepare("
                INSERT INTO product_images (product_id, image_url, alt_text, is_primary) 
                VALUES (?, ?, ?, 1)
            ");
            $stmt->execute([
                $product_id, 
                $image_path, 
                $product_name
            ]);
            $success_count++;
            echo "<p>✅ Đã gắn ảnh cho sản phẩm $product_id: $product_name</p>";
            echo "<p>   Ảnh: $image_path</p>";
        } catch (Exception $e) {
            $error_count++;
            echo "<p>❌ Lỗi sản phẩm $product_id: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h2>📊 Kết quả:</h2>";
    echo "<p>✅ Thành công: $success_count sản phẩm</p>";
    echo "<p>❌ Lỗi: $error_count sản phẩm</p>";
    
    if ($success_count > 0) {
        echo "<h2>🎉 Hoàn thành!</h2>";
        echo "<p>Bây giờ bạn có thể xem hình ảnh tại:</p>";
        echo "<ul>";
        echo "<li><a href='index.php'>🏠 Trang chủ</a></li>";
        echo "<li><a href='san-pham/'>🛍️ Trang sản phẩm</a></li>";
        echo "<li><a href='gio-hang/'>🛒 Trang giỏ hàng</a></li>";
        echo "</ul>";
        
        echo "<h3>📋 Danh sách ảnh đã sử dụng:</h3>";
        echo "<ul>";
        foreach ($image_files as $file) {
            echo "<li>$file</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Lỗi:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
