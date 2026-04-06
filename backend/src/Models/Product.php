<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Environment;
use PDO;

/**
 * Product model
 */
final class Product
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
            $sql = "CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                description TEXT,
                price REAL NOT NULL,
                stock INTEGER NOT NULL DEFAULT 0,
                image TEXT,
                category TEXT,
                created_by INTEGER NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(created_by) REFERENCES users(id)
            )";

            $this->db->exec($sql);
            $this->db->exec('CREATE INDEX IF NOT EXISTS idx_products_name ON products(name)');
            $this->db->exec('CREATE INDEX IF NOT EXISTS idx_products_category ON products(category)');
            return;
        }

        $sql = "CREATE TABLE IF NOT EXISTS products (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            stock INT NOT NULL DEFAULT 0,
            image TEXT,
            category VARCHAR(255),
            created_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_products_name (name),
            INDEX idx_products_category (category),
            CONSTRAINT fk_products_created_by FOREIGN KEY (created_by) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->exec($sql);
    }

    public function findAll(?string $search, int $limit, int $offset): array
    {
        $params = [
            ':limit' => $limit,
            ':offset' => $offset,
        ];

        $whereSql = '';
        if ($search !== null && trim($search) !== '') {
            $whereSql = ' WHERE name LIKE :search OR description LIKE :search';
            $params[':search'] = '%' . trim($search) . '%';
        }

        $stmt = $this->db->prepare("SELECT * FROM products{$whereSql} ORDER BY id DESC LIMIT :limit OFFSET :offset");
        foreach ($params as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }
        $stmt->execute();

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM products{$whereSql}");
        if (isset($params[':search'])) {
            $countStmt->bindValue(':search', $params[':search'], PDO::PARAM_STR);
        }
        $countStmt->execute();

        return [
            'products' => $stmt->fetchAll(),
            'total' => (int) $countStmt->fetchColumn(),
        ];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch();

        return $product !== false ? $product : null;
    }

    public function create(array $data, int $createdBy): ?array
    {
        $stmt = $this->db->prepare(
            'INSERT INTO products (name, description, price, stock, image, category, created_by) VALUES (:name, :description, :price, :stock, :image, :category, :created_by)'
        );

        $ok = $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':price' => $data['price'],
            ':stock' => $data['stock'],
            ':image' => $data['image'] ?? null,
            ':category' => $data['category'] ?? null,
            ':created_by' => $createdBy,
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

        $sql = 'UPDATE products SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM products WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function decrementStock(int $id, int $quantity): bool
    {
        $driver = Environment::get('DB_DRIVER', 'mysql');

        if ($driver === 'sqlite') {
            $sql = 'UPDATE products SET stock = stock - :qty, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND stock >= :qty';
        } else {
            $sql = 'UPDATE products SET stock = stock - :qty WHERE id = :id AND stock >= :qty';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':qty', $quantity, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public static function formatForResponse(array $product): array
    {
        return [
            'id' => (int) $product['id'],
            'name' => $product['name'],
            'description' => $product['description'] ?? null,
            'price' => (float) $product['price'],
            'stock' => (int) $product['stock'],
            'image' => $product['image'] ?? null,
            'category' => $product['category'] ?? null,
            'createdBy' => (int) $product['created_by'],
            'createdAt' => self::toIso8601($product['created_at'] ?? null),
            'updatedAt' => self::toIso8601($product['updated_at'] ?? null),
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
