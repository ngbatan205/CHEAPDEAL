<?php

class VisaCheck
{
    private const SUPPORTED_TYPES = ['Visa', 'Mastercard', 'JCB'];

    public function verifyNewCard(
        string $declaredType,
        string $cardNumber,
        string $expiry,
        string $cvv
    ): array {
        $number = preg_replace('/\D+/', '', $cardNumber);
        $errors = [];

        if (!in_array($declaredType, self::SUPPORTED_TYPES, true)) {
            $errors['card_type'] = 'Please select a supported card type.';
        }
        if ($number === '' || strlen($number) < 13 || strlen($number) > 19) {
            $errors['card_number'] = 'Card number must contain 13 to 19 digits.';
        } elseif (!$this->passesLuhn($number)) {
            $errors['card_number'] = 'Card number failed the VISAcheck checksum.';
        } elseif ($declaredType !== $this->detectNetwork($number)) {
            $errors['card_type'] = 'Selected card type does not match the card number.';
        }

        if (!$this->expiryIsValid($expiry)) {
            $errors['card_expiry'] = preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $expiry)
                ? 'This card has expired.'
                : 'Use the MM/YY format.';
        }
        if (!preg_match('/^[0-9]{3,4}$/', $cvv)) {
            $errors['cvv'] = 'CVV must contain 3 or 4 digits.';
        }

        if ($errors) {
            return $this->result('Declined', 'Card details failed VISAcheck validation.', $errors);
        }

        // Deterministic sandbox decline card. It is structurally valid and is
        // used to demonstrate a real-time declined-payment outcome safely.
        if ($number === '4000000000000002') {
            return $this->result('Declined', 'VISAcheck declined this sample transaction.');
        }

        return $this->result(
            'Approved',
            'Card details verified by VISAcheck.'
        );
    }

    public function verifySavedCard(array $card): array
    {
        $type = (string) ($card['card_type'] ?? '');
        $last4 = (string) ($card['card_last4'] ?? '');
        $expiry = (string) ($card['card_expiry'] ?? '');

        if (!in_array($type, self::SUPPORTED_TYPES, true) || !preg_match('/^[0-9]{4}$/', $last4)) {
            return $this->result(
                'Declined',
                'Saved card token is invalid.',
                ['payment_method' => 'VISAcheck could not verify this saved card.']
            );
        }
        if (!$this->expiryIsValid($expiry)) {
            return $this->result(
                'Declined',
                'Saved card has expired.',
                ['payment_method' => 'This saved card has expired. Please choose another card.']
            );
        }

        return $this->result('Approved', 'Saved card token verified by VISAcheck.');
    }

    private function result(string $status, string $message, array $errors = []): array
    {
        return [
            'status' => $status,
            'reference' => 'VC-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(4))),
            'message' => $message,
            'errors' => $errors,
        ];
    }

    private function expiryIsValid(string $expiry): bool
    {
        if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expiry, $parts)) {
            return false;
        }

        $expiryValue = (int) ('20' . $parts[2]) * 100 + (int) $parts[1];
        return $expiryValue >= (int) date('Ym');
    }

    private function passesLuhn(string $number): bool
    {
        $sum = 0;
        $alternate = false;
        for ($index = strlen($number) - 1; $index >= 0; $index--) {
            $digit = (int) $number[$index];
            if ($alternate) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
            $alternate = !$alternate;
        }

        return $sum % 10 === 0;
    }

    private function detectNetwork(string $number): ?string
    {
        if (preg_match('/^4[0-9]{12}(?:[0-9]{3})?(?:[0-9]{3})?$/', $number)) {
            return 'Visa';
        }
        if (preg_match('/^(?:5[1-5][0-9]{14}|2(?:2[2-9][1-9]|2[3-9][0-9]{2}|[3-6][0-9]{3}|7[01][0-9]{2}|720[0-9])[0-9]{12})$/', $number)) {
            return 'Mastercard';
        }
        if (preg_match('/^(?:35(?:2[89]|[3-8][0-9])[0-9]{12,15})$/', $number)) {
            return 'JCB';
        }

        return null;
    }
}
