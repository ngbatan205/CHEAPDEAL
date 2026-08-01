<?php

class PaymentController extends Controller
{
    public function payment(): void
    {
        $dealId = (int) $this->input('deal_id');
        $source = $dealId > 0
            ? 'deal'
            : ($this->input('source') === 'cart' ? 'cart' : 'direct');
        $items = [];
        $total = 0.0;
        $packageModel = new Package($this->db);

        if ($source === 'cart') {
            if (!empty($_SESSION['cart_invalid'])) {
                $_SESSION['flash'] = 'Please enter a valid quantity for every package before payment.';
                redirect('/checkout');
            }

            foreach (($_SESSION['cart'] ?? []) as $packageId => $quantity) {
                $package = $packageModel->find((int) $packageId);
                if (!$package) {
                    continue;
                }
                $package['quantity'] = max(1, (int) $quantity);
                $package['line_total'] = (float) $package['price'] * $package['quantity'];
                $items[] = $package;
                $total += $package['line_total'];
            }
        } elseif ($source === 'deal') {
            $deal = (new Deal($this->db))->find($dealId);
            foreach (($deal['packages'] ?? []) as $package) {
                $package['quantity'] = 1;
                $package['line_total'] = (float) $package['price'];
                $items[] = $package;
                $total += $package['line_total'];
            }
        } else {
            $package = $packageModel->find((int) $this->input('package_id'));
            if ($package) {
                $package['quantity'] = 1;
                $package['line_total'] = (float) $package['price'];
                $items[] = $package;
                $total = $package['line_total'];
            }
        }

        if (!$items) {
            $_SESSION['flash'] = 'Choose a package before continuing to payment.';
            redirect('/packages');
        }

        if (!$this->currentCustomer()) {
            $_SESSION['flash'] = 'Please login before making a payment.';
            $_SESSION['intended_url'] = $source === 'cart'
                ? '/payment?source=cart'
                : (
                    $source === 'deal'
                        ? '/payment?deal_id=' . $dealId
                        : '/payment?package_id=' . (int) $items[0]['id']
                );
            redirect('/login');
        }

        $paymentOld = $_SESSION['payment_old'] ?? [];
        $selectedOffer = strtoupper(trim((string) ($paymentOld['offer_code'] ?? '')));
        $bundle = $this->bundleDetails($items, $selectedOffer, 'App');

        $this->view('payment/payment', [
            'title' => 'Payment',
            'items' => $items,
            'total' => $total,
            'source' => $source,
            'dealId' => $dealId,
            'bundleName' => $bundle['name'],
            'categoryCount' => $bundle['category_count'],
            'discountPercent' => $bundle['discount_percent'],
            'discountAmount' => $bundle['discount_amount'],
            'finalTotal' => $total - $bundle['discount_amount'],
            'eligibleOffers' => $bundle['eligible_offers'],
            'selectedOffer' => $selectedOffer,
            'offerApplied' => $bundle['offer_applied'],
            'appDiscountPercent' => $bundle['app_discount_percent'],
            'appDiscountAmount' => $bundle['app_discount_amount'],
            'offerDiscountPercent' => $bundle['offer_discount_percent'],
            'offerDiscountAmount' => $bundle['offer_discount_amount'],
            'cards' => (new Customer($this->db))->paymentMethods(
                (int) $this->currentCustomer()['id']
            ),
            'errors' => $_SESSION['payment_errors'] ?? [],
            'old' => $paymentOld,
        ]);

        unset($_SESSION['payment_errors'], $_SESSION['payment_old']);
    }

