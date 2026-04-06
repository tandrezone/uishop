<?php

declare(strict_types=1);

namespace App\Core;

/**
 * HTTP Response handler
 */
final class Response
{
    private int $statusCode = 200;
    private array $headers = ['Content-Type' => 'application/json; charset=utf-8'];
    private mixed $data;

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setData(mixed $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Send success response
     */
    public function success(mixed $data, int $statusCode = 200): void
    {
        $this->setStatusCode($statusCode)
            ->setData($data)
            ->send();
    }

    /**
     * Send error response
     */
    public function error(string $message, int $statusCode = 400, array $data = []): void
    {
        $this->setStatusCode($statusCode)
            ->setData([
                'error' => $message,
                'status' => $statusCode,
                ...$data,
            ])
            ->send();
    }

    /**
     * Send no content response
     */
    public function noContent(): void
    {
        $this->setStatusCode(204)->send();
    }

    /**
     * Send the response
     */
    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        if ($this->statusCode !== 204 && $this->data !== null) {
            echo json_encode($this->data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        exit;
    }
}
