<?php
/**
 * Quản lý session an toàn
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

// Cấu hình session an toàn
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Đặt 1 nếu sử dụng HTTPS
session_start();

/**
 * Tạo CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Kiểm tra CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Kiểm tra đăng nhập
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Lấy thông tin user hiện tại
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return $_SESSION['user'] ?? null;
}

/**
 * Đăng xuất
 */
function logout() {
    session_destroy();
    header('Location: auth/dang-nhap.php');
    exit();
}
?>
