<?php

namespace App\Controllers;

use App\Core\View;

class HomeController
{
    public function index(): void
    {
        // Dữ liệu giả cho giao diện ban đầu
        $featured = [
            ['name' => 'Son lì Luxe Matte', 'price' => 690000, 'img' => '/assets/img/son1.jpg', 'colors' => ['#D81B60', '#8E24AA', '#F06292']],
            ['name' => 'Son dưỡng Glow Balm', 'price' => 520000, 'img' => '/assets/img/son2.jpg', 'colors' => ['#EC407A', '#F48FB1', '#AD1457']],
            ['name' => 'Phấn má Rosy Cheek', 'price' => 450000, 'img' => '/assets/img/phanma.jpg', 'colors' => ['#F06292', '#E91E63']],
        ];

        View::render('home/index', [
            'title' => 'Linh2Store - Mỹ phẩm cao cấp',
            'featured' => $featured,
        ]);
    }
}

