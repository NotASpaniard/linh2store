<?php

namespace App\Models;

class Product
{
    // Dữ liệu giả lập trong giai đoạn đầu
    public static function fakeList(): array
    {
        return [
            ['id' => 1, 'name' => 'Son lì Luxe Matte', 'price' => 690000, 'img' => '/assets/img/son1.jpg', 'colors' => ['#D81B60', '#8E24AA', '#F06292']],
            ['id' => 2, 'name' => 'Son dưỡng Glow Balm', 'price' => 520000, 'img' => '/assets/img/son2.jpg', 'colors' => ['#EC407A', '#F48FB1', '#AD1457']],
            ['id' => 3, 'name' => 'Phấn má Rosy Cheek', 'price' => 450000, 'img' => '/assets/img/phanma.jpg', 'colors' => ['#F06292', '#E91E63']],
        ];
    }

    public static function fakeFind(int $id): ?array
    {
        foreach (self::fakeList() as $p) {
            if ($p['id'] === $id) return $p;
        }
        return null;
    }
}

