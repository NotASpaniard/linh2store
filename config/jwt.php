<?php
/**
 * JWT Helper Class
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

class JWT {
    private static $secret_key = 'linh2store_secret_key_2025_very_secure';
    private static $algorithm = 'HS256';
    
    /**
     * Tạo JWT token
     */
    public static function encode($payload, $expiry_hours = 24) {
        $header = json_encode(['typ' => 'JWT', 'alg' => self::$algorithm]);
        
        // Thêm thời gian hết hạn
        $payload['exp'] = time() + ($expiry_hours * 3600);
        $payload['iat'] = time();
        
        $payload_encoded = json_encode($payload);
        
        $header_encoded = self::base64UrlEncode($header);
        $payload_encoded = self::base64UrlEncode($payload_encoded);
        
        $signature = hash_hmac('sha256', $header_encoded . "." . $payload_encoded, self::$secret_key, true);
        $signature_encoded = self::base64UrlEncode($signature);
        
        return $header_encoded . "." . $payload_encoded . "." . $signature_encoded;
    }
    
    /**
     * Giải mã JWT token
     */
    public static function decode($jwt) {
        $tokenParts = explode('.', $jwt);
        
        if (count($tokenParts) !== 3) {
            return false;
        }
        
        $header = json_decode(self::base64UrlDecode($tokenParts[0]), true);
        $payload = json_decode(self::base64UrlDecode($tokenParts[1]), true);
        $signature = $tokenParts[2];
        
        if (!$header || !$payload) {
            return false;
        }
        
        // Kiểm tra chữ ký
        $expected_signature = hash_hmac('sha256', $tokenParts[0] . "." . $tokenParts[1], self::$secret_key, true);
        $expected_signature = self::base64UrlEncode($expected_signature);
        
        if (!hash_equals($signature, $expected_signature)) {
            return false;
        }
        
        // Kiểm tra thời gian hết hạn
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }
    
    /**
     * Kiểm tra token có hợp lệ không
     */
    public static function validate($token) {
        if (!$token) {
            return false;
        }
        
        $payload = self::decode($token);
        return $payload !== false;
    }
    
    /**
     * Lấy thông tin user từ token
     */
    public static function getUserFromToken($token) {
        $payload = self::decode($token);
        if (!$payload) {
            return null;
        }
        
        return $payload['user'] ?? null;
    }
    
    /**
     * Tạo token cho user
     */
    public static function createUserToken($user, $expiry_hours = 24) {
        $payload = [
            'user' => $user,
            'type' => 'auth'
        ];
        
        return self::encode($payload, $expiry_hours);
    }
    
    /**
     * Refresh token (tạo token mới với thời gian hết hạn mới)
     */
    public static function refreshToken($token, $expiry_hours = 24) {
        $payload = self::decode($token);
        if (!$payload) {
            return false;
        }
        
        return self::createUserToken($payload['user'], $expiry_hours);
    }
    
    /**
     * Base64 URL encode
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL decode
     */
    private static function base64UrlDecode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
    
    /**
     * Lưu token vào cookie
     */
    public static function setTokenCookie($token, $expiry_hours = 24) {
        $expiry = time() + ($expiry_hours * 3600);
        setcookie('auth_token', $token, $expiry, '/', '', false, true); // httponly = true
    }
    
    /**
     * Lấy token từ cookie
     */
    public static function getTokenFromCookie() {
        return $_COOKIE['auth_token'] ?? null;
    }
    
    /**
     * Xóa token cookie
     */
    public static function clearTokenCookie() {
        setcookie('auth_token', '', time() - 3600, '/', '', false, true);
    }
}
?>
