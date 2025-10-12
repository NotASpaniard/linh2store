<?php

namespace App\Controllers;

use App\Core\View;
use App\Core\Auth;

class AdminController
{
    private function ensureAdmin(): bool
    {
        $user = Auth::user();
        if (!$user || ($user['role'] ?? 'user') !== 'admin') {
            http_response_code(403);
            echo 'Bạn không có quyền truy cập.';
            return false;
        }
        return true;
    }

    public function index(): void
    {
        if (!$this->ensureAdmin()) return;
        View::render('admin/index', ['title' => 'Admin - Linh2Store']);
    }

    public function products(): void
    {
        if (!$this->ensureAdmin()) return;
        View::render('admin/products', ['title' => 'Quản lý sản phẩm - Linh2Store']);
    }

    public function createProduct(): void { echo 'Đang bảo trì'; }
    public function updateProduct(): void { echo 'Đang bảo trì'; }
    public function deleteProduct(): void { echo 'Đang bảo trì'; }
}

