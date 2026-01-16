<?php

namespace SellNow\Models;

use PDO;

/**
 * Model: Base class for all entities
 * Responsibility: Define common attributes, timestamps, relationships
 */
abstract class Model
{
    protected int $id;
    protected string $created_at;
    protected string $updated_at;
    protected static PDO $connection;

    public static function setConnection(PDO $connection): void
    {
        self::$connection = $connection;
    }

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at ?? '';
    }

    public function getUpdatedAt(): string
    {
        return $this->updated_at ?? '';
    }

    protected static function getConnection(): PDO
    {
        if (!isset(self::$connection)) {
            throw new \Exception("Database connection not set in Model");
        }
        return self::$connection;
    }
}
