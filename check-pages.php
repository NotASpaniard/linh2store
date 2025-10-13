<?php
/**
 * Script kiểm tra trực tiếp các trang
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

echo "<h1>🔍 Kiểm Tra Các Trang</h1>";

echo "<h2>📋 Danh sách các trang cần kiểm tra:</h2>";
echo "<ul>";
echo "<li><a href='index.php' target='_blank'>🏠 Trang chủ</a></li>";
echo "<li><a href='san-pham/' target='_blank'>📦 Trang sản phẩm</a></li>";
echo "<li><a href='gio-hang/' target='_blank'>🛒 Trang giỏ hàng</a></li>";
echo "<li><a href='thuong-hieu/' target='_blank'>🏷️ Trang thương hiệu</a></li>";
echo "<li><a href='blog/' target='_blank'>📝 Trang blog</a></li>";
echo "</ul>";

echo "<h2>🧪 Test trực tiếp hàm getProductImage</h2>";
require_once 'config/image-helper.php';

echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;'>";

for ($i = 1; $i <= 6; $i++) {
    $image_url = getProductImage($i);
    echo "<div style='border: 1px solid #ddd; padding: 15px; text-align: center;'>";
    echo "<h4>Sản phẩm ID: $i</h4>";
    echo "<p><strong>Đường dẫn:</strong><br><small>$image_url</small></p>";
    echo "<p><strong>Tồn tại:</strong> " . (file_exists($image_url) ? "✅ Có" : "❌ Không") . "</p>";
    echo "<img src='$image_url' style='width: 150px; height: 150px; object-fit: cover; border: 1px solid #ccc; margin: 10px 0;' alt='Product $i'>";
    echo "</div>";
}

echo "</div>";

echo "<h2>🔧 Debug thông tin hệ thống</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Current Directory:</strong> " . getcwd() . "</p>";
echo "<p><strong>Images Directory:</strong> " . __DIR__ . '/images/' . "</p>";
echo "<p><strong>Images Directory Exists:</strong> " . (is_dir(__DIR__ . '/images/') ? "✅ Có" : "❌ Không") . "</p>";

$image_files = glob(__DIR__ . '/images/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
echo "<p><strong>Số ảnh trong thư mục:</strong> " . count($image_files) . "</p>";

if (!empty($image_files)) {
    echo "<h3>📁 Danh sách ảnh:</h3>";
    echo "<ul>";
    foreach ($image_files as $file) {
        echo "<li>" . basename($file) . " (" . filesize($file) . " bytes)</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<p><strong>💡 Hướng dẫn:</strong></p>";
echo "<ol>";
echo "<li>Nhấn vào các link trên để mở từng trang</li>";
echo "<li>Kiểm tra xem ảnh có hiển thị không</li>";
echo "<li>Nếu không hiển thị, mở Developer Tools (F12) và kiểm tra Console tab</li>";
echo "<li>Kiểm tra Network tab để xem ảnh có được load không</li>";
echo "</ol>";
?>
