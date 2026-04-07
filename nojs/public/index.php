<?php
declare(strict_types=1);

/**
 * NoJS UIShop - Server-Side Rendered Version
 * No JavaScript - Pure HTML and CSS with PHP backend
 */

// Start session for authentication
session_start();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Base path configuration
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('VIEWS_PATH', BASE_PATH . '/views');
define('INCLUDES_PATH', BASE_PATH . '/includes');

// Load configuration
require_once BASE_PATH . '/../backend/vendor/autoload.php';
\App\Core\Environment::load();

// API configuration - construct based on current server
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
define('API_BASE_URL', $protocol . '://' . $host . '/api');

// Helper functions
require_once INCLUDES_PATH . '/helpers.php';
require_once INCLUDES_PATH . '/api.php';

// Get the current page
$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? 'index';

// Handle logout
if ($page === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Check authentication
$isAuthenticated = isset($_SESSION['token']) && !empty($_SESSION['token']);
$user = $_SESSION['user'] ?? null;

// Redirect to login if not authenticated (except for auth pages)
if (!$isAuthenticated && !in_array($page, ['login', 'register', 'auth'])) {
    header('Location: index.php?page=login');
    exit;
}

// Redirect to products if authenticated and trying to access login/register
if ($isAuthenticated && in_array($page, ['login', 'register'])) {
    header('Location: index.php?page=products');
    exit;
}

// Route handling
switch ($page) {
    case 'login':
    case 'register':
        require_once BASE_PATH . '/controllers/auth.php';
        break;
    case 'auth':
        require_once BASE_PATH . '/controllers/auth_handler.php';
        break;
    case 'products':
        require_once BASE_PATH . '/controllers/products.php';
        break;
    case 'cart':
        require_once BASE_PATH . '/controllers/cart.php';
        break;
    case 'orders':
        require_once BASE_PATH . '/controllers/orders.php';
        break;
    case 'profile':
        require_once BASE_PATH . '/controllers/profile.php';
        break;
    case 'home':
    default:
        header('Location: index.php?page=products');
        exit;
}