    public function store(): void
    {
        $customer = $this->currentCustomer();
        if (!$customer) {
            redirect('/login');
        }

        $requestedSource = $this->input('source');
        $source = in_array($requestedSource, ['cart', 'deal'], true)
            ? $requestedSource
            : 'direct';
        if ($source === 'cart' && !empty($_SESSION['cart_invalid'])) {
            $_SESSION['flash'] = 'Please enter a valid quantity for every package before payment.';
            redirect('/checkout');
        }

        $packageId = (int) $this->input('package_id');
        $dealId = (int) $this->input('deal_id');
        if ($source === 'cart') {
            $cart = $_SESSION['cart'] ?? [];
        } elseif ($source === 'deal') {
            $deal = (new Deal($this->db))->find($dealId);
            if (!$deal || (int) $deal['stock'] < 1) {
                $_SESSION['flash'] = 'This combo is currently unavailable.';
                redirect('/combos');
            }
            $cart = [];
            foreach (($deal['packages'] ?? []) as $package) {
                $cart[(int) $package['id']] = 1;
            }
        } else {
            $cart = [$packageId => 1];
        }
        $packageModel = new Package($this->db);
        $purchaseItems = [];

        foreach ($cart as $cartPackageId => $quantity) {
            $package = $packageModel->find((int) $cartPackageId);
            if (!$package) {
                continue;
            }

            $package['quantity'] = max(1, (int) $quantity);
            if (!is_numeric($package['price']) || (float) $package['price'] <= 0) {
                $_SESSION['flash'] = 'Checkout is unavailable because the selected package has invalid pricing.';
                redirect('/packages');
            }
            if ((int) $package['stock'] < $package['quantity']) {
                $_SESSION['flash'] = $package['package_name'] . ' does not have enough stock for this order.';
                redirect($source === 'cart' ? '/checkout' : '/packages');
            }
            $package['line_total'] = round(
                (float) $package['price'] * $package['quantity'],
                2
            );
            $purchaseItems[] = $package;
        }

        if ($source === 'deal' && count($purchaseItems) !== count($cart)) {
            $_SESSION['flash'] = 'This combo is unavailable because one or more included packages are inactive.';
            redirect('/combos');
        }

        if (!$purchaseItems) {
            $_SESSION['flash'] = 'No valid package was found for payment.';
            redirect('/packages');
        }

        $offerCode = strtoupper($this->input('offer_code'));
        $bundle = $this->bundleDetails($purchaseItems, $offerCode, 'App');
        $paymentChoice = $this->input('payment_method');
        $customerModel = new Customer($this->db);
        $cards = $customerModel->paymentMethods((int) $customer['id']);
        $paymentLabel = '';
        $verification = null;
        $visaCheck = new VisaCheck();
        $errors = [];

        if ($offerCode !== '' && !$bundle['offer_applied']) {
            $errors['offer_code'] = 'This offer code is not valid for the selected package total.';
        }

        if (str_starts_with($paymentChoice, 'card_')) {
            $cardId = (int) substr($paymentChoice, 5);
            $selectedCard = null;
            foreach ($cards as $card) {
                if ((int) $card['id'] === $cardId) {
                    $selectedCard = $card;
                    break;
                }
            }

            if (!$selectedCard) {
                $errors['payment_method'] = 'Please select a valid saved card.';
            } else {
                $paymentLabel = $selectedCard['card_type'] . ' ending ' . $selectedCard['card_last4'];
                $verification = $visaCheck->verifySavedCard($selectedCard);
                $errors = array_merge($errors, $verification['errors']);
            }
        } elseif ($paymentChoice === 'new_card') {
            $cardType = $this->input('card_type');
            $cardNumber = preg_replace('/\D/', '', $this->input('card_number'));
            $expiry = $this->input('card_expiry');
            $cvv = $this->input('cvv');

            $verification = $visaCheck->verifyNewCard(
                $cardType,
                $cardNumber,
                $expiry,
                $cvv
            );
            $errors = array_merge($errors, $verification['errors']);

            if (!$errors) {
                $last4 = substr($cardNumber, -4);
                $paymentLabel = $cardType . ' ending ' . $last4;
                if (isset($_POST['save_card'])) {
                    $customerModel->addPaymentMethod(
                        (int) $customer['id'],
                        $cardType,
                        $last4,
                        $expiry,
                        $verification['status'],
                        $verification['reference']
                    );
                }
            }
        } else {
            $errors['payment_method'] = 'Please choose a payment method.';
        }

        if (!$errors && (!$verification || $verification['status'] !== 'Approved')) {
            if ($verification && $verification['status'] === 'Declined') {
                $this->recordDeclinedAttempt(
                    $customer,
                    $purchaseItems,
                    $bundle,
                    $paymentLabel,
                    $verification
                );
                $errors['payment_method'] = $verification['message'] . ' No stock was deducted; the order remains unpaid and can be retried.';
            } else {
                $errors['payment_method'] = 'VISAcheck could not approve this payment method.';
            }
        }

        if ($errors) {
            $_SESSION['payment_errors'] = $errors;
            $_SESSION['payment_old'] = [
                'payment_method' => $paymentChoice,
                'card_type' => $this->input('card_type'),
                'card_expiry' => $this->input('card_expiry'),
                'save_card' => isset($_POST['save_card']),
                'offer_code' => $offerCode,
            ];
            redirect(
                $source === 'cart'
                    ? '/payment?source=cart'
                    : (
                        $source === 'deal'
                            ? '/payment?deal_id=' . $dealId
                            : '/payment?package_id=' . $packageId
                    )
            );
        }

        $orderModel = new Order($this->db);
        $paymentModel = new Payment($this->db);
        $created = 0;
        $receiptReference = 'CD-' . date('Ymd') . '-' . strtoupper(
            substr(bin2hex(random_bytes(4)), 0, 8)
        );

        $this->db->beginTransaction();
        try {
            if ($source === 'deal' && !(new Deal($this->db))->decrementStock($dealId)) {
                throw new RuntimeException('This combo is no longer available.');
            }
            foreach ($purchaseItems as $package) {
                $quantity = (int) ($package['quantity'] ?? 1);
                $total = (float) $package['line_total'];
                $discount = $this->lineDiscount($total, $bundle);
                if (!$packageModel->decrementStock((int) $package['id'], $quantity)) {
                    throw new RuntimeException($package['package_name'] . ' no longer has enough stock.');
                }
                $order = $orderModel->create([
                    'user_id' => (int) $customer['id'],
                    'package_id' => (int) $package['id'],
                    'quantity' => $quantity,
                    'total' => $total,
                    'discount' => $discount,
                    'final_total' => round($total - $discount, 2),
                    'status' => 'Pending',
                    'order_channel' => 'App',
                ]);

                $paymentModel->create([
                    'order_id' => (int) $order['id'],
                    'payment_method' => $paymentLabel,
                    'amount' => round($total - $discount, 2),
                    'payment_status' => 'Success',
                    'verification_status' => $verification['status'],
                    'verification_reference' => $verification['reference'],
                    'verification_message' => $verification['message'],
                    'receipt_ref' => $receiptReference,
                ]);
                $orderModel->markPaid((int) $order['id']);
                (new AuditLog($this->db))->record(
                    (int) $customer['id'],
                    'payment.completed',
                    'order',
                    (int) $order['id'],
                    'Success',
                    'App payment approved; receipt ' . $receiptReference
                );
                $_SESSION['last_order_id'] = $order['id'];
                $created++;
            }

            $subscriptionModel = new Subscription($this->db);
            if ($source === 'deal' && $dealId > 0) {
                $subscriptionModel->activateDeal((int) $customer['id'], $dealId);
            } elseif (count($purchaseItems) === 1) {
                $subscriptionModel->activate(
                    (int) $customer['id'],
                    (int) $purchaseItems[0]['id']
                );
            }
            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollBack();
            $_SESSION['flash'] = $exception->getMessage();
            redirect($source === 'cart' ? '/checkout' : '/packages');
        }

        if ($source === 'cart' && $created > 0) {
            unset($_SESSION['cart']);
        }
        if ($created > 0 && $bundle['offer_applied'] && $bundle['offer_id']) {
            (new Offer($this->db))->recordUsage(
                (int) $bundle['offer_id'],
                (int) $customer['id']
            );
        }

        if ($created > 0) {
            $_SESSION['receipt_delivery_notice'] = [
                'to' => $customer['email'] ?? '',
                'reference' => $receiptReference,
                'sent_at' => date('c'),
            ];
        }

        $_SESSION['flash'] = $created > 0
            ? (
                $bundle['name']
                    ? $bundle['name'] . ' discount applied. Payment completed successfully.'
                    : 'Payment completed successfully.'
            )
            : 'No valid package was found for payment.';
        redirect(
            $created > 0
                ? '/bill?ref=' . urlencode($receiptReference)
                : '/packages'
        );
    }

