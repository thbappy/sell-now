<?php

namespace SellNow\Payments;

/**
 * PaymentGatewayFactory: Create payment gateway instances
 * Responsibility: Manage which payment provider to use
 * This allows easy switching between providers
 */
class PaymentGatewayFactory
{
    private static array $gateways = [];

    /**
     * Register a payment gateway provider
     */
    public static function register(string $name, PaymentGatewayInterface $gateway): void
    {
        self::$gateways[$name] = $gateway;
    }

    /**
     * Get a payment gateway by name
     */
    public static function get(string $name): ?PaymentGatewayInterface
    {
        // Default to mock if not found
        if (!isset(self::$gateways[$name])) {
            return new MockPaymentGateway();
        }

        return self::$gateways[$name];
    }

    /**
     * Get all available gateways
     */
    public static function all(): array
    {
        return self::$gateways;
    }
}
