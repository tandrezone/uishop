<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

/**
 * Database connection manager
 * Supports both MySQL and SQLite
 */
final class Database
{
    private static ?PDO $connection = null;

    /**
     * Get database connection (singleton pattern)
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            self::connect();
        }

        return self::$connection;
    }

    /**
     * Establish database connection
     */
    private static function connect(): void
    {
        try {
            $driver = Environment::get('DB_DRIVER', 'mysql');
            if ($driver === 'sqlite') {
                self::connectSqlite();
            } else {
                self::connectMysql();
            }
        } catch (PDOException $e) {
            throw new \RuntimeException("Database connection failed: {$e->getMessage()}");
        }
    }

    /**
     * Connect to MySQL database
     */
    private static function connectMysql(): void
    {
        $host = Environment::get('DB_HOST', 'localhost');
        $port = Environment::get('DB_PORT', '3306');
        $name = Environment::get('DB_NAME', 'uishop');
        $user = Environment::get('DB_USER', 'root');
        $pass = Environment::get('DB_PASS', '');

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        self::$connection = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    /**
     * Connect to SQLite database
     */
    private static function connectSqlite(): void
    {
        $dbFile = Environment::get('DB_FILE', 'data/uishop.db');

        // Make path relative to backend root, not public/
        if (!str_starts_with($dbFile, '/') && !preg_match('#^[a-z]:#i', $dbFile)) {
            $dbFile = dirname(__DIR__, 2) . '/' . $dbFile;
        }

        // Convert to absolute path
        $dbFile = realpath(dirname($dbFile)) . '/' . basename($dbFile);

        // Ensure directory exists
        $dir = dirname($dbFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $dsn = "sqlite:{$dbFile}";

        self::$connection = new PDO($dsn, '', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        // Enable foreign keys for SQLite
        self::$connection->exec('PRAGMA foreign_keys = ON');
    }

    /**
     * Close database connection
     */
    public static function close(): void
    {
        self::$connection = null;
    }
}
