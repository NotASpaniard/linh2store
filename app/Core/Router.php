<?php

namespace App\Core;

/**
 * Router tối giản theo pattern controller@action
 * - GET/POST tách biệt
 * - Hỗ trợ middleware đơn giản (chuỗi callable)
 */
class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, $handler, array $middlewares = []): void
    {
        $this->routes['GET'][$this->normalize($path)] = [$handler, $middlewares];
    }

    public function post(string $path, $handler, array $middlewares = []): void
    {
        $this->routes['POST'][$this->normalize($path)] = [$handler, $middlewares];
    }

    public function dispatch(string $method, string $uri)
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $path = $this->normalize($path);

        $route = $this->routes[$method][$path] ?? null;
        if (!$route) {
            http_response_code(404);
            echo 'Trang không tồn tại';
            return;
        }

        [$handler, $middlewares] = $route;

        // Middleware chain
        foreach ($middlewares as $mw) {
            if (is_callable($mw)) {
                $result = $mw();
                if ($result === false) {
                    return; // middleware đã xử lý phản hồi
                }
            }
        }

        if (is_string($handler)) {
            // Format: Controller@method
            [$controller, $method] = explode('@', $handler);
            $controllerClass = 'App\\Controllers\\' . $controller;
            if (!class_exists($controllerClass)) {
                http_response_code(500);
                echo 'Lỗi: Controller không tồn tại';
                return;
            }
            $instance = new $controllerClass();
            if (!method_exists($instance, $method)) {
                http_response_code(500);
                echo 'Lỗi: Phương thức không tồn tại';
                return;
            }
            return $instance->$method();
        }

        if (is_callable($handler)) {
            return $handler();
        }

        http_response_code(500);
        echo 'Lỗi: Handler không hợp lệ';
    }

    private function normalize(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        return rtrim($path, '/') ?: '/';
    }
}

