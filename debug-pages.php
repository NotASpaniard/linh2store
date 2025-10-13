<?php
/**
 * Script debug các trang để kiểm tra ảnh
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/database.php';
require_once 'config/image-helper.php';

echo "<h1>🔍 Debug Các Trang</h1>";

// Test trang sản phẩm
echo "<h2>📦 Test Trang Sản Phẩm</h2>";
try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Lấy 3 sản phẩm đầu tiên
    $stmt = $conn->prepare("
        SELECT p.*, b.name as brand_name, c.name as category_name
        FROM products p 
        LEFT JOIN brands b ON p.brand_id = b.id 
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'active'
        LIMIT 3
    ");
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    echo "<p><strong>Số sản phẩm:</strong> " . count($products) . "</p>";
    
    foreach ($products as $product) {
        $image_url = getProductImage($product['id']);
        echo "<div style='border: 1px solid #ddd; margin: 10px; padding: 10px;'>";
        echo "<h4>ID {$product['id']}: {$product['name']}</h4>";
        echo "<p><strong>Ảnh:</strong> $image_url</p>";
        echo "<p><strong>File tồn tại:</strong> " . (file_exists($image_url) ? "✅ Có" : "❌ Không") . "</p>";
        echo "<img src='$image_url' style='width: 100px; height: 100px; object-fit: cover; border: 1px solid #ccc;' alt='Product Image'>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Lỗi: " . $e->getMessage() . "</p>";
}

// Test trang giỏ hàng
echo "<h2>🛒 Test Trang Giỏ Hàng</h2>";
try {
    // Lấy 3 sản phẩm đầu tiên (giả lập giỏ hàng)
    $stmt = $conn->prepare("
        SELECT p.id as product_id, p.name, p.price, p.sale_price, b.name as brand_name
        FROM products p 
        LEFT JOIN brands b ON p.brand_id = b.id
        WHERE p.status = 'active'
        LIMIT 3
    ");
    $stmt->execute();
    $cart_items = $stmt->fetchAll();
    
    echo "<p><strong>Số sản phẩm trong giỏ:</strong> " . count($cart_items) . "</p>";
    
    foreach ($cart_items as $item) {
        $image_url = getProductImage($item['product_id']);
        echo "<div style='border: 1px solid #ddd; margin: 10px; padding: 10px;'>";
        echo "<h4>ID {$item['product_id']}: {$item['name']}</h4>";
        echo "<p><strong>Ảnh:</strong> $image_url</p>";
        echo "<p><strong>File tồn tại:</strong> " . (file_exists($image_url) ? "✅ Có" : "❌ Không") . "</p>";
        echo "<img src='$image_url' style='width: 100px; height: 100px; object-fit: cover; border: 1px solid #ccc;' alt='Cart Item Image'>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Lỗi: " . $e->getMessage() . "</p>";
}

// Test trang thương hiệu
echo "<h2>🏷️ Test Trang Thương Hiệu</h2>";
try {
    $stmt = $conn->prepare("
        SELECT b.*, COUNT(p.id) as product_count 
        FROM brands b 
        LEFT JOIN products p ON b.id = p.brand_id AND p.status = 'active'
        WHERE b.status = 'active' 
        GROUP BY b.id 
        ORDER BY b.name
        LIMIT 3
    ");
    $stmt->execute();
    $brands = $stmt->fetchAll();
    
    echo "<p><strong>Số thương hiệu:</strong> " . count($brands) . "</p>";
    
    foreach ($brands as $brand) {
        $image_url = getBrandImage($brand['id']);
        echo "<div style='border: 1px solid #ddd; margin: 10px; padding: 10px;'>";
        echo "<h4>ID {$brand['id']}: {$brand['name']}</h4>";
        echo "<p><strong>Ảnh:</strong> $image_url</p>";
        echo "<p><strong>File tồn tại:</strong> " . (file_exists($image_url) ? "✅ Có" : "❌ Không") . "</p>";
        echo "<img src='$image_url' style='width: 100px; height: 100px; object-fit: cover; border: 1px solid #ccc;' alt='Brand Image'>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Lỗi: " . $e->getMessage() . "</p>";
}

// Test trang blog
echo "<h2>📝 Test Trang Blog</h2>";
try {
    $stmt = $conn->prepare("
        SELECT * FROM blog_posts 
        WHERE status = 'published' 
        ORDER BY created_at DESC 
        LIMIT 3
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll();
    
    echo "<p><strong>Số bài viết:</strong> " . count($posts) . "</p>";
    
    foreach ($posts as $post) {
        $image_url = getProductImage($post['id']);
        echo "<div style='border: 1px solid #ddd; margin: 10px; padding: 10px;'>";
        echo "<h4>ID {$post['id']}: {$post['title']}</h4>";
        echo "<p><strong>Ảnh:</strong> $image_url</p>";
        echo "<p><strong>File tồn tại:</strong> " . (file_exists($image_url) ? "✅ Có" : "❌ Không") . "</p>";
        echo "<img src='$image_url' style='width: 100px; height: 100px; object-fit: cover; border: 1px solid #ccc;' alt='Blog Image'>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Lỗi: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>🎯 Kết luận:</strong> Nếu tất cả ảnh đều hiển thị ✅, vấn đề có thể là ở frontend hoặc CSS.</p>";
echo "<p><a href='index.php'>← Về trang chủ</a> | <a href='san-pham/'>Xem sản phẩm</a> | <a href='gio-hang/'>Xem giỏ hàng</a></p>";
?>
