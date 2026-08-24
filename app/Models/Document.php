<?php

namespace App\Models;

use App\Core\Database;

class Document extends Model
{
    protected static string $table = 'documents';

    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_DISABLED = 'disabled';

    public static function findByToken(string $token): ?array
    {
        return Database::fetch('SELECT * FROM documents WHERE token = ? LIMIT 1', [$token]);
    }

    public static function create(array $data): int
    {
        return Database::insert(
            'INSERT INTO documents
                (doc_number, client_id, original_name, stored_name, mime_type, file_size, status, token, verify_url)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['doc_number'],
                $data['client_id'],
                $data['original_name'],
                $data['stored_name'],
                $data['mime_type'],
                $data['file_size'],
                self::STATUS_INACTIVE,
                $data['token'],
                $data['verify_url'],
            ]
        );
    }

    /** إنشاء رقم مستند داخلي فريد */
    public static function generateDocNumber(int $id): string
    {
        return 'DOC-' . date('Y') . '-' . str_pad((string)$id, 5, '0', STR_PAD_LEFT);
    }

    /** إنشاء رمز تحقق عشوائي قوي */
    public static function generateToken(): string
    {
        do {
            $token = bin2hex(random_bytes(16));
        } while (self::findByToken($token) !== null);
        return $token;
    }

    public static function updateStatus(int $id, string $status): void
    {
        Database::update(
            'UPDATE documents SET status = ?, updated_at = NOW() WHERE id = ?',
            [$status, $id]
        );
    }

    public static function setQrPath(int $id, string $qrPath): void
    {
        Database::update(
            'UPDATE documents SET qr_path = ?, qr_generated_at = NOW(), updated_at = NOW() WHERE id = ?',
            [$qrPath, $id]
        );
    }

    public static function findByClient(int $clientId): array
    {
        return Database::fetchAll(
            'SELECT * FROM documents WHERE client_id = ? ORDER BY created_at DESC',
            [$clientId]
        );
    }

    // ---------- الإحصائيات ----------

    public static function stats(): array
    {
        return [
            'total' => self::count(),
            'active' => self::count('status = ?', [self::STATUS_ACTIVE]),
            'inactive' => self::count('status = ?', [self::STATUS_INACTIVE]),
            'disabled' => self::count('status = ?', [self::STATUS_DISABLED]),
        ];
    }

    public static function recent(int $limit = 5): array
    {
        return Database::fetchAll(
            'SELECT d.*, c.name AS client_name,
                    (SELECT COUNT(*) FROM verification_logs v WHERE v.document_id = d.id) AS verification_count
             FROM documents d
             LEFT JOIN clients c ON c.id = d.client_id
             ORDER BY d.created_at DESC
             LIMIT ' . (int)$limit
        );
    }

    // ---------- البحث والفلاتر ----------

    public static function filter(array $filters, int $perPage, int $page): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['q'])) {
            $like = '%' . $filters['q'] . '%';
            $where[] = '(d.original_name LIKE ? OR d.doc_number LIKE ?)';
            $params[] = $like;
            $params[] = $like;
        }
        if (!empty($filters['client_id'])) {
            $where[] = 'd.client_id = ?';
            $params[] = (int)$filters['client_id'];
        }
        if (!empty($filters['status']) && in_array($filters['status'], [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DISABLED], true)) {
            $where[] = 'd.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            $where[] = 'LOWER(SUBSTRING_INDEX(d.original_name, \'.\', -1)) = ?';
            $params[] = strtolower((string)$filters['type']);
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'DATE(d.created_at) >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'DATE(d.created_at) <= ?';
            $params[] = $filters['date_to'];
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $countRow = Database::fetch(
            'SELECT COUNT(*) AS c FROM documents d ' . $whereSql,
            $params
        );
        $total = (int)($countRow['c'] ?? 0);

        $offset = ($page - 1) * $perPage;
        $items = Database::fetchAll(
            'SELECT d.*, c.name AS client_name,
                    (SELECT COUNT(*) FROM verification_logs v WHERE v.document_id = d.id) AS verification_count
             FROM documents d
             LEFT JOIN clients c ON c.id = d.client_id
             ' . $whereSql . '
             ORDER BY d.created_at DESC
             LIMIT ' . (int)$perPage . ' OFFSET ' . (int)$offset,
            $params
        );

        return ['items' => $items, 'total' => $total];
    }
}
