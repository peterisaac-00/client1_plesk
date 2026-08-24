<?php

namespace App\Models;

use App\Core\Database;

class VerificationLog extends Model
{
    protected static string $table = 'verification_logs';

    public static function log(int $documentId, string $docStatus, ?string $ip, ?string $userAgent): void
    {
        Database::insert(
            'INSERT INTO verification_logs (document_id, doc_status, ip_address, user_agent) VALUES (?, ?, ?, ?)',
            [$documentId, $docStatus, $ip, $userAgent]
        );
    }

    public static function countByDocument(int $documentId): int
    {
        return self::count('document_id = ?', [$documentId]);
    }

    public static function total(): int
    {
        return self::count();
    }

    public static function recent(int $limit = 10): array
    {
        return Database::fetchAll(
            'SELECT v.*, d.doc_number, d.original_name, c.name AS client_name
             FROM verification_logs v
             JOIN documents d ON d.id = v.document_id
             LEFT JOIN clients c ON c.id = d.client_id
             ORDER BY v.created_at DESC
             LIMIT ' . (int)$limit
        );
    }

    /** عدد عمليات التحقق اليوم */
    public static function countToday(): int
    {
        return self::count('DATE(created_at) = CURDATE()');
    }

    /** عمليات التحقق لكل يوم من آخر أيام محددة (لرسم بياني حقيقي) */
    public static function countPerDay(int $days = 7): array
    {
        $days = max(1, min(31, $days));
        $rows = Database::fetchAll(
            "SELECT DATE(created_at) AS day, COUNT(*) AS c
             FROM verification_logs
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL " . ($days - 1) . " DAY)
             GROUP BY DATE(created_at)
             ORDER BY day ASC"
        );
        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['day']] = (int)$row['c'];
        }
        $labels = [];
        $values = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = (new \DateTime($date))->format('d/m');
            $values[] = $counts[$date] ?? 0;
        }
        return ['labels' => $labels, 'values' => $values];
    }

    /** بحث وفلاتر مع ترقيم */
    public static function filter(array $filters, int $perPage, int $page): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['q'])) {
            $like = '%' . $filters['q'] . '%';
            $where[] = '(d.original_name LIKE ? OR d.doc_number LIKE ? OR c.name LIKE ?)';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if (!empty($filters['client_id'])) {
            $where[] = 'd.client_id = ?';
            $params[] = (int)$filters['client_id'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'DATE(v.created_at) >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'DATE(v.created_at) <= ?';
            $params[] = $filters['date_to'];
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $countRow = Database::fetch(
            'SELECT COUNT(*) AS c
             FROM verification_logs v
             JOIN documents d ON d.id = v.document_id
             LEFT JOIN clients c ON c.id = d.client_id
             ' . $whereSql,
            $params
        );
        $total = (int)($countRow['c'] ?? 0);

        $offset = ($page - 1) * $perPage;
        $items = Database::fetchAll(
            'SELECT v.*, d.doc_number, d.original_name, c.name AS client_name
             FROM verification_logs v
             JOIN documents d ON d.id = v.document_id
             LEFT JOIN clients c ON c.id = d.client_id
             ' . $whereSql . '
             ORDER BY v.created_at DESC
             LIMIT ' . (int)$perPage . ' OFFSET ' . (int)$offset,
            $params
        );

        return ['items' => $items, 'total' => $total];
    }
}
