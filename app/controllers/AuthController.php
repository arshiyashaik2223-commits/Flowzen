<?php
declare(strict_types=1);

/**
 * Flowzen - Authentication Controller
 * Manages user registration, credential authentication, session lifecycles, and logout.
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/helpers/Session.php';
require_once dirname(__DIR__) . '/helpers/Security.php';
require_once dirname(__DIR__) . '/helpers/Validator.php';
require_once dirname(__DIR__) . '/helpers/View.php';
require_once dirname(__DIR__) . '/middleware/AuthMiddleware.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/ActivityLog.php';

class AuthController {
    /**
     * Show login form.
     */
    public function showLogin(): void {
        AuthMiddleware::guestOnly();
        View::render('auth/login', [
            'pageTitle' => 'Sign In - Flowzen'
        ], 'auth');
    }

    /**
     * Process login submission with attempt throttling and secure credentials verification.
     */
    public function login(): void {
        AuthMiddleware::guestOnly();

        // Basic login attempt throttling
        $attempts = Session::get('login_attempts', 0);
        $lastAttemptTime = Session::get('login_last_attempt', 0);

        if ($attempts >= 5 && (time() - $lastAttemptTime) < 300) {
            $remaining = 300 - (time() - $lastAttemptTime);
            Session::setFlash('error', "Too many failed login attempts. Please wait {$remaining} seconds before trying again.");
            View::redirect('login');
        }

        $validator = new Validator($_POST);
        $validator->required('email')->email('email');
        $validator->required('password');

        if ($validator->fails()) {
            $validator->flash();
            View::redirect('login');
        }

        $email    = Security::sanitizeString($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        $user = User::authenticate($email, $password);

        if (!$user) {
            // Increment failed attempt counter
            Session::set('login_attempts', $attempts + 1);
            Session::set('login_last_attempt', time());

            Session::setFlash('error', 'Invalid email or password. Please check your credentials.');
            $validator->flash();
            View::redirect('login');
        }

        // Reset login attempts on success
        Session::remove('login_attempts');
        Session::remove('login_last_attempt');

        // Log the user into authenticated session (regenerates session ID)
        Session::login($user);

        // Record audit log
        ActivityLog::record((int)$user['id'], null, 'auth', 'User signed in successfully.');

        Session::setFlash('success', "Welcome back, {$user['name']}!");
        View::redirect('dashboard');
    }

    /**
     * Show registration form.
     */
    public function showRegister(): void {
        AuthMiddleware::guestOnly();
        View::render('auth/register', [
            'pageTitle' => 'Create Account - Flowzen'
        ], 'auth');
    }

    /**
     * Process registration submission.
     */
    public function register(): void {
        AuthMiddleware::guestOnly();

        $validator = new Validator($_POST);
        $validator->required('name', 'Full name is required.')->max('name', 100);
        $validator->required('email')->email('email')->max('email', 191);
        $validator->required('password')->min('password', 8, 'Password must be at least 8 characters long.');
        $validator->required('confirm_password')->matches('password', 'confirm_password', 'Passwords do not match.');

        $email = Security::sanitizeString($_POST['email'] ?? '');
        if ($email !== '' && User::emailExists($email)) {
            $validator->addError('email', 'An account with this email address already exists.');
        }

        if ($validator->fails()) {
            $validator->flash();
            View::redirect('register');
        }

        $name = Security::sanitizeString($_POST['name'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        $userId = User::create($name, $email, $password);

        // Auto-login newly registered user
        $newUser = [
            'id'    => $userId,
            'name'  => $name,
            'email' => $email
        ];
        Session::login($newUser);

        // Record initial audit log
        ActivityLog::record($userId, null, 'auth', 'Account registered successfully.');

        Session::setFlash('success', 'Your Flowzen workspace has been created! Welcome aboard.');
        View::redirect('dashboard');
    }

    /**
     * Log user out and destroy session.
     */
    public function logout(): void {
        $userId = Session::userId();
        if ($userId) {
            ActivityLog::record($userId, null, 'auth', 'User logged out.');
        }

        Session::destroy();
        Session::start();
        Session::setFlash('info', 'You have been logged out securely.');
        View::redirect('login');
    }
}
