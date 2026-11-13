<?php
declare(strict_types=1);

/**
 * Flowzen - Notification Controller
 * Manages user in-app notifications and read/unread acknowledgments.
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/helpers/Session.php';
require_once dirname(__DIR__) . '/helpers/Security.php';
require_once dirname(__DIR__) . '/helpers/View.php';
require_once dirname(__DIR__) . '/middleware/AuthMiddleware.php';
require_once dirname(__DIR__) . '/models/Notification.php';

class NotificationController {
    public function __construct() {
        AuthMiddleware::handle();
    }

    public function index(): void {
        $userId = Session::userId();
        Notification::generateDeadlineAlerts($userId);

        $notifications = Notification::getForUser($userId);
        $unreadCount   = Notification::getUnreadCount($userId);

        View::render('notifications/index', [
            'pageTitle'     => 'Notifications - Flowzen',
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount
        ]);
    }

    public function markAsRead(): void {
        $userId = Session::userId();
        $id = (int)($_POST['id'] ?? 0);

        Notification::markAsRead($id, $userId);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            View::json(['success' => true]);
        }

        View::redirect('notifications');
    }

    public function markAllAsRead(): void {
        $userId = Session::userId();
        Notification::markAllAsRead($userId);

        Session::setFlash('success', 'All notifications marked as read.');
        View::redirect('notifications');
    }
}
