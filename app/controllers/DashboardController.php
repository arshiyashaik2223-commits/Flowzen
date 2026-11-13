<?php
declare(strict_types=1);

/**
 * Flowzen - Dashboard Controller
 * Aggregates dynamic user productivity metrics, Chart.js datasets,
 * upcoming deadlines, and recent activities.
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/helpers/Session.php';
require_once dirname(__DIR__) . '/helpers/Security.php';
require_once dirname(__DIR__) . '/helpers/View.php';
require_once dirname(__DIR__) . '/middleware/AuthMiddleware.php';
require_once dirname(__DIR__) . '/models/Task.php';
require_once dirname(__DIR__) . '/models/ActivityLog.php';
require_once dirname(__DIR__) . '/models/Notification.php';

class DashboardController {
    public function __construct() {
        AuthMiddleware::handle();
    }

    public function index(): void {
        $userId = Session::userId();

        // Trigger deadline notification check
        Notification::generateDeadlineAlerts($userId);

        $stats       = Task::getStats($userId);
        $weeklyTrend = Task::getWeeklyTrend($userId);
        $deadlines   = Task::getUpcomingDeadlines($userId, 5);
        $recentLogs  = ActivityLog::getRecent($userId, 5);
        $recentTasks = Task::getFiltered($userId, ['archived' => false], 5, 0);

        View::render('dashboard/index', [
            'pageTitle'   => 'Productivity Dashboard - Flowzen',
            'stats'       => $stats,
            'weeklyTrend' => $weeklyTrend,
            'deadlines'   => $deadlines,
            'recentLogs'  => $recentLogs,
            'recentTasks' => $recentTasks,
            'user'        => Session::user()
        ]);
    }
}
