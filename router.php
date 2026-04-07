<?php
/**
 * Router script for PHP built-in server
 * Usage: php -S localhost:8000 router.php
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// For real files in frontend directory, serve them directly
if (preg_match('/^\/((css|js|assets)\/.*|favicon\.ico)$/', $uri)) {
    $file = __DIR__ . '/frontend' . $uri;
    if (file_exists($file) && is_file($file)) {
        // Serve the file with proper MIME type
        $extension = pathinfo($file, PATHINFO_EXTENSION);
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
        
        readfile($file);
        return true;
    }
}

// Route through index.php
require __DIR__ . '/index.php';

