<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use App\Core\Database;
use App\Core\Environment;

/**
 * User model
 * Compatible with MySQL and SQLite
 */
final class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Create users table (call once during setup)
     * Works with both MySQL and SQLite
     */
    public function createTable(): void
    {
        $driver = Environment::get('DB_DRIVER', 'mysql');

        if ($driver === 'sqlite') {
            $this->createTableSqlite();
        } else {
            $this->createTableMysql();
        }

        $this->ensureSchemaCompatibility($driver);
    }

    /**
     * Create table for MySQL
     */
    private function createTableMysql(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(255) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(50) DEFAULT 'user',
            name VARCHAR(255),
            avatar TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->exec($sql);
    }

    /**
     * Create table for SQLite
     */
    private function createTableSqlite(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE,
            password TEXT NOT NULL,
            role TEXT DEFAULT 'user',
            name TEXT,
            avatar TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";

        $this->db->exec($sql);

        // Create indexes for SQLite
        $this->db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_username ON users(username)");
        $this->db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_email ON users(email)");
    }

    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute([':username' => $username]);

        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }

    /**
     * Update user by ID
     */
    public function updateById(int $id, array $fields): ?array
    {
        if ($fields === []) {
            return $this->findById($id);
        }

        $sets = [];
        $params = [':id' => $id];

        foreach ($fields as $key => $value) {
            $sets[] = "{$key} = :{$key}";
            $params[":{$key}"] = $value;
        }

        $driver = Environment::get('DB_DRIVER', 'mysql');
        if ($driver === 'sqlite') {
            $sets[] = 'updated_at = CURRENT_TIMESTAMP';
        }

        $sql = 'UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $this->findById($id);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);

        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }

    /**
     * Find user by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);

        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }

    /**
     * Create new user
     */
    public function create(string $username, string $password, ?string $email = null): ?array
    {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $this->db->prepare(
                'INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)'
            );

            $stmt->execute([
                ':username' => $username,
                ':email' => $email ?? null,
                ':password' => $hashedPassword,
                ':role' => 'user',
            ]);

            return $this->findByUsername($username);
        } catch (\PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                return null; // Username or email already exists
            }
            throw $e;
        }
    }

    /**
     * Verify password
     */
    public function verifyPassword(string $plainPassword, string $hashedPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }

    /**
     * Format user data for API response (exclude password)
     */
    public static function formatForResponse(array $user): array
    {
        return [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'email' => $user['email'] ?? null,
            'role' => $user['role'],
            'name' => $user['name'] ?? null,
            'avatar' => $user['avatar'] ?? null,
            'createdAt' => self::toIso8601($user['created_at'] ?? null),
            'updatedAt' => self::toIso8601($user['updated_at'] ?? null),
        ];
    }

    private function ensureSchemaCompatibility(string $driver): void
    {
        if ($driver === 'sqlite') {
            $stmt = $this->db->query('PRAGMA table_info(users)');
            $columns = $stmt !== false ? $stmt->fetchAll() : [];
            $columnNames = array_map(static fn(array $col): string => (string) ($col['name'] ?? ''), $columns);

            if (!in_array('avatar', $columnNames, true)) {
                $this->db->exec('ALTER TABLE users ADD COLUMN avatar TEXT');
            }

            return;
        }

        $stmt = $this->db->query("SHOW COLUMNS FROM users LIKE 'avatar'");
        $column = $stmt !== false ? $stmt->fetch() : false;

        if ($column === false) {
            $this->db->exec('ALTER TABLE users ADD COLUMN avatar TEXT NULL AFTER name');
        }
    }

    private static function toIso8601(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $timestamp = strtotime($value);
        return $timestamp !== false ? gmdate('c', $timestamp) : $value;
    }
}
