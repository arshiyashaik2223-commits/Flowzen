<?php
declare(strict_types=1);

/**
 * Flowzen - View Rendering Helper
 * Manages template compilation, layout composition, and HTTP redirection.
 */

class View {
    /**
     * Render a view within a layout or standalone.
     *
     * @param string $viewPath e.g. 'dashboard/index' or 'auth/login'
     * @param array $data Data variables to extract into the view
     * @param string|null $layout e.g. 'main', 'auth', or null for raw view
     */
    public static function render(string $viewPath, array $data = [], ?string $layout = 'main'): void {
        // Extract variables safely into current scope
        extract($data);

        // Retrieve flash messages and errors for the view
        $flashSuccess = Session::getFlash('success');
        $flashError   = Session::getFlash('error');
        $flashWarning = Session::getFlash('warning');
        $flashInfo    = Session::getFlash('info');

        $validationErrors = Session::get('_validation_errors', []);
        Validator::clearFlashed();

        // Calculate view file path
        $viewFile = VIEWS_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $viewPath) . '.php';

        if (!file_exists($viewFile)) {
            die("View template not found: " . htmlspecialchars($viewPath));
        }

        // Buffer the view content
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // Render with layout
        if ($layout === 'main') {
            include VIEWS_PATH . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'header.php';
            echo '<div class="app-layout-wrapper d-flex">';
            include VIEWS_PATH . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'sidebar.php';
            echo '<div class="app-main-content-area flex-grow-1">';
            include VIEWS_PATH . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'topnav.php';
            echo '<main class="app-content-body p-3 p-md-4">';
            echo $content;
            echo '</main>';
            echo '</div>'; // end app-main-content-area
            echo '</div>'; // end app-layout-wrapper
            include VIEWS_PATH . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'footer.php';
        } elseif ($layout === 'auth') {
            include VIEWS_PATH . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'header.php';
            echo '<main class="auth-wrapper">';
            echo $content;
            echo '</main>';
            include VIEWS_PATH . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'footer.php';
        } else {
            // Raw view without layout wrapper
            echo $content;
        }
    }

    /**
     * Send a JSON response and terminate.
     */
    public static function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Redirect to another URL within the application.
     */
    public static function redirect(string $path): void {
        $url = str_starts_with($path, 'http') ? $path : APP_URL . '/' . ltrim($path, '/');
        header("Location: {$url}");
        exit;
    }
}
