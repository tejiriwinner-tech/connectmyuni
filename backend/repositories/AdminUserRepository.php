<?php

/**
 * Connect MyUni — Admin User Repository
 *
 * Handles database operations for admin users.
 *
 * @package ConnectMyUni
 */

declare(strict_types=1);

namespace ConnectMyUni\Repositories;

use ConnectMyUni\Database;
use ConnectMyUni\Config\Security;
use PDO;

/**
 * Class AdminUserRepository
 */
class AdminUserRepository
{
    /**
     * Find admin user by ID
     */
    public function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, username, email, role, is_active, last_login_at, created_at FROM admin_users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    /**
     * Find admin user by username
     */
    public function findByUsername(string $username): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    /**
     * Find admin user by email
     */
    public function findByEmail(string $email): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    /**
     * Verify user credentials
     */
    public function verifyCredentials(string $username, string $password): ?array
    {
        $user = $this->findByUsername($username);
        
        if (!$user) {
            return null;
        }

        if (!$user['is_active']) {
            return null;
        }

        if (!Security::verifyPassword($password, $user['password_hash'])) {
            return null;
        }

        return $user;
    }

    /**
     * Update password
     */
    public function updatePassword(int $userId, string $newPasswordHash): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$newPasswordHash, $userId]);
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(int $userId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE admin_users SET last_login_at = NOW() WHERE id = ?");
        return $stmt->execute([$userId]);
    }

    /**
     * Get all admin users
     */
    public function getAll(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, username, email, role, is_active, last_login_at, created_at FROM admin_users ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Create admin user
     */
    public function create(string $username, string $email, string $password, string $role = 'admin'): int
    {
        $pdo = Database::getConnection();
        $passwordHash = Security::hashPassword($password);
        
        $stmt = $pdo->prepare("
            INSERT INTO admin_users (username, email, password_hash, role, is_active)
            VALUES (?, ?, ?, ?, 1)
        ");
        
        $stmt->execute([$username, $email, $passwordHash, $role]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Update user status
     */
    public function updateStatus(int $userId, bool $isActive): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE admin_users SET is_active = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$isActive ? 1 : 0, $userId]);
    }
}
