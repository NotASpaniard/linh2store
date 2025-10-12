<?php

namespace App\Controllers;

use App\Core\View;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Security;
use App\Models\User;

class AuthController
{
    public function loginForm(): void
    {
        View::render('auth/login');
    }

    public function login(): void
    {
        if (!CSRF::validate($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Yêu cầu không hợp lệ';
            return;
        }
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (Auth::attempt($email, $password)) {
            header('Location: /');
            return;
        }
        View::render('auth/login', ['error' => 'Email hoặc mật khẩu không đúng']);
    }

    public function registerForm(): void
    {
        View::render('auth/register');
    }

    public function register(): void
    {
        if (!CSRF::validate($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Yêu cầu không hợp lệ';
            return;
        }
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
            View::render('auth/register', ['error' => 'Vui lòng nhập thông tin hợp lệ']);
            return;
        }

        if (User::findByEmail($email)) {
            View::render('auth/register', ['error' => 'Email đã tồn tại']);
            return;
        }

        $created = User::create($name, $email, password_hash($password, PASSWORD_BCRYPT));
        if ($created) {
            header('Location: /dang-nhap');
            return;
        }
        View::render('auth/register', ['error' => 'Không thể tạo tài khoản']);
    }

    public function logout(): void
    {
        if (!CSRF::validate($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Yêu cầu không hợp lệ';
            return;
        }
        \App\Core\Auth::logout();
        header('Location: /');
    }
}

