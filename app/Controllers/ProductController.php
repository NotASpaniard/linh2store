<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\Product;

class ProductController
{
    public function index(): void
    {
        // Tạm thời dữ liệu giả; sẽ thay bằng DB sau
        $products = Product::fakeList();
        View::render('product/index', [
            'title' => 'Sản phẩm - Linh2Store',
            'products' => $products,
        ]);
    }

    public function show(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $product = Product::fakeFind($id);
        if (!$product) {
            http_response_code(404);
            echo 'Sản phẩm không tồn tại';
            return;
        }
        View::render('product/show', [
            'title' => $product['name'] . ' - Linh2Store',
            'product' => $product,
        ]);
    }
}

