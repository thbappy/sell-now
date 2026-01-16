<?php

namespace SellNow\Services;

use SellNow\Models\User;
use SellNow\Repositories\UserRepository;
use SellNow\Security\Validator;

/**
 * AuthService: Business logic for authentication
 * Responsibility: Register, login, session management
 */
class AuthService
{
    private UserRepository $userRepository;
    private Validator $validator;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->validator = new Validator();
    }

    /**
     * Register a new user
     * Returns array with 'success', 'user', and 'errors'
     */
    public function register(string $email, string $username, string $fullName, string $password): array
    {
        // Validate inputs
        $this->validator->clearErrors();
        $this->validator->validateEmail($email);
        $this->validator->validateUsername($username);
        $this->validator->validatePassword($password);

        if ($this->validator->hasErrors()) {
            return [
                'success' => false,
                'errors' => $this->validator->getErrors(),
            ];
        }

        // Check if email or username already exists
        if ($this->userRepository->emailExists($email)) {
            return [
                'success' => false,
                'errors' => ['email' => 'Email already registered'],
            ];
        }

        if ($this->userRepository->usernameExists($username)) {
            return [
                'success' => false,
                'errors' => ['username' => 'Username already taken'],
            ];
        }

        // Create user
        $user = new User(
            email: $email,
            username: $username,
            full_name: $fullName,
            password_hash: '' // Will be set next
        );

        $user->setPassword($password);

        try {
            $userId = $this->userRepository->create($user);
            $user = $this->userRepository->findById($userId);

            return [
                'success' => true,
                'user' => $user,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['general' => 'Registration failed: ' . $e->getMessage()],
            ];
        }
    }

    /**
     * Authenticate user with email and password
     */
    public function login(string $email, string $password): array
    {
        if (empty($email) || empty($password)) {
            return [
                'success' => false,
                'errors' => ['general' => 'Email and password required'],
            ];
        }

        $user = $this->userRepository->findByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            return [
                'success' => false,
                'errors' => ['general' => 'Invalid email or password'],
            ];
        }

        return [
            'success' => true,
            'user' => $user,
        ];
    }

    /**
     * Start session for authenticated user
     */
    public function startSession(User $user): void
    {
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['username'] = $user->getUsername();
        $_SESSION['email'] = $user->getEmail();
    }

    /**
     * Get current authenticated user
     */
    public function getCurrentUser(): ?User
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return $this->userRepository->findById($_SESSION['user_id']);
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Logout current user
     */
    public function logout(): void
    {
        session_destroy();
    }
}
