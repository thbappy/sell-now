<?php

namespace SellNow\Config;

use PDO;
use PDOException;

/**
 * Database: Enhanced connection management
 * Responsibility: Create and manage database connection
 * Support: SQLite (default) and MySQL
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $connection;
    private string $driver;

    private function __construct()
    {
        $driver = getenv('DB_DRIVER') ?: 'mysql';
        $this->driver = $driver;

        try {
            if ($driver === 'mysql') {
                $this->connectMySQL();
            } else {
                $this->connectSQLite();
            }

            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new \Exception("Database Connection Error: " . $e->getMessage());
        }
    }

    private function connectSQLite(): void
    {
        // Check if SQLite driver is available
        if (!in_array('sqlite', PDO::getAvailableDrivers())) {
            throw new \Exception(
                "SQLite PDO driver not available. " .
                "Available drivers: " . implode(', ', PDO::getAvailableDrivers()) .
                ". Please enable pdo_sqlite or use MySQL instead (DB_DRIVER=mysql)."
            );
        }

        $dbPath = getenv('DB_PATH') ?: __DIR__ . '/../../database/database.sqlite';
        
        if (!file_exists($dbPath)) {
            touch($dbPath);
        }

        $this->connection = new PDO("sqlite:" . $dbPath);
    }

    private function connectMySQL(): void
    {
        // Check if MySQL driver is available
        if (!in_array('mysql', PDO::getAvailableDrivers())) {
            throw new \Exception(
                "MySQL PDO driver not available. " .
                "Available drivers: " . implode(', ', PDO::getAvailableDrivers()) .
                ". Please enable pdo_mysql."
            );
        }

        $host = getenv('DB_HOST') ?: 'localhost';
        $database = getenv('DB_NAME') ?: 'sellnow';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: '0k';

        $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
        $this->connection = new PDO($dsn, $username, $password);
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * Get list of available PDO drivers
     */
    public static function getAvailableDrivers(): array
    {
        return PDO::getAvailableDrivers();
    }
}

