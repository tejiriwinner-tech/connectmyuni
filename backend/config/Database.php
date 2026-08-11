<?php

declare(strict_types=1);

namespace ConnectMyUni;

use PDO;
use PDOException;

/**
 * Database Connection Singleton
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
     * @throws PDOException
     */
    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $config = require __DIR__ . '/database.php';

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['dbname'],
                $config['charset']
            );

            try {
                self::$pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            } catch (PDOException $e) {
                error_log('Database connection failed: ' . $e->getMessage());
                throw new PDOException('Database connection failed: ' . $e->getMessage());
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
