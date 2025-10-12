<?php

namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $app = require __DIR__ . '/../../config/app.php';
            $s = $app['session'];
            session_name($s['name']);
            session_set_cookie_params([
                'lifetime' => $s['cookie_lifetime'],
                'path' => '/',
                'secure' => $s['cookie_secure'],
                'httponly' => $s['cookie_httponly'],
                'samesite' => $s['cookie_samesite'],
            ]);
            session_start();
        }
    }

    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            }
            session_destroy();
        }
    }
}

