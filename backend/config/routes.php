<?php

declare(strict_types=1);

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\ProductController;
use App\Controllers\OrderController;
use App\Controllers\UserController;
use App\Controllers\CartController;

/**
 * Application routes
 */
$router = new Router();

// Authentication routes
$authController = new AuthController();
$productController = new ProductController();
$orderController = new OrderController();
$userController = new UserController();
$cartController = new CartController();

$router->route('POST', '/auth/login', function (\App\Core\Request $request) use ($authController) {
    $authController->login($request);
});

$router->route('POST', '/auth/register', function (\App\Core\Request $request) use ($authController) {
    $authController->register($request);
});

// Product routes
$router->route('GET', '/products', function (\App\Core\Request $request) use ($productController) {
    $productController->index($request);
});

$router->route('POST', '/products', function (\App\Core\Request $request) use ($productController) {
    $productController->create($request);
});

$router->route('PUT', '/products/{id}', function (\App\Core\Request $request, string $id) use ($productController) {
    $productController->update($request, $id);
});

$router->route('DELETE', '/products/{id}', function (\App\Core\Request $request, string $id) use ($productController) {
    $productController->delete($request, $id);
});

// Order routes
$router->route('GET', '/orders', function (\App\Core\Request $request) use ($orderController) {
    $orderController->index($request);
});

$router->route('GET', '/orders/my', function (\App\Core\Request $request) use ($orderController) {
    $orderController->myOrders($request);
});

$router->route('GET', '/orders/{id}', function (\App\Core\Request $request, string $id) use ($orderController) {
    $orderController->show($request, $id);
});

$router->route('POST', '/orders', function (\App\Core\Request $request) use ($orderController) {
    $orderController->create($request);
});

$router->route('PUT', '/orders/{id}', function (\App\Core\Request $request, string $id) use ($orderController) {
    $orderController->update($request, $id);
});

// User routes
$router->route('GET', '/users/profile', function (\App\Core\Request $request) use ($userController) {
    $userController->profile($request);
});

$router->route('PUT', '/users/profile', function (\App\Core\Request $request) use ($userController) {
    $userController->updateProfile($request);
});

// Cart routes
$router->route('GET', '/cart', function (\App\Core\Request $request) use ($cartController) {
    $cartController->index($request);
});

$router->route('POST', '/cart/items', function (\App\Core\Request $request) use ($cartController) {
    $cartController->addItem($request);
});

$router->route('PUT', '/cart/items/{id}', function (\App\Core\Request $request, string $id) use ($cartController) {
    $cartController->updateItem($request, $id);
});

$router->route('DELETE', '/cart/items/{id}', function (\App\Core\Request $request, string $id) use ($cartController) {
    $cartController->removeItem($request, $id);
});

$router->route('DELETE', '/cart', function (\App\Core\Request $request) use ($cartController) {
    $cartController->clear($request);
});

return $router;
