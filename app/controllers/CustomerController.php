<?php

class CustomerController extends Controller
{
    private function requireCustomer(): array
    {
        $customer = $this->currentCustomer();

        if (!$customer) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            redirect('/login');
        }

        return $customer;
    }


    // =========================================
    // ACCOUNT
    // =========================================

    public function account(): void
    {
        $customer = $this->requireCustomer();

        $orders = (new Order($this->db))
            ->forUser((int) $customer['id']);

        $this->view('customer/account', [
            'title' => 'My account',
            'customer' => $customer,
            'orders' => $orders
        ]);
    }

    public function messages(): void
    {
        $customer = $this->requireCustomer();

        if (in_array($customer['role'] ?? '', ['admin', 'csr'], true)) {
            redirect('/crm/enquiries');
        }

        $this->view('customer/messages', [
            'title' => 'My messages',
            'customer' => $customer,
            'enquiries' => (new Enquiry($this->db))->forUser(
                (int) $customer['id']
            ),
        ]);
    }

    public function bill(): void
    {
        $customer = $this->requireCustomer();
        $reference = $this->input('ref');
        $payments = $reference !== ''
            ? (new Payment($this->db))->receiptForUser(
                $reference,
                (int) $customer['id']
            )
            : [];

        if (!$payments) {
            http_response_code(404);
        }

        $this->view('customer/bill', [
            'title' => $payments ? 'Payment receipt' : 'Bill not found',
            'customer' => $customer,
            'payments' => $payments,
            'reference' => $reference,
        ]);
    }


    // =========================================
    // PROFILE
    // =========================================

    public function profile(): void
    {
        $customer = $this->requireCustomer();

        $cards = (new Customer($this->db))
            ->paymentMethods((int) $customer['id']);

        $this->view('customer/profile', [
            'title' => 'Profile',
            'customer' => $customer,
            'cards' => $cards
        ]);
    }


    // =========================================
    // UPDATE PERSONAL INFORMATION
    // =========================================

    public function update(): void
    {
        $customer = $this->requireCustomer();

        $name = trim($this->input('name'));
        $phone = trim($this->input('phone'));
        $address = trim($this->input('address'));
        $errors = [];
        if (mb_strlen($name) < 2) {
            $errors[] = 'Please enter your full name.';
        }
        if (!preg_match('/^[0-9+()\s-]{8,20}$/', $phone)) {
            $errors[] = 'Please enter a valid phone number.';
        }
        if (mb_strlen($address) < 5) {
            $errors[] = 'Please enter your address.';
        }
        if ($errors) {
            $_SESSION['flash'] = implode(' ', $errors);
            redirect('/profile');
        }

        $updated = (new Customer($this->db))->update(
            (int) $customer['id'],
            [
                'full_name' => $name,
                'phone' => $phone,
                'address' => $address,
            ]
        );

        unset($updated['password']);

        $_SESSION['customer'] = $updated;

        $_SESSION['flash'] =
            'Profile updated successfully.';

        redirect('/profile');
    }


    // =========================================
    // UPDATE PAYMENT METHOD
    // =========================================

    public function updatePayment(): void
    {
        $customer = $this->requireCustomer();

        $cardType = trim($this->input('card_type'));
        $creditCard = trim($this->input('credit_card'));
        $digits = preg_replace('/\D+/', '', $creditCard) ?? '';
        $last4 = strlen($digits) >= 4 ? substr($digits, -4) : '';

        if (!in_array($cardType, ['Visa', 'Mastercard', 'JCB'], true) || $last4 === '') {
            $_SESSION['flash'] = 'Please enter a supported card and valid masked reference.';
            redirect('/profile?tab=payment');
        }

        (new Customer($this->db))->updatePayment(
            (int) $customer['id'],
            $cardType,
            $last4
        );

        $_SESSION['customer']['card_type'] = $cardType;
        $_SESSION['customer']['credit_card'] = $last4;

        $_SESSION['flash'] =
            'Payment method updated successfully.';

        redirect('/profile?tab=payment');
    }


    // =========================================
    // CHANGE PASSWORD
    // =========================================

    public function changePassword(): void
    {
        $customer = $this->requireCustomer();

        $current = $this->input('current_password');
        $new = $this->input('new_password');
        $confirm = $this->input('confirm_password');

        $model = new Customer($this->db);

        $user = $model->find(
            (int) $customer['id']
        );

        if (
            !$user ||
            !password_verify(
                $current,
                $user['password']
            )
        ) {
            $_SESSION['flash'] =
                'Current password is incorrect.';

            redirect('/profile?tab=password');
        }

        if (strlen($new) < 8) {
            $_SESSION['flash'] =
                'New password must have at least 8 characters.';

            redirect('/profile?tab=password');
        }

        if ($new !== $confirm) {
            $_SESSION['flash'] =
                'New passwords do not match.';

            redirect('/profile?tab=password');
        }

        $model->updatePassword(
            (int) $customer['id'],
            password_hash(
                $new,
                PASSWORD_DEFAULT
            )
        );

        $_SESSION['flash'] =
            'Password changed successfully.';

        redirect('/profile?tab=password');
    }


    // =========================================
    // ADD PAYMENT METHOD
    // =========================================

    public function verifyPaymentMethod(): void
    {
        $this->requireCustomer();

        $cardType = $this->input('card_type');
        $cardNumber = preg_replace('/\D/', '', $this->input('card_number'));
        $expiry = $this->input('card_expiry');
        $cvv = $this->input('cvv');
        $verification = (new VisaCheck())->verifyNewCard(
            $cardType,
            $cardNumber,
            $expiry,
            $cvv
        );

        header('Content-Type: application/json');
        if ($verification['status'] !== 'Approved') {
            unset($_SESSION['profile_card_verification']);
            http_response_code(422);
            echo json_encode([
                'ok' => false,
                'status' => $verification['status'],
                'message' => implode(' ', array_values($verification['errors']))
                    ?: $verification['message'],
            ]);
            return;
        }

        $_SESSION['profile_card_verification'] = [
            'fingerprint' => $this->cardVerificationFingerprint(
                $cardType,
                $cardNumber,
                $expiry,
                $cvv
            ),
            'reference' => $verification['reference'],
            'expires_at' => time() + 300,
        ];

        echo json_encode([
            'ok' => true,
            'status' => 'Approved',
            'reference' => $verification['reference'],
            'message' => 'Card approved by VISAcheck.',
        ]);
    }

    public function addPaymentMethod(): void
    {
        $customer = $this->requireCustomer();

        $cardType = trim(
            $this->input('card_type')
        );

        $cardNumber = preg_replace(
            '/\D/',
            '',
            $this->input('card_number')
        );

        $expiry = trim(
            $this->input('card_expiry')
        );

        $cvv = trim(
            $this->input('cvv')
        );

        $savedVerification = $_SESSION['profile_card_verification'] ?? [];
        $fingerprint = $this->cardVerificationFingerprint(
            $cardType,
            $cardNumber,
            $expiry,
            $cvv
        );
        if (
            ($savedVerification['fingerprint'] ?? '') !== $fingerprint
            || (int) ($savedVerification['expires_at'] ?? 0) < time()
        ) {
            unset($_SESSION['profile_card_verification']);
            $_SESSION['flash'] = 'Verify this card with VISAcheck before saving it.';
            redirect('/profile?tab=payment&add_card=1');
        }


        // Kiểm tra loại thẻ
        if (
            !in_array(
                $cardType,
                ['Visa', 'Mastercard', 'JCB'],
                true
            )
        ) {
            $_SESSION['flash'] =
                'Invalid card type.';

            redirect('/profile?tab=payment');
        }


        // Card number: 13 - 19 số
        if (
            !ctype_digit($cardNumber) ||
            strlen($cardNumber) < 13 ||
            strlen($cardNumber) > 19
        ) {
            $_SESSION['flash'] =
                'Card number must contain 13 to 19 digits.';

            redirect('/profile?tab=payment');
        }


        // Expiry MM/YY
        if (
            !preg_match(
                '/^(0[1-9]|1[0-2])\/[0-9]{2}$/',
                $expiry
            )
        ) {
            $_SESSION['flash'] =
                'Expiry date must use MM/YY format.';

            redirect('/profile?tab=payment');
        }


        // CVV 3 số
        if (
            !preg_match(
                '/^[0-9]{3}$/',
                $cvv
            )
        ) {
            $_SESSION['flash'] =
                'CVV must contain exactly 3 digits.';

            redirect('/profile?tab=payment');
        }


        // Chỉ lưu 4 số cuối
        $last4 = substr(
            $cardNumber,
            -4
        );


        (new Customer($this->db))
            ->addPaymentMethod(
                (int) $customer['id'],
                $cardType,
                $last4,
                $expiry,
                'Approved',
                $savedVerification['reference'] ?? null
            );


        unset($_SESSION['profile_card_verification']);
        $_SESSION['flash'] =
            'Payment method verified by VISAcheck and saved successfully.';


        // QUAN TRỌNG:
        // Không redirect sang /payment-methods
        // vì route này không tồn tại
        redirect('/profile?tab=payment');
    }

    private function cardVerificationFingerprint(
        string $cardType,
        string $cardNumber,
        string $expiry,
        string $cvv
    ): string {
        return hash('sha256', implode('|', [
            $cardType,
            preg_replace('/\D/', '', $cardNumber),
            $expiry,
            $cvv,
        ]));
    }


    // =========================================
    // DELETE PAYMENT METHOD
    // =========================================

    public function deletePaymentMethod(): void
    {
        $customer = $this->requireCustomer();

        $cardId = (int)
            $this->input('card_id');


        (new Customer($this->db))
            ->deletePaymentMethod(
                $cardId,
                (int) $customer['id']
            );


        $_SESSION['flash'] =
            'Payment method deleted successfully.';


        // Quay lại Profile
        // và mở tab Payment method
        redirect('/profile?tab=payment');
    }


    // =========================================
    // SET DEFAULT PAYMENT METHOD
    // =========================================

    public function setDefaultPaymentMethod(): void
    {
        $customer = $this->requireCustomer();

        $cardId = (int)
            $this->input('card_id');


        (new Customer($this->db))
            ->setDefaultPaymentMethod(
                $cardId,
                (int) $customer['id']
            );


        $_SESSION['flash'] =
            'Default payment method updated.';


        // Quay lại Profile
        // và mở tab Payment method
        redirect('/profile?tab=payment');
    }

    public function subscription(): void
    {
        $customer = $this->requireCustomer();
        if (($customer['role'] ?? 'customer') !== 'customer') {
            redirect('/crm');
        }

        $this->view('customer/subscription', [
            'title' => 'My subscription',
            'customer' => $customer,
            'subscription' => (new Subscription($this->db))->currentForUser((int) $customer['id']),
            'packages' => (new Package($this->db))->all(),
            'deals' => (new Deal($this->db))->all(),
        ]);
    }

    public function updateSubscription(): void
    {
        $customer = $this->requireCustomer();
        if (($customer['role'] ?? 'customer') !== 'customer') {
            redirect('/crm');
        }

        $target = $this->input('subscription_target');
        // Backward compatibility for existing forms/bookmarks that submit the
        // original package_id field to this unchanged route.
        if ($target === '' && (int) $this->input('package_id') > 0) {
            $target = 'package:' . (int) $this->input('package_id');
        }
        if (!preg_match('/^(package|deal):([1-9][0-9]*)$/', $target, $parts)) {
            $_SESSION['flash'] = 'Choose an active, available package or combo before confirming the change.';
            redirect('/subscription');
        }

        $targetType = $parts[1];
        $targetId = (int) $parts[2];
        $subscriptionModel = new Subscription($this->db);
        $previousSubscription = $subscriptionModel->currentForUser((int) $customer['id']);

        $alreadyActive = $previousSubscription
            && (($targetType === 'package'
                    && (int) ($previousSubscription['package_id'] ?? 0) === $targetId)
                || ($targetType === 'deal'
                    && (int) ($previousSubscription['deal_id'] ?? 0) === $targetId));
        if ($alreadyActive) {
            $_SESSION['flash'] = 'That plan is already active. Choose a different package or combo to make a change.';
            redirect('/subscription');
        }

        if ($targetType === 'package') {
            $package = (new Package($this->db))->find($targetId);
            if (!$package || (int) $package['stock'] < 1 || (float) $package['price'] <= 0) {
                $_SESSION['flash'] = 'Choose an active, available package before confirming the change.';
                redirect('/subscription');
            }
            $subscriptionModel->activate((int) $customer['id'], $targetId);
            $targetName = $package['package_name'];
        } else {
            $deal = (new Deal($this->db))->find($targetId);
            $componentsAvailable = $deal && count($deal['packages'] ?? []) >= 2;
            foreach (($deal['packages'] ?? []) as $component) {
                if ((int) ($component['is_active'] ?? 1) !== 1 || (int) $component['stock'] < 1) {
                    $componentsAvailable = false;
                }
            }
            if (!$deal || !$componentsAvailable || (int) $deal['stock'] < 1 || (float) $deal['price'] <= 0) {
                $_SESSION['flash'] = 'Choose an active combo whose included packages are all available.';
                redirect('/subscription');
            }
            $subscriptionModel->activateDeal((int) $customer['id'], $targetId);
            $targetName = $deal['deal_name'];
        }

        (new AuditLog($this->db))->record(
            (int) $customer['id'],
            'subscription.updated',
            'subscription',
            (int) $customer['id'],
            'Success',
            json_encode([
                'previous_type' => $previousSubscription
                    ? ((int) ($previousSubscription['deal_id'] ?? 0) > 0 ? 'Combo' : 'Package')
                    : 'None',
                'previous_id' => $previousSubscription
                    ? (int) (($previousSubscription['deal_id'] ?? 0) ?: ($previousSubscription['package_id'] ?? 0))
                    : null,
                'previous_name' => $previousSubscription['plan_name'] ?? 'No previous plan',
                'new_type' => $targetType === 'deal' ? 'Combo' : 'Package',
                'new_id' => $targetId,
                'new_name' => $targetName,
                'channel' => 'Self-service',
                'status' => 'Completed',
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
        $_SESSION['flash'] = 'Plan change completed. ' . $targetName . ' is now active and the customer service team can see this change in your account history.';
        redirect('/subscription');
    }
}
