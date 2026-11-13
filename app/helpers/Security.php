<?php
declare(strict_types=1);

/**
 * Flowzen - Security Helper
 * Provides XSS prevention escaping, CSRF protection token management,
 * and input sanitization.
 */

class Security {
    /**
     * Escape HTML output to prevent Cross-Site Scripting (XSS).
     */
    public static function escape(?string $value): string {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Generate or retrieve existing CSRF token.
     */
    public static function getCsrfToken(): string {
        Session::start();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Render an HTML hidden input for CSRF protection.
     */
    public static function csrfField(): string {
        $token = self::getCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . self::escape($token) . '">';
    }

    /**
     * Validate an incoming CSRF token using timing-safe comparison.
     */
    public static function validateCsrfToken(?string $token): bool {
        Session::start();

        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Regenerate CSRF token (useful after sensitive operations like login).
     */
    public static function regenerateCsrfToken(): string {
        Session::start();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    /**
     * Sanitize string input (stripping null bytes and extra whitespace).
     */
    public static function sanitizeString(?string $input): string {
        if ($input === null) {
            return '';
        }
        // Remove null bytes
        $cleaned = str_replace(chr(0), '', $input);
        return trim($cleaned);
    }
}

/**
 * Global shortcut for escaping output in view templates
 */
if (!function_exists('e')) {
    function e(?string $value): string {
        return Security::escape($value);
    }
}
