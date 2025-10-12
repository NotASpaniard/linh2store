<?php
require __DIR__ . '/../bootstrap.php';

use App\Core\Router;

$router = new Router();

// Nạp routes cấu hình
require __DIR__ . '/../config/routes.php';

// Điều phối
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');

