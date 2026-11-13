<?php
declare(strict_types=1);

/**
 * Flowzen - Application Configuration
 * Handles environment variables, session parameters, and global constants.
 */

// Define directory constants
define('ROOT_PATH', dirname(__DIR__, 2));
define('APP_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'app');
define('VIEWS_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'views');
define('STORAGE_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'storage');
define('PUBLIC_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'public');

// Simple .env parser (without external composer dependencies)
$envFile = ROOT_PATH . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Strip surrounding quotes if present
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// Helper function to read environment variable with fallback
if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed {
        if (array_key_exists($key, $_ENV)) {
            $val = $_ENV[$key];
            if ($val === 'true') return true;
            if ($val === 'false') return false;
            return $val;
        }
        $val = getenv($key);
        if ($val !== false) {
            if ($val === 'true') return true;
            if ($val === 'false') return false;
            return $val;
        }
        return $default;
    }
}

// Application settings
define('APP_NAME', env('APP_NAME', 'Flowzen'));
define('APP_ENV', env('APP_ENV', 'development'));
define('APP_DEBUG', (bool)env('APP_DEBUG', true));
define('APP_URL', rtrim(env('APP_URL', 'http://localhost/flowzen/public'), '/'));

// Database settings (defaulting to MySQL267 port 3306)
define('DB_HOST', env('DB_HOST', '127.0.0.1'));
define('DB_PORT', (int)env('DB_PORT', 3306));
define('DB_NAME', env('DB_NAME', 'flowzen_db'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));

// Session configuration
define('SESSION_LIFETIME', (int)env('SESSION_LIFETIME', 7200));

// Timezone setup
date_default_timezone_set('Asia/Kolkata');

// Error reporting configuration
if (APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// Log directory
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'app.log');

// Configure secure session parameters before starting any session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', (string)SESSION_LIFETIME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => '',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}
