<?php

namespace SellNow\Foundation;

/**
 * Response: Encapsulates HTTP response
 * Responsibility: Handle redirects, rendering, JSON responses with proper headers
 */
class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private string|array $body = '';

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function setHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function json(array $data, int $statusCode = 200): self
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'application/json');
        $this->body = json_encode($data);
        return $this;
    }

    public function html(string $html, int $statusCode = 200): self
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'text/html; charset=utf-8');
        $this->body = $html;
        return $this;
    }

    public function redirect(string $url, int $statusCode = 302): void
    {
        header("Location: $url", true, $statusCode);
        exit;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        echo $this->body;
    }

    public function getBody(): string|array
    {
        return $this->body;
    }
}
