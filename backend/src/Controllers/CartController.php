<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;

/**
 * Cart endpoints
 */
final class CartController
{
    private Response $response;
    private Cart $cartModel;
    private Product $productModel;
    private User $userModel;

    public function __construct()
    {
        $this->response = new Response();
        $this->cartModel = new Cart();
        $this->productModel = new Product();
        $this->userModel = new User();
    }

    public function index(Request $request): void
    {
        $user = Auth::user($request, $this->response, $this->userModel);

        $items = $this->cartModel->findByUserId((int) $user['id']);

        $formatted = array_map(static fn(array $item): array => Cart::formatForResponse($item), $items);

        $totalAmount = array_reduce($formatted, static function (float $sum, array $item): float {
            return $sum + ((float) $item['product']['price'] * (int) $item['quantity']);
        }, 0.0);

        $this->response->success([
            'items' => $formatted,
            'totalAmount' => $totalAmount,
            'totalItems' => count($formatted),
        ]);
    }

    public function addItem(Request $request): void
    {
        $user = Auth::user($request, $this->response, $this->userModel);

        $productId = (int) $request->getBodyParam('productId', 0);
        $quantity = (int) $request->getBodyParam('quantity', 1);

        if ($productId <= 0) {
            $this->response->error('Validation failed', 400, ['message' => 'Invalid product id']);
        }

        if ($quantity <= 0) {
            $this->response->error('Validation failed', 400, ['message' => 'Quantity must be greater than 0']);
        }

        $product = $this->productModel->findById($productId);
        if ($product === null) {
            $this->response->error('Product not found', 404);
        }

        if ((int) $product['stock'] < $quantity) {
            $this->response->error('Validation failed', 400, ['message' => 'Not enough stock available']);
        }

        $item = $this->cartModel->addItem((int) $user['id'], $productId, $quantity);

        if ($item === null) {
            $this->response->error('Failed to add item to cart', 500);
        }

        $this->response->success(Cart::formatForResponse($item), 201);
    }

    public function updateItem(Request $request, string $id): void
    {
        $user = Auth::user($request, $this->response, $this->userModel);

        $itemId = (int) $id;
        if ($itemId <= 0) {
            $this->response->error('Validation failed', 400, ['message' => 'Invalid cart item id']);
        }

        $quantity = (int) $request->getBodyParam('quantity', 0);

        if ($quantity <= 0) {
            $this->response->error('Validation failed', 400, ['message' => 'Quantity must be greater than 0']);
        }

        $items = $this->cartModel->findByUserId((int) $user['id']);
        $itemExists = false;
        $productId = null;

        foreach ($items as $item) {
            if ((int) $item['id'] === $itemId) {
                $itemExists = true;
                $productId = (int) $item['product_id'];
                break;
            }
        }

        if (!$itemExists) {
            $this->response->error('Cart item not found', 404);
        }

        $product = $this->productModel->findById($productId);
        if ($product === null) {
            $this->response->error('Product no longer available', 404);
        }

        if ((int) $product['stock'] < $quantity) {
            $this->response->error('Validation failed', 400, ['message' => 'Not enough stock available']);
        }

        $updated = $this->cartModel->updateQuantity($itemId, $quantity);

        if ($updated === null) {
            $this->response->error('Cart item not found', 404);
        }

        $this->response->success(Cart::formatForResponse($updated));
    }

    public function removeItem(Request $request, string $id): void
    {
        $user = Auth::user($request, $this->response, $this->userModel);

        $itemId = (int) $id;
        if ($itemId <= 0) {
            $this->response->error('Validation failed', 400, ['message' => 'Invalid cart item id']);
        }

        $success = $this->cartModel->removeItem($itemId, (int) $user['id']);

        if (!$success) {
            $this->response->error('Cart item not found or access denied', 404);
        }

        $this->response->noContent();
    }

    public function clear(Request $request): void
    {
        $user = Auth::user($request, $this->response, $this->userModel);

        $this->cartModel->clearCart((int) $user['id']);

        $this->response->noContent();
    }
}
