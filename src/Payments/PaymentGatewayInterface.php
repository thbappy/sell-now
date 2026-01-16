<?php

namespace SellNow\Payments;

/**
 * PaymentGatewayInterface: Contract for payment providers
 * Responsibility: Define interface that all payment providers must implement
 * This allows swapping providers without changing application code (Strategy Pattern)
 */
interface PaymentGatewayInterface
{
    /**
     * Process a payment
     * Returns: ['success' => bool, 'transaction_id' => string, 'message' => string]
     */
    public function charge(float $amount, string $currency = 'USD'): array;

    /**
     * Verify a payment (webhook)
     */
    public function verify(string $transactionId): array;

    /**
     * Get provider name
     */
    public function getName(): string;

    /**
     * Check if provider is configured
     */
    public function isConfigured(): bool;
}
