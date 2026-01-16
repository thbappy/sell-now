<?php

namespace SellNow\Models;

/**
 * Product Model
 * Responsibility: Encapsulate product data and validation
 */
class Product extends Model
{
    private int $id;
    private int $user_id;
    private string $title;
    private string $slug;
    private string $description;
    private float $price;
    private string $image_path;
    private string $file_path;
    private bool $is_active;
    private string $created_at;
    private string $updated_at;

    public function __construct(
        int $user_id = 0,
        string $title = '',
        string $slug = '',
        string $description = '',
        float $price = 0.0,
        string $image_path = '',
        string $file_path = '',
        bool $is_active = true,
        int $id = 0,
        string $created_at = '',
        string $updated_at = ''
    ) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->title = $title;
        $this->slug = $slug ?: $this->generateSlug($title);
        $this->description = $description;
        $this->price = $price;
        $this->image_path = $image_path;
        $this->file_path = $file_path;
        $this->is_active = $is_active;
        $this->created_at = $created_at ?: date('Y-m-d H:i:s');
        $this->updated_at = $updated_at ?: date('Y-m-d H:i:s');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getImagePath(): string
    {
        return $this->image_path;
    }

    public function getFilePath(): string
    {
        return $this->file_path;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): string
    {
        return $this->updated_at;
    }

    public function validate(): array
    {
        $errors = [];

        if (empty($this->title) || strlen($this->title) < 3) {
            $errors['title'] = 'Title must be at least 3 characters';
        }

        if ($this->price <= 0) {
            $errors['price'] = 'Price must be greater than 0';
        }

        if ($this->user_id <= 0) {
            $errors['user_id'] = 'Valid user is required';
        }

        return $errors;
    }

    private function generateSlug(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug .= '-' . random_int(10000, 99999);
        return $slug;
    }

    public static function fromArray(array $data): Product
    {
        return new Product(
            user_id: (int)($data['user_id'] ?? 0),
            title: $data['title'] ?? '',
            slug: $data['slug'] ?? '',
            description: $data['description'] ?? '',
            price: (float)($data['price'] ?? 0),
            image_path: $data['image_path'] ?? '',
            file_path: $data['file_path'] ?? '',
            is_active: (bool)($data['is_active'] ?? true),
            id: (int)($data['product_id'] ?? $data['id'] ?? 0),
            created_at: $data['created_at'] ?? '',
            updated_at: $data['updated_at'] ?? ''
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'image_path' => $this->image_path,
            'file_path' => $this->file_path,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
