<?php

namespace SellNow\Foundation;

/**
 * Request: Encapsulates HTTP request data
 * Responsibility: Provide safe, validated access to $_GET, $_POST, $_FILES, $_SERVER
 */
class Request
{
    private array $get;
    private array $post;
    private array $files;
    private array $server;
    private string $method;
    private string $path;

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->files = $_FILES;
        $this->server = $_SERVER;
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function input(string $key, $default = null): mixed
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    public function get(string $key, $default = null): mixed
    {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    public function has(string $key): bool
    {
        return isset($this->post[$key]) || isset($this->get[$key]);
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    public function header(string $key, $default = null): mixed
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$key] ?? $default;
    }
}
