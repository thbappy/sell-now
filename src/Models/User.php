<?php

namespace SellNow\Models;

use PDO;

/**
 * User Model
 * Responsibility: Encapsulate user data and behavior
 */
class User extends Model
{
    private int $id;
    private string $email;
    private string $username;
    private string $full_name;
    private string $password_hash;
    private string $created_at;
    private string $updated_at;

    public function __construct(
        string $email = '',
        string $username = '',
        string $full_name = '',
        string $password_hash = '',
        int $id = 0,
        string $created_at = '',
        string $updated_at = ''
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->username = $username;
        $this->full_name = $full_name;
        $this->password_hash = $password_hash;
        $this->created_at = $created_at ?: date('Y-m-d H:i:s');
        $this->updated_at = $updated_at ?: date('Y-m-d H:i:s');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getFullName(): string
    {
        return $this->full_name;
    }

    public function getPasswordHash(): string
    {
        return $this->password_hash;
    }

    public function setPassword(string $plainPassword): void
    {
        $this->password_hash = password_hash($plainPassword, PASSWORD_BCRYPT);
    }

    public function verifyPassword(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->password_hash);
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

        if (empty($this->email) || !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email is required';
        }

        if (empty($this->username) || strlen($this->username) < 3) {
            $errors['username'] = 'Username must be at least 3 characters';
        }

        if (empty($this->password_hash)) {
            $errors['password'] = 'Password is required';
        }

        return $errors;
    }

    public static function fromArray(array $data): User
    {
        return new User(
            email: $data['email'] ?? '',
            username: $data['username'] ?? '',
            full_name: $data['Full_Name'] ?? $data['full_name'] ?? '',
            password_hash: $data['password'] ?? '',
            id: (int)($data['id'] ?? 0),
            created_at: $data['created_at'] ?? '',
            updated_at: $data['updated_at'] ?? ''
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'username' => $this->username,
            'full_name' => $this->full_name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
