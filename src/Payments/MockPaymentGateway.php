<?php

namespace SellNow\Payments;

/**
 * MockPaymentGateway: Test payment provider
 * Responsibility: Simulate payment for testing/demo purposes
 */
class MockPaymentGateway implements PaymentGatewayInterface
{
    private string $name = 'Mock Provider';

    public function charge(float $amount, string $currency = 'USD'): array
    {
        // Simulate payment processing
        $transactionId = 'MOCK_' . strtoupper(uniqid());

        // In a real scenario, you'd call the payment provider's API here
        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'message' => 'Payment processed successfully (mock)',
        ];
    }

    public function verify(string $transactionId): array
    {
        // In a real scenario, verify with payment provider's API
        if (strpos($transactionId, 'MOCK_') === 0) {
            return [
                'success' => true,
                'status' => 'completed',
            ];
        }

        return [
            'success' => false,
            'status' => 'failed',
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isConfigured(): bool
    {
        return true; // Mock is always configured
    }
}
