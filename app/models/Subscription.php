<?php

class Subscription
{
    public function __construct(private Database $db)
    {
    }

    public function currentForUser(int $userId): ?array
    {
        return $this->db->fetch(
            'SELECT subscriptions.*,
                    COALESCE(packages.package_name, deals.deal_name) AS plan_name,
                    CASE
                        WHEN subscriptions.deal_id IS NOT NULL THEN deals.deal_type
                        ELSE packages.category
                    END AS plan_type,
                    COALESCE(packages.price, deals.price) AS price,
                    CASE
                        WHEN subscriptions.deal_id IS NOT NULL THEN (
                            SELECT COALESCE(SUM(component.minutes), 0)
                            FROM deal_packages
                            INNER JOIN packages AS component ON component.id = deal_packages.package_id
                            WHERE deal_packages.deal_id = subscriptions.deal_id
                        ) ELSE packages.minutes
                    END AS minutes,
                    CASE
                        WHEN subscriptions.deal_id IS NOT NULL THEN (
                            SELECT COALESCE(SUM(component.sms), 0)
                            FROM deal_packages
                            INNER JOIN packages AS component ON component.id = deal_packages.package_id
                            WHERE deal_packages.deal_id = subscriptions.deal_id
                        ) ELSE packages.sms
                    END AS sms,
                    CASE
                        WHEN subscriptions.deal_id IS NOT NULL THEN (
                            SELECT COALESCE(SUM(component.data_gb), 0)
                            FROM deal_packages
                            INNER JOIN packages AS component ON component.id = deal_packages.package_id
                            WHERE deal_packages.deal_id = subscriptions.deal_id
                        ) ELSE packages.data_gb
                    END AS data_gb,
                    COALESCE(packages.description, deals.description) AS description,
                    COALESCE(packages.is_active, deals.is_active) AS is_active
             FROM subscriptions
             LEFT JOIN packages ON packages.id = subscriptions.package_id
             LEFT JOIN deals ON deals.id = subscriptions.deal_id
             WHERE subscriptions.user_id = ?
             LIMIT 1',
            [$userId]
        );
    }

    public function activate(int $userId, int $packageId): void
    {
        $this->saveTarget($userId, $packageId, null);
    }

    public function activateDeal(int $userId, int $dealId): void
    {
        $this->saveTarget($userId, null, $dealId);
    }

    private function saveTarget(int $userId, ?int $packageId, ?int $dealId): void
    {
        if (($packageId === null) === ($dealId === null)) {
            throw new InvalidArgumentException('Choose exactly one subscription target.');
        }

        $existing = $this->db->fetch(
            'SELECT id FROM subscriptions WHERE user_id = ?',
            [$userId]
        );
        $renewal = date('Y-m-d', strtotime('+1 month'));

        if ($existing) {
            $this->db->execute(
                'UPDATE subscriptions
                 SET package_id = ?, deal_id = ?, status = ?, renewal_date = ?, updated_at = CURRENT_TIMESTAMP
                 WHERE user_id = ?',
                [$packageId, $dealId, 'Active', $renewal, $userId]
            );
            return;
        }

        $this->db->execute(
            'INSERT INTO subscriptions (user_id, package_id, deal_id, status, renewal_date)
             VALUES (?, ?, ?, ?, ?)',
            [$userId, $packageId, $dealId, 'Active', $renewal]
        );
    }
}
