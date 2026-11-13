<?php
declare(strict_types=1);

/**
 * Flowzen - Settings & Profile Controller
 * Allows authenticated users to view profile details, update credentials,
 * and toggle application themes.
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/helpers/Session.php';
require_once dirname(__DIR__) . '/helpers/Security.php';
require_once dirname(__DIR__) . '/helpers/Validator.php';
require_once dirname(__DIR__) . '/helpers/View.php';
require_once dirname(__DIR__) . '/middleware/AuthMiddleware.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/ActivityLog.php';

class SettingsController {
    public function __construct() {
        AuthMiddleware::handle();
    }

    public function index(): void {
        $userId = Session::userId();
        $user = User::findById($userId);

        View::render('settings/index', [
            'pageTitle' => 'Settings & Profile - Flowzen',
            'user'      => $user
        ]);
    }

    public function updateProfile(): void {
        $userId = Session::userId();

        $validator = new Validator($_POST);
        $validator->required('name', 'Full name is required.')->max('name', 100);

        $newPassword = (string)($_POST['new_password'] ?? '');
        if ($newPassword !== '') {
            $validator->min('new_password', 8, 'New password must be at least 8 characters long.')
                      ->required('confirm_password', 'Please confirm your new password.')
                      ->matches('new_password', 'confirm_password', 'Password confirmations do not match.');
        }

        if ($validator->fails()) {
            $validator->flash();
            View::redirect('settings');
        }

        $name = Security::sanitizeString($_POST['name'] ?? '');
        User::updateProfile($userId, $name, $newPassword !== '' ? $newPassword : null);

        // Update active session user info
        $_SESSION['user']['name'] = $name;

        ActivityLog::record($userId, null, 'profile_updated', 'Updated user profile information.');

        Session::setFlash('success', 'Profile updated successfully.');
        View::redirect('settings');
    }

    public function toggleTheme(): void {
        $currentTheme = $_COOKIE['flowzen_theme'] ?? 'light';
        $newTheme = ($currentTheme === 'dark') ? 'light' : 'dark';

        setcookie('flowzen_theme', $newTheme, [
            'expires'  => time() + (86400 * 30),
            'path'     => '/',
            'httponly' => false,
            'samesite' => 'Lax'
        ]);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            View::json(['success' => true, 'theme' => $newTheme]);
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? APP_URL . '/dashboard';
        header("Location: {$referer}");
        exit;
    }
}
