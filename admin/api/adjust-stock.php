<?php
/**
 * API điều chỉnh tồn kho
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../../config/auth-middleware.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

// Kiểm tra quyền admin
if (!AuthMiddleware::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit();
}

$user = getCurrentUser();
if (!$user || $user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $product_id = intval($input['product_id'] ?? 0);
    $quantity = intval($input['quantity'] ?? 0);
    
    if (!$product_id || $quantity < 0) {
        throw new Exception('Thông tin không hợp lệ');
    }
    
    $db = new Database();
    $conn = $db->getConnection();
    
    // Kiểm tra sản phẩm tồn tại
    $stmt = $conn->prepare("SELECT id, name, stock_quantity FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        throw new Exception('Sản phẩm không tồn tại');
    }
    
    $old_quantity = $product['stock_quantity'];
    
    // Cập nhật tồn kho
    $stmt = $conn->prepare("
        UPDATE products 
        SET stock_quantity = ?, updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    $stmt->execute([$quantity, $product_id]);
    
    // Ghi log điều chỉnh
    $stmt = $conn->prepare("
        INSERT INTO inventory_logs (product_id, type, quantity, old_quantity, new_quantity, created_by, created_at)
        VALUES (?, 'adjust', ?, ?, ?, ?, CURRENT_TIMESTAMP)
    ");
    $stmt->execute([$product_id, $quantity - $old_quantity, $old_quantity, $quantity, $user['id']]);
    
    echo json_encode([
        'success' => true,
        'message' => "Đã điều chỉnh tồn kho từ {$old_quantity} thành {$quantity}"
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
