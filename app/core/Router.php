<?php

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    public function dispatch(): void
    {
        $path = $this->normalize((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $base = $this->normalize(BASE_PATH);
        if ($base !== '/' && str_starts_with($path, $base)) {
            $path = $this->normalize(substr($path, strlen($base)));
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $handler = $this->routes[$method][$path] ?? null;

        if (!$handler) {
            http_response_code(404);
            echo '<h1>404</h1><p>Page not found.</p>';
            return;
        }

        if ($method === 'POST') {
            $sessionToken = (string) ($_SESSION['csrf_token'] ?? '');
            $requestToken = (string) ($_POST['_token'] ?? '');
            if ($sessionToken === '' || $requestToken === '' || !hash_equals($sessionToken, $requestToken)) {
                http_response_code(419);
                echo '<h1>Form expired</h1><p>Please go back, refresh the page and submit the form again.</p>';
                return;
            }
        }

        [$controller, $action] = $handler;
        (new $controller())->$action();
    }

    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '//' ? '/' : $path;
    }
}
