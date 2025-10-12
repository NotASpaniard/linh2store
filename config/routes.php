<?php

// Định nghĩa route cơ bản cho Linh2Store

/** @var \App\Core\Router $router */

// Trang chủ
$router->get('/', 'HomeController@index');

// Auth
$router->get('/dang-nhap', 'AuthController@loginForm');
$router->post('/dang-nhap', 'AuthController@login');
$router->get('/dang-ky', 'AuthController@registerForm');
$router->post('/dang-ky', 'AuthController@register');
$router->post('/dang-xuat', 'AuthController@logout');

// Sản phẩm
$router->get('/san-pham', 'ProductController@index');
$router->get('/san-pham/chi-tiet', 'ProductController@show'); // ?id=123

// Giỏ hàng
$router->post('/gio-hang/them', 'CartController@add');
$router->post('/gio-hang/cap-nhat', 'CartController@update');
$router->post('/gio-hang/xoa', 'CartController@remove');
$router->get('/gio-hang', 'CartController@view');

// Thanh toán
$router->get('/thanh-toan', 'CheckoutController@form');
$router->post('/thanh-toan', 'CheckoutController@process');

// Admin
$router->get('/admin', 'AdminController@index');
$router->get('/admin/san-pham', 'AdminController@products');
$router->post('/admin/san-pham/tao', 'AdminController@createProduct');
$router->post('/admin/san-pham/sua', 'AdminController@updateProduct');
$router->post('/admin/san-pham/xoa', 'AdminController@deleteProduct');

// Trang bảo trì cho tính năng nâng cao chưa làm
$router->get('/yeu-thich', function () {
    echo 'Đang bảo trì';
});
$router->get('/so-sanh', function () {
    echo 'Đang bảo trì';
});
$router->get('/goi-qua', function () {
    echo 'Đang bảo trì';
});
$router->get('/tu-van-ai', function () {
    echo 'Đang bảo trì';
});

