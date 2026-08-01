<?php

class Package
{
    public function __construct(private Database $db)
    {
    }

    public function all($category = null, $search = '', bool $includeInactive = false): array
    {
        $sql = 'SELECT * FROM packages WHERE 1';
        $params = [];

        if (!$includeInactive) {
            $sql .= ' AND is_active = 1';
        }

        if ($category) {
            $sql .= ' AND category = ?';
            $params[] = $category;
        }

        if ($search) {
            $sql .= ' AND (
                package_name LIKE ?
                OR description LIKE ?
                OR category LIKE ?
            )';

            $keyword = "%$search%";
            $params = array_merge(
                $params,
                [$keyword, $keyword, $keyword]
            );
        }

        $sql .= ' ORDER BY category, price';

        return $this->db->fetchAll($sql, $params);
    }

    public function featured(): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM packages
             WHERE is_active = 1
             ORDER BY stock DESC
             LIMIT 3'
        );
    }

    public function find($id, bool $includeInactive = false): ?array
    {
        return $this->db->fetch(
            'SELECT * FROM packages WHERE id = ?' . ($includeInactive ? '' : ' AND is_active = 1'),
            [$id]
        );
    }

    public function categories(): array
    {
        $rows = $this->db->fetchAll(
            'SELECT DISTINCT category
             FROM packages
             WHERE is_active = 1
             ORDER BY category'
        );

        return array_column($rows, 'category');
    }

    public function create(array $attributes): int
    {
        $this->db->execute(
            'INSERT INTO packages
             (package_name, category, price, minutes, sms, data_gb, description, stock, image)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $attributes['package_name'],
                $attributes['category'],
                $attributes['price'],
                $attributes['minutes'],
                $attributes['sms'],
                $attributes['data_gb'],
                $attributes['description'],
                $attributes['stock'],
                $attributes['image'],
            ]
        );

        return $this->db->lastInsertId();
    }

    public function update(int $id, array $attributes): void
    {
        $this->db->execute(
            'UPDATE packages
             SET package_name = ?, category = ?, price = ?, minutes = ?, sms = ?,
                 data_gb = ?, description = ?, stock = ?, image = ?
             WHERE id = ?',
            [
                $attributes['package_name'],
                $attributes['category'],
                $attributes['price'],
                $attributes['minutes'],
                $attributes['sms'],
                $attributes['data_gb'],
                $attributes['description'],
                $attributes['stock'],
                $attributes['image'],
                $id,
            ]
        );
    }

    public function hasHistory(int $id): bool
    {
        $orders = (int) ($this->db->fetch(
            'SELECT COUNT(*) AS total FROM orders WHERE package_id = ?', [$id]
        )['total'] ?? 0);
        $enquiries = (int) ($this->db->fetch(
            'SELECT COUNT(*) AS total FROM enquiries WHERE package_id = ?', [$id]
        )['total'] ?? 0);
        $combos = (int) ($this->db->fetch(
            'SELECT COUNT(*) AS total FROM deal_packages WHERE package_id = ?', [$id]
        )['total'] ?? 0);

        return $orders > 0 || $enquiries > 0 || $combos > 0;
    }

    public function delete(int $id): void
    {
        $this->db->execute('DELETE FROM packages WHERE id = ?', [$id]);
    }

    public function archive(int $id): void
    {
        $this->db->execute('UPDATE packages SET is_active = 0 WHERE id = ?', [$id]);
    }

    public function reactivate(int $id): void
    {
        $this->db->execute('UPDATE packages SET is_active = 1 WHERE id = ?', [$id]);
    }

    public function decrementStock(int $id, int $quantity): bool
    {
        if ($quantity < 1) {
            return false;
        }

        return $this->db->executeAffected(
            'UPDATE packages SET stock = stock - ? WHERE id = ? AND is_active = 1 AND stock >= ?',
            [$quantity, $id, $quantity]
        ) === 1;
    }
}
