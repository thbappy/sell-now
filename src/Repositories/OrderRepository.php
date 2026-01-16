<?php

namespace SellNow\Repositories;

use PDO;
use SellNow\Models\Order;

/**
 * OrderRepository: Data access layer for Order
 * Responsibility: All database operations for orders
 */
class OrderRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find order by ID
     */
    public function findById(int $id): ?Order
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? Order::fromArray($data) : null;
    }

    /**
     * Find orders by user
     */
    public function findByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
        $stmt->execute([$userId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($data) => Order::fromArray($data), $results);
    }

    /**
     * Create a new order
     */
    public function create(Order $order): int
    {
        $data = $order->toArray();
        $stmt = $this->db->prepare(
            "INSERT INTO orders (user_id, total_amount, payment_provider, payment_status, transaction_id, order_date)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $data['user_id'],
            $data['total_amount'],
            $data['payment_provider'],
            $data['payment_status'],
            $data['transaction_id'],
            $data['order_date']
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update order payment status
     */
    public function updatePaymentStatus(int $orderId, string $status, string $transactionId = ''): bool
    {
        if (!empty($transactionId)) {
            $stmt = $this->db->prepare("UPDATE orders SET payment_status = ?, transaction_id = ? WHERE id = ?");
            return $stmt->execute([$status, $transactionId, $orderId]);
        }

        $stmt = $this->db->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
        return $stmt->execute([$status, $orderId]);
    }

    /**
     * Find order by transaction ID
     */
    public function findByTransactionId(string $transactionId): ?Order
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE transaction_id = ?");
        $stmt->execute([$transactionId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? Order::fromArray($data) : null;
    }
}
