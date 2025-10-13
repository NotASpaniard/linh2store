<?php
/**
 * Helper functions for image handling
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

/**
 * Lấy đường dẫn ảnh sản phẩm từ thư mục images/
 * @param int $product_id ID của sản phẩm
 * @param string $fallback_url URL ảnh mặc định nếu không tìm thấy
 * @return string Đường dẫn ảnh
 */
function getProductImage($product_id, $fallback_url = 'https://via.placeholder.com/300x300/E3F2FD/EC407A?text=No+Image') {
    $images_dir = __DIR__ . '/../images/';
    
    // Danh sách các định dạng ảnh được hỗ trợ
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Tìm ảnh theo pattern: product_{id}.{extension}
    foreach ($allowed_extensions as $ext) {
        $image_path = $images_dir . "product_{$product_id}.{$ext}";
        if (file_exists($image_path)) {
            return "images/product_{$product_id}.{$ext}";
        }
    }
    
    // Nếu không tìm thấy ảnh cụ thể, lấy ảnh theo thứ tự từ thư mục
    $image_files = glob($images_dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
    
    if (!empty($image_files)) {
        // Sử dụng product_id để chọn ảnh theo thứ tự (có thể lặp lại)
        $image_index = ($product_id - 1) % count($image_files);
        $selected_image = $image_files[$image_index];
        $relative_path = 'images/' . basename($selected_image);
        return $relative_path;
    }
    
    return $fallback_url;
}

/**
 * Lấy đường dẫn ảnh thương hiệu từ thư mục images/
 * @param int $brand_id ID của thương hiệu
 * @param string $fallback_url URL ảnh mặc định nếu không tìm thấy
 * @return string Đường dẫn ảnh
 */
function getBrandImage($brand_id, $fallback_url = 'https://via.placeholder.com/150x80/E3F2FD/EC407A?text=Brand') {
    $images_dir = __DIR__ . '/../images/';
    
    // Danh sách các định dạng ảnh được hỗ trợ
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Tìm ảnh theo pattern: brand_{id}.{extension}
    foreach ($allowed_extensions as $ext) {
        $image_path = $images_dir . "brand_{$brand_id}.{$ext}";
        if (file_exists($image_path)) {
            return "images/brand_{$brand_id}.{$ext}";
        }
    }
    
    // Nếu không tìm thấy ảnh cụ thể, lấy ảnh theo thứ tự từ thư mục
    $image_files = glob($images_dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
    
    if (!empty($image_files)) {
        // Sử dụng brand_id để chọn ảnh theo thứ tự (có thể lặp lại)
        $image_index = ($brand_id - 1) % count($image_files);
        $selected_image = $image_files[$image_index];
        $relative_path = 'images/' . basename($selected_image);
        return $relative_path;
    }
    
    return $fallback_url;
}

/**
 * Lấy danh sách tất cả ảnh trong thư mục images/
 * @return array Danh sách đường dẫn ảnh
 */
function getAllImages() {
    $images_dir = __DIR__ . '/../images/';
    $image_files = glob($images_dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
    
    $images = [];
    foreach ($image_files as $file) {
        $images[] = 'images/' . basename($file);
    }
    
    return $images;
}

/**
 * Kiểm tra xem thư mục images/ có tồn tại và có ảnh không
 * @return bool True nếu có ảnh, false nếu không
 */
function hasImages() {
    $images_dir = __DIR__ . '/../images/';
    return is_dir($images_dir) && !empty(glob($images_dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE));
}
?>
