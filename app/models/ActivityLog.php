<?php
declare(strict_types=1);

/**
 * Flowzen - Activity Log Model
 * Tracks audit trail actions: create, update, status change, delete, archive, restore.
 */

require_once dirname(__DIR__) . '/config/database.php';

class ActivityLog {
    /**
     * Record a new user action.
     */
    public static function record(int $userId, ?int $taskId, string $action, string $description): bool {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('
                INSERT INTO activity_logs (user_id, task_id, action, description, created_at)
                VALUES (:user_id, :task_id, :action, :description, NOW())
            ');
            return $stmt->execute([
                'user_id'     => $userId,
                'task_id'     => $taskId,
                'action'      => $action,
                'description' => trim($description)
            ]);
        } catch (PDOException $e) {
            error_log('Activity log record error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve user's recent activity logs.
     */
    public static function getRecent(int $userId, int $limit = 30): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT a.*, t.title AS task_title
            FROM activity_logs a
            LEFT JOIN tasks t ON a.task_id = t.id
            WHERE a.user_id = :user_id
            ORDER BY a.created_at DESC
            LIMIT :limit
        ');
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
