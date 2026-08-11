<?php

/**
 * Connect MyUni — Security Utilities
 *
 * Provides CSRF protection, input validation, and output escaping.
 *
 * @package ConnectMyUni
 */

declare(strict_types=1);

namespace ConnectMyUni\Config;

/**
 * Class Security
 */
class Security
{
    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token']) || 
            (time() - ($_SESSION['csrf_token_time'] ?? 0)) > 1800) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken(string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Get CSRF token as hidden input field
     */
    public static function csrfInput(): string
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Sanitize string input
     */
    public static function sanitizeString(string $input): string
    {
        return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Validate email
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate phone number (basic)
     */
    public static function validatePhone(string $phone): bool
    {
        // Remove common formatting characters
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        return preg_match('/^\+?[0-9]{10,15}$/', $phone);
    }

    /**
     * Validate URL
     */
    public static function validateUrl(string $url): bool
    {
        if (empty($url)) {
            return true; // Optional field
        }
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Escape output for HTML
     */
    public static function escape(string $output): string
    {
        return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate required fields
     */
    public static function validateRequired(array $data, array $requiredFields): array
    {
        $errors = [];

        foreach ($requiredFields as $field) {
            if (empty(trim($data[$field] ?? ''))) {
                $errors[] = ucfirst($field) . ' is required';
            }
        }

        return $errors;
    }

    /**
     * Hash password
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify password
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
