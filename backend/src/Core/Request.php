<?php

declare(strict_types=1);

namespace App\Core;

/**
 * HTTP request wrapper
 */
final class Request
{
    private string $method;
    private string $path;
    private array $query;
    private array $headers;
    private array $body;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $this->query = $_GET ?? [];
        $this->headers = $this->collectHeaders();
        $this->body = $this->parseBody();
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $normalized = trim($path);
        $this->path = $normalized === '' ? '/' : $normalized;
    }

    public function getQueryParam(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function getBodyParam(string $key, $default = null)
    {
        return $this->body[$key] ?? $default;
    }

    public function getHeader(string $name): ?string
    {
        $normalized = strtolower($name);
        return $this->headers[$normalized] ?? null;
    }

    public function getBearerToken(): ?string
    {
        $authorization = $this->getHeader('Authorization');

        if ($authorization === null) {
            return null;
        }

        if (preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches) !== 1) {
            return null;
        }

        return trim($matches[1]);
    }

    private function collectHeaders(): array
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (!is_string($value)) {
                continue;
            }

            if (strpos($key, 'HTTP_') === 0) {
                $headerName = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$headerName] = $value;
            }

            if ($key === 'CONTENT_TYPE') {
                $headers['content-type'] = $value;
            }

            if ($key === 'CONTENT_LENGTH') {
                $headers['content-length'] = $value;
            }
        }

        return $headers;
    }

    private function parseBody(): array
    {
        $raw = file_get_contents('php://input');

        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $contentType = strtolower((string) $this->getHeader('Content-Type'));

        if (strpos($contentType, 'application/json') !== false) {
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }

        parse_str($raw, $parsed);
        return is_array($parsed) ? $parsed : [];
    }
}
