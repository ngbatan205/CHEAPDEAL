<?php

class CartController extends Controller
{
    public function add(): void
    {
        $packageId = (int) $this->input('package_id');
        $package = (new Package($this->db))->find($packageId);

        if (!$package || (int) $package['stock'] < 1) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'The selected package is no longer available.'], 404);
            }
            $_SESSION['flash'] = 'The selected package is unavailable or out of stock.';
            redirect('/packages');
        }

        $cart = $_SESSION['cart'] ?? [];
        $cart[$packageId] = min(99, ((int) ($cart[$packageId] ?? 0)) + 1);
        $_SESSION['cart'] = $cart;
        unset($_SESSION['cart_invalid'][$packageId]);
        $message = $package['package_name'] . ' was added to your cart.';

        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'message' => $message,
                'cartCount' => array_sum(array_map('intval', $cart)),
            ]);
        }

        $_SESSION['flash'] = $message;
        $returnTo = $this->input('return_to', '/packages');
        redirect(str_starts_with($returnTo, '/') ? $returnTo : '/packages');
    }

    public function update(): void
    {
        $packageId = (int) $this->input('package_id');
        $quantityInput = $this->input('quantity');
        $cart = $_SESSION['cart'] ?? [];

        if (isset($cart[$packageId])) {
            if (
                $quantityInput === ''
                || !ctype_digit($quantityInput)
                || (int) $quantityInput < 1
                || (int) $quantityInput > 99
            ) {
                $_SESSION['cart_invalid'][$packageId] = true;

                if ($this->isAjax()) {
                    $this->json([
                        'success' => true,
                        'valid' => false,
                        'message' => 'Please enter a quantity from 1 to 99.',
                    ]);
                }

                $_SESSION['flash'] = 'Please enter a quantity from 1 to 99.';
                redirect('/checkout');
            }

            $quantity = (int) $quantityInput;
            $package = (new Package($this->db))->find($packageId);
            if (!$package || $quantity > (int) $package['stock']) {
                $_SESSION['cart_invalid'][$packageId] = true;
                $_SESSION['flash'] = 'The requested quantity is greater than current stock.';
                redirect('/checkout');
            }
            $cart[$packageId] = $quantity;
            $_SESSION['cart'] = $cart;
            unset($_SESSION['cart_invalid'][$packageId]);

            if ($this->isAjax()) {
                $package = (new Package($this->db))->find($packageId);
                $lineTotal = $package ? (float) $package['price'] * $quantity : 0.0;
                $this->json([
                    'success' => true,
                    'valid' => true,
                    'message' => 'Cart updated.',
                    'quantity' => $quantity,
                    'lineTotal' => round($lineTotal, 2),
                    'cartTotal' => $this->cartTotal($cart),
                    'cartCount' => array_sum(array_map('intval', $cart)),
                ]);
            }

            $_SESSION['flash'] = 'Cart updated.';
        } elseif ($this->isAjax()) {
            $this->json(['success' => false, 'message' => 'This package is not in your cart.'], 404);
        }

        redirect('/checkout');
    }

    public function addDeal(): void
    {
        $deal = (new Deal($this->db))->find((int) $this->input('deal_id'));
        if (!$deal) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'This combo is no longer available.'], 404);
            }
            $_SESSION['flash'] = 'This combo is no longer available.';
            redirect('/combos');
        }

        $cart = $_SESSION['cart'] ?? [];
        foreach ($deal['packages'] as $package) {
            if ((int) ($package['stock'] ?? 0) < 1) {
                $_SESSION['flash'] = 'This combo contains an unavailable package and cannot be added.';
                redirect('/combos');
            }
            $packageId = (int) $package['id'];
            $cart[$packageId] = min(99, ((int) ($cart[$packageId] ?? 0)) + 1);
            unset($_SESSION['cart_invalid'][$packageId]);
        }
        $_SESSION['cart'] = $cart;
        $message = $deal['deal_name'] . ' was added to your cart.';

        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'message' => $message,
                'cartCount' => array_sum(array_map('intval', $cart)),
            ]);
        }

        $_SESSION['flash'] = $message;
        $returnTo = $this->input('return_to', '/combos');
        redirect(str_starts_with($returnTo, '/') ? $returnTo : '/combos');
    }

    public function remove(): void
    {
        $packageId = (int) $this->input('package_id');
        $cart = $_SESSION['cart'] ?? [];
        unset($cart[$packageId]);
        $_SESSION['cart'] = $cart;
        unset($_SESSION['cart_invalid'][$packageId]);
        $_SESSION['flash'] = 'Package removed from your cart.';

        redirect('/checkout');
    }

    private function cartTotal(array $cart): float
    {
        $total = 0.0;
        $packageModel = new Package($this->db);

        foreach ($cart as $packageId => $quantity) {
            if (!empty($_SESSION['cart_invalid'][$packageId])) {
                continue;
            }
            $package = $packageModel->find((int) $packageId);
            if ($package) {
                $total += (float) $package['price'] * max(1, (int) $quantity);
            }
        }

        return round($total, 2);
    }

    private function isAjax(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }

    private function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
