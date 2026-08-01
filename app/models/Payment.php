<?php

class Payment
{
    public function __construct(private Database $db)
    {
    }

    public function create(array $attributes): array
    {
        $this->db->execute(
            'INSERT INTO payments
             (order_id, amount, payment_method, payment_status, verification_status,
              verification_reference, verification_message, receipt_ref)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $attributes['order_id'],
                $attributes['amount'],
                $attributes['payment_method'],
                $attributes['payment_status'],
                $attributes['verification_status'] ?? null,
                $attributes['verification_reference'] ?? null,
                $attributes['verification_message'] ?? null,
                $attributes['receipt_ref'] ?? null,
            ]
        );
        return $this->db->fetch('SELECT * FROM payments WHERE id = ?', [$this->db->lastInsertId()]);
    }

    public function all(): array
    {
        return $this->db->fetchAll('SELECT * FROM payments ORDER BY id DESC');
    }

    public function receiptForUser(string $reference, int $userId): array
    {
        return $this->db->fetchAll(
            'SELECT payments.*, orders.total, orders.discount,
                    orders.final_total, orders.status AS order_status,
                    packages.package_name, packages.category,
                    users.full_name, users.email, users.phone, users.address
             FROM payments
             INNER JOIN orders ON orders.id = payments.order_id
             INNER JOIN users ON users.id = orders.user_id
             LEFT JOIN packages ON packages.id = orders.package_id
             WHERE payments.receipt_ref = ? AND orders.user_id = ?
             ORDER BY payments.id',
            [$reference, $userId]
        );
    }
}
