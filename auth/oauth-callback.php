<?php
/**
 * OAuth Callback Handler
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/oauth.php';
require_once '../config/auth-middleware.php';

$error = '';
$success = '';

// Kiểm tra có code và state không
if (!isset($_GET['code']) || !isset($_GET['state'])) {
    $error = 'Thiếu thông tin xác thực từ nhà cung cấp';
} else {
    $code = $_GET['code'];
    $state = $_GET['state'];
    
    try {
        $oauth_data = null;
        
        // Xác định provider từ state
        if (strpos($state, 'google_') === 0) {
            $oauth_data = OAuthProvider::handleGoogleCallback($code);
        } elseif (strpos($state, 'facebook_') === 0) {
            $oauth_data = OAuthProvider::handleFacebookCallback($code);
        } else {
            $error = 'Provider không được hỗ trợ';
        }
        
        if ($oauth_data) {
            // Lưu user vào database
            $user = OAuthProvider::saveOAuthUser($oauth_data);
            
            if ($user) {
                // Đăng nhập user
                AuthMiddleware::loginUser($user);
            } else {
                $error = 'Không thể tạo hoặc cập nhật tài khoản';
            }
        } else {
            $error = 'Không thể lấy thông tin từ ' . (strpos($state, 'google_') === 0 ? 'Google' : 'Facebook');
        }
        
    } catch (Exception $e) {
        $error = 'Có lỗi xảy ra: ' . $e->getMessage();
    }
}

// Nếu có lỗi, redirect về trang đăng nhập với thông báo
if ($error) {
    header('Location: dang-nhap.php?error=' . urlencode($error));
    exit();
}
?>
