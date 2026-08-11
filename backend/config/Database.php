<?php

declare(strict_types=1);

namespace ConnectMyUni;

use PDO;
use PDOException;

/**
 * Database Connection Singleton for PostgreSQL/Supabase
 *
 * @package ConnectMyUni
 */
class Database
{
    /**
     * @var PDO|null
     */
    private static ?PDO $pdo = null;

    /**
     * Get database connection instance
     *
     * @return PDO
     * @throws PDOException on connection failure
     */
    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $config = require_once __DIR__ . '/db_config.php';

            // Build PostgreSQL DSN.
            $dsn = sprintf(
                'pgsql:host=%s;port=%d;dbname=%s',
                $config['host'],
                $config['port'],
                $config['dbname']
            );

            // Add SSL mode if specified (required for Supabase).
            if (!empty($config['sslmode'])) {
                $dsn .= ';sslmode=' . $config['sslmode'];
            }

            try {
                self::$pdo = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                // Log the technical detail server-side, but never expose
                // the DSN/host/credentials in a message delivered to users.
                error_log('Database connection failed: ' . $e->getMessage());
                throw new PDOException(
                    'Database connection failed. Check server logs for details.'
                );
            }
        }

        return self::$pdo;
    }

    /**
     * Reset connection (for testing)
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$pdo = null;
    }
}