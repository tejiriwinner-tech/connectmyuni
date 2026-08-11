<?php

/**
 * Connect MyUni — Authentication Middleware
 *
 * Protects admin pages by requiring authentication.
 * Redirects unauthenticated users to login page.
 *
 * @package ConnectMyUni
 */

declare(strict_types=1);

namespace ConnectMyUni\Middleware;

use ConnectMyUni\Config\Security;
use ConnectMyUni\Repositories\AdminUserRepository;

/**
 * Class AuthMiddleware
 */
class AuthMiddleware
{
    /**
     * Check if user is authenticated
     */
    public static function check(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['admin_user_id']) 
            && isset($_SESSION['admin_username'])
            && !empty($_SESSION['admin_user_id']);
    }

    /**
     * Require authentication - redirect to login if not authenticated
     */
    public static function requireAuth(): void
    {
        if (!self::check()) {
            // Store intended URL for redirect after login
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/admin/index.php';
            
            header('Location: /admin/login.php');
            exit;
        }
    }

    /**
     * Get current logged-in user
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        $repo = new AdminUserRepository();
        return $repo->findById((int) $_SESSION['admin_user_id']);
    }

    /**
     * Get current user ID
     */
    public static function userId(): ?int
    {
        return $_SESSION['admin_user_id'] ?? null;
    }

    /**
     * Get current username
     */
    public static function username(): ?string
    {
        return $_SESSION['admin_username'] ?? null;
    }

    /**
     * Login user
     */
    public static function login(int $userId, string $username, string $role): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        $_SESSION['admin_user_id'] = $userId;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_role'] = $role;
        $_SESSION['logged_in_at'] = time();
    }

    /**
     * Logout user
     */
    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear all session data
        $_SESSION = [];

        // Delete session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destroy session
        session_destroy();
    }

    /**
     * Check if session has expired
     */
    public static function isSessionExpired(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['logged_in_at'])) {
            return true;
        }

        $lifetime = 1440; // 24 minutes (default PHP session lifetime)
        return (time() - $_SESSION['logged_in_at']) > $lifetime;
    }

    /**
     * Refresh session timestamp
     */
    public static function refreshSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['logged_in_at'] = time();
    }
}
