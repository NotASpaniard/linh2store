<?php
/**
 * JWT Authentication Middleware
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once __DIR__ . '/jwt.php';
require_once __DIR__ . '/database.php';

class AuthMiddleware {
    
    /**
     * Kiểm tra đăng nhập (JWT)
     */
    public static function requireLogin() {
        $token = self::getToken();
        
        if (!$token) {
            self::redirectToLogin();
        }
        
        $payload = JWT::decode($token);
        if (!$payload) {
            self::clearAuth();
            self::redirectToLogin();
        }
        
        // Kiểm tra user có tồn tại trong database không
        $user = self::validateUser($payload['user']);
        if (!$user) {
            self::clearAuth();
            self::redirectToLogin();
        }
        
        // Lưu user vào global để sử dụng
        $GLOBALS['current_user'] = $user;
        return $user;
    }
    
    /**
     * Kiểm tra quyền admin
     */
    public static function requireAdmin() {
        $user = self::requireLogin();
        
        if ($user['role'] !== 'admin') {
            header('Location: ../');
            exit();
        }
        
        return $user;
    }
    
    /**
     * Kiểm tra đã đăng nhập (redirect về trang chủ nếu đã đăng nhập)
     */
    public static function requireGuest() {
        $token = self::getToken();
        
        if ($token && JWT::validate($token)) {
            $payload = JWT::decode($token);
            $user = self::validateUser($payload['user']);
            
            if ($user) {
                if ($user['role'] === 'admin') {
                    header('Location: ../admin/');
                } else {
                    header('Location: ../');
                }
                exit();
            }
        }
    }
    
    /**
     * Lấy token từ header hoặc cookie
     */
    public static function getToken() {
        // Thử lấy từ Authorization header
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (isset($headers['Authorization'])) {
                $auth_header = $headers['Authorization'];
                if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
                    return $matches[1];
                }
            }
        } else {
            // Fallback for CLI or non-Apache servers
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
                if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
                    return $matches[1];
                }
            }
        }
        
        // Thử lấy từ cookie
        return JWT::getTokenFromCookie();
    }
    
    /**
     * Lấy user hiện tại
     */
    public static function getCurrentUser() {
        if (isset($GLOBALS['current_user'])) {
            return $GLOBALS['current_user'];
        }
        
        $token = self::getToken();
        if (!$token) {
            return null;
        }
        
        $payload = JWT::decode($token);
        if (!$payload) {
            return null;
        }
        
        return self::validateUser($payload['user']);
    }
    
    /**
     * Kiểm tra đăng nhập (không redirect)
     */
    public static function isLoggedIn() {
        $user = self::getCurrentUser();
        return $user !== null;
    }
    
    /**
     * Validate user từ database
     */
    private static function validateUser($user_data) {
        if (!$user_data || !isset($user_data['id'])) {
            return null;
        }
        
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT id, username, email, full_name, role, status 
                FROM users 
                WHERE id = ? AND status = 'active'
            ");
            $stmt->execute([$user_data['id']]);
            $user = $stmt->fetch();
            
            return $user;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Đăng xuất
     */
    public static function logout() {
        self::clearAuth();
        header('Location: ../auth/dang-nhap.php');
        exit();
    }
    
    /**
     * Xóa thông tin xác thực
     */
    private static function clearAuth() {
        JWT::clearTokenCookie();
        unset($GLOBALS['current_user']);
    }
    
    /**
     * Redirect đến trang đăng nhập
     */
    private static function redirectToLogin() {
        // Lưu URL hiện tại để redirect về sau khi đăng nhập
        $current_url = $_SERVER['REQUEST_URI'];
        setcookie('redirect_after_login', $current_url, time() + 300, '/'); // 5 phút
        
        header('Location: ../auth/dang-nhap.php');
        exit();
    }
    
    /**
     * Đăng nhập user
     */
    public static function loginUser($user) {
        $token = JWT::createUserToken($user);
        JWT::setTokenCookie($token);
        
        // Redirect về URL trước đó hoặc trang chủ
        $redirect_url = $_COOKIE['redirect_after_login'] ?? '../';
        if ($redirect_url) {
            setcookie('redirect_after_login', '', time() - 3600, '/'); // Xóa cookie
        }
        
        if ($user['role'] === 'admin') {
            $redirect_url = '../admin/';
        }
        
        header('Location: ' . $redirect_url);
        exit();
    }
    
    /**
     * Tạo CSRF token
     */
    public static function generateCSRFToken() {
        if (!isset($_COOKIE['csrf_token'])) {
            $token = bin2hex(random_bytes(32));
            setcookie('csrf_token', $token, time() + 3600, '/', '', false, true);
            return $token;
        }
        return $_COOKIE['csrf_token'];
    }
    
    /**
     * Kiểm tra CSRF token
     */
    public static function verifyCSRFToken($token) {
        return isset($_COOKIE['csrf_token']) && hash_equals($_COOKIE['csrf_token'], $token);
    }
}
?>
