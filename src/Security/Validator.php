<?php

namespace SellNow\Security;

/**
 * Validator: Input validation
 * Responsibility: Validate all user input safely
 */
class Validator
{
    private array $errors = [];

    public function validateEmail(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Invalid email format';
            return false;
        }
        return true;
    }

    public function validateUsername(string $username): bool
    {
        if (strlen($username) < 3) {
            $this->errors['username'] = 'Username must be at least 3 characters';
            return false;
        }
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            $this->errors['username'] = 'Username can only contain letters, numbers, dash and underscore';
            return false;
        }
        return true;
    }

    public function validatePassword(string $password): bool
    {
        if (strlen($password) < 6) {
            $this->errors['password'] = 'Password must be at least 6 characters';
            return false;
        }
        return true;
    }

    public function validatePrice(float $price): bool
    {
        if ($price <= 0) {
            $this->errors['price'] = 'Price must be greater than 0';
            return false;
        }
        if ($price > 999999.99) {
            $this->errors['price'] = 'Price is too high';
            return false;
        }
        return true;
    }

    public function validateTitle(string $title): bool
    {
        if (strlen($title) < 3) {
            $this->errors['title'] = 'Title must be at least 3 characters';
            return false;
        }
        if (strlen($title) > 255) {
            $this->errors['title'] = 'Title is too long';
            return false;
        }
        return true;
    }

    public function validateFile(array $file, int $maxSizeBytes = 52428800): bool // 50MB default
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors['file'] = $this->getUploadErrorMessage($file['error']);
            return false;
        }

        if ($file['size'] > $maxSizeBytes) {
            $this->errors['file'] = 'File is too large';
            return false;
        }

        return true;
    }

    public function validateImage(array $file, int $maxSizeBytes = 5242880): bool // 5MB default
    {
        if (!$this->validateFile($file, $maxSizeBytes)) {
            return false;
        }

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed)) {
            $this->errors['image'] = 'Invalid image type. Only JPEG, PNG, GIF, and WebP allowed';
            return false;
        }

        return true;
    }

    private function getUploadErrorMessage(int $errorCode): string
    {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds PHP upload limit',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form limit',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
            UPLOAD_ERR_EXTENSION => 'File upload blocked by extension',
        ];
        return $messages[$errorCode] ?? 'Unknown upload error';
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    public function clearErrors(): void
    {
        $this->errors = [];
    }
}
