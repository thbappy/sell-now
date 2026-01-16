<?php

namespace SellNow\Services;

use SellNow\Models\Order;
use SellNow\Repositories\OrderRepository;

/**
 * CheckoutService: Business logic for checkout
 * Responsibility: Order creation, payment processing
 */
class CheckoutService
{
    private OrderRepository $orderRepository;
    private CartService $cartService;

    public function __construct(OrderRepository $orderRepository, CartService $cartService)
    {
        $this->orderRepository = $orderRepository;
        $this->cartService = $cartService;
    }

    /**
     * Create order from cart
     */
    public function createOrder(int $userId, string $paymentProvider): array
    {
        if ($this->cartService->isEmpty()) {
            return [
                'success' => false,
                'errors' => ['cart' => 'Cart is empty'],
            ];
        }

        $totalAmount = $this->cartService->getCartTotal();

        if ($totalAmount <= 0) {
            return [
                'success' => false,
                'errors' => ['total' => 'Invalid order total'],
            ];
        }

        $order = new Order(
            user_id: $userId,
            total_amount: $totalAmount,
            payment_provider: $paymentProvider,
            payment_status: 'pending'
        );

        try {
            $orderId = $this->orderRepository->create($order);
            $order = $this->orderRepository->findById($orderId);

            return [
                'success' => true,
                'order' => $order,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['general' => 'Failed to create order'],
            ];
        }
    }

    /**
     * Simulate payment completion
     */
    public function completePayment(int $orderId, string $transactionId): array
    {
        $order = $this->orderRepository->findById($orderId);

        if (!$order) {
            return [
                'success' => false,
                'errors' => ['order' => 'Order not found'],
            ];
        }

        try {
            $this->orderRepository->updatePaymentStatus($orderId, 'completed', $transactionId);
            $this->cartService->clearCart();

            return [
                'success' => true,
                'order' => $this->orderRepository->findById($orderId),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['general' => 'Failed to complete payment'],
            ];
        }
    }

    /**
     * Get user's orders
     */
    public function getUserOrders(int $userId): array
    {
        return $this->orderRepository->findByUserId($userId);
    }
}
