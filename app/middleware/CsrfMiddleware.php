<?php
declare(strict_types=1);

/**
 * Flowzen - CSRF Protection Middleware
 * Guards against Cross-Site Request Forgery on all state-changing HTTP requests.
 */

class CsrfMiddleware {
    public static function handle(): void {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            $token = $_POST['csrf_token'] ?? null;

            // Also check for X-CSRF-TOKEN header in AJAX requests
            if (!$token && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
                $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
            }

            if (!Security::validateCsrfToken($token)) {
                // If it is an AJAX request, respond in JSON
                $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
                    (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json'));

                if ($isAjax) {
                    View::json([
                        'success' => false,
                        'message' => 'Security token invalid or expired. Please refresh the page.'
                    ], 403);
                }

                Session::setFlash('error', 'Security verification failed (CSRF token expired). Please submit again.');
                $referer = $_SERVER['HTTP_REFERER'] ?? APP_URL;
                header("Location: {$referer}");
                exit;
            }
        }
    }
}
