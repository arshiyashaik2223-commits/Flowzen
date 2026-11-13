<?php
declare(strict_types=1);

/**
 * Flowzen - Session Helper
 * Manages authenticated user state, flash notifications, and secure session lifecycles.
 */

class Session {
    /**
     * Start session if not already active.
     */
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            if (headers_sent()) {
                @session_start();
            } else {
                session_start();
            }
        }
    }

    /**
     * Set a session value.
     */
    public static function set(string $key, mixed $value): void {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Retrieve a session value.
     */
    public static function get(string $key, mixed $default = null): mixed {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if a session key exists.
     */
    public static function has(string $key): bool {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session key.
     */
    public static function remove(string $key): void {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Store a one-time flash message for display on next request.
     */
    public static function setFlash(string $type, string $message): void {
        self::start();
        $_SESSION['_flash'][$type] = $message;
    }

    /**
     * Retrieve and clear a one-time flash message.
     */
    public static function getFlash(string $type): ?string {
        self::start();
        if (isset($_SESSION['_flash'][$type])) {
            $msg = $_SESSION['_flash'][$type];
            unset($_SESSION['_flash'][$type]);
            return $msg;
        }
        return null;
    }

    /**
     * Check if a flash message exists.
     */
    public static function hasFlash(string $type): bool {
        self::start();
        return isset($_SESSION['_flash'][$type]);
    }

    /**
     * Check if current visitor is authenticated.
     */
    public static function isLoggedIn(): bool {
        self::start();
        return !empty($_SESSION['user_id']);
    }

    /**
     * Get the authenticated user's ID.
     */
    public static function userId(): ?int {
        self::start();
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    /**
     * Get the authenticated user's info array.
     */
    public static function user(): ?array {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    /**
     * Login a user into the session and regenerate session ID to prevent session fixation.
     */
    public static function login(array $user): void {
        self::start();
        // Prevent session fixation attack
        if (!headers_sent() && session_status() === PHP_SESSION_ACTIVE) {
            @session_regenerate_id(true);
        }

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user'] = [
            'id'    => (int)$user['id'],
            'name'  => $user['name'],
            'email' => $user['email']
        ];

        // Also rotate CSRF token on login
        Security::regenerateCsrfToken();
    }

    /**
     * Destroy the authenticated session completely.
     */
    public static function destroy(): void {
        self::start();
        $_SESSION = [];

        // Expire the session cookie in browser if headers not yet sent
        if (!headers_sent() && ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }
    }
}
