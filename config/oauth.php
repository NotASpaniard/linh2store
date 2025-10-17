<?php
/**
 * OAuth Integration for Google & Facebook
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

// Load OAuth configuration
require_once __DIR__ . '/oauth-config.php';

class OAuthProvider {
    
    // Google OAuth Configuration - Loaded from oauth-config.php
    private static $google_client_id = GOOGLE_CLIENT_ID;
    private static $google_client_secret = GOOGLE_CLIENT_SECRET;
    private static $google_redirect_uri = GOOGLE_REDIRECT_URI;
    
    // Facebook OAuth Configuration - Loaded from oauth-config.php
    private static $facebook_app_id = FACEBOOK_APP_ID;
    private static $facebook_app_secret = FACEBOOK_APP_SECRET;
    private static $facebook_redirect_uri = FACEBOOK_REDIRECT_URI;
    
    /**
     * Tạo Google OAuth URL
     */
    public static function getGoogleAuthUrl() {
        $params = [
            'client_id' => self::$google_client_id,
            'redirect_uri' => self::$google_redirect_uri,
            'scope' => 'openid email profile',
            'response_type' => 'code',
            'state' => 'google_' . bin2hex(random_bytes(16))
        ];
        
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }
    
    /**
     * Tạo Facebook OAuth URL
     */
    public static function getFacebookAuthUrl() {
        $params = [
            'client_id' => self::$facebook_app_id,
            'redirect_uri' => self::$facebook_redirect_uri,
            'scope' => 'email,public_profile',
            'response_type' => 'code',
            'state' => 'facebook_' . bin2hex(random_bytes(16))
        ];
        
        return 'https://www.facebook.com/v18.0/dialog/oauth?' . http_build_query($params);
    }
    
    /**
     * Xử lý Google OAuth callback
     */
    public static function handleGoogleCallback($code) {
        // Exchange code for access token
        $token_url = 'https://oauth2.googleapis.com/token';
        $token_data = [
            'client_id' => self::$google_client_id,
            'client_secret' => self::$google_client_secret,
            'redirect_uri' => self::$google_redirect_uri,
            'grant_type' => 'authorization_code',
            'code' => $code
        ];
        
        $token_response = self::makeHttpRequest($token_url, 'POST', $token_data);
        $token_info = json_decode($token_response, true);
        
        if (!isset($token_info['access_token'])) {
            return false;
        }
        
        // Get user info
        $user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $token_info['access_token'];
        $user_response = self::makeHttpRequest($user_info_url);
        $user_info = json_decode($user_response, true);
        
        if (!$user_info || !isset($user_info['email'])) {
            return false;
        }
        
        return [
            'provider' => 'google',
            'provider_id' => $user_info['id'],
            'email' => $user_info['email'],
            'full_name' => $user_info['name'],
            'avatar' => $user_info['picture'] ?? null,
            'verified' => $user_info['verified_email'] ?? false
        ];
    }
    
    /**
     * Xử lý Facebook OAuth callback
     */
    public static function handleFacebookCallback($code) {
        // Exchange code for access token
        $token_url = 'https://graph.facebook.com/v18.0/oauth/access_token';
        $token_data = [
            'client_id' => self::$facebook_app_id,
            'client_secret' => self::$facebook_app_secret,
            'redirect_uri' => self::$facebook_redirect_uri,
            'code' => $code
        ];
        
        $token_response = self::makeHttpRequest($token_url, 'GET', $token_data);
        $token_info = json_decode($token_response, true);
        
        if (!isset($token_info['access_token'])) {
            return false;
        }
        
        // Get user info
        $user_info_url = 'https://graph.facebook.com/v18.0/me?fields=id,name,email,picture&access_token=' . $token_info['access_token'];
        $user_response = self::makeHttpRequest($user_info_url);
        $user_info = json_decode($user_response, true);
        
        if (!$user_info || !isset($user_info['email'])) {
            return false;
        }
        
        return [
            'provider' => 'facebook',
            'provider_id' => $user_info['id'],
            'email' => $user_info['email'],
            'full_name' => $user_info['name'],
            'avatar' => $user_info['picture']['data']['url'] ?? null,
            'verified' => true
        ];
    }
    
    /**
     * Lưu hoặc tạo user từ OAuth
     */
    public static function saveOAuthUser($oauth_data) {
        require_once __DIR__ . '/database.php';
        
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Kiểm tra user đã tồn tại chưa
            $stmt = $conn->prepare("
                SELECT u.* FROM users u 
                LEFT JOIN oauth_accounts oa ON u.id = oa.user_id 
                WHERE u.email = ? OR (oa.provider = ? AND oa.provider_id = ?)
            ");
            $stmt->execute([$oauth_data['email'], $oauth_data['provider'], $oauth_data['provider_id']]);
            $existing_user = $stmt->fetch();
            
            if ($existing_user) {
                // User đã tồn tại, cập nhật thông tin
                $stmt = $conn->prepare("
                    UPDATE users 
                    SET full_name = ?, avatar = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?
                ");
                $stmt->execute([$oauth_data['full_name'], $oauth_data['avatar'], $existing_user['id']]);
                
                // Kiểm tra OAuth account đã liên kết chưa
                $stmt = $conn->prepare("
                    SELECT id FROM oauth_accounts 
                    WHERE user_id = ? AND provider = ? AND provider_id = ?
                ");
                $stmt->execute([$existing_user['id'], $oauth_data['provider'], $oauth_data['provider_id']]);
                
                if (!$stmt->fetch()) {
                    // Liên kết OAuth account mới
                    $stmt = $conn->prepare("
                        INSERT INTO oauth_accounts (user_id, provider, provider_id, created_at) 
                        VALUES (?, ?, ?, CURRENT_TIMESTAMP)
                    ");
                    $stmt->execute([$existing_user['id'], $oauth_data['provider'], $oauth_data['provider_id']]);
                }
                
                return $existing_user;
            } else {
                // Tạo user mới
                $username = self::generateUsername($oauth_data['email']);
                
                $stmt = $conn->prepare("
                    INSERT INTO users (username, email, full_name, avatar, role, status, created_at) 
                    VALUES (?, ?, ?, ?, 'user', 'active', CURRENT_TIMESTAMP)
                ");
                $stmt->execute([
                    $username, 
                    $oauth_data['email'], 
                    $oauth_data['full_name'], 
                    $oauth_data['avatar']
                ]);
                
                $user_id = $conn->lastInsertId();
                
                // Lưu OAuth account
                $stmt = $conn->prepare("
                    INSERT INTO oauth_accounts (user_id, provider, provider_id, created_at) 
                    VALUES (?, ?, ?, CURRENT_TIMESTAMP)
                ");
                $stmt->execute([$user_id, $oauth_data['provider'], $oauth_data['provider_id']]);
                
                // Lấy thông tin user mới tạo
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                return $stmt->fetch();
            }
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Tạo username từ email
     */
    private static function generateUsername($email) {
        $base_username = explode('@', $email)[0];
        $username = $base_username;
        $counter = 1;
        
        require_once __DIR__ . '/database.php';
        $db = new Database();
        $conn = $db->getConnection();
        
        while (true) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            
            if (!$stmt->fetch()) {
                return $username;
            }
            
            $username = $base_username . $counter;
            $counter++;
        }
    }
    
    /**
     * Make HTTP request
     */
    private static function makeHttpRequest($url, $method = 'GET', $data = null) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code !== 200) {
            return false;
        }
        
        return $response;
    }
}
?>
