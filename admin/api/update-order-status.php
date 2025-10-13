<?php
/**
 * API cập nhật trạng thái đơn hàng
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../../config/session.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

// Kiểm tra quyền admin
if (!isLoggedIn()) {
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
    $order_id = intval($input['order_id'] ?? 0);
    $status = trim($input['status'] ?? '');
    
    if (!$order_id || !$status) {
        throw new Exception('Thông tin không hợp lệ');
    }
    
    $valid_statuses = ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'];
    if (!in_array($status, $valid_statuses)) {
        throw new Exception('Trạng thái không hợp lệ');
    }
    
    $db = new Database();
    $conn = $db->getConnection();
    
    // Kiểm tra đơn hàng tồn tại
    $stmt = $conn->prepare("SELECT id, status FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('Đơn hàng không tồn tại');
    }
    
    // Cập nhật trạng thái
    $stmt = $conn->prepare("
        UPDATE orders 
        SET status = ?, updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    $stmt->execute([$status, $order_id]);
    
    // Ghi log thay đổi
    $stmt = $conn->prepare("
        INSERT INTO order_status_logs (order_id, old_status, new_status, changed_by, created_at)
        VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)
    ");
    $stmt->execute([$order_id, $order['status'], $status, $user['id']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Đã cập nhật trạng thái đơn hàng'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
