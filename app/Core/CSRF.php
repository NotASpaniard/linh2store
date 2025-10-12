<?php

namespace App\Core;

class CSRF
{
    // Tạo token và lưu vào session
    public static function token(): string
    {
        Session::start();
        if (!isset($_SESSION['_csrf_token'])) {
            $app = require __DIR__ . '/../../config/app.php';
            $_SESSION['_csrf_token'] = hash_hmac('sha256', bin2hex(random_bytes(16)), $app['security']['csrf_key']);
        }
        return $_SESSION['_csrf_token'];
    }

    // Xác thực token từ form
    public static function validate(?string $token): bool
    {
        Session::start();
        return is_string($token) && isset($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], $token);
    }
}

