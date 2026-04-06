<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\Product;
use App\Models\User;

/**
 * Product endpoints
 */
final class ProductController
{
    private Response $response;
    private Product $productModel;
    private User $userModel;

    public function __construct()
    {
        $this->response = new Response();
        $this->productModel = new Product();
        $this->userModel = new User();
    }

    public function index(Request $request): void
    {
        Auth::user($request, $this->response, $this->userModel);

        $search = $request->getQueryParam('search');
        $limit = max(1, min(100, (int) $request->getQueryParam('limit', 20)));
        $offset = max(0, (int) $request->getQueryParam('offset', 0));

        $result = $this->productModel->findAll(is_string($search) ? $search : null, $limit, $offset);

        $this->response->success([
            'products' => array_map(static fn(array $product): array => Product::formatForResponse($product), $result['products']),
            'total' => $result['total'],
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function create(Request $request): void
    {
        $user = Auth::user($request, $this->response, $this->userModel);
        Auth::requireAdmin($user, $this->response);

        $name = trim((string) $request->getBodyParam('name', ''));
        $price = $request->getBodyParam('price');
        $stock = $request->getBodyParam('stock');

        if ($name === '') {
            $this->response->error('Validation failed', 400, ['message' => 'Name is required']);
        }

        if (!is_numeric($price) || (float) $price <= 0) {
            $this->response->error('Validation failed', 400, ['message' => 'Price must be greater than 0']);
        }

        if (!is_numeric($stock) || (int) $stock < 0) {
            $this->response->error('Validation failed', 400, ['message' => 'Stock must be 0 or greater']);
        }

        $product = $this->productModel->create([
            'name' => $name,
            'description' => $request->getBodyParam('description'),
            'price' => (float) $price,
            'stock' => (int) $stock,
            'image' => $request->getBodyParam('image'),
            'category' => $request->getBodyParam('category'),
        ], (int) $user['id']);

        if ($product === null) {
            $this->response->error('Failed to create product', 500);
        }

        $this->response->success(Product::formatForResponse($product), 201);
    }

    public function update(Request $request, string $id): void
    {
        $user = Auth::user($request, $this->response, $this->userModel);
        Auth::requireAdmin($user, $this->response);

        $productId = (int) $id;
        if ($productId <= 0) {
            $this->response->error('Validation failed', 400, ['message' => 'Invalid product id']);
        }

        $existing = $this->productModel->findById($productId);
        if ($existing === null) {
            $this->response->error('Product not found', 404);
        }

        $body = $request->getBody();
        $allowed = ['name', 'description', 'price', 'stock', 'image', 'category'];
        $fields = [];

        foreach ($allowed as $key) {
            if (array_key_exists($key, $body)) {
                $fields[$key] = $body[$key];
            }
        }

        if (isset($fields['name']) && trim((string) $fields['name']) === '') {
            $this->response->error('Validation failed', 400, ['message' => 'Name cannot be empty']);
        }

        if (isset($fields['price']) && (!is_numeric($fields['price']) || (float) $fields['price'] <= 0)) {
            $this->response->error('Validation failed', 400, ['message' => 'Price must be greater than 0']);
        }

        if (isset($fields['stock']) && (!is_numeric($fields['stock']) || (int) $fields['stock'] < 0)) {
            $this->response->error('Validation failed', 400, ['message' => 'Stock must be 0 or greater']);
        }

        if (isset($fields['name'])) {
            $fields['name'] = trim((string) $fields['name']);
        }
        if (isset($fields['price'])) {
            $fields['price'] = (float) $fields['price'];
        }
        if (isset($fields['stock'])) {
            $fields['stock'] = (int) $fields['stock'];
        }

        $updated = $this->productModel->update($productId, $fields);
        if ($updated === null) {
            $this->response->error('Product not found', 404);
        }

        $this->response->success(Product::formatForResponse($updated));
    }

    public function delete(Request $request, string $id): void
    {
        $user = Auth::user($request, $this->response, $this->userModel);
        Auth::requireAdmin($user, $this->response);

        $productId = (int) $id;
        if ($productId <= 0) {
            $this->response->error('Validation failed', 400, ['message' => 'Invalid product id']);
        }

        $existing = $this->productModel->findById($productId);
        if ($existing === null) {
            $this->response->error('Product not found', 404);
        }

        $this->productModel->delete($productId);
        $this->response->noContent();
    }
}
