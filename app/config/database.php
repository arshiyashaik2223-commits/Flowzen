<?php
declare(strict_types=1);

/**
 * Flowzen - Database Singleton Manager
 * Encapsulates secure PDO connection handling, prepared statements configuration,
 * and transaction management.
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct() {}

    /**
     * Get or initialize the shared PDO connection.
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                DB_HOST,
                DB_PORT,
                DB_NAME
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // Enforce real server-side prepared statements
                PDO::ATTR_TIMEOUT            => 5,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // Log technical error securely to local log
                error_log('Database Connection Failed: ' . $e->getMessage());

                // If in development mode and table/db doesn't exist yet, provide helpful message
                if (APP_DEBUG) {
                    die('<div style="font-family:sans-serif;max-width:600px;margin:50px auto;padding:24px;border:1px solid #E68D78;border-radius:12px;background:#FFF5F3;color:#211B2B;">'
                        . '<h2 style="margin-top:0;color:#C44536;">Database Connection Notice</h2>'
                        . '<p>Could not connect to the database <code>' . htmlspecialchars(DB_NAME) . '</code> on <code>' . htmlspecialchars(DB_HOST) . ':' . DB_PORT . '</code>.</p>'
                        . '<p style="font-size:14px;color:#746B78;"><strong>System Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>'
                        . '<p style="font-size:13px;">Please ensure your MySQL password in <code>.env</code> is correct and that the database has been imported.</p>'
                        . '</div>');
                } else {
                    die('<div style="font-family:sans-serif;max-width:500px;margin:50px auto;padding:24px;border-radius:8px;background:#f8f9fa;color:#333;text-align:center;">'
                        . '<h2>Service Temporarily Unavailable</h2>'
                        . '<p>Our engineers have been notified. Please try again shortly.</p>'
                        . '</div>');
                }
            }
        }

        return self::$instance;
    }

    /**
     * Start a database transaction.
     */
    public static function beginTransaction(): bool {
        return self::getConnection()->beginTransaction();
    }

    /**
     * Commit an active transaction.
     */
    public static function commit(): bool {
        return self::getConnection()->commit();
    }

    /**
     * Rollback an active transaction.
     */
    public static function rollBack(): bool {
        if (self::getConnection()->inTransaction()) {
            return self::getConnection()->rollBack();
        }
        return false;
    }
}
