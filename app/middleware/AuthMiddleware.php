<?php
declare(strict_types=1);

/**
 * Flowzen - Authentication Middleware
 * Enforces that protected routes are accessible only by authenticated sessions.
 */

class AuthMiddleware {
    public static function handle(): void {
        Session::start();
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Please log in to access this page.');
            View::redirect('login');
        }
    }

    /**
     * Prevent logged-in users from visiting guest-only pages like login and registration.
     */
    public static function guestOnly(): void {
        Session::start();
        if (Session::isLoggedIn()) {
            View::redirect('dashboard');
        }
    }
}
