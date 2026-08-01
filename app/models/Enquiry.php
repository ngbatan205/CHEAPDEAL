<?php

class Enquiry
{
    public function __construct(private Database $db)
    {
    }

    public function all(): array
    {
        return $this->db->fetchAll(
            'SELECT enquiries.*, users.full_name, users.email, packages.package_name
             FROM enquiries
             LEFT JOIN users ON users.id = enquiries.user_id
             LEFT JOIN packages ON packages.id = enquiries.package_id
             ORDER BY enquiries.id DESC'
        );
    }

    public function create(array $attributes): array
    {
        $packageId = !empty($attributes['package_id'])
            ? (int) $attributes['package_id']
            : null;
        $this->db->execute(
            'INSERT INTO enquiries (user_id, package_id, subject, message, status) VALUES (?, ?, ?, ?, ?)',
            [$attributes['user_id'], $packageId, trim($attributes['subject']), trim($attributes['message']), 'Pending']
        );
        return $this->db->fetch('SELECT * FROM enquiries WHERE id = ?', [$this->db->lastInsertId()]);
    }

    public function forUser(int $userId): array
    {
        return $this->db->fetchAll(
            'SELECT enquiries.*, packages.package_name, packages.category
             FROM enquiries
             LEFT JOIN packages ON packages.id = enquiries.package_id
             WHERE enquiries.user_id = ?
             ORDER BY enquiries.id DESC',
            [$userId]
        );
    }

    public function reply(int $id, string $reply): void
    {
        $this->db->execute(
            'UPDATE enquiries
             SET reply = ?, status = ?
             WHERE id = ?',
            [$reply, 'Answered', $id]
        );
    }
}
