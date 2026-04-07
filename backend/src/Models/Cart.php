<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Environment;
use PDO;

/**
 * Cart model
 */
final class Cart
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
            $sql = "CREATE TABLE IF NOT EXISTS cart_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE,
                UNIQUE(user_id, product_id)
            )";

            $this->db->exec($sql);
            $this->db->exec('CREATE INDEX IF NOT EXISTS idx_cart_items_user_id ON cart_items(user_id)');
            $this->db->exec('CREATE INDEX IF NOT EXISTS idx_cart_items_product_id ON cart_items(product_id)');
            return;
        }

        $sql = "CREATE TABLE IF NOT EXISTS cart_items (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_product (user_id, product_id),
            CONSTRAINT fk_cart_items_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT fk_cart_items_product_id FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->exec($sql);
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_cart_items_user_id ON cart_items(user_id)');
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_cart_items_product_id ON cart_items(product_id)');
    }

    public function findByUserId(int $userId): array
    {
        $stmt = $this->db->prepare('
            SELECT c.id, c.user_id, c.product_id, c.quantity, c.created_at, c.updated_at,
                   p.name, p.description, p.price, p.stock, p.image, p.category
            FROM cart_items c
            INNER JOIN products p ON c.product_id = p.id
            WHERE c.user_id = :user_id
            ORDER BY c.created_at DESC
        ');
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function findByUserAndProduct(int $userId, int $productId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM cart_items WHERE user_id = :user_id AND product_id = :product_id');
        $stmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId,
        ]);
        $item = $stmt->fetch();

        return $item !== false ? $item : null;
    }

    public function addItem(int $userId, int $productId, int $quantity): ?array
    {
        $existing = $this->findByUserAndProduct($userId, $productId);

        if ($existing !== null) {
            return $this->updateQuantity((int) $existing['id'], (int) $existing['quantity'] + $quantity);
        }

        $stmt = $this->db->prepare('INSERT INTO cart_items (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)');
        $stmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId,
            ':quantity' => $quantity,
        ]);

        $id = (int) $this->db->lastInsertId();
        return $this->findById($id);
    }

    public function updateQuantity(int $id, int $quantity): ?array
    {
        $driver = Environment::get('DB_DRIVER', 'mysql');

        if ($driver === 'sqlite') {
            $sql = 'UPDATE cart_items SET quantity = :quantity, updated_at = CURRENT_TIMESTAMP WHERE id = :id';
        } else {
            $sql = 'UPDATE cart_items SET quantity = :quantity WHERE id = :id';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':quantity' => $quantity,
            ':id' => $id,
        ]);

        return $this->findById($id);
    }

    public function removeItem(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM cart_items WHERE id = :id AND user_id = :user_id');
        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId,
        ]);
    }

    public function clearCart(int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM cart_items WHERE user_id = :user_id');
        return $stmt->execute([':user_id' => $userId]);
    }

    private function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT c.id, c.user_id, c.product_id, c.quantity, c.created_at, c.updated_at,
                   p.name, p.description, p.price, p.stock, p.image, p.category
            FROM cart_items c
            INNER JOIN products p ON c.product_id = p.id
            WHERE c.id = :id
        ');
        $stmt->execute([':id' => $id]);
        $item = $stmt->fetch();

        return $item !== false ? $item : null;
    }

    public static function formatForResponse(array $item): array
    {
        return [
            'id' => (int) $item['id'],
            'userId' => (int) $item['user_id'],
            'productId' => (int) $item['product_id'],
            'quantity' => (int) $item['quantity'],
            'product' => [
                'name' => $item['name'],
                'description' => $item['description'] ?? null,
                'price' => (float) $item['price'],
                'stock' => (int) $item['stock'],
                'image' => $item['image'] ?? null,
                'category' => $item['category'] ?? null,
            ],
            'createdAt' => self::toIso8601($item['created_at'] ?? null),
            'updatedAt' => self::toIso8601($item['updated_at'] ?? null),
        ];
    }

    private static function toIso8601(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        return gmdate('c', $timestamp);
    }
}
