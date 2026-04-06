<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Environment;
use PDO;
use Throwable;

/**
 * Order model
 */
final class Order
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
            $ordersSql = "CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                total_amount REAL NOT NULL,
                status TEXT NOT NULL DEFAULT 'pending',
                shipping_address TEXT,
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(user_id) REFERENCES users(id)
            )";

            $itemsSql = "CREATE TABLE IF NOT EXISTS order_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL,
                price REAL NOT NULL,
                FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY(product_id) REFERENCES products(id)
            )";

            $this->db->exec($ordersSql);
            $this->db->exec($itemsSql);
            $this->db->exec('CREATE INDEX IF NOT EXISTS idx_orders_user_id ON orders(user_id)');
            $this->db->exec('CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status)');
            $this->db->exec('CREATE INDEX IF NOT EXISTS idx_order_items_order_id ON order_items(order_id)');
            return;
        }

        $ordersSql = "CREATE TABLE IF NOT EXISTS orders (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'pending',
            shipping_address TEXT,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_orders_user_id (user_id),
            INDEX idx_orders_status (status),
            CONSTRAINT fk_orders_user_id FOREIGN KEY (user_id) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $itemsSql = "CREATE TABLE IF NOT EXISTS order_items (
            id INT PRIMARY KEY AUTO_INCREMENT,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            INDEX idx_order_items_order_id (order_id),
            CONSTRAINT fk_order_items_order_id FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            CONSTRAINT fk_order_items_product_id FOREIGN KEY (product_id) REFERENCES products(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->exec($ordersSql);
        $this->db->exec($itemsSql);
    }

    public function findAll(?string $status, int $limit, int $offset, ?int $userId = null): array
    {
        $where = [];
        $params = [
            ':limit' => $limit,
            ':offset' => $offset,
        ];

        if ($status !== null && trim($status) !== '') {
            $where[] = 'status = :status';
            $params[':status'] = trim($status);
        }

        if ($userId !== null) {
            $where[] = 'user_id = :user_id';
            $params[':user_id'] = $userId;
        }

        $whereSql = $where !== [] ? ' WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("SELECT * FROM orders{$whereSql} ORDER BY id DESC LIMIT :limit OFFSET :offset");
        foreach ($params as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }
        $stmt->execute();
        $orders = $stmt->fetchAll();

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM orders{$whereSql}");
        if (isset($params[':status'])) {
            $countStmt->bindValue(':status', $params[':status'], PDO::PARAM_STR);
        }
        if (isset($params[':user_id'])) {
            $countStmt->bindValue(':user_id', $params[':user_id'], PDO::PARAM_INT);
        }
        $countStmt->execute();

        $formatted = [];
        foreach ($orders as $order) {
            $order['products'] = $this->getOrderItems((int) $order['id']);
            $formatted[] = $order;
        }

        return [
            'orders' => $formatted,
            'total' => (int) $countStmt->fetchColumn(),
        ];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $order = $stmt->fetch();

        if ($order === false) {
            return null;
        }

        $order['products'] = $this->getOrderItems((int) $order['id']);
        return $order;
    }

    public function create(int $userId, array $items, ?string $shippingAddress, ?string $notes): ?array
    {
        $productModel = new Product();

        try {
            $this->db->beginTransaction();

            $totalAmount = 0.0;
            $orderItems = [];

            foreach ($items as $item) {
                $productId = (int) ($item['productId'] ?? 0);
                $quantity = (int) ($item['quantity'] ?? 0);

                $product = $productModel->findById($productId);
                if ($product === null) {
                    $this->db->rollBack();
                    return null;
                }

                if ($quantity <= 0 || (int) $product['stock'] < $quantity) {
                    $this->db->rollBack();
                    return null;
                }

                if (!$productModel->decrementStock($productId, $quantity)) {
                    $this->db->rollBack();
                    return null;
                }

                $price = (float) $product['price'];
                $totalAmount += ($price * $quantity);

                $orderItems[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $price,
                ];
            }

            $stmt = $this->db->prepare('INSERT INTO orders (user_id, total_amount, status, shipping_address, notes) VALUES (:user_id, :total_amount, :status, :shipping_address, :notes)');
            $stmt->execute([
                ':user_id' => $userId,
                ':total_amount' => $totalAmount,
                ':status' => 'pending',
                ':shipping_address' => $shippingAddress,
                ':notes' => $notes,
            ]);

            $orderId = (int) $this->db->lastInsertId();

            $itemStmt = $this->db->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)');
            foreach ($orderItems as $orderItem) {
                $itemStmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $orderItem['product_id'],
                    ':quantity' => $orderItem['quantity'],
                    ':price' => $orderItem['price'],
                ]);
            }

            $this->db->commit();
            return $this->findById($orderId);
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
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

        $sql = 'UPDATE orders SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $this->findById($id);
    }

    private function getOrderItems(int $orderId): array
    {
        $stmt = $this->db->prepare('SELECT product_id, quantity, price FROM order_items WHERE order_id = :order_id');
        $stmt->execute([':order_id' => $orderId]);
        $rows = $stmt->fetchAll();

        return array_map(static function (array $row): array {
            return [
                'productId' => (int) $row['product_id'],
                'quantity' => (int) $row['quantity'],
                'price' => (float) $row['price'],
            ];
        }, $rows);
    }

    public static function formatForResponse(array $order): array
    {
        return [
            'id' => (int) $order['id'],
            'userId' => (int) $order['user_id'],
            'products' => $order['products'] ?? [],
            'totalAmount' => (float) $order['total_amount'],
            'status' => $order['status'],
            'shippingAddress' => $order['shipping_address'] ?? null,
            'notes' => $order['notes'] ?? null,
            'createdAt' => self::toIso8601($order['created_at'] ?? null),
            'updatedAt' => self::toIso8601($order['updated_at'] ?? null),
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
