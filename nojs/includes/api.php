<?php
declare(strict_types=1);

/**
 * API helper functions
 */

/**
 * Make API request
 */
function apiRequest(string $method, string $endpoint, ?array $data = null, ?string $token = null): array
{
    $url = API_BASE_URL . $endpoint;
    
    $options = [
        'http' => [
            'method' => $method,
            'header' => [
                'Content-Type: application/json',
            ],
            'ignore_errors' => true,
        ]
    ];
    
    // Add authorization token if provided
    if ($token) {
        $options['http']['header'][] = 'Authorization: Bearer ' . $token;
    }
    
    // Add request body for POST, PUT requests
    if ($data && in_array($method, ['POST', 'PUT'])) {
        $options['http']['content'] = json_encode($data);
    }
    
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    
    // Parse response headers to get status code
    $statusCode = 500;
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
                $statusCode = (int)$matches[1];
                break;
            }
        }
    }
    
    $result = [
        'success' => $statusCode >= 200 && $statusCode < 300,
        'status' => $statusCode,
        'data' => null,
        'error' => null
    ];
    
    if ($response !== false) {
        $decoded = json_decode($response, true);
        if ($result['success']) {
            $result['data'] = $decoded;
        } else {
            $result['error'] = $decoded['error'] ?? $decoded['message'] ?? 'Unknown error';
        }
    } else {
        $result['error'] = 'Failed to connect to API';
    }
    
    return $result;
}

/**
 * API Login
 */
function apiLogin(string $username, string $password): array
{
    return apiRequest('POST', '/auth/login', [
        'username' => $username,
        'password' => $password
    ]);
}

/**
 * API Register
 */
function apiRegister(string $username, string $password, ?string $email = null): array
{
    $data = [
        'username' => $username,
        'password' => $password
    ];
    
    if ($email) {
        $data['email'] = $email;
    }
    
    return apiRequest('POST', '/auth/register', $data);
}

/**
 * API Get Products
 */
function apiGetProducts(?string $search = null, int $limit = 100, int $offset = 0): array
{
    $token = $_SESSION['token'] ?? null;
    $query = http_build_query([
        'search' => $search,
        'limit' => $limit,
        'offset' => $offset
    ]);
    
    $endpoint = '/products' . ($query ? '?' . $query : '');
    return apiRequest('GET', $endpoint, null, $token);
}

/**
 * API Create Product
 */
function apiCreateProduct(array $data): array
{
    $token = $_SESSION['token'] ?? null;
    return apiRequest('POST', '/products', $data, $token);
}

/**
 * API Update Product
 */
function apiUpdateProduct(string $id, array $data): array
{
    $token = $_SESSION['token'] ?? null;
    return apiRequest('PUT', '/products/' . $id, $data, $token);
}

/**
 * API Delete Product
 */
function apiDeleteProduct(string $id): array
{
    $token = $_SESSION['token'] ?? null;
    return apiRequest('DELETE', '/products/' . $id, null, $token);
}

/**
 * API Get Cart
 */
function apiGetCart(): array
{
    $token = $_SESSION['token'] ?? null;
    return apiRequest('GET', '/cart', null, $token);
}

/**
 * API Add to Cart
 */
function apiAddToCart(int $productId, int $quantity = 1): array
{
    $token = $_SESSION['token'] ?? null;
    return apiRequest('POST', '/cart/items', [
        'productId' => $productId,
        'quantity' => $quantity
    ], $token);
}

/**
 * API Update Cart Item
 */
function apiUpdateCartItem(int $id, int $quantity): array
{
    $token = $_SESSION['token'] ?? null;
    return apiRequest('PUT', '/cart/items/' . $id, [
        'quantity' => $quantity
    ], $token);
}

/**
 * API Remove from Cart
 */
function apiRemoveFromCart(int $id): array
{
    $token = $_SESSION['token'] ?? null;
    return apiRequest('DELETE', '/cart/items/' . $id, null, $token);
}

/**
 * API Clear Cart
 */
function apiClearCart(): array
{
    $token = $_SESSION['token'] ?? null;
    return apiRequest('DELETE', '/cart', null, $token);
}

/**
 * API Checkout
 */
function apiCheckout(?string $shippingAddress = null, ?string $notes = null): array
{
    $token = $_SESSION['token'] ?? null;
    $data = [];
    
    if ($shippingAddress) {
        $data['shippingAddress'] = $shippingAddress;
    }
    
    if ($notes) {
        $data['notes'] = $notes;
    }
    
    return apiRequest('POST', '/cart/checkout', $data, $token);
}

/**
 * API Get Orders
 */
function apiGetOrders(?string $status = null, int $limit = 100, int $offset = 0): array
{
    $token = $_SESSION['token'] ?? null;
    $query = http_build_query(array_filter([
        'status' => $status,
        'limit' => $limit,
        'offset' => $offset
    ]));
    
    $endpoint = '/orders' . ($query ? '?' . $query : '');
    return apiRequest('GET', $endpoint, null, $token);
}

/**
 * API Get My Orders
 */
function apiGetMyOrders(?string $status = null, int $limit = 100, int $offset = 0): array
{
    $token = $_SESSION['token'] ?? null;
    $query = http_build_query(array_filter([
        'status' => $status,
        'limit' => $limit,
        'offset' => $offset
    ]));
    
    $endpoint = '/orders/my' . ($query ? '?' . $query : '');
    return apiRequest('GET', $endpoint, null, $token);
}

/**
 * API Get Order
 */
function apiGetOrder(string $id): array
{
    $token = $_SESSION['token'] ?? null;
    return apiRequest('GET', '/orders/' . $id, null, $token);
}

/**
 * API Update Order
 */
function apiUpdateOrder(string $id, array $data): array
{
    $token = $_SESSION['token'] ?? null;
    return apiRequest('PUT', '/orders/' . $id, $data, $token);
}

/**
 * API Get Profile
 */
function apiGetProfile(): array
{
    $token = $_SESSION['token'] ?? null;
    return apiRequest('GET', '/users/profile', null, $token);
}

/**
 * API Update Profile
 */
function apiUpdateProfile(array $data): array
{
    $token = $_SESSION['token'] ?? null;
    return apiRequest('PUT', '/users/profile', $data, $token);
}
