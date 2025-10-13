<?php
/**
 * Xử lý thanh toán
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/session.php';
require_once '../config/database.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    header('Location: ../auth/dang-nhap.php');
    exit();
}

$user = getCurrentUser();

// Kiểm tra method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

// Lấy dữ liệu từ form
$full_name = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$district = trim($_POST['district'] ?? '');
$payment_method = $_POST['payment_method'] ?? 'cod';
$notes = trim($_POST['notes'] ?? '');

// Validation
$errors = [];

if (empty($full_name)) $errors[] = 'Họ và tên không được để trống';
if (empty($phone)) $errors[] = 'Số điện thoại không được để trống';
if (empty($address)) $errors[] = 'Địa chỉ không được để trống';
if (empty($city)) $errors[] = 'Thành phố không được để trống';
if (empty($district)) $errors[] = 'Quận/Huyện không được để trống';

// Validate phone number
if (!empty($phone) && !preg_match('/^[0-9]{10,11}$/', $phone)) {
    $errors[] = 'Số điện thoại không hợp lệ';
}

if (!empty($errors)) {
    $_SESSION['checkout_errors'] = $errors;
    header('Location: index.php');
    exit();
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Bắt đầu transaction
    $conn->beginTransaction();
    
    // Lấy giỏ hàng của user
    $stmt = $conn->prepare("
        SELECT c.*, p.name, p.price, p.sale_price, p.stock_quantity
        FROM cart c
        LEFT JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user['id']]);
    $cart_items = $stmt->fetchAll();
    
    if (empty($cart_items)) {
        throw new Exception('Giỏ hàng trống');
    }
    
    // Kiểm tra tồn kho
    foreach ($cart_items as $item) {
        if ($item['quantity'] > $item['stock_quantity']) {
            throw new Exception("Sản phẩm '{$item['name']}' không đủ hàng trong kho");
        }
    }
    
    // Tính tổng tiền
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $price = $item['sale_price'] ?: $item['price'];
        $subtotal += $price * $item['quantity'];
    }
    
    $shipping_fee = $subtotal >= 500000 ? 0 : 30000;
    $total_amount = $subtotal + $shipping_fee;
    
    // Tạo đơn hàng
    $order_number = 'L2S' . date('Ymd') . rand(1000, 9999);
    
    $stmt = $conn->prepare("
        INSERT INTO orders (
            user_id, order_number, full_name, phone, email, address, city, district,
            payment_method, notes, subtotal, shipping_fee, total_amount, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    $stmt->execute([
        $user['id'], $order_number, $full_name, $phone, $email, $address, $city, $district,
        $payment_method, $notes, $subtotal, $shipping_fee, $total_amount
    ]);
    
    $order_id = $conn->lastInsertId();
    
    // Tạo chi tiết đơn hàng và cập nhật tồn kho
    foreach ($cart_items as $item) {
        $price = $item['sale_price'] ?: $item['price'];
        
        // Thêm chi tiết đơn hàng
        $stmt = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $price]);
        
        // Cập nhật tồn kho
        $new_stock = $item['stock_quantity'] - $item['quantity'];
        $stmt = $conn->prepare("
            UPDATE products SET stock_quantity = ? WHERE id = ?
        ");
        $stmt->execute([$new_stock, $item['product_id']]);
        
        // Ghi log tồn kho
        $stmt = $conn->prepare("
            INSERT INTO inventory_logs (product_id, type, quantity, old_quantity, new_quantity, created_by, created_at)
            VALUES (?, 'sale', ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$item['product_id'], $item['quantity'], $item['stock_quantity'], $new_stock, $user['id']]);
    }
    
    // Xóa giỏ hàng
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    
    // Commit transaction
    $conn->commit();
    
    // Chuyển hướng theo phương thức thanh toán
    switch ($payment_method) {
        case 'bank_transfer':
            header("Location: bank-transfer.php?order_id=$order_id");
            break;
        case 'momo':
            header("Location: momo-payment.php?order_id=$order_id");
            break;
        case 'vnpay':
            header("Location: vnpay-payment.php?order_id=$order_id");
            break;
        case 'cod':
        default:
            header("Location: success.php?order_id=$order_id");
            break;
    }
    
} catch (Exception $e) {
    // Rollback transaction
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    
    $_SESSION['checkout_error'] = $e->getMessage();
    header('Location: index.php');
    exit();
}
?>
