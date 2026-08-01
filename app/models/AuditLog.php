<?php

class AuditLog
{
    public function __construct(private Database $db)
    {
    }

    public function record(
        ?int $actorId,
        string $eventType,
        string $entityType,
        ?int $entityId,
        string $outcome = 'Success',
        ?string $details = null
    ): void {
        $this->db->execute(
            'INSERT INTO audit_logs
             (actor_id, event_type, entity_type, entity_id, outcome, details)
             VALUES (?, ?, ?, ?, ?, ?)',
            [$actorId, $eventType, $entityType, $entityId, $outcome, $details]
        );
    }

    public function all(): array
    {
        return $this->db->fetchAll(
            'SELECT audit_logs.*, users.full_name AS actor_name
             FROM audit_logs
             LEFT JOIN users ON users.id = audit_logs.actor_id
             ORDER BY audit_logs.id DESC'
        );
    }

    /**
     * Return the customer-facing plan changes that staff need for follow-up.
     * The underlying audit log remains the single operational source of truth;
     * this method only turns its structured details into presentation fields.
     */
    public function subscriptionChanges(?int $customerId = null, string $query = ''): array
    {
        $sql = 'SELECT audit_logs.*, users.full_name AS customer_name,
                       users.email AS customer_email
                FROM audit_logs
                LEFT JOIN users ON users.id = audit_logs.actor_id
                WHERE audit_logs.event_type = ?';
        $params = ['subscription.updated'];

        if ($customerId !== null) {
            $sql .= ' AND audit_logs.entity_id = ?';
            $params[] = $customerId;
        }

        $sql .= ' ORDER BY audit_logs.id DESC';
        $rows = array_map(function (array $row): array {
            $details = self::decodeDetails($row['details'] ?? null);
            return [
                'id' => (int) $row['id'],
                'customer_id' => (int) ($row['entity_id'] ?? $row['actor_id'] ?? 0),
                'customer_name' => $row['customer_name'] ?: 'Unknown customer',
                'customer_email' => $row['customer_email'] ?: '',
                'previous_name' => $details['previous_name'] ?? 'Not recorded',
                'previous_type' => $details['previous_type'] ?? '',
                'new_name' => $details['new_name'] ?? self::humanDetails($row['details'] ?? null),
                'new_type' => $details['new_type'] ?? '',
                'channel' => $details['channel'] ?? 'Self-service',
                'status' => $details['status'] ?? 'Completed',
                'created_at' => (string) $row['created_at'],
            ];
        }, $this->db->fetchAll($sql, $params));

        $query = strtolower(trim($query));
        if ($query === '') {
            return $rows;
        }

        return array_values(array_filter($rows, static function (array $row) use ($query): bool {
            return str_contains(strtolower(implode(' ', [
                $row['customer_name'],
                $row['customer_email'],
                $row['previous_name'],
                $row['new_name'],
                $row['channel'],
                $row['status'],
            ])), $query);
        }));
    }

    public static function decodeDetails(?string $details): array
    {
        if (!$details) {
            return [];
        }

        $decoded = json_decode($details, true);
        return is_array($decoded) ? $decoded : [];
    }

    public static function humanDetails(?string $details): string
    {
        if (!$details) {
            return 'No additional details';
        }

        $decoded = self::decodeDetails($details);
        if (!$decoded) {
            return $details;
        }

        if (isset($decoded['new_name'])) {
            $previous = $decoded['previous_name'] ?? 'No previous plan';
            return $previous . ' changed to ' . $decoded['new_name']
                . ' · ' . ($decoded['status'] ?? 'Completed')
                . ' via ' . ($decoded['channel'] ?? 'Self-service');
        }

        return implode(' · ', array_map(
            static fn (mixed $value): string => is_scalar($value) ? (string) $value : '',
            array_values($decoded)
        ));
    }
}
