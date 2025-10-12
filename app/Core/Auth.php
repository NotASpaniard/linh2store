<?php

namespace App\Core;

use App\Models\User;

class Auth
{
    public static function user(): ?array
    {
        Session::start();
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function attempt(string $email, string $password): bool
    {
        $user = User::findByEmail($email);
        if ($user && password_verify($password, $user['password_hash'])) {
            Session::start();
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'role' => $user['role'],
            ];
            return true;
        }
        return false;
    }

    public static function logout(): void
    {
        Session::start();
        unset($_SESSION['user']);
    }
}

