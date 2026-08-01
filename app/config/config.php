<?php

define('APP_NAME', 'CheapDeals');
define('APP_ROOT', dirname(__DIR__));
define('BASE_PATH', rtrim(getenv('CHEAPDEALS_BASE_PATH') ?: '/COMP1807/cheapdeals/public', '/'));
define('DB_HOST', getenv('CHEAPDEALS_DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('CHEAPDEALS_DB_NAME') ?: 'cheapdeals_db');
define('DB_USER', getenv('CHEAPDEALS_DB_USER') ?: 'root');
define('DB_PASS', getenv('CHEAPDEALS_DB_PASS') ?: '');

date_default_timezone_set('Asia/Ho_Chi_Minh');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => BASE_PATH === '' ? '/' : BASE_PATH,
    ]);
    session_start();
}

function url(string $path = ''): string
{
    $path = '/' . ltrim($path, '/');
    if (BASE_PATH !== '' && ($path === BASE_PATH || str_starts_with($path, BASE_PATH . '/'))) {
        return $path;
    }

    return (BASE_PATH ?: '') . ($path === '/' ? '/' : $path);
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function csrf_is_valid(?string $token): bool
{
    $sessionToken = (string) ($_SESSION['csrf_token'] ?? '');
    return $sessionToken !== '' && is_string($token) && hash_equals($sessionToken, $token);
}
