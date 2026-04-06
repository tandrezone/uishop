<?php

declare(strict_types=1);

/**
 * UIShop API - Main Entry Point
 * PSR-compliant REST API
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Required files
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load environment variables
\App\Core\Environment::load();

// Enable error logging if debug mode
if (\App\Core\Environment::get('APP_DEBUG') === 'true') {
    ini_set('display_errors', '1');
}

// Set JSON response header for all requests
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle CORS preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Create request object
    $request = new \App\Core\Request();

    // Clean up path
    $request->setPath(preg_replace('/^\/api/', '', $request->getPath()));

    // Load routes
    $router = require_once dirname(__DIR__) . '/config/routes.php';

    // Dispatch request
    $router->dispatch($request);
} catch (Throwable $e) {
    $response = new \App\Core\Response();

    if (\App\Core\Environment::get('APP_DEBUG') === 'true') {
        $response->error(
            $e->getMessage(),
            500,
            ['trace' => $e->getTraceAsString()]
        );
    } else {
        $response->error('Internal Server Error', 500);
    }
}
