<?php

class Offer
{
    public function __construct(private Database $db)
    {
    }

    public function all(): array
    {
        return $this->db->fetchAll(
            'SELECT offers.*,
                    (SELECT COUNT(*)
                     FROM offer_usage
                     WHERE offer_usage.offer_id = offers.id
                       AND offer_usage.used = 1) AS usage_count
             FROM offers
             ORDER BY is_active DESC,
                      CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END,
                      expiry_date ASC,
                      id DESC'
        );
    }

    public function active(): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM offers
             WHERE is_active = 1
               AND (expiry_date IS NULL OR expiry_date >= CURDATE())
             ORDER BY discount_percent DESC, expiry_date ASC'
        );
    }

    public function find(int $id): ?array
    {
        return $this->db->fetch('SELECT * FROM offers WHERE id = ?', [$id]);
    }

    public function findByCode(string $code): ?array
    {
        return $this->db->fetch(
            'SELECT * FROM offers
             WHERE UPPER(code) = UPPER(?)
               AND is_active = 1
               AND (expiry_date IS NULL OR expiry_date >= CURDATE())',
            [$code]
        );
    }

    public function codeExists(string $code, int $exceptId = 0): bool
    {
        $sql = 'SELECT id FROM offers WHERE UPPER(code) = UPPER(?)';
        $params = [$code];
        if ($exceptId > 0) {
            $sql .= ' AND id <> ?';
            $params[] = $exceptId;
        }

        return $this->db->fetch($sql, $params) !== null;
    }

    public function create(array $data): int
    {
        $this->db->execute(
            'INSERT INTO offers
             (code, description, discount_percent, expiry_date, is_active)
             VALUES (?, ?, ?, ?, ?)',
            [
                $data['code'],
                $data['description'],
                $data['discount_percent'],
                $data['expiry_date'],
                $data['is_active'] ?? 1,
            ]
        );

        return $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        return $this->db->execute(
            'UPDATE offers
             SET code = ?, description = ?, discount_percent = ?, expiry_date = ?
             WHERE id = ?',
            [
                $data['code'],
                $data['description'],
                $data['discount_percent'],
                $data['expiry_date'],
                $id,
            ]
        );
    }

    public function setActive(int $id, bool $active): bool
    {
        return $this->db->execute(
            'UPDATE offers SET is_active = ? WHERE id = ?',
            [$active ? 1 : 0, $id]
        );
    }

    public function recordUsage(int $offerId, int $userId): void
    {
        $this->db->execute(
            'INSERT INTO offer_usage (offer_id, user_id, used) VALUES (?, ?, 1)',
            [$offerId, $userId]
        );
    }
}
