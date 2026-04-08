<?php
declare(strict_types=1);

/**
 * UIShop - Global Entry Point
 * Routes requests to appropriate modules:
 * - / -> Frontend (SPA)
 * - /api -> Backend API
 * - /nojs -> NoJS version (server-side rendered)
 */

// Get the request URI and remove query string
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normalize the URI (remove trailing slashes except for root)
$requestUri = rtrim($requestUri, '/');
if (empty($requestUri)) {
    $requestUri = '/';
}

// Route to appropriate module
if ($requestUri === '/' || $requestUri === '') {
    // Serve frontend
    serveFrontend();
} elseif (strpos($requestUri, '/api') === 0) {
    // Route to backend API
    serveBackendAPI();
} elseif (strpos($requestUri, '/nojs') === 0) {
    // Route to NoJS version
    serveNoJS();
} else {
    // Check if it's a frontend asset
    $filePath = __DIR__ . '/frontend' . $requestUri;
    if (file_exists($filePath) && is_file($filePath)) {
        serveFrontendAsset($filePath);
    } else {
        // Default to frontend for client-side routing
        serveFrontend();
    }
}

/**
 * Serve the frontend SPA
 */
function serveFrontend(): void
{
    $indexPath = __DIR__ . '/frontend/index.html';
    if (file_exists($indexPath)) {
        header('Content-Type: text/html; charset=utf-8');
        readfile($indexPath);
    } else {
        http_response_code(500);
        echo 'Frontend not found';
    }
    exit;
}

/**
 * Serve frontend assets (CSS, JS, images)
 */
function serveFrontendAsset(string $filePath): void
{
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
    ];

    if (isset($mimeTypes[$extension])) {
        header('Content-Type: ' . $mimeTypes[$extension]);
    }
    
    readfile($filePath);
    exit;
}

/**
 * Route to backend API
 */
function serveBackendAPI(): void
{
    // Set up the request for the backend
    $_SERVER['SCRIPT_NAME'] = '/backend/public/index.php';
    
    // Include the backend entry point
    require __DIR__ . '/backend/public/index.php';
    exit;
}

/**
 * Route to NoJS version
 */
function serveNoJS(): void
{
    // Remove /nojs prefix from the request URI for the nojs router
    $originalUri = $_SERVER['REQUEST_URI'];
    $_SERVER['REQUEST_URI'] = preg_replace('#^/nojs#', '', $originalUri);
    
    // Update script name for nojs
    $_SERVER['SCRIPT_NAME'] = '/nojs/public/index.php';
    
    // Change to nojs directory for correct relative paths
    chdir(__DIR__ . '/nojs/public');
    
    // Include the nojs entry point
    require __DIR__ . '/nojs/public/index.php';
    exit;
}
