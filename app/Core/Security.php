<?php

namespace App\Core;

class Security
{
    // Escape HTML để chống XSS khi hiển thị
    public static function e(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

