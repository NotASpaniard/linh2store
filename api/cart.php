<?php
/**
 * API xử lý giỏ hàng
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/auth-middleware.php';
require_once '../config/database.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
$user = AuthMiddleware::requireLogin();
$method = $_SERVER['REQUEST_METHOD'];
$user_id = $user['id'];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    switch ($method) {
        case 'GET':
            // Lấy giỏ hàng
            $action = $_GET['action'] ?? '';
            
            if ($action === 'count') {
                // Chỉ trả về số lượng
                $stmt = $conn->prepare("
                    SELECT COUNT(*) as count 
                    FROM cart 
                    WHERE user_id = ?
                ");
                $stmt->execute([$user_id]);
                $result = $stmt->fetch();
                
                echo json_encode([
                    'success' => true,
                    'count' => $result['count']
                ]);
            } else {
                // Lấy chi tiết giỏ hàng
                $stmt = $conn->prepare("
                    SELECT c.*, p.name, p.price, p.sale_price, pi.image_url, b.name as brand_name
                    FROM cart c
                    LEFT JOIN products p ON c.product_id = p.id
                    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                    LEFT JOIN brands b ON p.brand_id = b.id
                    WHERE c.user_id = ?
                    ORDER BY c.created_at DESC
                ");
                $stmt->execute([$user_id]);
                $cart_items = $stmt->fetchAll();
                
                echo json_encode([
                    'success' => true,
                    'items' => $cart_items
                ]);
            }
            break;
            
        case 'POST':
            // Thêm vào giỏ hàng
            $input = json_decode(file_get_contents('php://input'), true);
            $action = $input['action'] ?? '';
            
            if ($action === 'add') {
                $product_id = intval($input['product_id'] ?? 0);
                $color_id = intval($input['color_id'] ?? 0) ?: null;
                $quantity = intval($input['quantity'] ?? 1);
            } else {
                // Fallback cho form data
                $product_id = intval($_POST['product_id'] ?? 0);
                $color_id = intval($_POST['color_id'] ?? 0) ?: null;
                $quantity = intval($_POST['quantity'] ?? 1);
            }
            
            if (!$product_id || $quantity < 1) {
                throw new Exception('Thông tin không hợp lệ');
            }
            
            // Kiểm tra sản phẩm tồn tại
            $stmt = $conn->prepare("
                SELECT id, name, price, stock_quantity 
                FROM products 
                WHERE id = ? AND status = 'active'
            ");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if (!$product) {
                throw new Exception('Sản phẩm không tồn tại');
            }
            
            if ($product['stock_quantity'] < $quantity) {
                throw new Exception('Số lượng không đủ');
            }
            
            // Kiểm tra item đã tồn tại trong giỏ hàng
            $stmt = $conn->prepare("
                SELECT id, quantity 
                FROM cart 
                WHERE user_id = ? AND product_id = ? AND (product_color_id = ? OR (product_color_id IS NULL AND ? IS NULL))
            ");
            $stmt->execute([$user_id, $product_id, $color_id, $color_id]);
            $existing_item = $stmt->fetch();
            
            if ($existing_item) {
                // Cập nhật số lượng
                $new_quantity = $existing_item['quantity'] + $quantity;
                if ($new_quantity > $product['stock_quantity']) {
                    throw new Exception('Số lượng không đủ');
                }
                
                $stmt = $conn->prepare("
                    UPDATE cart 
                    SET quantity = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?
                ");
                $stmt->execute([$new_quantity, $existing_item['id']]);
            } else {
                // Thêm mới
                $stmt = $conn->prepare("
                    INSERT INTO cart (user_id, product_id, product_color_id, quantity) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$user_id, $product_id, $color_id, $quantity]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Đã thêm vào giỏ hàng'
            ]);
            break;
            
        case 'PUT':
            // Cập nhật số lượng
            $input = json_decode(file_get_contents('php://input'), true);
            $cart_id = intval($input['cart_id'] ?? 0);
            $quantity = intval($input['quantity'] ?? 1);
            
            if (!$cart_id || $quantity < 1) {
                throw new Exception('Thông tin không hợp lệ');
            }
            
            // Kiểm tra item thuộc về user
            $stmt = $conn->prepare("
                SELECT c.*, p.stock_quantity 
                FROM cart c
                LEFT JOIN products p ON c.product_id = p.id
                WHERE c.id = ? AND c.user_id = ?
            ");
            $stmt->execute([$cart_id, $user_id]);
            $item = $stmt->fetch();
            
            if (!$item) {
                throw new Exception('Sản phẩm không tồn tại trong giỏ hàng');
            }
            
            if ($quantity > $item['stock_quantity']) {
                throw new Exception('Số lượng không đủ');
            }
            
            $stmt = $conn->prepare("
                UPDATE cart 
                SET quantity = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            $stmt->execute([$quantity, $cart_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Đã cập nhật giỏ hàng'
            ]);
            break;
            
        case 'DELETE':
            // Xóa khỏi giỏ hàng
            $cart_id = intval($_GET['id'] ?? 0);
            
            if ($cart_id) {
                // Xóa item cụ thể
                $stmt = $conn->prepare("
                    DELETE FROM cart 
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$cart_id, $user_id]);
            } else {
                // Xóa tất cả giỏ hàng
                $stmt = $conn->prepare("
                    DELETE FROM cart 
                    WHERE user_id = ?
                ");
                $stmt->execute([$user_id]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Đã xóa khỏi giỏ hàng'
            ]);
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
