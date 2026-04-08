<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Environment;
use PDO;

/**
 * ProductVariant model
 */
final class ProductVariant
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
            $sql = "CREATE TABLE IF NOT EXISTS product_variants (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_id INTEGER NOT NULL,
                name TEXT NOT NULL,
                sku TEXT,
                price REAL NOT NULL,
                stock INTEGER NOT NULL DEFAULT 0,
                attributes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
            )";

            $this->db->exec($sql);
            $this->db->exec('CREATE INDEX IF NOT EXISTS idx_product_variants_product_id ON product_variants(product_id)');
            $this->db->exec('CREATE UNIQUE INDEX IF NOT EXISTS idx_product_variants_sku ON product_variants(sku) WHERE sku IS NOT NULL');
            return;
        }

        $sql = "CREATE TABLE IF NOT EXISTS product_variants (
            id INT PRIMARY KEY AUTO_INCREMENT,
            product_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            sku VARCHAR(255),
            price DECIMAL(10,2) NOT NULL,
            stock INT NOT NULL DEFAULT 0,
            attributes JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_product_variants_product_id FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->exec($sql);
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_product_variants_product_id ON product_variants(product_id)');
        $this->db->exec('CREATE UNIQUE INDEX IF NOT EXISTS idx_product_variants_sku ON product_variants(sku)');
    }

    public function findByProductId(int $productId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM product_variants WHERE product_id = :product_id ORDER BY id ASC');
        $stmt->execute([':product_id' => $productId]);

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM product_variants WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $variant = $stmt->fetch();

        return $variant !== false ? $variant : null;
    }

    public function create(int $productId, array $data): ?array
    {
        $stmt = $this->db->prepare(
            'INSERT INTO product_variants (product_id, name, sku, price, stock, attributes) VALUES (:product_id, :name, :sku, :price, :stock, :attributes)'
        );

        $attributes = isset($data['attributes']) ? json_encode($data['attributes']) : null;

        $ok = $stmt->execute([
            ':product_id' => $productId,
            ':name' => $data['name'],
            ':sku' => $data['sku'] ?? null,
            ':price' => $data['price'],
            ':stock' => $data['stock'] ?? 0,
            ':attributes' => $attributes,
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

        $sql = 'UPDATE product_variants SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM product_variants WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public static function formatForResponse(array $variant): array
    {
        $attributes = null;
        if (isset($variant['attributes']) && $variant['attributes'] !== null) {
            $decoded = json_decode((string) $variant['attributes'], true);
            $attributes = is_array($decoded) ? $decoded : null;
        }

        return [
            'id' => (int) $variant['id'],
            'productId' => (int) $variant['product_id'],
            'name' => $variant['name'],
            'sku' => $variant['sku'] ?? null,
            'price' => (float) $variant['price'],
            'stock' => (int) $variant['stock'],
            'attributes' => $attributes,
            'createdAt' => self::toIso8601($variant['created_at'] ?? null),
            'updatedAt' => self::toIso8601($variant['updated_at'] ?? null),
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
