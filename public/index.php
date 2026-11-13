<?php
declare(strict_types=1);

/**
 * Flowzen - Front Controller & Router
 * Dispatches all HTTP requests to appropriate controllers.
 */

require_once dirname(__DIR__) . '/app/config/config.php';
require_once dirname(__DIR__) . '/app/config/database.php';
require_once dirname(__DIR__) . '/app/helpers/Session.php';
require_once dirname(__DIR__) . '/app/helpers/Security.php';
require_once dirname(__DIR__) . '/app/helpers/Validator.php';
require_once dirname(__DIR__) . '/app/helpers/View.php';
require_once dirname(__DIR__) . '/app/middleware/AuthMiddleware.php';
require_once dirname(__DIR__) . '/app/middleware/CsrfMiddleware.php';

// Controllers
require_once dirname(__DIR__) . '/app/controllers/AuthController.php';
require_once dirname(__DIR__) . '/app/controllers/TaskController.php';
require_once dirname(__DIR__) . '/app/controllers/DashboardController.php';
require_once dirname(__DIR__) . '/app/controllers/KanbanController.php';
require_once dirname(__DIR__) . '/app/controllers/CalendarController.php';
require_once dirname(__DIR__) . '/app/controllers/ActivityController.php';
require_once dirname(__DIR__) . '/app/controllers/NotificationController.php';
require_once dirname(__DIR__) . '/app/controllers/SettingsController.php';

Session::start();

// Determine URI / Route
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

// Parse query string away
$path = parse_url($requestUri, PHP_URL_PATH);

// If URL parameter is set (via .htaccess rewrite or explicit query)
if (isset($_GET['url'])) {
    $route = trim($_GET['url'], '/');
} else {
    // Strip public/ and script name prefix
    $baseDir = dirname($scriptName);
    if ($baseDir !== '/' && str_starts_with($path, $baseDir)) {
        $path = substr($path, strlen($baseDir));
    }
    // Also handle /flowzen/public/ or /flowzen/
    $path = preg_replace('#^/+(flowzen(/public)?)?#i', '', $path);
    $route = trim($path, '/');
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

// Run CSRF check for state-changing HTTP requests
if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
    CsrfMiddleware::handle();
}

// Route mapping table
$routes = [
    'GET' => [
        ''                  => fn() => Session::isLoggedIn() ? View::redirect('dashboard') : View::redirect('login'),
        'login'             => [AuthController::class, 'showLogin'],
        'register'          => [AuthController::class, 'showRegister'],
        'logout'            => [AuthController::class, 'logout'],
        'dashboard'         => [DashboardController::class, 'index'],
        'tasks'             => [TaskController::class, 'index'],
        'tasks/create'      => [TaskController::class, 'create'],
        'tasks/edit'        => [TaskController::class, 'edit'],
        'tasks/archived'    => [TaskController::class, 'archived'],
        'tasks/export'      => [TaskController::class, 'exportCsv'],
        'kanban'            => [KanbanController::class, 'index'],
        'calendar'          => [CalendarController::class, 'index'],
        'activity'          => [ActivityController::class, 'index'],
        'notifications'     => [NotificationController::class, 'index'],
        'settings'          => [SettingsController::class, 'index'],
    ],
    'POST' => [
        'login'                  => [AuthController::class, 'login'],
        'register'               => [AuthController::class, 'register'],
        'logout'                 => [AuthController::class, 'logout'],
        'tasks/store'            => [TaskController::class, 'store'],
        'tasks/update'           => [TaskController::class, 'update'],
        'tasks/update-status'    => [TaskController::class, 'updateStatus'],
        'tasks/delete'           => [TaskController::class, 'delete'],
        'tasks/archive'          => [TaskController::class, 'archive'],
        'tasks/restore'          => [TaskController::class, 'restore'],
        'kanban/update-status'   => [KanbanController::class, 'updateStatus'],
        'notifications/read'     => [NotificationController::class, 'markAsRead'],
        'notifications/read-all' => [NotificationController::class, 'markAllAsRead'],
        'settings/profile'       => [SettingsController::class, 'updateProfile'],
        'settings/theme'         => [SettingsController::class, 'toggleTheme'],
    ]
];

// Check route match
if (isset($routes[$method][$route])) {
    $handler = $routes[$method][$route];
    if (is_callable($handler)) {
        $handler();
    } elseif (is_array($handler)) {
        [$class, $action] = $handler;
        $controller = new $class();
        $controller->$action();
    }
} else {
    // 404 Not Found
    http_response_code(404);
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        View::json(['success' => false, 'message' => 'Endpoint not found.'], 404);
    }

    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>404 Page Not Found - Flowzen</title>'
       . '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">'
       . '<link href="' . APP_URL . '/assets/css/style.css" rel="stylesheet">'
       . '</head><body class="d-flex align-items-center justify-content-center vh-100 bg-porcelain">'
       . '<div class="text-center p-5 card shadow-sm border-0 rounded-4" style="max-width:480px;">'
       . '<span class="badge bg-citron text-plum px-3 py-2 fs-6 rounded-pill mb-3">Error 404</span>'
       . '<h1 class="h2 fw-bold text-plum mb-3">Page Not Found</h1>'
       . '<p class="text-muted mb-4">The page or resource you are looking for does not exist in your Flowzen workspace.</p>'
       . '<a href="' . APP_URL . '/dashboard" class="btn btn-citron px-4 py-2 fw-semibold">Return to Dashboard</a>'
       . '</div></body></html>';
}
