<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Router for handling HTTP routes
 */
final class Router
{
    private array $routes = [];

    /**
     * Register a route
     */
    public function route(string $method, string $path, callable $handler): self
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
        ];

        return $this;
    }

    /**
     * Get route handler
     */
    public function dispatch(Request $request): void
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            // Simple path matching (no regex, just exact match)
            if ($route['path'] === $path) {
                call_user_func($route['handler'], $request);
                return;
            }

            // Path matching with parameters
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $path, $matches)) {
                // Filter only named groups
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                call_user_func($route['handler'], $request, ...$params);
                return;
            }
        }

        $response = new Response();
        $response->error('Route not found', 404);
    }
}
