<?php

class OperationalRecord
{
    public function __construct(private Database $db)
    {
    }

    public function all(string $type = '', string $status = '', string $query = ''): array
    {
        $records = array_merge(
            $this->customerRecords(),
            $this->orderRecords(),
            $this->paymentRecords(),
            $this->enquiryRecords(),
            $this->auditRecords()
        );

        $type = strtolower(trim($type));
        $status = strtolower(trim($status));
        $query = strtolower(trim($query));
        $records = array_values(array_filter(
            $records,
            static function (array $record) use ($type, $status, $query): bool {
                if ($type !== '' && strtolower($record['type']) !== $type) {
                    return false;
                }
                if ($status !== '' && strtolower($record['status']) !== $status) {
                    return false;
                }
                if ($query !== '') {
                    $haystack = strtolower(implode(' ', [
                        $record['reference'],
                        $record['summary'],
                        $record['status'],
                        $record['created_at'],
                    ]));
                    if (!str_contains($haystack, $query)) {
                        return false;
                    }
                }
                return true;
            }
        ));

        usort($records, static fn (array $a, array $b): int => strcmp($b['created_at'], $a['created_at']));
        return $records;
    }

    public function find(string $type, int $id): ?array
    {
        foreach ($this->all($type) as $record) {
            if ((int) $record['id'] === $id) {
                return $record;
            }
        }
        return null;
    }

    private function customerRecords(): array
    {
        return array_map(static fn (array $row): array => [
            'id' => (int) $row['id'],
            'type' => 'customer',
            'reference' => 'CUS-' . (int) $row['id'],
            'status' => ucfirst((string) $row['role']),
            'summary' => $row['full_name'] . ' · ' . $row['email'] . ' · ' . ($row['phone'] ?: 'No phone'),
            'created_at' => (string) $row['created_at'],
            'details' => [
                'Name' => $row['full_name'],
                'Email' => $row['email'],
                'Phone' => $row['phone'] ?: 'Not provided',
                'Address' => $row['address'] ?: 'Not provided',
                'Role' => $row['role'],
                'Payment card' => 'Full card number and CVV are not retained',
            ],
        ], $this->db->fetchAll('SELECT id, full_name, email, phone, address, role, created_at FROM users'));
    }

    private function orderRecords(): array
    {
        return array_map(static fn (array $row): array => [
            'id' => (int) $row['id'],
            'type' => 'order',
            'reference' => 'ORD-' . (int) $row['id'],
            'status' => (string) $row['status'],
            'summary' => ($row['full_name'] ?: 'Unknown customer') . ' · ' . ($row['package_name'] ?: 'Historical package') . ' · £' . number_format((float) $row['final_total'], 2),
            'created_at' => (string) $row['created_at'],
            'details' => [
                'Customer' => $row['full_name'] ?: 'Unknown customer',
                'Package' => $row['package_name'] ?: 'Historical package',
                'Quantity' => (int) $row['quantity'],
                'Channel' => $row['order_channel'],
                'Original total' => '£' . number_format((float) $row['total'], 2),
                'Discount' => '£' . number_format((float) $row['discount'], 2),
                'Final total' => '£' . number_format((float) $row['final_total'], 2),
            ],
        ], $this->db->fetchAll(
            'SELECT orders.*, users.full_name, packages.package_name
             FROM orders
             LEFT JOIN users ON users.id = orders.user_id
             LEFT JOIN packages ON packages.id = orders.package_id'
        ));
    }

    private function paymentRecords(): array
    {
        return array_map(static fn (array $row): array => [
            'id' => (int) $row['id'],
            'type' => 'payment',
            'reference' => $row['receipt_ref'] ?: ($row['verification_reference'] ?: 'PAY-' . (int) $row['id']),
            'status' => (string) $row['payment_status'],
            'summary' => ($row['payment_method'] ?: 'Card token not saved') . ' · £' . number_format((float) $row['amount'], 2),
            'created_at' => (string) $row['payment_date'],
            'details' => [
                'Order' => 'ORD-' . (int) $row['order_id'],
                'Amount' => '£' . number_format((float) $row['amount'], 2),
                'Payment method' => $row['payment_method'] ?: 'Not retained',
                'VISAcheck status' => $row['verification_status'] ?: 'Not available',
                'VISAcheck reference' => $row['verification_reference'] ?: 'Not available',
                'Sensitive data' => 'Full card number and CVV are never displayed or retained',
            ],
        ], $this->db->fetchAll('SELECT * FROM payments'));
    }

    private function enquiryRecords(): array
    {
        return array_map(static fn (array $row): array => [
            'id' => (int) $row['id'],
            'type' => 'enquiry',
            'reference' => 'ENQ-' . (int) $row['id'],
            'status' => (string) $row['status'],
            'summary' => $row['subject'] . ' · ' . ($row['email'] ?: 'Guest'),
            'created_at' => (string) $row['created_at'],
            'details' => [
                'Customer' => $row['full_name'] ?: 'Guest',
                'Email' => $row['email'] ?: 'No account email',
                'Subject' => $row['subject'],
                'Message' => $row['message'],
                'Response' => $row['reply'] ?: 'Not answered yet',
            ],
        ], $this->db->fetchAll(
            'SELECT enquiries.*, users.full_name, users.email
             FROM enquiries LEFT JOIN users ON users.id = enquiries.user_id'
        ));
    }

    private function auditRecords(): array
    {
        return array_map(static fn (array $row): array => [
            'id' => (int) $row['id'],
            'type' => 'catalogue',
            'reference' => strtoupper($row['entity_type']) . '-' . (int) $row['entity_id'],
            'status' => (string) $row['outcome'],
            'summary' => $row['event_type'] . ' · ' . ($row['actor_name'] ?: 'System') . ' · ' . AuditLog::humanDetails($row['details'] ?? null),
            'created_at' => (string) $row['created_at'],
            'details' => [
                'Event' => $row['event_type'],
                'Actor' => $row['actor_name'] ?: 'System',
                'Entity' => $row['entity_type'] . ' #' . (int) $row['entity_id'],
                'Outcome' => $row['outcome'],
                'Details' => AuditLog::humanDetails($row['details'] ?? null),
            ],
        ], $this->db->fetchAll(
            'SELECT audit_logs.*, users.full_name AS actor_name
             FROM audit_logs LEFT JOIN users ON users.id = audit_logs.actor_id'
        ));
    }
}
