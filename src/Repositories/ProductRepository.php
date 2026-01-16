<?php

namespace SellNow\Repositories;

use PDO;
use SellNow\Models\Product;

/**
 * ProductRepository: Data access layer for Product
 * Responsibility: All database operations for products
 */
class ProductRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find product by ID
     */
    public function findById(int $id): ?Product
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? Product::fromArray($data) : null;
    }

    /**
     * Find products by user (seller)
     */
    public function findByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE user_id = ? AND is_active = 1 ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($data) => Product::fromArray($data), $results);
    }

    /**
     * Find all active products (for public listing)
     */
    public function findActive(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($data) => Product::fromArray($data), $results);
    }

    /**
     * Create a new product
     */
    public function create(Product $product): int
    {
        $data = $product->toArray();
        $stmt = $this->db->prepare(
            "INSERT INTO products (user_id, title, slug, description, price, image_path, file_path, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $data['user_id'],
            $data['title'],
            $data['slug'],
            $data['description'],
            $data['price'],
            $data['image_path'],
            $data['file_path'],
            (int)$data['is_active']
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update a product
     */
    public function update(Product $product): bool
    {
        $data = $product->toArray();
        $stmt = $this->db->prepare(
            "UPDATE products SET title = ?, description = ?, price = ?, image_path = ?, file_path = ?, is_active = ? 
             WHERE product_id = ?"
        );

        return $stmt->execute([
            $data['title'],
            $data['description'],
            $data['price'],
            $data['image_path'],
            $data['file_path'],
            (int)$data['is_active'],
            $data['id']
        ]);
    }

    /**
     * Delete (deactivate) a product
     */
    public function delete(int $productId): bool
    {
        $stmt = $this->db->prepare("UPDATE products SET is_active = 0 WHERE product_id = ?");
        return $stmt->execute([$productId]);
    }

    /**
     * Check if product belongs to user
     */
    public function belongsToUser(int $productId, int $userId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE product_id = ? AND user_id = ?");
        $stmt->execute([$productId, $userId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
