<?php

class Customer
{
    public function __construct(private Database $db)
    {
    }

    public function all(): array
    {
        return $this->db->fetchAll(
            'SELECT
                id,
                full_name,
                email,
                phone,
                address,
                role,
                created_at
             FROM users
             WHERE role = ?
             ORDER BY id DESC',
            ['customer']
        );
    }

    public function search(string $query = ''): array
    {
        $query = trim($query);
        if ($query === '') {
            return $this->all();
        }

        $needle = mb_strtolower($query);
        $phoneNeedle = preg_replace('/\D+/', '', $query) ?? '';

        return array_values(array_filter(
            $this->all(),
            function (array $customer) use ($needle, $phoneNeedle): bool {
                $matchesText = str_contains(mb_strtolower((string) ($customer['full_name'] ?? '')), $needle)
                    || str_contains(mb_strtolower((string) ($customer['email'] ?? '')), $needle);
                $matchesPhone = $phoneNeedle !== ''
                    && str_contains($this->normalisePhone((string) ($customer['phone'] ?? '')), $this->normalisePhone($phoneNeedle));
                return $matchesText || $matchesPhone;
            }
        ));
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetch(
            'SELECT *
             FROM users
             WHERE LOWER(email) = LOWER(?)',
            [trim($email)]
        );
    }

    public function find(int $id): ?array
    {
        return $this->db->fetch(
            'SELECT *
             FROM users
             WHERE id = ?',
            [$id]
        );
    }

    public function create(array $attributes): array
    {
        $role = $attributes['role'] ?? 'customer';

        $this->db->execute(
            'INSERT INTO users
            (
                full_name,
                email,
                password,
                phone,
                address,
                role
            )
            VALUES (?, ?, ?, ?, ?, ?)',
            [
                $attributes['full_name'],
                strtolower(trim($attributes['email'])),
                $attributes['password'],
                $attributes['phone'] ?? null,
                $attributes['address'] ?? null,
                $role,
            ]
        );

        return $this->find(
            $this->db->lastInsertId()
        );
    }

    public function update(
        int $id,
        array $attributes
    ): ?array {
        $this->db->execute(
            'UPDATE users
             SET
                full_name = ?,
                phone = ?,
                address = ?,
                credit_card = NULL
             WHERE id = ?',
            [
                $attributes['full_name'],
                $attributes['phone'] ?? null,
                $attributes['address'] ?? null,
                $id,
            ]
        );

        return $this->find($id);
    }
    public function findByEmailAndPhone($email, $phone): ?array
    {
        $customer = $this->findByEmail((string) $email);

        if (!$customer) {
            return null;
        }

        return $this->normalisePhone($customer['phone'] ?? '')
            === $this->normalisePhone((string) $phone)
            ? $customer
            : null;
    }

    public function findByPhone(string $phone): ?array
    {
        $normalisedPhone = $this->normalisePhone($phone);
        if ($normalisedPhone === '') {
            return null;
        }

        foreach ($this->all() as $customer) {
            if ($this->normalisePhone($customer['phone'] ?? '') === $normalisedPhone) {
                return $this->find((int) $customer['id']);
            }
        }

        return null;
    }

    private function normalisePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        // Treat common Vietnamese local and international formats as the same
        // number, for example 0900... and +84 900....
        if (strlen($digits) === 11 && str_starts_with($digits, '84')) {
            return '0' . substr($digits, 2);
        }

        return $digits;
    }

public function updatePassword($id, $password)
{
    return $this->db->execute(
        "UPDATE users SET password = ? WHERE id = ?",
        [$password, $id]
    );
}
public function updatePayment($id, $cardType, $creditCard)
{
    $digits = preg_replace('/\D+/', '', (string) $creditCard) ?? '';
    $last4 = strlen($digits) >= 4 ? substr($digits, -4) : null;
    return $this->db->execute(
        "UPDATE users
         SET card_type = ?, credit_card = ?
         WHERE id = ?",
        [$cardType ?: null, $last4, $id]
    );
}
// Lấy tất cả thẻ của user
public function paymentMethods(int $userId): array
{
    return $this->db->fetchAll(
        "SELECT *
         FROM payment_methods
         WHERE user_id = ?
         ORDER BY is_default DESC, id DESC",
        [$userId]
    );
}


// Thêm thẻ mới
public function addPaymentMethod(
    int $userId,
    string $cardType,
    string $last4,
    string $expiry,
    string $verificationStatus = 'Approved',
    ?string $verificationReference = null
): void {
    // Kiểm tra user đã có thẻ chưa
    $cards = $this->paymentMethods($userId);

    // Thẻ đầu tiên tự động là mặc định
    $isDefault = empty($cards) ? 1 : 0;

    $this->db->execute(
        "INSERT INTO payment_methods
        (user_id, card_type, card_last4, card_expiry, verification_status,
         verification_reference, is_default)
        VALUES (?, ?, ?, ?, ?, ?, ?)",
        [
            $userId,
            $cardType,
            $last4,
            $expiry,
            $verificationStatus,
            $verificationReference,
            $isDefault
        ]
    );
}


// Xóa thẻ
public function deletePaymentMethod(
    int $cardId,
    int $userId
): void {
    $deleted = $this->db->fetch(
        'SELECT is_default FROM payment_methods WHERE id = ? AND user_id = ?',
        [$cardId, $userId]
    );
    $this->db->execute(
        "DELETE FROM payment_methods
         WHERE id = ? AND user_id = ?",
        [$cardId, $userId]
    );

    if ((int) ($deleted['is_default'] ?? 0) === 1) {
        $replacement = $this->db->fetch(
            'SELECT id FROM payment_methods WHERE user_id = ? ORDER BY id DESC LIMIT 1',
            [$userId]
        );
        if ($replacement) {
            $this->db->execute(
                'UPDATE payment_methods SET is_default = 1 WHERE id = ? AND user_id = ?',
                [(int) $replacement['id'], $userId]
            );
        }
    }
}


// Đặt thẻ mặc định
public function setDefaultPaymentMethod(
    int $cardId,
    int $userId
): void {
    // Bỏ mặc định tất cả thẻ của user
    $this->db->execute(
        "UPDATE payment_methods
         SET is_default = 0
         WHERE user_id = ?",
        [$userId]
    );

    // Đặt thẻ được chọn thành mặc định
    $this->db->execute(
        "UPDATE payment_methods
         SET is_default = 1
         WHERE id = ? AND user_id = ?",
        [$cardId, $userId]
    );
}
}
