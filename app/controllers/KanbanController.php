<?php
declare(strict_types=1);

/**
 * Flowzen - Kanban Controller
 * Serves the visual Kanban workflow board and handles asynchronous status changes.
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/helpers/Session.php';
require_once dirname(__DIR__) . '/helpers/Security.php';
require_once dirname(__DIR__) . '/helpers/View.php';
require_once dirname(__DIR__) . '/middleware/AuthMiddleware.php';
require_once dirname(__DIR__) . '/models/Task.php';
require_once dirname(__DIR__) . '/models/ActivityLog.php';

class KanbanController {
    public function __construct() {
        AuthMiddleware::handle();
    }

    /**
     * Render 3-column Kanban board.
     */
    public function index(): void {
        $userId = Session::userId();
        $kanban = Task::getKanbanTasks($userId);

        View::render('kanban/index', [
            'pageTitle' => 'Kanban Workflow Board - Flowzen',
            'kanban'    => $kanban
        ]);
    }

    /**
     * AJAX endpoint for Drag-and-Drop column updates.
     */
    public function updateStatus(): void {
        $userId = Session::userId();

        // Support both JSON body and standard POST
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $taskId = (int)($input['task_id'] ?? 0);
        $status = (string)($input['status'] ?? '');
        $token  = $input['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

        if (!Security::validateCsrfToken($token)) {
            View::json(['success' => false, 'message' => 'CSRF verification failed.'], 403);
        }

        if (!in_array($status, ['pending', 'in_progress', 'completed'], true)) {
            View::json(['success' => false, 'message' => 'Invalid status selected.'], 400);
        }

        $task = Task::findByIdAndUser($taskId, $userId);
        if (!$task) {
            View::json(['success' => false, 'message' => 'Task not found or access denied.'], 404);
        }

        Task::updateStatus($taskId, $userId, $status);
        ActivityLog::record(
            $userId,
            $taskId,
            'status_changed',
            "Kanban: Moved \"{$task['title']}\" to " . ucwords(str_replace('_', ' ', $status))
        );

        View::json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'status'  => $status
        ]);
    }
}
