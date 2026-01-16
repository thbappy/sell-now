<?php

namespace SellNow\Services;

/**
 * CartService: Business logic for shopping cart
 * Responsibility: Manage cart items in session
 */
class CartService
{
    private const SESSION_KEY = 'cart';

    /**
     * Add product to cart
     */
    public function addToCart(int $productId, string $title, float $price, int $quantity = 1): bool
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }

        // Check if product already in cart
        foreach ($_SESSION[self::SESSION_KEY] as &$item) {
            if ($item['product_id'] == $productId) {
                $item['quantity'] += $quantity;
                return true;
            }
        }

        // Add new item
        $_SESSION[self::SESSION_KEY][] = [
            'product_id' => $productId,
            'title' => $title,
            'price' => $price,
            'quantity' => $quantity,
        ];

        return true;
    }

    /**
     * Get all cart items
     */
    public function getCartItems(): array
    {
        return $_SESSION[self::SESSION_KEY] ?? [];
    }

    /**
     * Get cart total
     */
    public function getCartTotal(): float
    {
        $total = 0;
        foreach ($this->getCartItems() as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return round($total, 2);
    }

    /**
     * Get cart item count
     */
    public function getCartCount(): int
    {
        return count($this->getCartItems());
    }

    /**
     * Clear cart
     */
    public function clearCart(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }

    /**
     * Check if cart is empty
     */
    public function isEmpty(): bool
    {
        return empty($_SESSION[self::SESSION_KEY]);
    }
}
