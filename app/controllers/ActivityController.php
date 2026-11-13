<?php
declare(strict_types=1);

/**
 * Flowzen - Activity Controller
 * Displays the historical audit trail of actions taken by the user.
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/helpers/Session.php';
require_once dirname(__DIR__) . '/helpers/View.php';
require_once dirname(__DIR__) . '/middleware/AuthMiddleware.php';
require_once dirname(__DIR__) . '/models/ActivityLog.php';

class ActivityController {
    public function __construct() {
        AuthMiddleware::handle();
    }

    public function index(): void {
        $userId = Session::userId();
        $activities = ActivityLog::getRecent($userId, 40);

        View::render('activity/index', [
            'pageTitle'  => 'Activity Timeline - Flowzen',
            'activities' => $activities
        ]);
    }
}
