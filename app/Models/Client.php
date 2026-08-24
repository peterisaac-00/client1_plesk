<?php

namespace App\Models;

use App\Core\Database;

class Client extends Model
{
    protected static string $table = 'clients';

    public static function create(array $data): int
    {
        return Database::insert(
            'INSERT INTO clients (name, phone, email, notes) VALUES (?, ?, ?, ?)',
            [trim($data['name']), $data['phone'] ?? '', $data['email'] ?? '', $data['notes'] ?? '']
        );
    }

    public static function updateById(int $id, array $data): void
    {
        Database::update(
            'UPDATE clients SET name = ?, phone = ?, email = ?, notes = ?, updated_at = NOW() WHERE id = ?',
            [trim($data['name']), $data['phone'] ?? '', $data['email'] ?? '', $data['notes'] ?? '', $id]
        );
    }

    /** عدد المستندات المرتبطة بالعميل */
    public static function documentsCount(int $id): int
    {
        return Document::count('client_id = ?', [$id]);
    }

    public static function allWithCounts(): array
    {
        return Database::fetchAll(
            'SELECT c.*,
                    (SELECT COUNT(*) FROM documents d WHERE d.client_id = c.id) AS documents_count
             FROM clients c
             ORDER BY c.created_at DESC'
        );
    }

    /** بحث في العملاء */
    public static function search(string $q): array
    {
        $like = '%' . $q . '%';
        return Database::fetchAll(
            'SELECT * FROM clients WHERE name LIKE ? OR phone LIKE ? OR email LIKE ?
             ORDER BY created_at DESC',
            [$like, $like, $like]
        );
    }
}
