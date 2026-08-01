<?php

class OrderController extends Controller
{
    public function checkout(): void
    {
        $cart = $_SESSION['cart'] ?? [];
        $items = [];
        $total = 0.0;
        $hasInvalidQuantity = false;
        $packageModel = new Package($this->db);

        foreach ($cart as $packageId => $quantity) {
            $package = $packageModel->find((int) $packageId);
            if (!$package) {
                continue;
            }

            $isInvalid = !empty($_SESSION['cart_invalid'][$packageId]);
            $quantity = max(1, (int) $quantity);
            $package['quantity'] = $isInvalid ? '' : $quantity;
            $package['line_total'] = $isInvalid
                ? null
                : (float) $package['price'] * $quantity;
            $package['quantity_invalid'] = $isInvalid;
            $items[] = $package;
            if ($isInvalid) {
                $hasInvalidQuantity = true;
            } else {
                $total += $package['line_total'];
            }
        }

        $this->view('order/checkout', [
            'title' => 'Confirm cart',
            'items' => $items,
            'total' => $total,
            'hasInvalidQuantity' => $hasInvalidQuantity,
        ]);
    }

    public function store(): void
    {
        $package = (new Package($this->db))->find((int) $this->input('package_id'));
        if (!$package) {
            redirect('/packages');
        }

        redirect('/payment?package_id=' . (int) $package['id']);
    }

    public function success(): void
    {
        $order = null;
        if (!empty($_SESSION['last_order_id'])) {
            $order = (new Order($this->db))->find((int) $_SESSION['last_order_id']);
        }

        $this->view('order/success', ['title' => 'Order confirmed', 'order' => $order]);
    }
}
