<?php
declare(strict_types=1);

/**
 * Flowzen - Task Model
 * Handles CRUD operations, query filters, ownership verification,
 * and statistical aggregations for the authenticated user.
 */

require_once dirname(__DIR__) . '/config/database.php';

class Task {
    /**
     * Create a new task under the authenticated user.
     */
    public static function create(int $userId, array $data): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            INSERT INTO tasks (user_id, title, description, status, priority, due_date, created_at)
            VALUES (:user_id, :title, :description, :status, :priority, :due_date, NOW())
        ');

        $stmt->execute([
            'user_id'     => $userId,
            'title'       => trim($data['title']),
            'description' => !empty($data['description']) ? trim($data['description']) : null,
            'status'      => $data['status'] ?? 'pending',
            'priority'    => $data['priority'] ?? 'medium',
            'due_date'    => !empty($data['due_date']) ? $data['due_date'] : null
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Find a task by ID ensuring strict user ownership.
     */
    public static function findByIdAndUser(int $id, int $userId): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT * FROM tasks
            WHERE id = :id AND user_id = :user_id
            LIMIT 1
        ');
        $stmt->execute([
            'id'      => $id,
            'user_id' => $userId
        ]);
        $task = $stmt->fetch();
        return $task ?: null;
    }

    /**
     * Update an existing task ensuring strict user ownership.
     */
    public static function update(int $id, int $userId, array $data): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            UPDATE tasks
            SET title = :title,
                description = :description,
                status = :status,
                priority = :priority,
                due_date = :due_date,
                updated_at = NOW()
            WHERE id = :id AND user_id = :user_id
        ');

        $executed = $stmt->execute([
            'id'          => $id,
            'user_id'     => $userId,
            'title'       => trim($data['title']),
            'description' => !empty($data['description']) ? trim($data['description']) : null,
            'status'      => $data['status'] ?? 'pending',
            'priority'    => $data['priority'] ?? 'medium',
            'due_date'    => !empty($data['due_date']) ? $data['due_date'] : null
        ]);

        return $executed && $stmt->rowCount() > 0;
    }

    /**
     * Update task status (used by Kanban and quick-toggle controls).
     */
    public static function updateStatus(int $id, int $userId, string $status): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            UPDATE tasks
            SET status = :status, updated_at = NOW()
            WHERE id = :id AND user_id = :user_id
        ');

        $executed = $stmt->execute([
            'id'      => $id,
            'user_id' => $userId,
            'status'  => $status
        ]);

