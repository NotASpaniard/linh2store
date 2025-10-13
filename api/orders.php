<?php
/**
 * API xử lý đơn hàng
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/session.php';
require_once '../config/database.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_SESSION['user_id'];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    switch ($method) {
        case 'GET':
            // Lấy chi tiết đơn hàng
            $order_id = intval($_GET['id'] ?? 0);
            
            if (!$order_id) {
                throw new Exception('ID đơn hàng không hợp lệ');
            }
            
            // Lấy thông tin đơn hàng
            $stmt = $conn->prepare("
                SELECT o.*, u.username, u.email as user_email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.id = ? AND o.user_id = ?
            ");
            $stmt->execute([$order_id, $user_id]);
            $order = $stmt->fetch();
            
            if (!$order) {
                throw new Exception('Đơn hàng không tồn tại');
            }
            
            // Lấy chi tiết đơn hàng
            $stmt = $conn->prepare("
                SELECT oi.*, p.name, p.sku, b.name as brand_name, pi.image_url
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$order_id]);
            $order_items = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'order' => $order,
                'items' => $order_items
            ]);
            break;
            
        case 'POST':
            // Xử lý các thao tác với đơn hàng
            $input = json_decode(file_get_contents('php://input'), true);
            $action = $input['action'] ?? '';
            
            switch ($action) {
                case 'cancel':
                    // Hủy đơn hàng
                    $order_id = intval($input['order_id'] ?? 0);
                    
                    if (!$order_id) {
                        throw new Exception('ID đơn hàng không hợp lệ');
                    }
                    
                    // Kiểm tra đơn hàng thuộc về user và có thể hủy
                    $stmt = $conn->prepare("
                        SELECT id, status FROM orders 
                        WHERE id = ? AND user_id = ? AND status = 'pending'
                    ");
                    $stmt->execute([$order_id, $user_id]);
                    $order = $stmt->fetch();
                    
                    if (!$order) {
                        throw new Exception('Đơn hàng không thể hủy');
                    }
                    
                    // Cập nhật trạng thái đơn hàng
                    $stmt = $conn->prepare("
                        UPDATE orders SET status = 'cancelled', updated_at = NOW() 
                        WHERE id = ?
                    ");
                    $stmt->execute([$order_id]);
                    
                    // Ghi log thay đổi trạng thái
                    $stmt = $conn->prepare("
                        INSERT INTO order_status_logs (order_id, old_status, new_status, changed_by, created_at)
                        VALUES (?, 'pending', 'cancelled', ?, NOW())
                    ");
                    $stmt->execute([$order_id, $user_id]);
                    
                    // Hoàn trả tồn kho
                    $stmt = $conn->prepare("
                        SELECT oi.product_id, oi.quantity, p.stock_quantity
                        FROM order_items oi
                        LEFT JOIN products p ON oi.product_id = p.id
                        WHERE oi.order_id = ?
                    ");
                    $stmt->execute([$order_id]);
                    $items = $stmt->fetchAll();
                    
                    foreach ($items as $item) {
                        $new_stock = $item['stock_quantity'] + $item['quantity'];
                        
                        // Cập nhật tồn kho
                        $stmt = $conn->prepare("
                            UPDATE products SET stock_quantity = ? WHERE id = ?
                        ");
                        $stmt->execute([$new_stock, $item['product_id']]);
                        
                        // Ghi log tồn kho
                        $stmt = $conn->prepare("
                            INSERT INTO inventory_logs (product_id, type, quantity, old_quantity, new_quantity, created_by, created_at)
                            VALUES (?, 'return', ?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([$item['product_id'], $item['quantity'], $item['stock_quantity'], $new_stock, $user_id]);
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Đã hủy đơn hàng thành công'
                    ]);
                    break;
                    
                case 'reorder':
                    // Đặt lại đơn hàng
                    $order_id = intval($input['order_id'] ?? 0);
                    
                    if (!$order_id) {
                        throw new Exception('ID đơn hàng không hợp lệ');
                    }
                    
                    // Kiểm tra đơn hàng thuộc về user
                    $stmt = $conn->prepare("
                        SELECT id FROM orders 
                        WHERE id = ? AND user_id = ?
                    ");
                    $stmt->execute([$order_id, $user_id]);
                    $order = $stmt->fetch();
                    
                    if (!$order) {
                        throw new Exception('Đơn hàng không tồn tại');
                    }
                    
                    // Lấy chi tiết đơn hàng cũ
                    $stmt = $conn->prepare("
                        SELECT product_id, product_color_id, quantity
                        FROM order_items
                        WHERE order_id = ?
                    ");
                    $stmt->execute([$order_id]);
                    $old_items = $stmt->fetchAll();
                    
                    // Xóa giỏ hàng hiện tại
                    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    
                    // Thêm sản phẩm vào giỏ hàng
                    foreach ($old_items as $item) {
                        // Kiểm tra sản phẩm còn tồn tại không
                        $stmt = $conn->prepare("
                            SELECT id, stock_quantity FROM products 
                            WHERE id = ? AND status = 'active'
                        ");
                        $stmt->execute([$item['product_id']]);
                        $product = $stmt->fetch();
                        
                        if ($product && $product['stock_quantity'] >= $item['quantity']) {
                            $stmt = $conn->prepare("
                                INSERT INTO cart (user_id, product_id, product_color_id, quantity) 
                                VALUES (?, ?, ?, ?)
                            ");
                            $stmt->execute([$user_id, $item['product_id'], $item['product_color_id'], $item['quantity']]);
                        }
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Đã thêm sản phẩm vào giỏ hàng'
                    ]);
                    break;
                    
                default:
                    throw new Exception('Hành động không hợp lệ');
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
