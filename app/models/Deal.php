<?php

class Deal
{
    public function __construct(private Database $db)
    {
    }

    public function all(?string $type = null, bool $includeInactive = false): array
    {
        $sql = 'SELECT * FROM deals';
        $params = [];

        $conditions = [];
        if (!$includeInactive) {
            $conditions[] = 'is_active = 1';
        }

        if ($type !== null) {
            $conditions[] = 'deal_type = ?';
            $params[] = $type;
        }

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY deal_type, price, id';
        $deals = $this->db->fetchAll($sql, $params);

        foreach ($deals as &$deal) {
            $deal = $this->withDetails($deal);
        }
        unset($deal);

        return $deals;
    }

    public function find(int $id, bool $includeInactive = false): ?array
    {
        $deal = $this->db->fetch(
            'SELECT * FROM deals WHERE id = ?' . ($includeInactive ? '' : ' AND is_active = 1'),
            [$id]
        );

        return $deal ? $this->withDetails($deal) : null;
    }

    public function packages(int $dealId): array
    {
        return $this->db->fetchAll(
            'SELECT packages.*
             FROM deal_packages
             INNER JOIN packages ON packages.id = deal_packages.package_id
             WHERE deal_packages.deal_id = ?
             ORDER BY FIELD(packages.category, "Mobile", "Broadband", "Tablet"), packages.id',
            [$dealId]
        );
    }

    public function create(array $attributes, array $packageIds): int
    {
        $this->db->beginTransaction();
        try {
            $this->db->execute(
                'INSERT INTO deals
                 (deal_name, deal_type, normal_price, price, description, stock, image)
                 VALUES (?, ?, ?, ?, ?, ?, ?)',
                [
                    $attributes['deal_name'],
                    $attributes['deal_type'],
                    $attributes['normal_price'],
                    $attributes['price'],
                    $attributes['description'],
                    $attributes['stock'],
                    $attributes['image'],
                ]
            );
            $dealId = $this->db->lastInsertId();
            $this->syncPackages($dealId, $packageIds);
            $this->db->commit();
            return $dealId;
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function update(int $id, array $attributes, array $packageIds): void
    {
        $this->db->beginTransaction();
        try {
            $this->db->execute(
                'UPDATE deals
                 SET deal_name = ?, deal_type = ?, normal_price = ?, price = ?,
                     description = ?, stock = ?, image = ?
                 WHERE id = ?',
                [
                    $attributes['deal_name'],
                    $attributes['deal_type'],
                    $attributes['normal_price'],
                    $attributes['price'],
                    $attributes['description'],
                    $attributes['stock'],
                    $attributes['image'],
                    $id,
                ]
            );
            $this->db->execute('DELETE FROM deal_packages WHERE deal_id = ?', [$id]);
            $this->syncPackages($id, $packageIds);
            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function delete(int $id): void
    {
        $this->db->execute('DELETE FROM deals WHERE id = ?', [$id]);
    }

    public function archive(int $id): void
    {
        $this->db->execute('UPDATE deals SET is_active = 0 WHERE id = ?', [$id]);
    }

    public function reactivate(int $id): void
    {
        $this->db->execute('UPDATE deals SET is_active = 1 WHERE id = ?', [$id]);
    }

    public function decrementStock(int $id, int $quantity = 1): bool
    {
        if ($quantity < 1) {
            return false;
        }

        return $this->db->executeAffected(
            'UPDATE deals SET stock = stock - ? WHERE id = ? AND is_active = 1 AND stock >= ?',
            [$quantity, $id, $quantity]
        ) === 1;
    }

    private function syncPackages(int $dealId, array $packageIds): void
    {
        foreach ($packageIds as $packageId) {
            $this->db->execute(
                'INSERT INTO deal_packages (deal_id, package_id) VALUES (?, ?)',
                [$dealId, $packageId]
            );
        }
    }

    private function withDetails(array $deal): array
    {
        $discountPercent = 15;
        $deal['discount_percent'] = $discountPercent;
        $deal['price'] = round(
            (float) $deal['normal_price'] * (1 - $discountPercent / 100),
            2
        );
        $deal['packages'] = $this->packages((int) $deal['id']);

        return $deal;
    }
}
