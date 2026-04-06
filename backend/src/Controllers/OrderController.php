<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\Order;
use App\Models\User;

/**
 * Order endpoints
 */
final class OrderController
{
    private Response $response;
    private Order $orderModel;
    private User $userModel;

    public function __construct()
    {
        $this->response = new Response();
        $this->orderModel = new Order();
        $this->userModel = new User();
    }

    public function index(Request $request): void
    {
        $user = Auth::user($request, $this->response, $this->userModel);

        $status = $request->getQueryParam('status');
        $limit = max(1, min(100, (int) $request->getQueryParam('limit', 20)));
        $offset = max(0, (int) $request->getQueryParam('offset', 0));

        $scopeUserId = ($user['role'] === 'admin') ? null : (int) $user['id'];
        $result = $this->orderModel->findAll(is_string($status) ? $status : null, $limit, $offset, $scopeUserId);

        $this->response->success([
            'orders' => array_map(static fn(array $order): array => Order::formatForResponse($order), $result['orders']),
            'total' => $result['total'],
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function myOrders(Request $request): void
    {
        $user = Auth::user($request, $this->response, $this->userModel);

        $status = $request->getQueryParam('status');
        $limit = max(1, min(100, (int) $request->getQueryParam('limit', 20)));
        $offset = max(0, (int) $request->getQueryParam('offset', 0));

        $result = $this->orderModel->findAll(is_string($status) ? $status : null, $limit, $offset, (int) $user['id']);

        $this->response->success([
            'orders' => array_map(static fn(array $order): array => Order::formatForResponse($order), $result['orders']),
            'total' => $result['total'],
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function show(Request $request, string $id): void
    {
        $user = Auth::user($request, $this->response, $this->userModel);
        $orderId = (int) $id;

        if ($orderId <= 0) {
            $this->response->error('Validation failed', 400, ['message' => 'Invalid order id']);
        }

        $order = $this->orderModel->findById($orderId);
        if ($order === null) {
            $this->response->error('Order not found', 404);
        }

        if ($user['role'] !== 'admin' && (int) $order['user_id'] !== (int) $user['id']) {
            $this->response->error('Forbidden', 403);
        }

        $this->response->success(Order::formatForResponse($order));
    }

    public function create(Request $request): void
    {
        $user = Auth::user($request, $this->response, $this->userModel);

        $products = $request->getBodyParam('products', []);
        if (!is_array($products) || $products === []) {
            $this->response->error('Validation failed', 400, ['message' => 'Products array is required']);
        }

        foreach ($products as $item) {
            if (!is_array($item) || !isset($item['productId']) || !isset($item['quantity'])) {
                $this->response->error('Validation failed', 400, ['message' => 'Each item must include productId and quantity']);
            }

            if ((int) $item['quantity'] <= 0) {
                $this->response->error('Validation failed', 400, ['message' => 'Quantity must be greater than 0']);
            }
        }

        $order = $this->orderModel->create(
            (int) $user['id'],
            $products,
            $request->getBodyParam('shippingAddress'),
            $request->getBodyParam('notes')
        );

        if ($order === null) {
            $this->response->error('Validation failed', 400, ['message' => 'Product not found or insufficient stock']);
        }

        $this->response->success(Order::formatForResponse($order), 201);
    }

    public function update(Request $request, string $id): void
    {
        $user = Auth::user($request, $this->response, $this->userModel);
        $orderId = (int) $id;

        if ($orderId <= 0) {
            $this->response->error('Validation failed', 400, ['message' => 'Invalid order id']);
        }

        $existing = $this->orderModel->findById($orderId);
        if ($existing === null) {
            $this->response->error('Order not found', 404);
        }

        $body = $request->getBody();
        $fields = [];

        if ($user['role'] === 'admin') {
            if (array_key_exists('status', $body)) {
                $allowed = ['pending', 'processing', 'completed', 'cancelled'];
                $status = (string) $body['status'];
                if (!in_array($status, $allowed, true)) {
                    $this->response->error('Validation failed', 400, ['message' => 'Invalid status']);
                }
                $fields['status'] = $status;
            }
            if (array_key_exists('shippingAddress', $body)) {
                $fields['shipping_address'] = $body['shippingAddress'];
            }
            if (array_key_exists('notes', $body)) {
                $fields['notes'] = $body['notes'];
            }
        } else {
            if ((int) $existing['user_id'] !== (int) $user['id']) {
                $this->response->error('Forbidden', 403);
            }
            if (array_key_exists('shippingAddress', $body)) {
                $fields['shipping_address'] = $body['shippingAddress'];
            }
            if (array_key_exists('notes', $body)) {
                $fields['notes'] = $body['notes'];
            }
        }

        $updated = $this->orderModel->update($orderId, $fields);
        if ($updated === null) {
            $this->response->error('Order not found', 404);
        }

        $this->response->success(Order::formatForResponse($updated));
    }
}
