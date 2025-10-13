<?php
/**
 * Script test ảnh từ thư mục images/
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/database.php';
require_once 'config/image-helper.php';

echo "<h1>🧪 Test Hệ Thống Ảnh</h1>";

// Kiểm tra thư mục images
$images_dir = __DIR__ . '/images/';
echo "<h2>📁 Kiểm tra thư mục images/</h2>";
echo "<p><strong>Đường dẫn:</strong> $images_dir</p>";
echo "<p><strong>Tồn tại:</strong> " . (is_dir($images_dir) ? "✅ Có" : "❌ Không") . "</p>";

if (is_dir($images_dir)) {
    $image_files = glob($images_dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
    echo "<p><strong>Số ảnh tìm thấy:</strong> " . count($image_files) . "</p>";
    
    if (!empty($image_files)) {
        echo "<h3>🖼️ Danh sách ảnh:</h3>";
        echo "<ul>";
        foreach (array_slice($image_files, 0, 10) as $file) {
            echo "<li>" . basename($file) . "</li>";
        }
        if (count($image_files) > 10) {
            echo "<li>... và " . (count($image_files) - 10) . " ảnh khác</li>";
        }
        echo "</ul>";
    }
}

// Test hàm getProductImage
echo "<h2>🔧 Test hàm getProductImage()</h2>";
echo "<p><strong>Test với product_id = 1:</strong> " . getProductImage(1) . "</p>";
echo "<p><strong>Test với product_id = 2:</strong> " . getProductImage(2) . "</p>";
echo "<p><strong>Test với product_id = 3:</strong> " . getProductImage(3) . "</p>";

// Test hàm getBrandImage
echo "<h2>🏷️ Test hàm getBrandImage()</h2>";
echo "<p><strong>Test với brand_id = 1:</strong> " . getBrandImage(1) . "</p>";
echo "<p><strong>Test với brand_id = 2:</strong> " . getBrandImage(2) . "</p>";

// Test database connection
echo "<h2>🗄️ Test kết nối database</h2>";
try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "<p>✅ Kết nối database thành công</p>";
    
    // Lấy 5 sản phẩm đầu tiên
    $stmt = $conn->prepare("SELECT id, name FROM products LIMIT 5");
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    echo "<h3>📦 5 sản phẩm đầu tiên:</h3>";
    echo "<ul>";
    foreach ($products as $product) {
        $image_url = getProductImage($product['id']);
        echo "<li><strong>ID {$product['id']}:</strong> {$product['name']} → <em>{$image_url}</em></li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>❌ Lỗi kết nối database: " . $e->getMessage() . "</p>";
}

// Test hiển thị ảnh
echo "<h2>🖼️ Test hiển thị ảnh</h2>";
if (hasImages()) {
    echo "<p>✅ Có ảnh trong thư mục images/</p>";
    
    // Hiển thị 3 ảnh đầu tiên
    $all_images = getAllImages();
    echo "<h3>Ảnh mẫu:</h3>";
    foreach (array_slice($all_images, 0, 3) as $image) {
        echo "<div style='margin: 10px; display: inline-block;'>";
        echo "<img src='$image' style='width: 150px; height: 150px; object-fit: cover; border: 1px solid #ddd;' alt='Test Image'>";
        echo "<br><small>$image</small>";
        echo "</div>";
    }
} else {
    echo "<p>❌ Không có ảnh trong thư mục images/</p>";
    echo "<p><strong>Hướng dẫn:</strong> Đặt ảnh vào thư mục <code>images/</code> để test</p>";
}

echo "<hr>";
echo "<p><strong>🎯 Kết luận:</strong> Nếu tất cả test đều ✅, hệ thống ảnh đã sẵn sàng!</p>";
echo "<p><a href='index.php'>← Về trang chủ</a> | <a href='san-pham/'>Xem sản phẩm</a> | <a href='gio-hang/'>Xem giỏ hàng</a></p>";
?>