    private function bundleDetails(array $items, string $offerCode = '', string $orderChannel = 'App'): array
    {
        $categories = [];
        $subtotal = 0.0;
        foreach ($items as $item) {
            $categories[strtolower(trim((string) $item['category']))] = true;
            $subtotal += (float) $item['line_total'];
        }

        $categoryCount = count($categories);
        $isApp = strcasecmp($orderChannel, 'App') === 0;
        $appDiscountPercent = $isApp ? 15 : 0;
        $offerDiscountPercent = 0;
        $name = $isApp ? '15% app-order promotion' : null;
        $eligibleOffers = [];
        $offerApplied = false;
        $offerId = null;

        if ($categoryCount === 1) {
            $offerModel = new Offer($this->db);
            $eligibleOffers = $offerModel->active();
            $activeOffer = $offerCode !== ''
                ? $offerModel->findByCode($offerCode)
                : null;

            if ($activeOffer) {
                $offerDiscountPercent = (int) $activeOffer['discount_percent'];
                $name = $isApp
                    ? '15% app promotion + ' . strtoupper((string) $activeOffer['code'])
                    : strtoupper((string) $activeOffer['code']) . ' offer';
                $offerApplied = true;
                $offerId = (int) $activeOffer['id'];
            }
        }

        $appDiscountAmount = round($subtotal * ($appDiscountPercent / 100), 2);
        $afterAppDiscount = $subtotal - $appDiscountAmount;
        $offerDiscountAmount = round($afterAppDiscount * ($offerDiscountPercent / 100), 2);
        $discountAmount = round($appDiscountAmount + $offerDiscountAmount, 2);
        $discountPercent = $subtotal > 0
            ? round(($discountAmount / $subtotal) * 100, 2)
            : 0;

        return [
            'name' => $name,
            'category_count' => $categoryCount,
            'discount_percent' => $discountPercent,
            'discount_amount' => round($discountAmount, 2),
            'app_discount_percent' => $appDiscountPercent,
            'app_discount_amount' => $appDiscountAmount,
            'offer_discount_percent' => $offerDiscountPercent,
            'offer_discount_amount' => $offerDiscountAmount,
            'eligible_offers' => $eligibleOffers,
            'offer_applied' => $offerApplied,
            'offer_id' => $offerId,
        ];
    }

