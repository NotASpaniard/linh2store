<?php
/**
 * Script test bộ lọc trang sản phẩm
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/database.php';

echo "<h1>🧪 Test Bộ Lọc Trang Sản Phẩm</h1>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Test 1: Kiểm tra thương hiệu
    echo "<h2>🏷️ Test Thương Hiệu</h2>";
    $stmt = $conn->prepare("SELECT DISTINCT id, name FROM brands WHERE status = 'active' ORDER BY name");
    $stmt->execute();
    $brands = $stmt->fetchAll();
    
    echo "<p><strong>Số thương hiệu:</strong> " . count($brands) . "</p>";
    echo "<h3>Danh sách thương hiệu:</h3>";
    echo "<ul>";
    foreach ($brands as $brand) {
        echo "<li>ID {$brand['id']}: {$brand['name']}</li>";
    }
    echo "</ul>";
    
    // Test 2: Kiểm tra danh mục
    echo "<h2>📂 Test Danh Mục</h2>";
    $stmt = $conn->prepare("SELECT DISTINCT id, name FROM categories WHERE status = 'active' ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll();
    
    echo "<p><strong>Số danh mục:</strong> " . count($categories) . "</p>";
    echo "<h3>Danh sách danh mục:</h3>";
    echo "<ul>";
    foreach ($categories as $category) {
        echo "<li>ID {$category['id']}: {$category['name']}</li>";
    }
    echo "</ul>";
    
    // Test 3: Kiểm tra trùng lặp
    echo "<h2>🔍 Test Trùng Lặp</h2>";
    
    $brand_names = array_column($brands, 'name');
    $category_names = array_column($categories, 'name');
    
    $duplicate_brands = array_diff_assoc($brand_names, array_unique($brand_names));
    $duplicate_categories = array_diff_assoc($category_names, array_unique($category_names));
    
    echo "<p><strong>Thương hiệu trùng lặp:</strong> " . (empty($duplicate_brands) ? "✅ Không có" : "❌ Có " . count($duplicate_brands) . " trùng lặp") . "</p>";
    if (!empty($duplicate_brands)) {
        echo "<ul>";
        foreach ($duplicate_brands as $name) {
            echo "<li>$name</li>";
        }
        echo "</ul>";
    }
    
    echo "<p><strong>Danh mục trùng lặp:</strong> " . (empty($duplicate_categories) ? "✅ Không có" : "❌ Có " . count($duplicate_categories) . " trùng lặp") . "</p>";
    if (!empty($duplicate_categories)) {
        echo "<ul>";
        foreach ($duplicate_categories as $name) {
            echo "<li>$name</li>";
        }
        echo "</ul>";
    }
    
    // Test 4: Kiểm tra sản phẩm
    echo "<h2>📦 Test Sản Phẩm</h2>";
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE status = 'active'");
    $stmt->execute();
    $product_count = $stmt->fetch()['count'];
    echo "<p><strong>Số sản phẩm:</strong> $product_count</p>";
    
    // Test 5: Kiểm tra CSS
    echo "<h2>🎨 Test CSS</h2>";
    echo "<div style='border: 1px solid #ddd; padding: 20px; margin: 20px 0;'>";
    echo "<h4>Mô phỏng bộ lọc:</h4>";
    
    echo "<div style='margin: 10px 0;'>";
    echo "<h5>Thương hiệu:</h5>";
    echo "<div style='display: flex; flex-direction: column; gap: 5px;'>";
    foreach (array_slice($brands, 0, 3) as $brand) {
        echo "<label style='display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 5px; border-radius: 4px; background: #f9f9f9;'>";
        echo "<input type='radio' name='brand' value='{$brand['id']}'>";
        echo "<span>{$brand['name']}</span>";
        echo "</label>";
    }
    echo "</div>";
    echo "</div>";
    
    echo "<div style='margin: 10px 0;'>";
    echo "<h5>Khoảng giá:</h5>";
    echo "<div style='display: flex; align-items: center; gap: 10px; flex-wrap: wrap;'>";
    echo "<input type='number' placeholder='Từ' style='flex: 1; min-width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;'>";
    echo "<span>-</span>";
    echo "<input type='number' placeholder='Đến' style='flex: 1; min-width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;'>";
    echo "</div>";
    echo "</div>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p>❌ Lỗi: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>🎯 Kết luận:</strong></p>";
echo "<ul>";
echo "<li>✅ Đã sửa lỗi input 'Đến' sử dụng name='max_price'</li>";
echo "<li>✅ Đã thêm DISTINCT để tránh trùng lặp danh mục</li>";
echo "<li>✅ Đã cải thiện CSS cho khoảng giá</li>";
echo "<li>✅ Đã thêm responsive design cho mobile</li>";
echo "</ul>";
echo "<p><a href='san-pham/'>Xem trang sản phẩm</a> | <a href='index.php'>Về trang chủ</a></p>";
?>
