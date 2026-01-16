<?php

namespace SellNow\Repositories;

use PDO;
use SellNow\Models\User;

/**
 * UserRepository: Data access layer for User
 * Responsibility: All database operations for users go through here
 */
class UserRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find user by ID
     */
    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? User::fromArray($data) : null;
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? User::fromArray($data) : null;
    }

    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? User::fromArray($data) : null;
    }

    /**
     * Save a new user (insert)
     */
    public function create(User $user): int
    {
        $data = $user->toArray();
        $stmt = $this->db->prepare(
            "INSERT INTO users (email, username, Full_Name, password) 
             VALUES (?, ?, ?, ?)"
        );

        $stmt->execute([
            $data['email'],
            $data['username'],
            $data['full_name'],
            $user->getPasswordHash()
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Check if email already exists
     */
    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Check if username already exists
     */
    public function usernameExists(string $username): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
