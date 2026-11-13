<?php
declare(strict_types=1);

/**
 * Flowzen - Notification Model
 * Manages in-app alerts for approaching deadlines, overdue tasks, and milestone completions.
 */

require_once dirname(__DIR__) . '/config/database.php';

class Notification {
    /**
     * Create an in-app notification.
     */
    public static function create(int $userId, ?int $taskId, string $title, string $message, string $type = 'info'): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            INSERT INTO notifications (user_id, task_id, title, message, type, is_read, created_at)
            VALUES (:user_id, :task_id, :title, :message, :type, 0, NOW())
        ');
        $stmt->execute([
            'user_id' => $userId,
            'task_id' => $taskId,
            'title'   => trim($title),
            'message' => trim($message),
            'type'    => in_array($type, ['info', 'warning', 'danger', 'success'], true) ? $type : 'info'
        ]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Get all notifications for user.
     */
    public static function getForUser(int $userId, bool $unreadOnly = false): array {
        $pdo = Database::getConnection();
        $sql = '
            SELECT n.*, t.title AS task_title
            FROM notifications n
            LEFT JOIN tasks t ON n.task_id = t.id
            WHERE n.user_id = :user_id
        ';
        if ($unreadOnly) {
            $sql .= ' AND n.is_read = 0';
        }
        $sql .= ' ORDER BY n.created_at DESC LIMIT 50';

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get count of unread notifications.
     */
    public static function getUnreadCount(int $userId): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0');
        $stmt->execute(['user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Mark a single notification as read (with user verification).
     */
    public static function markAsRead(int $id, int $userId): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id');
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    /**
     * Mark all notifications for user as read.
     */
    public static function markAllAsRead(int $userId): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0');
        return $stmt->execute(['user_id' => $userId]);
    }

    /**
     * Check upcoming and overdue tasks and generate notifications without duplicates.
     */
    public static function generateDeadlineAlerts(int $userId): void {
        $pdo = Database::getConnection();

        // 1. Check tasks due today
        $stmt = $pdo->prepare("
            SELECT id, title, due_date
            FROM tasks
            WHERE user_id = :user_id
              AND archived_at IS NULL
              AND status != 'completed'
              AND due_date = CURRENT_DATE
        ");
        $stmt->execute(['user_id' => $userId]);
        $dueToday = $stmt->fetchAll();

        foreach ($dueToday as $task) {
            // Check if already notified today
            $check = $pdo->prepare("
                SELECT 1 FROM notifications
                WHERE user_id = :user_id
                  AND task_id = :task_id
                  AND type = 'warning'
                  AND DATE(created_at) = CURRENT_DATE
                LIMIT 1
            ");
            $check->execute(['user_id' => $userId, 'task_id' => $task['id']]);
            if (!$check->fetchColumn()) {
                self::create(
                    $userId,
                    (int)$task['id'],
                    'Task Due Today',
                    "Task '{$task['title']}' is due today. Keep your momentum going!",
                    'warning'
                );
            }
        }

        // 2. Check overdue tasks
        $stmt = $pdo->prepare("
            SELECT id, title, due_date
            FROM tasks
            WHERE user_id = :user_id
              AND archived_at IS NULL
              AND status != 'completed'
              AND due_date < CURRENT_DATE
        ");
        $stmt->execute(['user_id' => $userId]);
        $overdue = $stmt->fetchAll();

        foreach ($overdue as $task) {
            // Prevent duplicate overdue alert today
            $check = $pdo->prepare("
                SELECT 1 FROM notifications
                WHERE user_id = :user_id
                  AND task_id = :task_id
                  AND type = 'danger'
                  AND DATE(created_at) = CURRENT_DATE
                LIMIT 1
            ");
            $check->execute(['user_id' => $userId, 'task_id' => $task['id']]);
            if (!$check->fetchColumn()) {
                self::create(
                    $userId,
                    (int)$task['id'],
                    'Task Overdue',
                    "Task '{$task['title']}' was due on {$task['due_date']}.",
                    'danger'
                );
            }
        }
    }
}