    private function lineDiscount(float $lineTotal, array $bundle): float
    {
        $appDiscount = round(
            $lineTotal * ((float) $bundle['app_discount_percent'] / 100),
            2
        );
        $offerDiscount = round(
            ($lineTotal - $appDiscount) * ((float) $bundle['offer_discount_percent'] / 100),
            2
        );
        return round($appDiscount + $offerDiscount, 2);
    }

    private function recordDeclinedAttempt(
        array $customer,
        array $purchaseItems,
        array $bundle,
        string $paymentLabel,
        array $verification
    ): void {
        $orderModel = new Order($this->db);
        $paymentModel = new Payment($this->db);
        $this->db->beginTransaction();
        try {
            foreach ($purchaseItems as $package) {
                $total = (float) $package['line_total'];
                $discount = $this->lineDiscount($total, $bundle);
                $order = $orderModel->create([
                    'user_id' => (int) $customer['id'],
                    'package_id' => (int) $package['id'],
                    'quantity' => (int) ($package['quantity'] ?? 1),
                    'total' => $total,
                    'discount' => $discount,
                    'final_total' => round($total - $discount, 2),
                    'status' => 'Pending',
                    'order_channel' => 'App',
                ]);
                $paymentModel->create([
                    'order_id' => (int) $order['id'],
                    'payment_method' => $paymentLabel,
                    'amount' => round($total - $discount, 2),
                    'payment_status' => 'Failed',
                    'verification_status' => 'Declined',
                    'verification_reference' => $verification['reference'],
                    'verification_message' => $verification['message'],
                    'receipt_ref' => null,
                ]);
                (new AuditLog($this->db))->record(
                    (int) $customer['id'],
                    'payment.declined',
                    'order',
                    (int) $order['id'],
                    'Declined',
                    $verification['reference']
                );
            }
            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }
}
