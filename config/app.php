<?php
// Cấu hình ứng dụng Linh2Store
return [
    // Tên website hiển thị trên tiêu đề
    'app_name' => 'Linh2Store',

    // Phát hiện base URL tự động (phục vụ routing và asset)
    // Chú ý: Sửa lại nếu deploy trên domain cụ thể
    'base_url' => (function () {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $dir = rtrim(str_replace('index.php', '', $scriptName), '/');
        return rtrim($scheme . '://' . $host . $dir, '/');
    })(),

    // Cấu hình session an toàn
    'session' => [
        'name' => 'linh2store_sid',
        'cookie_lifetime' => 60 * 60 * 24 * 7, // 7 ngày
        'cookie_secure' => false, // Bật true nếu dùng HTTPS
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ],

    // Cấu hình bảo mật
    'security' => [
        // Khoá CSRF riêng cho app
        'csrf_key' => 'linh2store_csrf_key_please_change',
    ],
];

