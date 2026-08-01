<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use PDO;

/**
 * Activity / Audit logger
 */
final class ActivityLogger
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    public function log(
        string $action,
        ?int $userId = null,
        ?string $targetType = null,
        ?string $targetId = null,
        ?array $details = null
    ): void {
        $stmt = $this->db->prepare(
            'INSERT INTO activity_logs (user_id, action, target_type, target_id, details, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $userId,
            $action,
            $targetType,
            $targetId,
            $details !== null ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : null,
        ]);
    }

    public function recent(int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, u.username
             FROM activity_logs a
             LEFT JOIN users u ON a.user_id = u.id
             ORDER BY a.created_at DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }
}
