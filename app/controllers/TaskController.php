<?php
declare(strict_types=1);

/**
 * Flowzen - Task Controller
 * Handles task creation, listing, searching, filtering, editing,
 * deletion, CSV export, archiving, and status updates.
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/helpers/Session.php';
require_once dirname(__DIR__) . '/helpers/Security.php';
require_once dirname(__DIR__) . '/helpers/Validator.php';
require_once dirname(__DIR__) . '/helpers/View.php';
require_once dirname(__DIR__) . '/middleware/AuthMiddleware.php';
require_once dirname(__DIR__) . '/models/Task.php';
require_once dirname(__DIR__) . '/models/ActivityLog.php';
require_once dirname(__DIR__) . '/models/Notification.php';

class TaskController {
    public function __construct() {
        AuthMiddleware::handle();
        // Generate notifications for deadlines whenever an authenticated user acts
        Notification::generateDeadlineAlerts(Session::userId());
    }

    /**
     * List active tasks with search, status & priority filters, sorting, and pagination.
     */
    public function index(): void {
        $userId = Session::userId();

        $search   = Security::sanitizeString($_GET['search'] ?? '');
        $status   = Security::sanitizeString($_GET['status'] ?? '');
        $priority = Security::sanitizeString($_GET['priority'] ?? '');
        $sort     = Security::sanitizeString($_GET['sort'] ?? 'created_desc');
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $perPage  = 8;
        $offset   = ($page - 1) * $perPage;

        $filters = [
            'search'   => $search,
            'status'   => $status,
            'priority' => $priority,
            'sort'     => $sort,
            'archived' => false
        ];

        $totalTasks = Task::countFiltered($userId, $filters);
        $tasks = Task::getFiltered($userId, $filters, $perPage, $offset);
        $totalPages = max(1, (int)ceil($totalTasks / $perPage));

        View::render('tasks/index', [
            'pageTitle'   => 'My Tasks - Flowzen',
            'tasks'       => $tasks,
            'totalTasks'  => $totalTasks,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'filters'     => $filters
        ]);
    }

    /**
     * Show task creation form.
     */
    public function create(): void {
        View::render('tasks/create', [
            'pageTitle' => 'New Task - Flowzen'
        ]);
    }

    /**
     * Store newly created task.
     */
    public function store(): void {
        $userId = Session::userId();

        $validator = new Validator($_POST);
        $validator->required('title', 'Task title is required.')
                  ->max('title', 255, 'Title cannot exceed 255 characters.')
                  ->in('status', ['pending', 'in_progress', 'completed'])
                  ->in('priority', ['low', 'medium', 'high'])
                  ->date('due_date');

        if ($validator->fails()) {
            $validator->flash();
            View::redirect('tasks/create');
        }

        $data = [
            'title'       => Security::sanitizeString($_POST['title'] ?? ''),
            'description' => Security::sanitizeString($_POST['description'] ?? ''),
            'status'      => $_POST['status'] ?? 'pending',
            'priority'    => $_POST['priority'] ?? 'medium',
            'due_date'    => !empty($_POST['due_date']) ? $_POST['due_date'] : null
        ];

        $taskId = Task::create($userId, $data);

        // Record audit trail
        ActivityLog::record($userId, $taskId, 'created', "Created new task: \"{$data['title']}\"");

        Session::setFlash('success', 'Task created successfully.');
        View::redirect('tasks');
    }

    /**
     * Show task edit form with strict ownership check.
     */
    public function edit(): void {
        $userId = Session::userId();
        $id = (int)($_GET['id'] ?? 0);

        $task = Task::findByIdAndUser($id, $userId);
        if (!$task) {
            Session::setFlash('error', 'Task not found or access denied.');
            View::redirect('tasks');
        }

        View::render('tasks/edit', [
            'pageTitle' => 'Edit Task - Flowzen',
            'task'      => $task
        ]);
    }

    /**
     * Update existing task with strict ownership validation.
     */
    public function update(): void {
        $userId = Session::userId();
        $id = (int)($_POST['id'] ?? 0);

        // Verify ownership first
        $existing = Task::findByIdAndUser($id, $userId);
        if (!$existing) {
            Session::setFlash('error', 'Task not found or access denied.');
            View::redirect('tasks');
        }

        $validator = new Validator($_POST);
        $validator->required('title', 'Task title cannot be empty.')
                  ->max('title', 255)
                  ->in('status', ['pending', 'in_progress', 'completed'])
                  ->in('priority', ['low', 'medium', 'high'])
                  ->date('due_date');

        if ($validator->fails()) {
            $validator->flash();
            View::redirect("tasks/edit?id={$id}");
        }

        $data = [
            'title'       => Security::sanitizeString($_POST['title'] ?? ''),
            'description' => Security::sanitizeString($_POST['description'] ?? ''),
            'status'      => $_POST['status'] ?? 'pending',
            'priority'    => $_POST['priority'] ?? 'medium',
            'due_date'    => !empty($_POST['due_date']) ? $_POST['due_date'] : null
        ];

        Task::update($id, $userId, $data);

        // Record audit trail
        ActivityLog::record($userId, $id, 'updated', "Updated details for task: \"{$data['title']}\"");

        // If status changed to completed, also log a status change
        if ($existing['status'] !== $data['status']) {
            ActivityLog::record($userId, $id, 'status_changed', "Changed status from {$existing['status']} to {$data['status']}");
        }

        Session::setFlash('success', 'Task updated successfully.');
        View::redirect('tasks');
    }

    /**
     * Quick status update endpoint (supports both standard POST and AJAX).
     */
    public function updateStatus(): void {
        $userId = Session::userId();
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if (!in_array($status, ['pending', 'in_progress', 'completed'], true)) {
            if ($this->isAjax()) {
                View::json(['success' => false, 'message' => 'Invalid status selected.'], 400);
            }
            Session::setFlash('error', 'Invalid status selected.');
            View::redirect('tasks');
        }

        $task = Task::findByIdAndUser($id, $userId);
        if (!$task) {
            if ($this->isAjax()) {
                View::json(['success' => false, 'message' => 'Task not found or unauthorized.'], 404);
            }
            Session::setFlash('error', 'Task not found or unauthorized.');
            View::redirect('tasks');
        }

        Task::updateStatus($id, $userId, $status);
        ActivityLog::record($userId, $id, 'status_changed', "Changed status of \"{$task['title']}\" to " . ucfirst(str_replace('_', ' ', $status)));

        if ($this->isAjax()) {
            View::json(['success' => true, 'message' => 'Status updated successfully.']);
        }

        Session::setFlash('success', 'Task status updated.');
        View::redirect('tasks');
    }

    /**
     * Delete a task with verification.
     */
    public function delete(): void {
        $userId = Session::userId();
        $id = (int)($_POST['id'] ?? 0);

        $task = Task::findByIdAndUser($id, $userId);
        if (!$task) {
            Session::setFlash('error', 'Task not found or unauthorized.');
            View::redirect('tasks');
        }

        Task::delete($id, $userId);

        // Record audit trail (task_id will be null in activity_logs due to ON DELETE SET NULL)
        ActivityLog::record($userId, null, 'deleted', "Deleted task: \"{$task['title']}\"");

        Session::setFlash('success', 'Task deleted successfully.');
        View::redirect('tasks');
    }

    /**
     * Archive task.
     */
    public function archive(): void {
        $userId = Session::userId();
        $id = (int)($_POST['id'] ?? 0);

        $task = Task::findByIdAndUser($id, $userId);
        if (!$task) {
            Session::setFlash('error', 'Task not found or unauthorized.');
            View::redirect('tasks');
        }

        Task::archive($id, $userId);
        ActivityLog::record($userId, $id, 'archived', "Archived task: \"{$task['title']}\"");

        Session::setFlash('success', 'Task moved to archive.');
        View::redirect('tasks');
    }

    /**
     * Restore archived task.
     */
    public function restore(): void {
        $userId = Session::userId();
        $id = (int)($_POST['id'] ?? 0);

        $task = Task::findByIdAndUser($id, $userId);
        if (!$task) {
            Session::setFlash('error', 'Task not found or unauthorized.');
            View::redirect('tasks/archived');
        }

        Task::restore($id, $userId);
        ActivityLog::record($userId, $id, 'restored', "Restored task from archive: \"{$task['title']}\"");

        Session::setFlash('success', 'Task restored successfully.');
        View::redirect('tasks/archived');
    }

    /**
     * View list of archived tasks.
     */
    public function archived(): void {
        $userId = Session::userId();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $filters = ['archived' => true];
        $totalArchived = Task::countFiltered($userId, $filters);
        $tasks = Task::getFiltered($userId, $filters, $perPage, $offset);
        $totalPages = max(1, (int)ceil($totalArchived / $perPage));

        View::render('tasks/archived', [
            'pageTitle'     => 'Archived Tasks - Flowzen',
            'tasks'         => $tasks,
            'totalArchived' => $totalArchived,
            'currentPage'   => $page,
            'totalPages'    => $totalPages
        ]);
    }

    /**
     * Export all user tasks to CSV.
     */
    public function exportCsv(): void {
        $userId = Session::userId();
        $tasks = Task::getForExport($userId);

        $filename = 'flowzen_tasks_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // CSV Header
        fputcsv($output, ['ID', 'Title', 'Description', 'Status', 'Priority', 'Due Date', 'Created At', 'Last Updated']);

        foreach ($tasks as $row) {
            fputcsv($output, [
                $row['id'],
                $row['title'],
                $row['description'] ?? '',
                ucwords(str_replace('_', ' ', $row['status'])),
                ucwords($row['priority']),
                $row['due_date'] ?? 'None',
                $row['created_at'],
                $row['updated_at']
            ]);
        }

        fclose($output);
        exit;
    }

    private function isAjax(): bool {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
            (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json'));
    }
}
