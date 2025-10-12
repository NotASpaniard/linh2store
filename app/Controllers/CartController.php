<?php

namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Models\Product;

class CartController
{
    public function view(): void
    {
        $cart = Session::get('cart', []);
        View::render('cart/view', [
            'title' => 'Giỏ hàng - Linh2Store',
            'cart' => $cart,
        ]);
    }

    public function add(): void
    {
        $id = (int)($_POST['product_id'] ?? 0);
        $product = Product::fakeFind($id);
        if (!$product) {
            header('Location: /gio-hang');
            return;
        }
        $cart = Session::get('cart', []);
        if (!isset($cart[$id])) {
            $cart[$id] = ['product' => $product, 'qty' => 1];
        } else {
            $cart[$id]['qty'] += 1;
        }
        Session::set('cart', $cart);
        header('Location: /gio-hang');
    }

    public function update(): void
    {
        $id = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        $cart = Session::get('cart', []);
        if (isset($cart[$id])) {
            $cart[$id]['qty'] = $qty;
            Session::set('cart', $cart);
        }
        header('Location: /gio-hang');
    }

    public function remove(): void
    {
        $id = (int)($_POST['product_id'] ?? 0);
        $cart = Session::get('cart', []);
        unset($cart[$id]);
        Session::set('cart', $cart);
        header('Location: /gio-hang');
    }
}

