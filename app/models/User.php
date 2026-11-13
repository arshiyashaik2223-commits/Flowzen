<?php
declare(strict_types=1);

/**
 * Flowzen - User Model
 * Handles user persistence, password hashing, and credentials verification.
 */

require_once dirname(__DIR__) . '/config/database.php';

class User {
    /**
     * Find a user record by email.
     */
    public static function findByEmail(string $email): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => strtolower(trim($email))]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find a user record by primary key ID.
     */
    public static function findById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, name, email, created_at, updated_at FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Check if an email is already registered.
     */
    public static function emailExists(string $email, ?int $excludeId = null): bool {
        $pdo = Database::getConnection();
        $sql = 'SELECT 1 FROM users WHERE email = :email';
        $params = ['email' => strtolower(trim($email))];

        if ($excludeId !== null) {
            $sql .= ' AND id != :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $stmt = $pdo->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Create a new user with secure password hashing.
     */
    public static function create(string $name, string $email, string $plainPassword): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            INSERT INTO users (name, email, password, created_at)
            VALUES (:name, :email, :password, NOW())
        ');

        // BCRYPT password hash with default work factor (cost 10)
        $passwordHash = password_hash($plainPassword, PASSWORD_BCRYPT);

        $stmt->execute([
            'name'     => trim($name),
            'email'    => strtolower(trim($email)),
            'password' => $passwordHash
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Verify user credentials for login.
     */
    public static function authenticate(string $email, string $plainPassword): ?array {
        $user = self::findByEmail($email);
        if (!$user) {
            return null;
        }

        if (password_verify($plainPassword, $user['password'])) {
            // Remove sensitive password hash from memory
            unset($user['password']);
            return $user;
        }

        return null;
    }

    /**
     * Update user profile and optional new password.
     */
    public static function updateProfile(int $id, string $name, ?string $newPassword = null): bool {
        $pdo = Database::getConnection();
        if (!empty($newPassword)) {
            $stmt = $pdo->prepare('
                UPDATE users
                SET name = :name, password = :password, updated_at = NOW()
                WHERE id = :id
            ');
            return $stmt->execute([
                'id'       => $id,
                'name'     => trim($name),
                'password' => password_hash($newPassword, PASSWORD_BCRYPT)
            ]);
        }

        $stmt = $pdo->prepare('UPDATE users SET name = :name, updated_at = NOW() WHERE id = :id');
        return $stmt->execute([
            'id'   => $id,
            'name' => trim($name)
        ]);
    }
}
