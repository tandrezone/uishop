<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Environment;
use PDO;

/**
 * Supplier model
 */
final class Supplier
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function createTable(): void
    {
        $driver = Environment::get('DB_DRIVER', 'mysql');

        if ($driver === 'sqlite') {
            $sql = "CREATE TABLE IF NOT EXISTS suppliers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                global_rating REAL,
                comments TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )";

            $this->db->exec($sql);
            $this->db->exec('CREATE INDEX IF NOT EXISTS idx_suppliers_name ON suppliers(name)');
            return;
        }

        $sql = "CREATE TABLE IF NOT EXISTS suppliers (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            global_rating DECIMAL(2,1),
            comments TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->exec($sql);
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_suppliers_name ON suppliers(name)');
    }

    public function findAll(?string $search, int $limit, int $offset): array
    {
        $params = [
            ':limit' => $limit,
            ':offset' => $offset,
        ];

        $whereSql = '';
        if ($search !== null && trim($search) !== '') {
            $whereSql = ' WHERE name LIKE :search OR comments LIKE :search';
            $params[':search'] = '%' . trim($search) . '%';
        }

        $stmt = $this->db->prepare("SELECT * FROM suppliers{$whereSql} ORDER BY id DESC LIMIT :limit OFFSET :offset");
        foreach ($params as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }
        $stmt->execute();

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM suppliers{$whereSql}");
        if (isset($params[':search'])) {
            $countStmt->bindValue(':search', $params[':search'], PDO::PARAM_STR);
        }
        $countStmt->execute();

        return [
            'suppliers' => $stmt->fetchAll(),
            'total' => (int) $countStmt->fetchColumn(),
        ];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM suppliers WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $supplier = $stmt->fetch();

        return $supplier !== false ? $supplier : null;
    }

    public function create(array $data): ?array
    {
        $stmt = $this->db->prepare(
            'INSERT INTO suppliers (name, global_rating, comments) VALUES (:name, :global_rating, :comments)'
        );

        $ok = $stmt->execute([
            ':name' => $data['name'],
            ':global_rating' => $data['global_rating'] ?? null,
            ':comments' => $data['comments'] ?? null,
        ]);

        if (!$ok) {
            return null;
        }

        return $this->findById((int) $this->db->lastInsertId());
    }

    public function update(int $id, array $fields): ?array
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

        $sql = 'UPDATE suppliers SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM suppliers WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public static function formatForResponse(array $supplier): array
    {
        return [
            'id' => (int) $supplier['id'],
            'name' => $supplier['name'],
            'globalRating' => $supplier['global_rating'] !== null ? (float) $supplier['global_rating'] : null,
            'comments' => $supplier['comments'] ?? null,
            'createdAt' => self::toIso8601($supplier['created_at'] ?? null),
            'updatedAt' => self::toIso8601($supplier['updated_at'] ?? null),
        ];
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
