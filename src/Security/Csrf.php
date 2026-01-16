<?php

namespace SellNow\Security;

/**
 * Csrf: CSRF token generation and validation
 * Responsibility: Prevent cross-site request forgery attacks
 */
class Csrf
{
    private const TOKEN_LENGTH = 32;
    private const SESSION_KEY = '_csrf_token';

    /**
     * Generate or retrieve CSRF token
     */
    public static function token(): string
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(self::TOKEN_LENGTH));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Validate CSRF token from request
     */
    public static function validate(string $token): bool
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            return false;
        }

        return hash_equals($_SESSION[self::SESSION_KEY], $token);
    }

    /**
     * Validate token from POST request
     */
    public static function validateRequest(): bool
    {
        $token = $_POST['_csrf_token'] ?? '';
        return self::validate($token);
    }
}