        return $executed && $stmt->rowCount() > 0;
    }

    /**
     * Delete a task ensuring strict user ownership.
     */
    public static function delete(int $id, int $userId): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            DELETE FROM tasks
            WHERE id = :id AND user_id = :user_id
        ');

        $executed = $stmt->execute([
            'id'      => $id,
            'user_id' => $userId
        ]);

        return $executed && $stmt->rowCount() > 0;
    }

    /**
     * Archive a task.
     */
    public static function archive(int $id, int $userId): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            UPDATE tasks
            SET archived_at = NOW()
            WHERE id = :id AND user_id = :user_id
        ');
        return $stmt->execute(['id' => $id, 'user_id' => $userId]) && $stmt->rowCount() > 0;
    }

    /**
     * Restore an archived task.
     */
    public static function restore(int $id, int $userId): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            UPDATE tasks
            SET archived_at = NULL
            WHERE id = :id AND user_id = :user_id
        ');
        return $stmt->execute(['id' => $id, 'user_id' => $userId]) && $stmt->rowCount() > 0;
    }

    /**
     * Query tasks with search, status & priority filters, sorting, and pagination.
     */
    public static function getFiltered(int $userId, array $filters, int $limit = 10, int $offset = 0): array {
        $pdo = Database::getConnection();
        $sql = 'SELECT * FROM tasks WHERE user_id = :user_id';
        $params = ['user_id' => $userId];

        // Archive status filter
        if (!empty($filters['archived'])) {
            $sql .= ' AND archived_at IS NOT NULL';
        } else {
            $sql .= ' AND archived_at IS NULL';
        }

        // Title search
        if (!empty($filters['search'])) {
            $sql .= ' AND (title LIKE :search OR description LIKE :search_desc)';
            $params['search'] = '%' . trim($filters['search']) . '%';
            $params['search_desc'] = '%' . trim($filters['search']) . '%';
        }

        // Status filter
        if (!empty($filters['status']) && in_array($filters['status'], ['pending', 'in_progress', 'completed'], true)) {
            $sql .= ' AND status = :status';
            $params['status'] = $filters['status'];
        }

        // Priority filter
        if (!empty($filters['priority']) && in_array($filters['priority'], ['low', 'medium', 'high'], true)) {
            $sql .= ' AND priority = :priority';
            $params['priority'] = $filters['priority'];
        }

        // Sorting
        $allowedSorts = [
            'created_desc' => 'created_at DESC',
            'created_asc'  => 'created_at ASC',
            'due_asc'      => 'due_date IS NULL, due_date ASC',
            'due_desc'     => 'due_date IS NULL, due_date DESC',
            'title_asc'    => 'title ASC',
            'priority_desc'=> "FIELD(priority, 'high', 'medium', 'low')"
        ];

        $sort = $filters['sort'] ?? 'created_desc';
        $orderBy = $allowedSorts[$sort] ?? 'created_at DESC';
        $sql .= ' ORDER BY ' . $orderBy;

        // Pagination
        $sql .= ' LIMIT :limit OFFSET :offset';

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Count total filtered tasks for pagination.
     */
    public static function countFiltered(int $userId, array $filters): int {
        $pdo = Database::getConnection();
        $sql = 'SELECT COUNT(*) FROM tasks WHERE user_id = :user_id';
        $params = ['user_id' => $userId];

        if (!empty($filters['archived'])) {
            $sql .= ' AND archived_at IS NOT NULL';
        } else {
            $sql .= ' AND archived_at IS NULL';
        }

        if (!empty($filters['search'])) {
            $sql .= ' AND (title LIKE :search OR description LIKE :search_desc)';
            $params['search'] = '%' . trim($filters['search']) . '%';
            $params['search_desc'] = '%' . trim($filters['search']) . '%';
        }

        if (!empty($filters['status']) && in_array($filters['status'], ['pending', 'in_progress', 'completed'], true)) {
            $sql .= ' AND status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['priority']) && in_array($filters['priority'], ['low', 'medium', 'high'], true)) {
            $sql .= ' AND priority = :priority';
            $params['priority'] = $filters['priority'];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Compute comprehensive, live productivity statistics for dashboard KPIs.
     */
    public static function getStats(int $userId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
                SUM(CASE WHEN status != 'completed' AND due_date IS NOT NULL AND due_date < CURRENT_DATE THEN 1 ELSE 0 END) AS overdue,
                SUM(CASE WHEN priority = 'high' THEN 1 ELSE 0 END) AS high_priority,
                SUM(CASE WHEN priority = 'medium' THEN 1 ELSE 0 END) AS medium_priority,
                SUM(CASE WHEN priority = 'low' THEN 1 ELSE 0 END) AS low_priority
            FROM tasks
            WHERE user_id = :user_id AND archived_at IS NULL
        ");
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();

        $total       = (int)($row['total'] ?? 0);
        $pending     = (int)($row['pending'] ?? 0);
        $inProgress  = (int)($row['in_progress'] ?? 0);
        $completed   = (int)($row['completed'] ?? 0);
        $overdue     = (int)($row['overdue'] ?? 0);
        $highPri     = (int)($row['high_priority'] ?? 0);
        $medPri      = (int)($row['medium_priority'] ?? 0);
        $lowPri      = (int)($row['low_priority'] ?? 0);

        $completionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0.0;

        return [
            'total'           => $total,
            'pending'         => $pending,
            'in_progress'     => $inProgress,
            'completed'       => $completed,
            'overdue'         => $overdue,
            'high_priority'   => $highPri,
            'medium_priority' => $medPri,
            'low_priority'    => $lowPri,
            'completion_rate' => $completionRate
        ];
    }

    /**
     * Weekly productivity trend: tasks completed per day over the last 7 days.
     */
    public static function getWeeklyTrend(int $userId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT DATE(updated_at) AS day_date, COUNT(*) AS count
            FROM tasks
            WHERE user_id = :user_id
              AND status = 'completed'
              AND updated_at >= DATE_SUB(CURRENT_DATE, INTERVAL 6 DAY)
            GROUP BY DATE(updated_at)
        ");
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $labels = [];
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('D (M j)', strtotime($d));
            $data[] = (int)($rows[$d] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Upcoming deadlines (active tasks sorted by earliest due date).
     */
    public static function getUpcomingDeadlines(int $userId, int $limit = 5): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM tasks
            WHERE user_id = :user_id
              AND archived_at IS NULL
              AND status != 'completed'
              AND due_date IS NOT NULL
            ORDER BY due_date ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get tasks grouped for the Kanban board.
     */
    public static function getKanbanTasks(int $userId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM tasks
            WHERE user_id = :user_id AND archived_at IS NULL
            ORDER BY FIELD(priority, 'high', 'medium', 'low'), due_date ASC, created_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        $all = $stmt->fetchAll();

        $kanban = [
            'pending'     => [],
            'in_progress' => [],
            'completed'   => []
        ];

        foreach ($all as $t) {
            $status = $t['status'];
            if (isset($kanban[$status])) {
                $kanban[$status][] = $t;
            }
        }

        return $kanban;
    }

    /**
     * Get tasks scheduled for a given month for the Calendar view.
     */
    public static function getCalendarTasks(int $userId, string $yearMonth): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT id, title, status, priority, due_date
            FROM tasks
            WHERE user_id = :user_id
              AND archived_at IS NULL
              AND due_date IS NOT NULL
              AND due_date LIKE :month_pattern
            ORDER BY due_date ASC
        ");
        $stmt->execute([
            'user_id'       => $userId,
            'month_pattern' => $yearMonth . '-%'
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Fetch all user's active tasks formatted for CSV export.
     */
    public static function getForExport(int $userId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT id, title, description, status, priority, due_date, created_at, updated_at
            FROM tasks
            WHERE user_id = :user_id AND archived_at IS NULL
            ORDER BY created_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
