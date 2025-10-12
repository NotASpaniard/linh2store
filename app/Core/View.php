<?php

namespace App\Core;

/**
 * View engine đơn giản: render file PHP trong thư mục Views
 */
class View
{
    public static function render(string $viewPath, array $data = []): void
    {
        // $viewPath: ví dụ 'home/index' => /app/Views/home/index.php
        $fullPath = __DIR__ . '/../Views/' . str_replace('.', '/', $viewPath) . '.php';
        if (!file_exists($fullPath)) {
            http_response_code(500);
            echo 'View không tồn tại: ' . htmlspecialchars($viewPath);
            return;
        }
        extract($data, EXTR_OVERWRITE);
        include $fullPath;
    }
}

