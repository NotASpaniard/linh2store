<?php
// Bootstrap: nạp autoload đơn giản và cấu hình cơ bản

// Hiển thị lỗi trong môi trường phát triển
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Autoload thủ công theo PSR-4 tối giản cho namespace App\
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Nạp Session sớm
\App\Core\Session::start();

