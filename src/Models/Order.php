<?php

namespace SellNow\Models;

/**
 * Order Model
 * Responsibility: Encapsulate order data
 */
class Order extends Model
{
    private int $id;
    private int $user_id;
    private float $total_amount;
    private string $payment_provider;
    private string $payment_status;
    private string $transaction_id;
    private string $order_date;
    private string $created_at;

    public function __construct(
        int $user_id = 0,
        float $total_amount = 0.0,
        string $payment_provider = '',
        string $payment_status = 'pending',
        string $transaction_id = '',
        string $order_date = '',
        int $id = 0,
        string $created_at = ''
    ) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->total_amount = $total_amount;
        $this->payment_provider = $payment_provider;
        $this->payment_status = $payment_status;
        $this->transaction_id = $transaction_id;
        $this->order_date = $order_date ?: date('Y-m-d H:i:s');
        $this->created_at = $created_at ?: date('Y-m-d H:i:s');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getTotalAmount(): float
    {
        return $this->total_amount;
    }

    public function getPaymentProvider(): string
    {
        return $this->payment_provider;
    }

    public function getPaymentStatus(): string
    {
        return $this->payment_status;
    }

    public function setPaymentStatus(string $status): void
    {
        $validStatuses = ['pending', 'completed', 'failed', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid payment status: $status");
        }
        $this->payment_status = $status;
    }

    public function getTransactionId(): string
    {
        return $this->transaction_id;
    }

    public function getOrderDate(): string
    {
        return $this->order_date;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function validate(): array
    {
        $errors = [];

        if ($this->user_id <= 0) {
            $errors['user_id'] = 'Valid user is required';
        }

        if ($this->total_amount <= 0) {
            $errors['total_amount'] = 'Total amount must be greater than 0';
        }

        if (empty($this->payment_provider)) {
            $errors['payment_provider'] = 'Payment provider is required';
        }

        return $errors;
    }

    public static function fromArray(array $data): Order
    {
        return new Order(
            user_id: (int)($data['user_id'] ?? 0),
            total_amount: (float)($data['total_amount'] ?? 0),
            payment_provider: $data['payment_provider'] ?? '',
            payment_status: $data['payment_status'] ?? 'pending',
            transaction_id: $data['transaction_id'] ?? '',
            order_date: $data['order_date'] ?? '',
            id: (int)($data['id'] ?? 0),
            created_at: $data['created_at'] ?? ''
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'total_amount' => $this->total_amount,
            'payment_provider' => $this->payment_provider,
            'payment_status' => $this->payment_status,
            'transaction_id' => $this->transaction_id,
            'order_date' => $this->order_date,
            'created_at' => $this->created_at,
        ];
    }
}
