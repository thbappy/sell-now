<?php

namespace SellNow\Foundation;

/**
 * Container: Simple dependency injection container
 * Responsibility: Register and resolve dependencies (Factory pattern)
 */
class Container
{
    private static ?Container $instance = null;
    private array $bindings = [];
    private array $instances = [];

    private function __construct() {}

    public static function getInstance(): Container
    {
        if (self::$instance === null) {
            self::$instance = new Container();
        }
        return self::$instance;
    }

    /**
     * Bind a service to the container
     * @param string $abstract The interface or name
     * @param callable|string $concrete The class or closure to instantiate
     */
    public function bind(string $abstract, callable|string $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    /**
     * Register a singleton instance
     */
    public function singleton(string $abstract, $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * Resolve a service from the container
     */
    public function make(string $abstract): mixed
    {
        // If already instantiated as singleton, return it
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (!isset($this->bindings[$abstract])) {
            throw new \Exception("Service [$abstract] not found in container");
        }

        $concrete = $this->bindings[$abstract];

        if (is_callable($concrete)) {
            return $concrete($this);
        }

        // Try to instantiate the class
        if (class_exists($concrete)) {
            return new $concrete();
        }

        throw new \Exception("Cannot resolve [$abstract]");
    }

    /**
     * Get all registered bindings (for debugging)
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }
}
