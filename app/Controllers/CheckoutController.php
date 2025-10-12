<?php

namespace App\Controllers;

use App\Core\View;
use App\Core\Session;

class CheckoutController
{
    public function form(): void
    {
        $cart = Session::get('cart', []);
        View::render('checkout/form', [
            'title' => 'Thanh toán - Linh2Store',
            'cart' => $cart,
        ]);
    }

    public function process(): void
    {
        // Tạm thời chỉ xoá giỏ và thông báo
        Session::set('cart', []);
        View::render('checkout/success', [
            'title' => 'Đặt hàng thành công - Linh2Store',
        ]);
    }
}

