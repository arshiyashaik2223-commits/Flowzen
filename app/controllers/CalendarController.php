<?php
declare(strict_types=1);

/**
 * Flowzen - Calendar Controller
 * Generates monthly timeline view with tasks plotted according to due dates.
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/helpers/Session.php';
require_once dirname(__DIR__) . '/helpers/Security.php';
require_once dirname(__DIR__) . '/helpers/View.php';
require_once dirname(__DIR__) . '/middleware/AuthMiddleware.php';
require_once dirname(__DIR__) . '/models/Task.php';

class CalendarController {
    public function __construct() {
        AuthMiddleware::handle();
    }

    public function index(): void {
        $userId = Session::userId();

        // Get requested month or default to current
        $monthParam = Security::sanitizeString($_GET['month'] ?? date('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
            $monthParam = date('Y-m');
        }

        $timestamp = strtotime($monthParam . '-01');
        $year  = (int)date('Y', $timestamp);
        $month = (int)date('m', $timestamp);

        // Previous and Next month links
        $prevMonth = date('Y-m', strtotime('-1 month', $timestamp));
        $nextMonth = date('Y-m', strtotime('+1 month', $timestamp));
        $monthName = date('F Y', $timestamp);

        // Days in month
        $daysInMonth = (int)date('t', $timestamp);
        $firstDayOfWeek = (int)date('w', $timestamp); // 0 = Sunday, 6 = Saturday

        // Tasks in this month
        $tasks = Task::getCalendarTasks($userId, $monthParam);

        // Group tasks by date
        $tasksByDate = [];
        foreach ($tasks as $task) {
            $date = $task['due_date'];
            $tasksByDate[$date][] = $task;
        }

        View::render('calendar/index', [
            'pageTitle'      => 'Calendar Schedule - Flowzen',
            'currentMonth'   => $monthParam,
            'monthName'      => $monthName,
            'prevMonth'      => $prevMonth,
            'nextMonth'      => $nextMonth,
            'daysInMonth'    => $daysInMonth,
            'firstDayOfWeek' => $firstDayOfWeek,
            'tasksByDate'    => $tasksByDate
        ]);
    }
}
