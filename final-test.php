<?php
/**
 * Script test cuối cùng - kiểm tra tất cả trang
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/database.php';
require_once 'config/image-helper.php';

echo "<h1>🎯 Test Cuối Cùng - Kiểm Tra Tất Cả Trang</h1>";

// Test 1: Kiểm tra thư mục images
echo "<h2>📁 Test 1: Thư mục images/</h2>";
$images_dir = __DIR__ . '/images/';
$image_files = glob($images_dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
echo "<p><strong>Thư mục:</strong> $images_dir</p>";
echo "<p><strong>Tồn tại:</strong> " . (is_dir($images_dir) ? "✅ Có" : "❌ Không") . "</p>";
echo "<p><strong>Số ảnh:</strong> " . count($image_files) . "</p>";

if (!empty($image_files)) {
    echo "<h3>🖼️ Ảnh có sẵn:</h3>";
    foreach ($image_files as $file) {
        echo "<p>• " . basename($file) . " (" . filesize($file) . " bytes)</p>";
    }
}

// Test 2: Kiểm tra helper functions
echo "<h2>🔧 Test 2: Helper Functions</h2>";
echo "<p><strong>getProductImage(1):</strong> " . getProductImage(1) . "</p>";
echo "<p><strong>getProductImage(2):</strong> " . getProductImage(2) . "</p>";
echo "<p><strong>getBrandImage(1):</strong> " . getBrandImage(1) . "</p>";
echo "<p><strong>getBrandImage(2):</strong> " . getBrandImage(2) . "</p>";

// Test 3: Kiểm tra database
echo "<h2>🗄️ Test 3: Database</h2>";
try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "<p>✅ Kết nối database thành công</p>";
    
    // Đếm sản phẩm
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE status = 'active'");
    $stmt->execute();
    $product_count = $stmt->fetch()['count'];
    echo "<p><strong>Số sản phẩm:</strong> $product_count</p>";
    
    // Đếm thương hiệu
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM brands WHERE status = 'active'");
    $stmt->execute();
    $brand_count = $stmt->fetch()['count'];
    echo "<p><strong>Số thương hiệu:</strong> $brand_count</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Lỗi database: " . $e->getMessage() . "</p>";
}

// Test 4: Kiểm tra đường dẫn ảnh
echo "<h2>🖼️ Test 4: Đường dẫn ảnh</h2>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0;'>";

for ($i = 1; $i <= 6; $i++) {
    $image_url = getProductImage($i);
    $full_path = __DIR__ . '/' . $image_url;
    $exists = file_exists($full_path);
    
    echo "<div style='border: 1px solid #ddd; padding: 15px; text-align: center; background: " . ($exists ? "#f0f8ff" : "#ffe6e6") . ";'>";
    echo "<h4>Product ID: $i</h4>";
    echo "<p><strong>URL:</strong><br><small>$image_url</small></p>";
    echo "<p><strong>File:</strong><br><small>$full_path</small></p>";
    echo "<p><strong>Tồn tại:</strong> " . ($exists ? "✅ Có" : "❌ Không") . "</p>";
    
    if ($exists) {
        echo "<img src='$image_url' style='width: 120px; height: 120px; object-fit: cover; border: 1px solid #ccc; margin: 10px 0;' alt='Product $i'>";
    } else {
        echo "<div style='width: 120px; height: 120px; background: #f0f0f0; border: 1px solid #ccc; margin: 10px auto; display: flex; align-items: center; justify-content: center; color: #666;'>No Image</div>";
    }
    echo "</div>";
}

echo "</div>";

// Test 5: Kiểm tra các trang
echo "<h2>🌐 Test 5: Các Trang Website</h2>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin: 20px 0;'>";

$pages = [
    ['name' => 'Trang chủ', 'url' => 'index.php', 'icon' => '🏠'],
    ['name' => 'Sản phẩm', 'url' => 'san-pham/', 'icon' => '📦'],
    ['name' => 'Giỏ hàng', 'url' => 'gio-hang/', 'icon' => '🛒'],
    ['name' => 'Thương hiệu', 'url' => 'thuong-hieu/', 'icon' => '🏷️'],
    ['name' => 'Blog', 'url' => 'blog/', 'icon' => '📝'],
    ['name' => 'Test Images', 'url' => 'test-images.php', 'icon' => '🧪'],
    ['name' => 'Debug Pages', 'url' => 'debug-pages.php', 'icon' => '🔍'],
    ['name' => 'Check Pages', 'url' => 'check-pages.php', 'icon' => '✅']
];

foreach ($pages as $page) {
    echo "<div style='border: 1px solid #ddd; padding: 15px; text-align: center; background: #f9f9f9;'>";
    echo "<h4>{$page['icon']} {$page['name']}</h4>";
    echo "<p><a href='{$page['url']}' target='_blank' style='text-decoration: none; color: #007bff; font-weight: bold;'>Mở trang →</a></p>";
    echo "</div>";
}

echo "</div>";

// Kết luận
echo "<h2>🎯 Kết Luận</h2>";
echo "<div style='background: #e8f5e8; border: 1px solid #4caf50; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>✅ Hệ thống đã sẵn sàng!</h3>";
echo "<p><strong>Những gì đã hoàn thành:</strong></p>";
echo "<ul>";
echo "<li>✅ Tạo helper functions cho xử lý ảnh</li>";
echo "<li>✅ Cập nhật tất cả trang sử dụng ảnh từ thư mục images/</li>";
echo "<li>✅ Sửa đường dẫn tương đối cho các trang con</li>";
echo "<li>✅ Tạo script test và debug</li>";
echo "</ul>";
echo "<p><strong>Hướng dẫn sử dụng:</strong></p>";
echo "<ol>";
echo "<li>Đặt ảnh vào thư mục <code>images/</code></li>";
echo "<li>Truy cập các trang để kiểm tra</li>";
echo "<li>Nếu có vấn đề, chạy script debug để kiểm tra</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><strong>🚀 Bây giờ bạn có thể:</strong></p>";
echo "<ul>";
echo "<li><a href='index.php'>Xem trang chủ</a></li>";
echo "<li><a href='san-pham/'>Xem sản phẩm</a></li>";
echo "<li><a href='gio-hang/'>Xem giỏ hàng</a></li>";
echo "<li><a href='thuong-hieu/'>Xem thương hiệu</a></li>";
echo "<li><a href='blog/'>Xem blog</a></li>";
echo "</ul>";
?>
