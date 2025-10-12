<?php

namespace App\Core;

use PDO;

class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $config = require __DIR__ . '/../../config/database.php';
            $dsn = sprintf('%s:host=%s;port=%d;dbname=%s;charset=%s',
                $config['driver'], $config['host'], $config['port'], $config['database'], $config['charset']
            );
            self::$pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        }
        return self::$pdo;
    }
}

