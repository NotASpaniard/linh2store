<?php
/**
 * API tìm kiếm sản phẩm
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Xử lý OPTIONS request
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = trim($_GET['q'] ?? '');
    
    if (empty($query)) {
        echo json_encode([
            'success' => false,
            'message' => 'Vui lòng nhập từ khóa tìm kiếm'
        ]);
        exit;
    }
    
    // Tìm kiếm sản phẩm
    $sql = "
        SELECT p.id, p.name, p.price, p.image, p.slug,
               b.name as brand_name, c.name as category_name
        FROM products p 
        LEFT JOIN brands b ON p.brand_id = b.id 
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'active' 
        AND (p.name LIKE ? OR p.description LIKE ?)
        ORDER BY p.created_at DESC
        LIMIT 10
    ";
    
    $stmt = $conn->prepare($sql);
    $searchTerm = "%$query%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $products = $stmt->fetchAll();
    
    // Format kết quả
    $results = [];
    foreach ($products as $product) {
        $results[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => floatval($product['price']),
            'image' => $product['image'] ?: 'assets/images/no-image.jpg',
            'slug' => $product['slug'],
            'brand' => $product['brand_name'],
            'category' => $product['category_name']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'results' => $results,
        'count' => count($results)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage()
    ]);
}
?>
