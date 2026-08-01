<?php

class Controller
{
    protected Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    protected function view(string $view, array $data = []): void
    {
        extract($data);
        require APP_ROOT . '/views/layout/header.php';
        require APP_ROOT . '/views/layout/navbar.php';
        require APP_ROOT . '/views/' . $view . '.php';
        require APP_ROOT . '/views/layout/footer.php';
    }

    protected function input(string $key, string $default = ''): string
    {
        return trim((string) ($_POST[$key] ?? $_GET[$key] ?? $default));
    }

    protected function currentCustomer(): ?array
    {
        return $_SESSION['customer'] ?? null;
    }

    protected function hasRole(string ...$roles): bool
    {
        return in_array((string) ($this->currentCustomer()['role'] ?? ''), $roles, true);
    }

    protected function csrfValid(): bool
    {
        $token = (string) ($_POST['_token'] ?? '');
        $sessionToken = (string) ($_SESSION['csrf_token'] ?? '');
        return $sessionToken !== '' && $token !== '' && hash_equals($sessionToken, $token);
    }

    protected function rejectInvalidCsrf(string $returnPath): void
    {
        if ($this->csrfValid()) {
            return;
        }

        $_SESSION['flash'] = 'Your form session expired. Please refresh the page and try again.';
        redirect($returnPath);
    }
}
