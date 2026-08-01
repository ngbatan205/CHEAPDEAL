<?php

class Order
{
    public function __construct(private Database $db)
    {
    }

    public function all(): array
    {
        return $this->db->fetchAll(
            'SELECT orders.*, users.full_name, users.email, packages.package_name, packages.category,
                    staff.full_name AS created_by_name,
                    payments.payment_method, payments.payment_status,
                    payments.verification_status, payments.verification_reference
             FROM orders
             LEFT JOIN users ON users.id = orders.user_id
             LEFT JOIN users AS staff ON staff.id = orders.created_by
             LEFT JOIN packages ON packages.id = orders.package_id
             LEFT JOIN payments ON payments.order_id = orders.id
             ORDER BY orders.id DESC'
        );
    }

    public function forUser(int $userId): array
    {
        return $this->db->fetchAll(
            'SELECT orders.*, packages.package_name, packages.category,
                    staff.full_name AS created_by_name,
                    payments.id AS payment_id,
                    payments.payment_method,
                    payments.payment_status,
                    payments.verification_status,
                    payments.verification_reference,
                    payments.payment_date,
                    payments.receipt_ref
             FROM orders
             LEFT JOIN packages ON packages.id = orders.package_id
             LEFT JOIN users AS staff ON staff.id = orders.created_by
             LEFT JOIN payments ON payments.order_id = orders.id
             WHERE orders.user_id = ?
             ORDER BY orders.id DESC',
            [$userId]
        );
    }

    public function find(int $id): ?array
    {
        return $this->db->fetch(
            'SELECT orders.*, users.full_name, users.email, packages.package_name, packages.category
             FROM orders
             LEFT JOIN users ON users.id = orders.user_id
             LEFT JOIN packages ON packages.id = orders.package_id
             WHERE orders.id = ?',
            [$id]
        );
    }

    public function create(array $attributes): array
    {
        $this->db->execute(
            'INSERT INTO orders
             (user_id, package_id, quantity, total, discount, final_total, status, order_channel, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $attributes['user_id'],
                $attributes['package_id'],
                $attributes['quantity'] ?? 1,
                $attributes['total'],
                $attributes['discount'],
                $attributes['final_total'],
                $attributes['status'] ?? 'Pending',
                $attributes['order_channel'] ?? 'Website',
                $attributes['created_by'] ?? null,
            ]
        );
        return $this->find($this->db->lastInsertId());
    }

    public function markPaid(int $id): void
    {
        $this->db->execute('UPDATE orders SET status = ? WHERE id = ?', ['Paid', $id]);
    }
}
